<?php

namespace app\common\service\cms;

/**
 * CMS internal URL generator and legacy-link sanitizer for the current site.
 *
 * Only routes that still exist are generated. Product routes remain active; retired case/
 * construction URLs are neutralized so upgraded databases cannot recreate
 * dead links. Historical company subpages are folded into the retained
 * "about" page.
 */
class CmsUrlService
{
    private const CURRENT_PAGES = ['label', 'bags', 'boxes', 'about', 'contact'];

    public function productIndex()
    {
        return '/products';
    }

    public function productCategory($slug)
    {
        $slug = trim((string)$slug, " /\t\n\r\0\x0B");
        return $slug === '' ? $this->productIndex() : $this->withQuery($this->productIndex(), ['category' => $slug]);
    }

    public function productDetail($slug)
    {
        return '/product/' . rawurlencode(trim((string)$slug, " /\t\n\r\0\x0B"));
    }

    public function newsIndex()
    {
        return '/news';
    }

    public function newsCategory($slug)
    {
        $slug = trim((string)$slug, " /\t\n\r\0\x0B");
        return $slug === '' ? $this->newsIndex() : '/news-list/' . rawurlencode($slug);
    }

    public function newsDetail($slug)
    {
        return '/news/' . rawurlencode(trim((string)$slug, " /\t\n\r\0\x0B"));
    }

    public function page($slug)
    {
        $slug = trim((string)$slug, " /\t\n\r\0\x0B");
        if (strpos($slug, 'page/') === 0) {
            $slug = substr($slug, 5);
        }
        $slug = $this->pageAlias($slug);
        if ($slug === 'about') {
            return '/page/about';
        }
        if (!in_array($slug, self::CURRENT_PAGES, true)) {
            return '#';
        }
        return '/page/' . rawurlencode($slug);
    }

    public function search(array $query = [])
    {
        return $this->withQuery('/search', $query);
    }

    public function sitemap()
    {
        return '/sitemap';
    }

    public function withPage($url, $page)
    {
        $page = max(1, (int)$page);
        if ($page <= 1 || $url === '#') {
            return (string)$url;
        }
        return $this->mergeQuery((string)$url, ['page' => $page]);
    }

    /**
     * Normalize a stored internal URL without reviving retired routes.
     *
     * @param string $url
     * @param string $typeHint article|case|product|page for old /articles/*.html
     * @return string
     */
    public function normalizeInternal($url, $typeHint = '')
    {
        $url = trim((string)$url);
        if ($url === '') {
            return $url;
        }
        $url = $this->internalizeCloneHost($url);
        if ($this->isExternalOrSpecial($url)) {
            return $url;
        }

        $fragment = '';
        $fragmentPos = strpos($url, '#');
        if ($fragmentPos !== false) {
            $fragment = substr($url, $fragmentPos);
            $url = substr($url, 0, $fragmentPos);
        }

        $query = [];
        $queryString = parse_url($url, PHP_URL_QUERY);
        if (is_string($queryString) && $queryString !== '') {
            parse_str($queryString, $query);
        }
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return $url . $fragment;
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        $mobile = false;
        if ($path === '/mobile' || strpos($path, '/mobile/') === 0) {
            $mobile = true;
            $path = $path === '/mobile' ? '/' : substr($path, 7);
            if ($path === '') {
                $path = '/';
            }
        }

        $page = 1;
        $normalized = null;

        if ($path === '/' || $path === '/index.html' || $path === '/index') {
            $normalized = '/';
        } elseif ($path === '/mobile.html') {
            $normalized = '/';
            $mobile = true;
        } elseif (preg_match('#^/product_index(?:_([0-9]{4}))?\.html$#i', $path, $m)) {
            $normalized = $this->productIndex();
            $page = !empty($m[1]) ? max(1, (int)$m[1]) : 1;
        } elseif ($path === '/products.html') {
            $normalized = $this->productIndex();
        } elseif (preg_match('#^/product_([a-zA-Z0-9_-]+?)(?:_([0-9]{4}))?\.html$#', $path, $m)) {
            $normalized = $m[1] === 'index' ? $this->productIndex() : $this->productCategory($m[1]);
            $page = !empty($m[2]) ? max(1, (int)$m[2]) : 1;
        } elseif (preg_match('#^/products/([a-zA-Z0-9_-]+)\.html$#', $path, $m)) {
            $normalized = $this->productDetail($m[1]);
        } elseif (preg_match('#^/product/([a-zA-Z0-9_-]+)\.html$#', $path, $m)) {
            $normalized = $this->productDetail($m[1]);
        } elseif ($this->isRetiredCasePath($path)) {
            $normalized = '#';
        } elseif ($path === '/article_xwdt.html' || $path === '/news' || $path === '/news.html') {
            $normalized = $this->newsIndex();
        } elseif (preg_match('#^/article_xwdt_([0-9]{4})\.html$#', $path, $m)) {
            $normalized = $this->newsIndex();
            $page = max(1, (int)$m[1]);
        } elseif (preg_match('#^/article_(cfwqtz|dpqgc)(?:_([0-9]{4}))?\.html$#', $path)) {
            $normalized = '#';
        } elseif (preg_match('#^/article_([a-zA-Z0-9_-]+?)(?:_([0-9]{4}))?\.html$#', $path, $m)) {
            $normalized = $this->newsCategory($m[1]);
            $page = !empty($m[2]) ? max(1, (int)$m[2]) : 1;
        } elseif (preg_match('#^/articles/([a-zA-Z0-9_-]+)\.html$#', $path, $m)) {
            $normalized = $this->detailByHint($m[1], $typeHint);
        } elseif (preg_match('#^/news/([a-zA-Z0-9_-]+)\.html$#', $path, $m)) {
            $normalized = $this->newsDetail($m[1]);
        } elseif (preg_match('#^/(?:help_|video_)([a-zA-Z0-9_-]+?)(?:_([0-9]{4}))?\.html$#', $path, $m)) {
            $normalized = $this->page($m[1]);
            $page = !empty($m[2]) ? max(1, (int)$m[2]) : 1;
        } elseif (preg_match('#^/helps/([a-zA-Z0-9_-]+)\.html$#', $path, $m)) {
            $normalized = $this->page($m[1]);
        } elseif (preg_match('#^/page/([a-zA-Z0-9_-]+)\.html$#', $path, $m)) {
            $normalized = $this->page($m[1]);
        } elseif (preg_match('#^/page/([a-zA-Z0-9_-]+)$#', $path, $m)) {
            $normalized = $this->page($m[1]);
        } elseif (preg_match('#^/news-list/([a-zA-Z0-9_-]+)$#', $path, $m)) {
            $normalized = $this->newsCategory($m[1]);
        } elseif (preg_match('#^/news/([a-zA-Z0-9_-]+)$#', $path, $m)) {
            $normalized = $this->newsDetail($m[1]);
        } elseif ($path === '/search' || $path === '/search.html' || $path === '/search.php') {
            $normalized = $this->search();
        } elseif ($path === '/Tools/leaveword.html' || $path === '/tools/leaveword.html') {
            $normalized = $this->page('contact');
        } elseif ($path === '/sitemap' || $path === '/sitemap.html') {
            $normalized = $this->sitemap();
        } else {
            $normalized = $this->normalizeCleanPath($path);
        }

        if ($normalized === '#') {
            return '#';
        }
        if ($page > 1) {
            $query['page'] = $page;
        }
        $normalized = $this->mergeQuery($normalized, $query);
        if ($mobile && $normalized !== '/') {
            $normalized = '/mobile' . $normalized;
        } elseif ($mobile && $normalized === '/') {
            $normalized = '/mobile';
        }
        return $normalized . $fragment;
    }

    private function detailByHint($slug, $typeHint)
    {
        switch (strtolower(trim((string)$typeHint))) {
            case 'case':
                return '#';
            case 'product':
                return $this->productDetail($slug);
            case 'page':
                return $this->page($slug);
            case 'article':
            case 'news':
            default:
                return $this->newsDetail($slug);
        }
    }

    private function pageAlias($slug)
    {
        $aliases = [
            'zjkcm' => 'about',
            'lxkcm' => 'contact',
            'ryzz' => 'about',
            'zlzs' => 'about',
            'gsxc' => 'about',
            'spzx' => 'about',
            'honor' => 'about',
            'patent' => 'about',
            'gallery' => 'about',
            'video' => 'about',
            'tlsg' => '__retired__',
            'construction' => '__retired__',
        ];
        return isset($aliases[$slug]) ? $aliases[$slug] : $slug;
    }

    private function isRetiredCasePath($path)
    {
        return $path === '/cases'
            || $path === '/cases.html'
            || $path === '/article_gcal.html'
            || (bool)preg_match('#^/article_gcal_[0-9]{4}\.html$#', $path)
            || (bool)preg_match('#^/case/[a-zA-Z0-9_-]+(?:\.html)?$#', $path);
    }

    private function normalizeCleanPath($path)
    {
        if ($this->isRetiredCasePath($path)) {
            return '#';
        }
        if (preg_match('#^/product/([a-zA-Z0-9_-]+)\.html$#', $path, $m)) {
            return $this->productDetail($m[1]);
        }
        if (preg_match('#^/products/([a-zA-Z0-9_-]+)\.html$#', $path, $m)) {
            return $this->productDetail($m[1]);
        }
        if (preg_match('#^/news/([a-zA-Z0-9_-]+)\.html$#', $path, $m)) {
            return $this->newsDetail($m[1]);
        }
        if (preg_match('#^/news-list/([a-zA-Z0-9_-]+)\.html$#', $path, $m)) {
            return $this->newsCategory($m[1]);
        }
        if (preg_match('#^/page/([a-zA-Z0-9_-]+)\.html$#', $path, $m)) {
            return $this->page($m[1]);
        }
        return $path;
    }

    private function internalizeCloneHost($url)
    {
        if (!preg_match('#^(?:https?:)?//#i', $url)) {
            return $url;
        }
        $parseTarget = strpos($url, '//') === 0 ? 'http:' . $url : $url;
        $host = strtolower((string)parse_url($parseTarget, PHP_URL_HOST));
        if (!in_array($host, ['ahkcm.com', 'www.ahkcm.com', 'm.ahkcm.com'], true)) {
            return $url;
        }
        $path = (string)parse_url($parseTarget, PHP_URL_PATH);
        $path = $path === '' ? '/' : '/' . ltrim($path, '/');
        if ($host === 'm.ahkcm.com') {
            $path = $path === '/' ? '/mobile' : '/mobile' . $path;
        }
        $query = (string)parse_url($parseTarget, PHP_URL_QUERY);
        $fragment = (string)parse_url($parseTarget, PHP_URL_FRAGMENT);
        return $path
            . ($query !== '' ? '?' . $query : '')
            . ($fragment !== '' ? '#' . $fragment : '');
    }

    private function isExternalOrSpecial($url)
    {
        return preg_match('#^(?:https?:)?//#i', $url)
            || preg_match('~^(?:mailto:|tel:|javascript:|data:|#)~i', $url);
    }

    private function withQuery($path, array $query)
    {
        $query = array_filter($query, function ($value) { return $value !== null && $value !== ''; });
        return $query ? $path . '?' . http_build_query($query) : $path;
    }

    private function mergeQuery($url, array $query)
    {
        if ($url === '#') {
            return '#';
        }
        $existing = [];
        $existingQuery = parse_url($url, PHP_URL_QUERY);
        if (is_string($existingQuery) && $existingQuery !== '') {
            parse_str($existingQuery, $existing);
        }
        $path = parse_url($url, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : $url;
        return $this->withQuery($path, array_merge($existing, $query));
    }
}
