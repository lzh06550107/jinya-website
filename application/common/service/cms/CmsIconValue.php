<?php
namespace app\common\service\cms;

class CmsIconValue
{
    public function isFontAwesome($value)
    {
        $value = $this->normalizeFontWhitespace($value);
        return $value !== '' && preg_match('/^fa\s+fa-[a-z0-9-]+$/', $value) === 1;
    }

    public function view($value)
    {
        $raw = trim(is_scalar($value) ? (string)$value : '');
        if ($raw === '') {
            return ['type' => 'empty', 'class' => '', 'url' => ''];
        }

        $font = $this->normalizeFontWhitespace($raw);
        if ($this->isFontAwesome($font)) {
            return ['type' => 'font', 'class' => $font, 'url' => ''];
        }

        return ['type' => 'image', 'class' => '', 'url' => $raw];
    }

    public function decorateRow(array $row, $field = 'icon', $viewField = 'icon_view')
    {
        $row[$viewField] = $this->view(isset($row[$field]) ? $row[$field] : '');
        return $row;
    }

    public function decorateRows(array $rows, $field = 'icon', $viewField = 'icon_view')
    {
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $out[] = $this->decorateRow($row, $field, $viewField);
        }
        return $out;
    }

    private function normalizeFontWhitespace($value)
    {
        $value = trim(is_scalar($value) ? (string)$value : '');
        return preg_replace('/\s+/', ' ', $value);
    }
}
