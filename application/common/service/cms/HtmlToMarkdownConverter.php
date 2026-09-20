<?php

namespace app\common\service\cms;

/**
 * Legacy CMS HTML -> Markdown migration helper.
 *
 * This class is intentionally dependency-free so the v9 FastAdmin baseline does
 * not need a new Composer package. It migrates content semantics only; layout
 * HTML/CSS/JS is discarded because layout belongs to templates/static assets.
 */
final class HtmlToMarkdownConverter
{
    public static function convert($value)
    {
        $value = str_replace(["\r\n", "\r"], "\n", trim((string)$value));
        if ($value === '') {
            return '';
        }
        if (!preg_match('/<[a-zA-Z][^>]*>/', $value)) {
            return trim($value);
        }

        $html = preg_replace('/<!--.*?-->/s', '', $value);
        $html = preg_replace('#<(script|style|noscript|object|embed|form)\b[^>]*>.*?</\1>#isu', '', $html);
        $html = preg_replace('#<(input|button|textarea|select|option)\b[^>]*>.*?</\1>#isu', '', $html);
        $html = preg_replace('#<(input|button)\b[^>]*/?>#isu', '', $html);

        $html = preg_replace_callback('#<iframe\b([^>]*)>(?:.*?)</iframe>#isu', function ($match) {
            $src = self::attribute($match[1], 'src');
            return $src !== '' && self::safeUrl($src) ? '[嵌入内容](' . $src . ')' : '';
        }, $html);
        $html = preg_replace_callback('#<iframe\b([^>]*)/?>#isu', function ($match) {
            $src = self::attribute($match[1], 'src');
            return $src !== '' && self::safeUrl($src) ? '[嵌入内容](' . $src . ')' : '';
        }, $html);
        $html = preg_replace_callback('#<video\b([^>]*)>(.*?)</video>#isu', function ($match) {
            $src = self::attribute($match[1], 'src');
            if ($src === '' && preg_match('#<source\b([^>]*)/?>#isu', $match[2], $source)) {
                $src = self::attribute($source[1], 'src');
            }
            return $src !== '' && self::safeUrl($src) ? '[视频](' . $src . ')' : '';
        }, $html);

        $tokens = [];
        $store = function ($markdown) use (&$tokens) {
            $key = '@@CMSMD' . count($tokens) . '@@';
            $tokens[$key] = "\n\n" . trim($markdown) . "\n\n";
            return $key;
        };

        $html = preg_replace_callback('#<pre\b[^>]*>(.*?)</pre>#isu', function ($match) use ($store) {
            $code = preg_replace('#^\s*<code\b[^>]*>|</code>\s*$#isu', '', $match[1]);
            $code = html_entity_decode(strip_tags($code), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return $store("```\n" . trim($code, "\n") . "\n```");
        }, $html);

        $html = preg_replace_callback('#<table\b[^>]*>(.*?)</table>#isu', function ($match) use ($store) {
            return $store(self::tableToMarkdown($match[1]));
        }, $html);

        $html = preg_replace_callback('#<ol\b[^>]*>(.*?)</ol>#isu', function ($match) use ($store) {
            $items = self::listItems($match[1]);
            $lines = [];
            foreach ($items as $index => $item) {
                $lines[] = ($index + 1) . '. ' . $item;
            }
            return $store(implode("\n", $lines));
        }, $html);
        $html = preg_replace_callback('#<ul\b[^>]*>(.*?)</ul>#isu', function ($match) use ($store) {
            $items = self::listItems($match[1]);
            return $store(implode("\n", array_map(function ($item) { return '- ' . $item; }, $items)));
        }, $html);

        $html = preg_replace_callback('#<img\b([^>]*)/?>#isu', function ($match) {
            $src = self::attribute($match[1], 'src');
            if ($src === '' || !self::safeUrl($src)) {
                return '';
            }
            $alt = self::attribute($match[1], 'alt');
            return '![' . self::escapeLabel($alt) . '](' . $src . ')';
        }, $html);

        $html = preg_replace_callback('#<a\b([^>]*)>(.*?)</a>#isu', function ($match) {
            $href = self::attribute($match[1], 'href');
            $label = self::inlineText($match[2]);
            if ($href === '' || !self::safeUrl($href)) {
                return $label;
            }
            return '[' . self::escapeLabel($label) . '](' . $href . ')';
        }, $html);

        $html = preg_replace_callback('#<(strong|b)\b[^>]*>(.*?)</\1>#isu', function ($match) {
            $text = self::inlineText($match[2]);
            return $text === '' ? '' : '**' . $text . '**';
        }, $html);
        $html = preg_replace_callback('#<(em|i)\b[^>]*>(.*?)</\1>#isu', function ($match) {
            $text = self::inlineText($match[2]);
            return $text === '' ? '' : '*' . $text . '*';
        }, $html);
        $html = preg_replace_callback('#<(del|s|strike)\b[^>]*>(.*?)</\1>#isu', function ($match) {
            $text = self::inlineText($match[2]);
            return $text === '' ? '' : '~~' . $text . '~~';
        }, $html);
        $html = preg_replace_callback('#<code\b[^>]*>(.*?)</code>#isu', function ($match) {
            $text = self::inlineText($match[1]);
            return $text === '' ? '' : '`' . str_replace('`', '\\`', $text) . '`';
        }, $html);

        $html = preg_replace_callback('#<h([1-6])\b[^>]*>(.*?)</h\1>#isu', function ($match) {
            $text = self::inlineText($match[2]);
            return $text === '' ? "\n" : "\n\n" . str_repeat('#', (int)$match[1]) . ' ' . $text . "\n\n";
        }, $html);
        $html = preg_replace_callback('#<blockquote\b[^>]*>(.*?)</blockquote>#isu', function ($match) {
            $text = self::inlineText($match[1]);
            if ($text === '') {
                return '';
            }
            return "\n\n> " . str_replace("\n", "\n> ", $text) . "\n\n";
        }, $html);

        $html = preg_replace('#<br\s*/?>#iu', "\n", $html);
        $html = preg_replace('#</?(p|div|section|article|header|footer|main|aside|figure|figcaption|dl|dt|dd)\b[^>]*>#iu', "\n\n", $html);
        $html = preg_replace('#<hr\s*/?>#iu', "\n\n---\n\n", $html);
        $html = strip_tags($html);
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        foreach ($tokens as $key => $markdown) {
            $html = str_replace($key, $markdown, $html);
        }

        $html = preg_replace('/[ \t]+\n/u', "\n", $html);
        $html = preg_replace('/\n[ \t]+/u', "\n", $html);
        $html = preg_replace('/\n{3,}/u', "\n\n", $html);
        return trim($html);
    }

    private static function tableToMarkdown($html)
    {
        $rows = [];
        if (!preg_match_all('#<tr\b[^>]*>(.*?)</tr>#isu', $html, $rowMatches, PREG_SET_ORDER)) {
            return self::inlineText($html);
        }
        foreach ($rowMatches as $rowMatch) {
            $cells = [];
            if (preg_match_all('#<(th|td)\b[^>]*>(.*?)</\1>#isu', $rowMatch[1], $cellMatches, PREG_SET_ORDER)) {
                foreach ($cellMatches as $cellMatch) {
                    $cell = self::inlineText($cellMatch[2]);
                    $cell = str_replace('|', '\\|', preg_replace('/\s+/u', ' ', $cell));
                    $cells[] = trim($cell);
                }
            }
            if ($cells) {
                $rows[] = $cells;
            }
        }
        if (!$rows) {
            return '';
        }
        $width = max(array_map('count', $rows));
        foreach ($rows as &$row) {
            $row = array_pad($row, $width, '');
        }
        unset($row);
        $lines = [];
        $lines[] = '| ' . implode(' | ', $rows[0]) . ' |';
        $lines[] = '| ' . implode(' | ', array_fill(0, $width, '---')) . ' |';
        foreach (array_slice($rows, 1) as $row) {
            $lines[] = '| ' . implode(' | ', $row) . ' |';
        }
        return implode("\n", $lines);
    }

    private static function listItems($html)
    {
        $items = [];
        if (preg_match_all('#<li\b[^>]*>(.*?)</li>#isu', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = self::inlineText($match[1]);
                if ($text !== '') {
                    $items[] = $text;
                }
            }
        }
        return $items;
    }

    private static function inlineText($html)
    {
        $html = preg_replace('#<br\s*/?>#iu', "\n", (string)$html);
        $html = strip_tags($html);
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = preg_replace('/[ \t]+/u', ' ', $html);
        $html = preg_replace('/\s*\n\s*/u', ' ', $html);
        return trim($html);
    }

    private static function attribute($attributes, $name)
    {
        if (preg_match('/\b' . preg_quote($name, '/') . '\s*=\s*(["\'])(.*?)\1/isu', (string)$attributes, $match)) {
            return trim(html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        if (preg_match('/\b' . preg_quote($name, '/') . '\s*=\s*([^\s>]+)/iu', (string)$attributes, $match)) {
            return trim(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        return '';
    }

    private static function safeUrl($url)
    {
        $url = trim((string)$url);
        return $url !== '' && !preg_match('~^(?:javascript|vbscript|data):~i', $url);
    }

    private static function escapeLabel($value)
    {
        return str_replace(['[', ']'], ['\\[', '\\]'], trim((string)$value));
    }
}
