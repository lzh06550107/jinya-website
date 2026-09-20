<?php

namespace app\common\service\cms\layout_editor;

use app\common\service\cms\InstallerService;
use app\common\service\cms\LayoutSchemaRegistry;
use app\common\service\cms\PageConfigConflictException;
use app\common\service\cms\SiteConfigDefinitionRegistry;
use app\common\service\cms\render\CmsCacheInvalidator;

/**
 * 8 个固定公共布局组件的统一聚合编辑服务。
 */
class LayoutUnifiedEditorService
{
    private $store;
    private $siteRefresher;
    private $cacheInvalidator;

    public function __construct(LayoutEditorStoreInterface $store = null, callable $siteRefresher = null, callable $cacheInvalidator = null)
    {
        $this->store = $store ?: new ThinkLayoutEditorStore();
        $this->siteRefresher = $siteRefresher ?: function () {
            (new InstallerService())->refreshSiteConfig();
        };
        $this->cacheInvalidator = $cacheInvalidator ?: function () {
            (new CmsCacheInvalidator())->invalidateLayout();
        };
    }

    public function load($layoutId)
    {
        $row = $this->requireLayoutById($layoutId);
        $definition = LayoutEditorRegistry::definition($row['component_key']);
        $siteDefinitions = SiteConfigDefinitionRegistry::pick($definition['site_fields']);
        $siteValues = $this->store->loadSiteConfig($definition['site_fields']);
        $siteFields = [];
        foreach ($siteDefinitions as $name => $fieldDefinition) {
            $siteFields[$name] = [
                'definition' => $fieldDefinition,
                'value' => array_key_exists($name, $siteValues) ? (string)$siteValues[$name] : (string)$fieldDefinition['default'],
            ];
        }

        $navigation = $definition['navigation_position'] !== null
            ? $this->store->loadNavigation($definition['navigation_position'])
            : [];
        $config = $this->decodeConfig($row);
        $related = [];
        foreach ($definition['related_components'] as $key) {
            $child = $this->store->findLayoutByKey($key);
            if ($child) {
                $child['config'] = $this->decodeConfig($child);
                $related[$key] = $child;
            }
        }

        $hotSearchItems = [];
        if ($definition['list_type'] === 'hot_search') {
            $hotSearchItems = $this->normalizeHotSearchForLoad(isset($config['items']) && is_array($config['items']) ? $config['items'] : []);
        }

        $socialItems = [];
        if ($definition['list_type'] === 'social' || in_array('layout.footer.qrcode', $definition['related_components'], true)) {
            $qrcodeRow = $row['component_key'] === 'layout.footer.qrcode'
                ? $row
                : (isset($related['layout.footer.qrcode']) ? $related['layout.footer.qrcode'] : null);
            $qrcodeConfig = $qrcodeRow ? (isset($qrcodeRow['config']) ? $qrcodeRow['config'] : $this->decodeConfig($qrcodeRow)) : [];
            $qrNames = $this->qrSiteFieldNames();
            $qrValues = $this->store->loadSiteConfig(array_values($qrNames));
            $socialItems = $this->normalizeSocialForLoad(isset($qrcodeConfig['items']) && is_array($qrcodeConfig['items']) ? $qrcodeConfig['items'] : [], $qrValues);
        }

        $friendLinkItems = [];
        if ($definition['list_type'] === 'friend_links' || in_array('layout.friend_links', $definition['related_components'], true)) {
            $friendRow = $row['component_key'] === 'layout.friend_links'
                ? $row
                : (isset($related['layout.friend_links']) ? $related['layout.friend_links'] : null);
            $friendConfig = $friendRow ? (isset($friendRow['config']) ? $friendRow['config'] : $this->decodeConfig($friendRow)) : [];
            $friendLinkItems = $this->normalizeFriendLinksForLoad(
                isset($friendConfig['items']) && is_array($friendConfig['items']) ? $friendConfig['items'] : []
            );
        }

        return [
            'component' => $row,
            'definition' => $definition,
            'site_fields' => $siteFields,
            'navigation' => $this->normalizeNavigationForLoad($navigation),
            'component_values' => [
                'title' => isset($row['title']) ? (string)$row['title'] : '',
                'content' => isset($row['content']) ? (string)$row['content'] : '',
            ],
            'layout_config' => $this->layoutConfigForLoad($row, $definition, $config),
            'layout_field_definitions' => $this->layoutFieldDefinitions($row, $definition),
            'content_layout_field_definitions' => $this->layoutFieldDefinitions(
                $row,
                $definition,
                isset($definition['content_layout_fields']) && is_array($definition['content_layout_fields']) ? $definition['content_layout_fields'] : []
            ),
            'display_layout_field_definitions' => $this->layoutFieldDefinitions(
                $row,
                $definition,
                isset($definition['display_layout_fields']) && is_array($definition['display_layout_fields']) ? $definition['display_layout_fields'] : $definition['layout_fields']
            ),
            'hot_search_items' => $hotSearchItems,
            'social_items' => $socialItems,
            'friend_link_items' => $friendLinkItems,
            'related_components' => $related,
        ];
    }

    public function save($layoutId, $expectedVersion, array $payload, $adminId)
    {
        $row = $this->requireLayoutById($layoutId);
        $definition = LayoutEditorRegistry::definition($row['component_key']);

        $siteValues = $this->sanitizeSiteValues(isset($payload['site']) && is_array($payload['site']) ? $payload['site'] : [], $definition);
        $componentValues = $this->sanitizeComponentValues(isset($payload['component']) && is_array($payload['component']) ? $payload['component'] : [], $definition);
        $configInput = isset($payload['config']) && is_array($payload['config']) ? $payload['config'] : [];
        $navigation = $definition['navigation_position'] !== null
            ? $this->sanitizeNavigation(isset($payload['navigation']) && is_array($payload['navigation']) ? $payload['navigation'] : [])
            : [];
        $hotSearchItems = isset($payload['hot_search_items']) && is_array($payload['hot_search_items'])
            ? $this->sanitizeHotSearchItems($payload['hot_search_items'])
            : null;
        $socialItems = isset($payload['social_items']) && is_array($payload['social_items'])
            ? $this->sanitizeSocialItems($payload['social_items'], $siteValues, $definition)
            : null;
        $friendLinkItems = isset($payload['friend_link_items']) && is_array($payload['friend_link_items'])
            ? $this->sanitizeFriendLinkItems($payload['friend_link_items'])
            : null;
        $relatedPayload = isset($payload['related_components']) && is_array($payload['related_components'])
            ? $payload['related_components']
            : [];

        $mainConfig = $this->buildMainConfig($row, $definition, $configInput, $hotSearchItems, $socialItems, $friendLinkItems);
        $mainFields = $componentValues;
        $mainFields['config_json'] = $this->encode($mainConfig);
        $mainFields = array_merge($mainFields, $this->visibilityFields($row, $definition, $configInput));

        $relatedUpdates = $this->prepareRelatedUpdates($definition, $relatedPayload, $socialItems, $friendLinkItems);
        $store = $this->store;
        $result = $store->transaction(function () use ($store, $row, $expectedVersion, $mainFields, $siteValues, $definition, $navigation, $relatedUpdates, $payload, $adminId) {
            $saved = $store->updateLayoutVersioned((int)$row['id'], (int)$expectedVersion, $mainFields);
            if ($siteValues) {
                $store->saveSiteConfig($siteValues);
            }
            if ($definition['navigation_position'] !== null) {
                $store->saveNavigation($definition['navigation_position'], $navigation);
            }
            foreach ($relatedUpdates as $update) {
                $store->updateLayoutVersioned($update['id'], $update['version'], $update['fields']);
            }
            return $saved;
        });

        call_user_func($this->siteRefresher);
        call_user_func($this->cacheInvalidator);
        return $result;
    }

    private function requireLayoutById($id)
    {
        $row = $this->store->findLayoutById((int)$id);
        if (!$row) {
            throw new \InvalidArgumentException('公共布局组件不存在');
        }
        if (!in_array($row['component_key'], LayoutEditorRegistry::keys(), true)) {
            throw new \InvalidArgumentException('该公共布局不支持统一编辑：' . $row['component_key']);
        }
        return $row;
    }

    private function decodeConfig(array $row)
    {
        if (isset($row['config']) && is_array($row['config'])) {
            return $row['config'];
        }
        $decoded = json_decode(isset($row['config_json']) ? (string)$row['config_json'] : '', true);
        return is_array($decoded) ? $decoded : [];
    }

    private function layoutConfigForLoad(array $row, array $definition, array $config)
    {
        $result = [];
        $schema = [];
        try {
            $schema = LayoutSchemaRegistry::fields($row['component_type']);
        } catch (\InvalidArgumentException $e) {
            $schema = [];
        }
        foreach ($definition['layout_fields'] as $name) {
            if ($name === 'enabled') {
                $result[$name] = ($row['status'] === 'normal' && !empty($row['pc_visible']) && !empty($row['mobile_visible'])) ? 1 : 0;
                continue;
            }
            if (isset($schema[$name])) {
                $definitionValue = $schema[$name];
                $default = isset($definitionValue['default']) ? $definitionValue['default'] : null;
                $value = array_key_exists($name, $config) ? $config[$name] : $default;
                try {
                    $result[$name] = LayoutSchemaRegistry::sanitizeField($row['component_type'], $name, $value);
                } catch (\InvalidArgumentException $e) {
                    $result[$name] = LayoutSchemaRegistry::sanitizeField($row['component_type'], $name, $default);
                }
                continue;
            }
            if ($name === 'max_items') {
                $result[$name] = isset($config[$name]) ? max(1, (int)$config[$name]) : 8;
                continue;
            }
            $result[$name] = array_key_exists($name, $config) ? (int)!empty($config[$name]) : 0;
        }
        return $result;
    }


    private function layoutFieldDefinitions(array $row, array $definition, array $fieldNames = null)
    {
        $schema = [];
        try {
            $schema = LayoutSchemaRegistry::fields($row['component_type']);
        } catch (\InvalidArgumentException $e) {
            $schema = [];
        }
        $result = [];
        $names = $fieldNames === null ? $definition['layout_fields'] : $fieldNames;
        foreach ($names as $name) {
            if (isset($schema[$name])) {
                $result[$name] = $schema[$name];
                continue;
            }
            if ($name === 'enabled') {
                $result[$name] = ['title' => '显示该组件', 'type' => 'boolean', 'default' => 1];
                continue;
            }
            if ($name === 'max_items') {
                $result[$name] = ['title' => '最大显示数量', 'type' => 'number', 'default' => 8];
                continue;
            }
            $result[$name] = ['title' => $name, 'type' => 'boolean', 'default' => 0];
        }
        return $result;
    }

    private function sanitizeSiteValues(array $values, array $definition)
    {
        $allowed = array_flip($definition['site_fields']);
        $definitions = SiteConfigDefinitionRegistry::pick($definition['site_fields']);
        $result = [];
        foreach ($values as $name => $value) {
            if (!isset($allowed[$name])) {
                throw new \InvalidArgumentException('当前公共布局不允许修改网站配置：' . $name);
            }
            $field = $definitions[$name];
            $value = trim((string)$value);
            if ($field['rule'] === 'required' && $value === '') {
                throw new \InvalidArgumentException($field['title'] . '不能为空');
            }
            if ($field['rule'] === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException($field['title'] . '格式不正确');
            }
            if (strlen($value) > 10000 || preg_match('/<\s*script\b/i', $value)) {
                throw new \InvalidArgumentException($field['title'] . '内容不合法');
            }
            $result[$name] = $value;
        }
        return $result;
    }

    private function sanitizeComponentValues(array $values, array $definition)
    {
        $allowed = array_flip($definition['component_fields']);
        $result = [];
        foreach ($values as $name => $value) {
            if (!isset($allowed[$name])) {
                throw new \InvalidArgumentException('当前公共布局不允许修改组件字段：' . $name);
            }
            $value = trim((string)$value);
            if (strlen($value) > 10000 || preg_match('/<\s*script\b/i', $value)) {
                throw new \InvalidArgumentException('组件内容不合法：' . $name);
            }
            $result[$name] = $value;
        }
        return $result;
    }

    private function buildMainConfig(array $row, array $definition, array $configInput, $hotSearchItems, $socialItems, $friendLinkItems)
    {
        $config = $this->decodeConfig($row);
        $allowed = array_flip($definition['layout_fields']);
        $schema = [];
        try {
            $schema = LayoutSchemaRegistry::fields($row['component_type']);
        } catch (\InvalidArgumentException $e) {
            $schema = [];
        }
        foreach ($configInput as $name => $value) {
            if (!isset($allowed[$name])) {
                throw new \InvalidArgumentException('当前公共布局不允许修改显示字段：' . $name);
            }
            if ($name === 'enabled') {
                continue;
            }
            if (isset($schema[$name])) {
                $config[$name] = LayoutSchemaRegistry::sanitizeField($row['component_type'], $name, $value);
                continue;
            }
            if ($name === 'max_items') {
                $config[$name] = max(1, min(50, (int)$value));
                continue;
            }
            $config[$name] = $this->boolValue($value);
        }
        if ($definition['list_type'] === 'hot_search' && $hotSearchItems !== null) {
            $config['items'] = $hotSearchItems;
        }
        if ($definition['list_type'] === 'social' && $socialItems !== null) {
            $config['items'] = $this->socialMetadata($socialItems);
        }
        if ($definition['list_type'] === 'friend_links' && $friendLinkItems !== null) {
            $config['items'] = $friendLinkItems;
        }
        return $config;
    }

    private function visibilityFields(array $row, array $definition, array $configInput)
    {
        if (!in_array('enabled', $definition['layout_fields'], true) || !array_key_exists('enabled', $configInput)) {
            return [];
        }
        $enabled = $this->boolValue($configInput['enabled']);
        return ['pc_visible' => $enabled, 'mobile_visible' => $enabled];
    }

    private function sanitizeNavigation(array $items)
    {
        $result = [];
        foreach (array_values($items) as $item) {
            if (!is_array($item)) {
                throw new \InvalidArgumentException('导航数据格式不正确');
            }
            $title = trim(isset($item['title']) ? (string)$item['title'] : '');
            if ($title === '' || strlen($title) > 300) {
                throw new \InvalidArgumentException('导航名称不能为空或过长');
            }
            $url = trim(isset($item['url']) ? (string)$item['url'] : '');
            if (!$this->isSafeLink($url)) {
                throw new \InvalidArgumentException('导航链接不安全：' . $title);
            }
            $target = isset($item['target']) && $item['target'] === '_blank' ? '_blank' : '_self';
            $status = isset($item['status']) && $item['status'] === 'hidden' ? 'hidden' : 'normal';
            $result[] = [
                'id' => isset($item['id']) ? (int)$item['id'] : 0,
                'parent_id' => isset($item['parent_id']) ? (int)$item['parent_id'] : 0,
                'title' => $title,
                'url' => $url,
                'target' => $target,
                'icon' => trim(isset($item['icon']) ? (string)$item['icon'] : ''),
                'pc_visible' => $this->boolValue(isset($item['pc_visible']) ? $item['pc_visible'] : 0),
                'mobile_visible' => $this->boolValue(isset($item['mobile_visible']) ? $item['mobile_visible'] : 0),
                'weigh' => isset($item['weigh']) ? (int)$item['weigh'] : 0,
                'status' => $status,
            ];
        }
        return $result;
    }

    private function normalizeNavigationForLoad(array $items)
    {
        $result = [];
        foreach ($items as $row) {
            $result[] = [
                'id' => isset($row['id']) ? (int)$row['id'] : 0,
                'parent_id' => isset($row['parent_id']) ? (int)$row['parent_id'] : 0,
                'title' => isset($row['title']) ? (string)$row['title'] : '',
                'url' => isset($row['url']) ? (string)$row['url'] : '',
                'target' => isset($row['target']) && $row['target'] === '_blank' ? '_blank' : '_self',
                'icon' => isset($row['icon']) ? (string)$row['icon'] : '',
                'pc_visible' => !empty($row['pc_visible']) ? 1 : 0,
                'mobile_visible' => !empty($row['mobile_visible']) ? 1 : 0,
                'weigh' => isset($row['weigh']) ? (int)$row['weigh'] : 0,
                'status' => isset($row['status']) && $row['status'] === 'hidden' ? 'hidden' : 'normal',
            ];
        }
        return $result;
    }

    private function sanitizeHotSearchItems(array $items)
    {
        $result = [];
        foreach (array_values($items) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $title = trim(isset($item['title']) ? (string)$item['title'] : '');
            if ($title === '') {
                continue;
            }
            $url = trim(isset($item['url']) ? (string)$item['url'] : '');
            if (!$this->isSafeLink($url)) {
                throw new \InvalidArgumentException('热搜链接不安全：' . $title);
            }
            $result[] = [
                'title' => $title,
                'url' => $url,
                'weigh' => isset($item['weigh']) ? (int)$item['weigh'] : 0,
                'status' => isset($item['status']) && $item['status'] === 'hidden' ? 'hidden' : 'normal',
            ];
        }
        usort($result, function ($a, $b) { return $a['weigh'] === $b['weigh'] ? 0 : ($a['weigh'] > $b['weigh'] ? -1 : 1); });
        return $result;
    }

    private function normalizeHotSearchForLoad(array $items)
    {
        $result = [];
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }
            $result[] = [
                'title' => isset($item['title']) ? (string)$item['title'] : '',
                'url' => isset($item['url']) ? (string)$item['url'] : '',
                'weigh' => isset($item['weigh']) ? (int)$item['weigh'] : (1000 - $index),
                'status' => isset($item['status']) && $item['status'] === 'hidden' ? 'hidden' : 'normal',
            ];
        }
        return $result;
    }

    private function sanitizeFriendLinkItems(array $items)
    {
        $result = [];
        foreach (array_values($items) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $title = trim(isset($item['title']) ? (string)$item['title'] : '');
            if ($title === '') {
                continue;
            }
            if (strlen($title) > 300) {
                throw new \InvalidArgumentException('友情链接名称过长');
            }
            $url = trim(isset($item['url']) ? (string)$item['url'] : '');
            if (!$this->isSafeLink($url)) {
                throw new \InvalidArgumentException('友情链接不安全：' . $title);
            }
            if (isset($item['target'])) {
                $target = $item['target'] === '_self' ? '_self' : '_blank';
            } else {
                $target = preg_match('#^https?://#i', $url) ? '_blank' : '_self';
            }
            $result[] = [
                'title' => $title,
                'url' => $url,
                'target' => $target,
                'weigh' => isset($item['weigh']) ? (int)$item['weigh'] : 0,
                'status' => isset($item['status']) && $item['status'] === 'hidden' ? 'hidden' : 'normal',
            ];
        }
        usort($result, function ($a, $b) {
            return $a['weigh'] === $b['weigh'] ? 0 : ($a['weigh'] > $b['weigh'] ? -1 : 1);
        });
        return array_slice($result, 0, 50);
    }

    private function normalizeFriendLinksForLoad(array $items)
    {
        $result = [];
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }
            $url = isset($item['url']) ? trim((string)$item['url']) : '';
            $result[] = [
                'title' => isset($item['title']) ? (string)$item['title'] : '',
                'url' => $url,
                'target' => isset($item['target'])
                    ? ($item['target'] === '_self' ? '_self' : '_blank')
                    : (preg_match('#^https?://#i', $url) ? '_blank' : '_self'),
                'weigh' => isset($item['weigh']) ? (int)$item['weigh'] : (1000 - $index),
                'status' => isset($item['status']) && $item['status'] === 'hidden' ? 'hidden' : 'normal',
            ];
        }
        return $result;
    }

    private function sanitizeSocialItems(array $items, array &$siteValues, array $definition)
    {
        $qrMap = $this->qrSiteFieldNames();
        $allowedSite = array_flip($definition['site_fields']);
        $result = [];
        foreach (array_values($items) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $title = trim(isset($item['title']) ? (string)$item['title'] : '');
            if ($title === '') {
                continue;
            }
            $link = trim(isset($item['link_url']) ? (string)$item['link_url'] : '');
            if (!$this->isSafeLink($link)) {
                throw new \InvalidArgumentException('社交平台链接不安全：' . $title);
            }
            $qrKey = trim(isset($item['qr_key']) ? (string)$item['qr_key'] : '');
            if ($qrKey !== '' && !isset($qrMap[$qrKey])) {
                throw new \InvalidArgumentException('不支持的二维码绑定：' . $qrKey);
            }
            if ($qrKey !== '') {
                $siteName = $qrMap[$qrKey];
                if (!isset($allowedSite[$siteName])) {
                    throw new \InvalidArgumentException('当前公共布局不允许修改二维码：' . $qrKey);
                }
                if (array_key_exists('qr_value', $item)) {
                    $siteValues[$siteName] = trim((string)$item['qr_value']);
                }
            }
            $result[] = [
                'title' => $title,
                'icon' => trim(isset($item['icon']) ? (string)$item['icon'] : ''),
                'link_url' => $link,
                'qr_key' => $qrKey,
                'weigh' => isset($item['weigh']) ? (int)$item['weigh'] : 0,
                'status' => isset($item['status']) && $item['status'] === 'hidden' ? 'hidden' : 'normal',
            ];
        }
        usort($result, function ($a, $b) { return $a['weigh'] === $b['weigh'] ? 0 : ($a['weigh'] > $b['weigh'] ? -1 : 1); });
        return $result;
    }

    private function normalizeSocialForLoad(array $items, array $siteValues)
    {
        $qrMap = $this->qrSiteFieldNames();
        $result = [];
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }
            $qrKey = isset($item['qr_key']) ? trim((string)$item['qr_key']) : '';
            $siteName = $qrKey !== '' && isset($qrMap[$qrKey]) ? $qrMap[$qrKey] : '';
            $result[] = [
                'title' => isset($item['title']) ? (string)$item['title'] : '',
                'icon' => isset($item['icon']) ? (string)$item['icon'] : '',
                'link_url' => isset($item['link_url']) ? (string)$item['link_url'] : '',
                'qr_key' => $qrKey,
                'qr_value' => $siteName !== '' && isset($siteValues[$siteName]) ? (string)$siteValues[$siteName] : '',
                'weigh' => isset($item['weigh']) ? (int)$item['weigh'] : (1000 - $index),
                'status' => isset($item['status']) && $item['status'] === 'hidden' ? 'hidden' : 'normal',
            ];
        }
        return $result;
    }

    private function socialMetadata(array $items)
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'title' => $item['title'],
                'icon' => $item['icon'],
                'link_url' => $item['link_url'],
                'qr_key' => $item['qr_key'],
                'weigh' => $item['weigh'],
                'status' => $item['status'],
            ];
        }
        return $result;
    }

    private function prepareRelatedUpdates(array $definition, array $payload, $socialItems, $friendLinkItems)
    {
        if (!$definition['related_components']) {
            return [];
        }
        $updates = [];
        foreach ($definition['related_components'] as $key) {
            $submitted = isset($payload[$key]) && is_array($payload[$key]) ? $payload[$key] : [];
            $needsSocial = $key === 'layout.footer.qrcode' && $socialItems !== null;
            $needsFriendLinks = $key === 'layout.friend_links' && $friendLinkItems !== null;
            if (!$submitted && !$needsSocial && !$needsFriendLinks) {
                continue;
            }
            $row = $this->store->findLayoutByKey($key);
            if (!$row) {
                throw new \InvalidArgumentException('关联公共布局不存在：' . $key);
            }
            if (!isset($submitted['version']) || (int)$submitted['version'] !== (int)$row['version']) {
                throw new PageConfigConflictException('公共布局已被其他管理员修改，请重新打开后再保存');
            }
            if (isset($submitted['id']) && (int)$submitted['id'] !== (int)$row['id']) {
                throw new \InvalidArgumentException('关联公共布局标识不匹配：' . $key);
            }
            $childDefinition = LayoutEditorRegistry::definition($key);
            $fields = [];
            foreach ($childDefinition['component_fields'] as $fieldName) {
                if (array_key_exists($fieldName, $submitted)) {
                    $value = trim((string)$submitted[$fieldName]);
                    if (strlen($value) > 10000 || preg_match('/<\s*script\b/i', $value)) {
                        throw new \InvalidArgumentException('关联组件内容不合法：' . $fieldName);
                    }
                    $fields[$fieldName] = $value;
                }
            }
            if ($needsSocial) {
                $config = $this->decodeConfig($row);
                $config['items'] = $this->socialMetadata($socialItems);
                $fields['config_json'] = $this->encode($config);
            }
            if ($needsFriendLinks) {
                $config = $this->decodeConfig($row);
                $config['items'] = $friendLinkItems;
                $fields['config_json'] = $this->encode($config);
            }
            if ($fields) {
                $updates[] = ['id'=>(int)$row['id'],'version'=>(int)$row['version'],'fields'=>$fields];
            }
        }
        return $updates;
    }

    private function qrSiteFieldNames()
    {
        return [
            'wechat_qr' => 'cms_wechat_qr',
            'douyin_qr' => 'cms_douyin_qr',
            'kuaishou_qr' => 'cms_kuaishou_qr',
            'xiaohongshu_qr' => 'cms_xiaohongshu_qr',
            'video_qr' => 'cms_video_qr',
            'bilibili_qr' => 'cms_bilibili_qr',
        ];
    }

    private function boolValue($value)
    {
        return in_array($value, [1, '1', true, 'true', 'on', 'yes'], true) ? 1 : 0;
    }

    private function isSafeLink($value)
    {
        if ($value === '' || $value[0] === '/' || $value[0] === '#' || $value[0] === '?') {
            return true;
        }
        $scheme = parse_url($value, PHP_URL_SCHEME);
        return $scheme !== null && in_array(strtolower($scheme), ['http', 'https', 'tel', 'mailto'], true);
    }

    private function encode($value)
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \InvalidArgumentException('公共布局配置无法转换为 JSON');
        }
        return $json;
    }
}
