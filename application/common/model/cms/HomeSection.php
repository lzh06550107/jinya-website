<?php

namespace app\common\model\cms;

use app\common\service\cms\HtmlSanitizer;
use app\common\service\cms\StructuredConfigCodec;

class HomeSection extends BaseModel
{
    protected $name = 'cms_home_section';

    protected $append = [
        'status_text', 'config_array',
        'config_social_icon_1', 'config_social_text_1', 'config_social_icon_2', 'config_social_text_2',
        'config_left_title', 'config_right_title',
        'config_left_url', 'config_right_url',
        'config_other_title', 'config_other_summary', 'config_other_url',
        'config_video_url', 'config_video_poster',
        'config_metrics', 'config_metrics_text', 'config_items', 'config_items_text',
        'config_media_items', 'config_media_items_text', 'config_social_links_text',
    ];

    public function getStatusTextAttr($value, $data)
    {
        $list = $this->getNormalStatusList();
        return isset($list[$data['status']]) ? $list[$data['status']] : $data['status'];
    }

    public function setContentAttr($value)
    {
        return HtmlSanitizer::clean($value);
    }

    public function getConfigArrayAttr($value, $data)
    {
        $codec = new StructuredConfigCodec();
        return $codec->normalizeHome($codec->decode(isset($data['config_json']) ? $data['config_json'] : ''));
    }

    public function getConfigSocialIcon_1Attr($value, $data) { return $this->configScalar($data, 'social_icon_1'); }
    public function getConfigSocialText_1Attr($value, $data) { return $this->configScalar($data, 'social_text_1'); }
    public function getConfigSocialIcon_2Attr($value, $data) { return $this->configScalar($data, 'social_icon_2'); }
    public function getConfigSocialText_2Attr($value, $data) { return $this->configScalar($data, 'social_text_2'); }
    public function getConfigLeftTitleAttr($value, $data) { return $this->configScalar($data, 'left_title'); }
    public function getConfigRightTitleAttr($value, $data) { return $this->configScalar($data, 'right_title'); }
    public function getConfigLeftUrlAttr($value, $data) { return $this->configScalar($data, 'left_url'); }
    public function getConfigRightUrlAttr($value, $data) { return $this->configScalar($data, 'right_url'); }
    public function getConfigOtherTitleAttr($value, $data) { return $this->configScalar($data, 'other_title'); }
    public function getConfigOtherSummaryAttr($value, $data) { return $this->configScalar($data, 'other_summary'); }
    public function getConfigOtherUrlAttr($value, $data) { return $this->configScalar($data, 'other_url'); }
    public function getConfigVideoUrlAttr($value, $data) { return $this->configScalar($data, 'video_url'); }
    public function getConfigVideoPosterAttr($value, $data) { return $this->configScalar($data, 'video_poster'); }

    public function getConfigMetricsAttr($value, $data)
    {
        $codec = new StructuredConfigCodec();
        $config = $codec->normalizeHome($codec->decode(isset($data['config_json']) ? $data['config_json'] : ''));
        return isset($config['metrics']) && is_array($config['metrics']) ? $config['metrics'] : [];
    }

    public function getConfigMetricsTextAttr($value, $data)
    {
        return (new StructuredConfigCodec())->formatHomeRows(isset($data['config_json']) ? $data['config_json'] : '', 'metrics', ['value', 'unit', 'text', 'icon', 'prefix']);
    }

    public function getConfigItemsAttr($value, $data)
    {
        $codec = new StructuredConfigCodec();
        $config = $codec->normalizeHome($codec->decode(isset($data['config_json']) ? $data['config_json'] : ''));
        return isset($config['items']) && is_array($config['items']) ? $config['items'] : [];
    }

    public function getConfigItemsTextAttr($value, $data)
    {
        return (new StructuredConfigCodec())->formatHomeRows(isset($data['config_json']) ? $data['config_json'] : '', 'items', ['title', 'text', 'image', 'icon', 'url', 'subtitle', 'mobile_image', 'pc_visible', 'mobile_visible']);
    }

    public function getConfigMediaItemsAttr($value, $data)
    {
        $codec = new StructuredConfigCodec();
        $config = $codec->normalizeHome($codec->decode(isset($data['config_json']) ? $data['config_json'] : ''));
        return isset($config['media_items']) && is_array($config['media_items']) ? $config['media_items'] : [];
    }

    public function getConfigMediaItemsTextAttr($value, $data)
    {
        return (new StructuredConfigCodec())->formatHomeRows(isset($data['config_json']) ? $data['config_json'] : '', 'media_items', ['title', 'image', 'video_url', 'url']);
    }

    public function getConfigSocialLinksTextAttr($value, $data)
    {
        return (new StructuredConfigCodec())->formatHomeRows(isset($data['config_json']) ? $data['config_json'] : '', 'social_links', ['title', 'url', 'image']);
    }

    protected function configScalar(array $data, $key)
    {
        return (new StructuredConfigCodec())->scalar(isset($data['config_json']) ? $data['config_json'] : '', $key, true);
    }
}
