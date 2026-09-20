<?php

namespace app\common\service\cms;

/**
 * 面向受信任后台编辑器的最小富文本白名单。
 * 不允许脚本、表单、内嵌页面、事件属性及危险协议。
 */
class HtmlSanitizer
{
    protected static $allowedTags = '<p><br><div><span><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><blockquote><pre><code><ul><ol><li><table><thead><tbody><tfoot><tr><th><td><a><img><figure><figcaption><video><source><hr>';

    public static function clean($html)
    {
        $html = (string)$html;
        if ($html === '') {
            return '';
        }
        $dangerous = 'script|style|iframe|object|embed|form|input|button|textarea|select|option|meta|link|base|svg|math';
        $html = preg_replace('#<\s*(' . $dangerous . ')\b[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html);
        $html = preg_replace('#<\s*/?\s*(' . $dangerous . ')\b[^>]*>#is', '', $html);
        $html = strip_tags($html, self::$allowedTags);
        $html = preg_replace('/\s+on[a-z0-9_-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $html);
        $html = preg_replace('/\s+(?:style|srcdoc|formaction)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $html);
        $html = preg_replace_callback(
            '/\s+(href|src|poster)\s*=\s*(["\'])\s*(javascript|vbscript|data)\s*:[^"\']*\2/iu',
            function ($match) {
                return ' ' . strtolower($match[1]) . '="#"';
            },
            $html
        );
        $html = preg_replace_callback(
            '/\s+(href|src|poster)\s*=\s*(javascript|vbscript|data)\s*:[^\s>]+/iu',
            function ($match) {
                return ' ' . strtolower($match[1]) . '="#"';
            },
            $html
        );
        return trim($html);
    }
}
