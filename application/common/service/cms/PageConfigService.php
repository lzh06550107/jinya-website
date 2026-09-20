<?php

namespace app\common\service\cms;

/**
 * 固定页面、功能块及公共布局的即时保存服务。
 */
class PageConfigService
{
    protected $store;
    protected $cacheClearer;

    public function __construct(PageConfigStoreInterface $store = null, callable $cacheClearer = null)
    {
        $this->store = $store ?: new ThinkPageConfigStore();
        $this->cacheClearer = $cacheClearer ?: function (array $keys) {
            CacheService::clearKeys($keys);
        };
    }

    public function saveBlock($blockId, $expectedVersion, array $payload, array $references, $adminId)
    {
        $current = $this->requireCurrent('page_block', $blockId, $expectedVersion);
        $input = isset($payload['config']) && is_array($payload['config']) ? $payload['config'] : [];
        $config = PageSchemaRegistry::sanitizeBlockConfig($current['page_key'], $current['block_key'], $input);
        $normalizedReferences = $this->normalizeReferences($references);
        $this->store->assertReferencesPublishable($normalizedReferences);

        $pcVisible = isset($config['pc_visible']) ? (int)$config['pc_visible'] : (int)$current['pc_visible'];
        $mobileVisible = isset($config['mobile_visible']) ? (int)$config['mobile_visible'] : (int)$current['mobile_visible'];
        $enabled = isset($config['enabled']) ? (int)$config['enabled'] : ($current['status'] === 'normal' ? 1 : 0);
        unset($config['pc_visible'], $config['mobile_visible'], $config['enabled']);

        $updated = $this->store->transaction(function () use ($blockId, $expectedVersion, $config, $pcVisible, $mobileVisible, $enabled, $normalizedReferences, $adminId, $current) {
            $row = $this->store->updateVersioned('page_block', $blockId, $expectedVersion, [
                'config_json' => $this->encode($config),
                'pc_visible' => $pcVisible,
                'mobile_visible' => $mobileVisible,
                'status' => $enabled ? 'normal' : 'hidden',
            ]);
            if (!$row) {
                throw $this->conflict();
            }
            $this->store->replaceReferences($blockId, $normalizedReferences);
            return $row;
        });

        $this->clearPages([$current['page_key']]);
        return $updated;
    }

    public function savePage($pageId, $expectedVersion, array $payload, $adminId)
    {
        $current = $this->requireCurrent('page_config', $pageId, $expectedVersion);
        PageSchemaRegistry::page($current['page_key']);

        $layoutFields = ['pc_header_key', 'pc_footer_key', 'mobile_header_key', 'mobile_footer_key'];
        $data = [];
        foreach ($layoutFields as $field) {
            $value = isset($payload[$field]) ? trim((string)$payload[$field]) : (string)$current[$field];
            if ($value === '' || !preg_match('/^layout\.[a-z0-9_.-]+$/', $value)) {
                throw new \InvalidArgumentException('公共布局标识不合法：' . $field);
            }
            $data[$field] = $value;
        }
        $layoutRows = $this->store->findLayoutsByKeys(array_values($data));
        $this->assertLayoutBindings($data, $layoutRows);
        $inputConfig = isset($payload['config']) && is_array($payload['config']) ? $payload['config'] : [];
        $config = PageSchemaRegistry::sanitizePageConfig($current['page_key'], $inputConfig);
        $data['config_json'] = $this->encode($config);

        $updated = $this->store->transaction(function () use ($pageId, $expectedVersion, $data, $adminId, $current, $config) {
            $row = $this->store->updateVersioned('page_config', $pageId, $expectedVersion, $data);
            if (!$row) {
                throw $this->conflict();
            }
            return $row;
        });

        $this->clearPages([$current['page_key']]);
        return $updated;
    }

    public function saveLayout($layoutId, $expectedVersion, array $payload, $adminId)
    {
        $current = $this->requireCurrent('layout_component', $layoutId, $expectedVersion);
        $input = isset($payload['config']) && is_array($payload['config']) ? $payload['config'] : [];
        $config = LayoutSchemaRegistry::sanitize($current['component_type'], $input);

        $updated = $this->store->transaction(function () use ($layoutId, $expectedVersion, $config, $adminId, $current) {
            $row = $this->store->updateVersioned('layout_component', $layoutId, $expectedVersion, [
                'config_json' => $this->encode($config),
            ]);
            if (!$row) {
                throw $this->conflict();
            }
            return $row;
        });

        $pageKeys = $this->store->findPageKeysByLayout($current['component_key']);
        $this->clearPages($pageKeys);
        return $updated;
    }

    /**
     * 前台稳定读取接口。数据库记录不存在时仍返回注册表默认结构。
     */
    public function resolvePage($pageKey, $device)
    {
        if (!in_array($device, ['pc', 'mobile'], true)) {
            throw new \InvalidArgumentException('终端类型必须是 pc 或 mobile');
        }
        $pageSchema = PageSchemaRegistry::page($pageKey);
        $pageRow = $this->store->findPageByKey($pageKey);
        $rawPageConfig = $pageRow ? $this->decode(isset($pageRow['config_json']) ? $pageRow['config_json'] : '') : [];
        try {
            $pageConfig = PageSchemaRegistry::sanitizePageConfig($pageKey, $rawPageConfig);
        } catch (\InvalidArgumentException $e) {
            $pageConfig = PageSchemaRegistry::sanitizePageConfig($pageKey, []);
        }
        $blocks = $this->store->findBlocksByPage($pageKey);
        $byKey = [];
        $blockIds = [];
        foreach ($blocks as $row) {
            $byKey[$row['block_key']] = $row;
            $blockIds[] = (int)$row['id'];
        }
        $references = $this->store->findReferencesByBlockIds($blockIds);

        $resolvedBlocks = [];
        foreach ($pageSchema['blocks'] as $blockKey => $schema) {
            $row = isset($byKey[$blockKey]) ? $byKey[$blockKey] : null;
            $visibleField = $device === 'pc' ? 'pc_visible' : 'mobile_visible';
            // 核心结构块不能被历史脏数据隐藏。旧版本曾允许首页块停用，
            // 导致升级后只剩零散区块，与冻结页面结构不一致。
            $visible = !empty($schema['core'])
                || !$row
                || ((int)$row[$visibleField] === 1 && $row['status'] === 'normal');
            if (!$visible) {
                continue;
            }
            $rawConfigJson = $row && isset($row['config_json']) ? trim((string)$row['config_json']) : '';
            $rawConfig = $row ? $this->decode($rawConfigJson) : [];
            $baseConfig = [
                'pc_visible' => $row ? (int)$row['pc_visible'] : 1,
                'mobile_visible' => $row ? (int)$row['mobile_visible'] : 1,
                'enabled' => !$row || $row['status'] === 'normal' ? 1 : 0,
            ];
            try {
                $config = PageSchemaRegistry::sanitizeBlockConfig($pageKey, $blockKey, array_merge($baseConfig, $rawConfig));
            } catch (\InvalidArgumentException $e) {
                // 历史异常配置不能拖垮整个前台页面，回退到 schema 默认值。
                $config = PageSchemaRegistry::sanitizeBlockConfig($pageKey, $blockKey, $baseConfig);
            }
            $config = PageSchemaRegistry::resolveDeviceImages($schema, $config, $device);
            $blockReferences = $row && isset($references[(int)$row['id']]) ? $references[(int)$row['id']] : [];
            $resolvedBlocks[$blockKey] = [
                'key' => $blockKey,
                'name' => $schema['name'],
                'type' => $schema['type'],
                'source_type' => $schema['source_type'],
                // 安装器会预建空记录。只有保存过非空配置或存在手动引用时，才接管旧首页数据。
                'configured' => !in_array($rawConfigJson, ['', '{}', '[]', 'null'], true) || !empty($blockReferences),
                'config' => $config,
                'references' => $blockReferences,
            ];
        }

        $layoutKeys = $this->layoutKeys($pageRow);
        $layouts = $this->store->findLayoutsByKeys(array_values($layoutKeys));
        $resolvedLayouts = [];
        foreach ($layoutKeys as $position => $layoutKey) {
            $layout = isset($layouts[$layoutKey]) ? $layouts[$layoutKey] : null;
            $resolvedLayouts[$position] = [
                'key' => $layoutKey,
                'config' => $layout ? $this->decode(isset($layout['config_json']) ? $layout['config_json'] : '') : [],
                'status' => $layout && isset($layout['status']) ? $layout['status'] : 'normal',
            ];
        }

        return [
            'page_key' => $pageKey,
            'page_name' => $pageSchema['name'],
            'page_type' => $pageSchema['type'],
            'device' => $device,
            'config' => $pageConfig,
            'layouts' => $resolvedLayouts,
            'blocks' => $resolvedBlocks,
        ];
    }

    protected function requireCurrent($entity, $id, $expectedVersion)
    {
        $current = $this->store->find($entity, $id);
        if (!$current) {
            throw new \InvalidArgumentException('配置记录不存在');
        }
        if ((int)$current['version'] !== (int)$expectedVersion) {
            throw $this->conflict();
        }
        return $current;
    }

    protected function conflict()
    {
        return new PageConfigConflictException('该配置已被其他用户修改，请重新加载页面后再保存');
    }

    protected function normalizeReferences(array $references)
    {
        $allowedTypes = ['product', 'article', 'case', 'page'];
        $result = [];
        $seen = [];
        foreach ($references as $index => $reference) {
            if (!is_array($reference)) {
                throw new \InvalidArgumentException('业务内容引用格式不正确');
            }
            $type = isset($reference['content_type']) ? trim((string)$reference['content_type']) : '';
            $id = isset($reference['content_id']) ? (int)$reference['content_id'] : 0;
            if (!in_array($type, $allowedTypes, true) || $id <= 0) {
                throw new \InvalidArgumentException('业务内容引用不合法');
            }
            $unique = $type . ':' . $id;
            if (isset($seen[$unique])) {
                continue;
            }
            $seen[$unique] = true;
            $result[] = [
                'content_type' => $type,
                'content_id' => $id,
                'weigh' => isset($reference['weigh']) ? (int)$reference['weigh'] : (count($references) - $index),
            ];
        }
        return $result;
    }

    protected function assertLayoutBindings(array $bindings, array $layoutRows)
    {
        $requirements = [
            'pc_header_key' => ['type' => 'header', 'devices' => ['pc', 'all']],
            'pc_footer_key' => ['type' => 'footer', 'devices' => ['pc', 'all']],
            'mobile_header_key' => ['type' => 'header', 'devices' => ['mobile', 'all']],
            'mobile_footer_key' => ['type' => 'footer', 'devices' => ['mobile', 'all']],
        ];
        foreach ($requirements as $field => $requirement) {
            $key = $bindings[$field];
            if (!isset($layoutRows[$key])) {
                throw new \InvalidArgumentException('所选公共布局不存在：' . $key);
            }
            $layout = $layoutRows[$key];
            if (isset($layout['status']) && $layout['status'] !== 'normal') {
                throw new \InvalidArgumentException('所选公共布局已停用：' . $key);
            }
            if (isset($layout['component_type']) && $layout['component_type'] !== $requirement['type']) {
                throw new \InvalidArgumentException('公共布局类型不匹配：' . $key);
            }
            if (isset($layout['device']) && !in_array($layout['device'], $requirement['devices'], true)) {
                throw new \InvalidArgumentException('公共布局终端不匹配：' . $key);
            }
        }
    }

    protected function sanitizeGenericConfig(array $config, $depth = 0)
    {
        if ($depth > 8) {
            throw new \InvalidArgumentException('配置嵌套层级过深');
        }
        $result = [];
        foreach ($config as $key => $value) {
            $key = trim((string)$key);
            if ($key === '' || !preg_match('/^[a-zA-Z0-9_.-]+$/', $key)) {
                throw new \InvalidArgumentException('配置字段名称不合法');
            }
            if (is_array($value)) {
                $result[$key] = $this->sanitizeGenericConfig($value, $depth + 1);
                continue;
            }
            if (!is_scalar($value) && $value !== null) {
                throw new \InvalidArgumentException('配置字段值不合法：' . $key);
            }
            if (is_string($value)) {
                $value = trim($value);
                if (strlen($value) > 10000) {
                    throw new \InvalidArgumentException('配置字段内容过长：' . $key);
                }
                if ((stripos($key, 'url') !== false || stripos($key, 'link') !== false) && $value !== '' && !$this->isSafeLink($value)) {
                    throw new \InvalidArgumentException('配置包含不安全链接：' . $key);
                }
                if (preg_match('/<\s*script\b/i', $value)) {
                    throw new \InvalidArgumentException('配置中不允许包含脚本');
                }
            }
            $result[$key] = $value;
        }
        return $result;
    }

    protected function isSafeLink($value)
    {
        if ($value === '' || $value[0] === '/' || $value[0] === '#' || $value[0] === '?') {
            return true;
        }
        $scheme = parse_url($value, PHP_URL_SCHEME);
        return $scheme !== null && in_array(strtolower($scheme), ['http', 'https', 'tel', 'mailto'], true);
    }

    protected function clearPages(array $pageKeys)
    {
        $keys = [];
        foreach (array_values(array_unique(array_filter(array_map('strval', $pageKeys)))) as $pageKey) {
            $keys[] = 'cms:page-config:' . $pageKey . ':pc';
            $keys[] = 'cms:page-config:' . $pageKey . ':mobile';
        }
        if ($keys) {
            call_user_func($this->cacheClearer, $keys);
        }
    }

    protected function layoutKeys($pageRow)
    {
        return [
            'pc_header' => $pageRow && !empty($pageRow['pc_header_key']) ? $pageRow['pc_header_key'] : 'layout.header.pc',
            'pc_footer' => $pageRow && !empty($pageRow['pc_footer_key']) ? $pageRow['pc_footer_key'] : 'layout.footer.pc',
            'mobile_header' => $pageRow && !empty($pageRow['mobile_header_key']) ? $pageRow['mobile_header_key'] : 'layout.header.mobile',
            'mobile_footer' => $pageRow && !empty($pageRow['mobile_footer_key']) ? $pageRow['mobile_footer_key'] : 'layout.footer.mobile',
        ];
    }

    protected function encode($value)
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \InvalidArgumentException('配置无法转换为 JSON');
        }
        return $json;
    }

    protected function decode($json)
    {
        $decoded = json_decode((string)$json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
