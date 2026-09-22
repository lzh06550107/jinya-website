<?php

namespace app\common\service\cms;

/**
 * Small dependency-free Markdown renderer for CMS body fields.
 *
 * Layout HTML/CSS belongs to templates. This class only translates authoring
 * markup into safe semantic HTML and delegates final allow-list filtering to
 * HtmlSanitizer.
 */
class MarkdownRenderer
{
    public static function render($markdown)
    {
        $markdown = str_replace(["\r\n", "\r"], "\n", (string)$markdown);
        if (trim($markdown) === '') {
            return '';
        }

        $lines = explode("\n", $markdown);
        $html = [];
        $paragraph = [];
        $listType = null;
        $quote = [];
        $inCode = false;
        $code = [];
        $count = count($lines);

        $flushParagraph = function () use (&$paragraph, &$html) {
            if (!$paragraph) return;
            $html[] = '<p>' . self::inline(implode("\n", $paragraph), true) . '</p>';
            $paragraph = [];
        };
        $flushList = function () use (&$listType, &$html) {
            if ($listType !== null) {
                $html[] = '</' . $listType . '>';
                $listType = null;
            }
        };
        $flushQuote = function () use (&$quote, &$html) {
            if (!$quote) return;
            $html[] = '<blockquote>' . self::inline(implode("\n", $quote), true) . '</blockquote>';
            $quote = [];
        };

        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];

            if (preg_match('/^\s*```/', $line)) {
                $flushParagraph();
                $flushList();
                $flushQuote();
                if ($inCode) {
                    $html[] = '<pre><code>' . htmlspecialchars(implode("\n", $code), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>';
                    $code = [];
                    $inCode = false;
                } else {
                    $inCode = true;
                }
                continue;
            }
            if ($inCode) {
                $code[] = $line;
                continue;
            }

            if (trim($line) === '') {
                $flushParagraph();
                $flushList();
                $flushQuote();
                continue;
            }

            // GFM-style pipe table: header line followed by separator line.
            if (strpos($line, '|') !== false && $i + 1 < $count && self::isTableSeparator($lines[$i + 1])) {
                $flushParagraph();
                $flushList();
                $flushQuote();
                $headers = self::tableCells($line);
                $i += 2;
                $rows = [];
                while ($i < $count && trim($lines[$i]) !== '' && strpos($lines[$i], '|') !== false) {
                    $rows[] = self::tableCells($lines[$i]);
                    $i++;
                }
                $i--;
                $html[] = '<table><thead><tr>' . implode('', array_map(function ($cell) {
                    return '<th>' . self::inline($cell, false) . '</th>';
                }, $headers)) . '</tr></thead><tbody>';
                foreach ($rows as $row) {
                    $html[] = '<tr>' . implode('', array_map(function ($cell) {
                        return '<td>' . self::inline($cell, false) . '</td>';
                    }, $row)) . '</tr>';
                }
                $html[] = '</tbody></table>';
                continue;
            }

            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $m)) {
                $flushParagraph();
                $flushList();
                $flushQuote();
                $level = strlen($m[1]);
                $html[] = '<h' . $level . '>' . self::inline($m[2], false) . '</h' . $level . '>';
                continue;
            }

            if (preg_match('/^\s*(?:---+|\*\*\*+|___+)\s*$/', $line)) {
                $flushParagraph();
                $flushList();
                $flushQuote();
                $html[] = '<hr>';
                continue;
            }

            if (preg_match('/^\s*>\s?(.*)$/', $line, $m)) {
                $flushParagraph();
                $flushList();
                $quote[] = $m[1];
                continue;
            }
            $flushQuote();

            if (preg_match('/^\s*[-+*]\s+(.+)$/', $line, $m)) {
                $flushParagraph();
                if ($listType !== 'ul') {
                    $flushList();
                    $html[] = '<ul>';
                    $listType = 'ul';
                }
                $html[] = '<li>' . self::inline($m[1], false) . '</li>';
                continue;
            }
            if (preg_match('/^\s*\d+[.)]\s+(.+)$/', $line, $m)) {
                $flushParagraph();
                if ($listType !== 'ol') {
                    $flushList();
                    $html[] = '<ol>';
                    $listType = 'ol';
                }
                $html[] = '<li>' . self::inline($m[1], false) . '</li>';
                continue;
            }
            $flushList();

            $paragraph[] = $line;
        }

        if ($inCode) {
            $html[] = '<pre><code>' . htmlspecialchars(implode("\n", $code), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>';
        }
        $flushParagraph();
        $flushList();
        $flushQuote();

        return self::restoreTextColors(HtmlSanitizer::clean(implode("\n", $html)));
    }

    /**
     * Render a short Markdown fragment without adding paragraph/list wrappers.
     * Used only by template slots whose cloned DOM requires inline content.
     */
    public static function renderInline($markdown)
    {
        $markdown = str_replace(["\r\n", "\r"], "\n", (string)$markdown);
        if (trim($markdown) === '') {
            return '';
        }
        return self::restoreTextColors(HtmlSanitizer::clean(self::inline($markdown, true)));
    }

    public static function plainText($markdown)
    {
        $html = self::render($markdown);
        if ($html === '') return '';
        $text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $html);
        $text = preg_replace('/<\/(?:p|h[1-6]|li|blockquote|tr|pre)>/i', "\n", $text);
        $text = preg_replace('/<\/(?:td|th)>/i', "\t", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+\n/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    private static function inline($text, $preserveLineBreaks)
    {
        $text = htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // Trusted editor syntax for partial text color. The source is escaped
        // first, so only this validated marker can create the temporary span.
        $text = preg_replace_callback('/\\[color=(#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?)\\]([\\s\\S]*?)\\[\\/color\\]/u', function ($m) {
            $color = strtolower($m[1]);
            if (strlen($color) === 4) {
                $color = '#' . $color[1] . $color[1] . $color[2] . $color[2] . $color[3] . $color[3];
            }
            return '<span data-cms-text-color="' . $color . '">' . $m[2] . '</span>';
        }, $text);

        // Images before links so ![...](...) is not consumed by link matching.
        $text = preg_replace_callback('/!\[([^\]]*)\]\(([^\s\)]+)(?:\s+&quot;([^&]*)&quot;)?\)/u', function ($m) {
            $url = self::safeUrl(html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($url === '') return $m[1];
            $alt = $m[1];
            $title = isset($m[3]) && $m[3] !== '' ? ' title="' . $m[3] . '"' : '';
            return '<img src="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" alt="' . $alt . '"' . $title . '>';
        }, $text);

        $text = preg_replace_callback('/\[([^\]]+)\]\(([^\s\)]+)(?:\s+&quot;([^&]*)&quot;)?\)/u', function ($m) {
            $url = self::safeUrl(html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $label = $m[1];
            if ($url === '') return $label;
            $title = isset($m[3]) && $m[3] !== '' ? ' title="' . $m[3] . '"' : '';
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' . $title . '>' . $label . '</a>';
        }, $text);

        $text = preg_replace('/`([^`\n]+)`/u', '<code>$1</code>', $text);
        $text = preg_replace('/\*\*([^*\n]+)\*\*/u', '<strong>$1</strong>', $text);
        $text = preg_replace('/__([^_\n]+)__/u', '<strong>$1</strong>', $text);
        $text = preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/u', '<em>$1</em>', $text);
        $text = preg_replace('/(?<!_)_([^_\n]+)_(?!_)/u', '<em>$1</em>', $text);
        $text = preg_replace('/~~([^~\n]+)~~/u', '<s>$1</s>', $text);

        if ($preserveLineBreaks) {
            $text = preg_replace('/ {2,}\n/u', '<br>', $text);
            $text = str_replace("\n", ' ', $text);
        }
        return $text;
    }

    private static function restoreTextColors($html)
    {
        return preg_replace_callback(
            '/\\sdata-cms-text-color=(["\\'])(#[0-9a-f]{6})\\1/u',
            function ($m) {
                return ' style="color:' . $m[2] . '"';
            },
            (string)$html
        );
    }

    private static function safeUrl($url)
    {
        $url = trim((string)$url);
        if ($url === '') return '';
        if (preg_match('/^(?:javascript|vbscript|data):/i', $url)) return '';
        if (preg_match('~^(?:https?:|mailto:|tel:|/|\./|\.\./|#|\?)~i', $url)) return $url;
        // Relative CMS routes such as products/fsq are valid as well.
        if (!preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)) return $url;
        return '';
    }

    private static function isTableSeparator($line)
    {
        $cells = self::tableCells($line);
        if (!$cells) return false;
        foreach ($cells as $cell) {
            if (!preg_match('/^:?-{3,}:?$/', trim($cell))) return false;
        }
        return true;
    }

    private static function tableCells($line)
    {
        $line = trim((string)$line);
        if ($line === '') return [];
        if ($line[0] === '|') $line = substr($line, 1);
        if ($line !== '' && substr($line, -1) === '|') $line = substr($line, 0, -1);
        return array_map('trim', preg_split('~(?<!\\\\)\|~', $line));
    }
}
