<?php

namespace app\common\service\cms;

class BannerOperationalSummary
{
    protected $highlightCodec;

    public function __construct(BannerHighlightCodec $highlightCodec = null)
    {
        $this->highlightCodec = $highlightCodec ?: new BannerHighlightCodec();
    }

    public function summarize(array $row, $now = null)
    {
        $now = $now === null ? time() : (int)$now;
        $pcMediaText = $this->mediaText(isset($row['media_type']) ? $row['media_type'] : 'image');
        $mobileType = isset($row['mobile_media_type']) ? trim((string)$row['mobile_media_type']) : '';
        $mobileMediaText = in_array($mobileType, ['image', 'video'], true)
            ? $this->mediaText($mobileType)
            : '继承 PC · ' . $pcMediaText;

        $pcVisible = isset($row['pc_visible']) && (int)$row['pc_visible'] === 1;
        $mobileVisible = isset($row['mobile_visible']) && (int)$row['mobile_visible'] === 1;
        $startTime = $this->timestamp(isset($row['start_time']) ? $row['start_time'] : null);
        $endTime = $this->timestamp(isset($row['end_time']) ? $row['end_time'] : null);
        list($deliveryState, $deliveryStateText) = $this->deliveryState(
            isset($row['status']) ? $row['status'] : '',
            $pcVisible,
            $mobileVisible,
            $startTime,
            $endTime,
            $now
        );

        $highlightsJson = isset($row['highlights_json']) ? $row['highlights_json'] : '';
        $highlightItems = is_array($highlightsJson)
            ? $this->highlightCodec->normalize($highlightsJson)
            : $this->highlightCodec->decode($highlightsJson);

        $pcMedia = $this->effectiveMedia($row, 'pc');
        $mobileMedia = $this->effectiveMedia($row, 'mobile');
        $warnings = $this->mediaWarnings($pcMedia, $mobileMedia, $pcVisible, $mobileVisible);
        $previewTerminal = $pcVisible ? 'pc' : ($mobileVisible ? 'mobile' : 'pc');
        $previewMedia = $previewTerminal === 'mobile' ? $mobileMedia : $pcMedia;

        return [
            'pc_media_text' => $pcMediaText,
            'mobile_media_text' => $mobileMediaText,
            'terminal_text' => $this->terminalText($pcVisible, $mobileVisible),
            'highlight_count' => count($highlightItems),
            'delivery_state' => $deliveryState,
            'delivery_state_text' => $deliveryStateText,
            'delivery_time_text' => $this->deliveryTimeText($startTime, $endTime),
            'preview_terminal' => $previewTerminal,
            'preview_terminal_text' => $previewTerminal === 'mobile' ? 'Mobile' : 'PC',
            'preview_type' => $previewMedia['type'],
            'preview_url' => $previewMedia['type'] === 'video' ? $previewMedia['video'] : $previewMedia['image'],
            'preview_poster' => $previewMedia['type'] === 'video' ? $previewMedia['image'] : '',
            'media_warnings' => $warnings,
            'media_warning_count' => count($warnings),
        ];
    }

    protected function effectiveMedia(array $row, $terminal)
    {
        $pcType = isset($row['media_type']) && trim((string)$row['media_type']) === 'video' ? 'video' : 'image';
        $pcImage = isset($row['image']) ? trim((string)$row['image']) : '';
        $pcVideo = isset($row['video_url']) ? trim((string)$row['video_url']) : '';
        if ($terminal !== 'mobile') {
            return ['type' => $pcType, 'image' => $pcImage, 'video' => $pcVideo];
        }

        $mobileType = isset($row['mobile_media_type']) ? trim((string)$row['mobile_media_type']) : '';
        if (!in_array($mobileType, ['image', 'video'], true)) {
            $mobileType = $pcType;
        }
        $mobileImage = isset($row['mobile_image']) ? trim((string)$row['mobile_image']) : '';
        $mobileVideo = isset($row['mobile_video_url']) ? trim((string)$row['mobile_video_url']) : '';

        return [
            'type' => $mobileType,
            'image' => $mobileImage !== '' ? $mobileImage : $pcImage,
            'video' => $mobileVideo !== '' ? $mobileVideo : $pcVideo,
        ];
    }

    protected function mediaWarnings(array $pcMedia, array $mobileMedia, $pcVisible, $mobileVisible)
    {
        $warnings = [];
        if ($pcVisible) {
            $pcValue = $pcMedia['type'] === 'video' ? $pcMedia['video'] : $pcMedia['image'];
            if ($pcValue === '') {
                $warnings[] = $pcMedia['type'] === 'video' ? 'PC 视频未配置' : 'PC 图片未配置';
            }
        }
        if ($mobileVisible) {
            $mobileValue = $mobileMedia['type'] === 'video' ? $mobileMedia['video'] : $mobileMedia['image'];
            if ($mobileValue === '') {
                $warnings[] = $mobileMedia['type'] === 'video'
                    ? '移动端视频未配置（含 PC 回退）'
                    : '移动端图片未配置（含 PC 回退）';
            }
        }
        return $warnings;
    }

    protected function mediaText($type)
    {
        return trim((string)$type) === 'video' ? '视频' : '图片';
    }

    protected function terminalText($pcVisible, $mobileVisible)
    {
        if ($pcVisible && $mobileVisible) {
            return 'PC + 移动端';
        }
        if ($pcVisible) {
            return '仅 PC';
        }
        if ($mobileVisible) {
            return '仅移动端';
        }
        return '前台隐藏';
    }

    protected function deliveryState($status, $pcVisible, $mobileVisible, $startTime, $endTime, $now)
    {
        if ((string)$status !== 'normal') {
            return ['disabled', '已停用'];
        }
        if (!$pcVisible && !$mobileVisible) {
            return ['hidden', '前台隐藏'];
        }
        if ($startTime > 0 && $startTime > $now) {
            return ['scheduled', '待开始'];
        }
        if ($endTime > 0 && $endTime < $now) {
            return ['expired', '已结束'];
        }
        return ['active', '生效中'];
    }

    protected function deliveryTimeText($startTime, $endTime)
    {
        if ($startTime <= 0 && $endTime <= 0) {
            return '长期';
        }
        if ($startTime > 0 && $endTime <= 0) {
            return date('Y-m-d H:i', $startTime) . ' 起';
        }
        if ($startTime <= 0 && $endTime > 0) {
            return '至 ' . date('Y-m-d H:i', $endTime);
        }
        return date('Y-m-d H:i', $startTime) . ' ～ ' . date('Y-m-d H:i', $endTime);
    }

    protected function timestamp($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        if (is_numeric($value)) {
            $timestamp = (int)$value;
            return $timestamp > 0 ? $timestamp : 0;
        }
        $timestamp = strtotime((string)$value);
        return $timestamp === false || $timestamp <= 0 ? 0 : $timestamp;
    }
}
