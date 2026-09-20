#!/usr/bin/env node
import fs from "node:fs";
import path from "node:path";

const root = process.cwd();
const pcPages = [
  "index.html","labels.html","bags.html","boxes.html",
  "about.html","news.html","news-detail.html","contact.html"
];
const mobilePages = pcPages.map((name) => `mobile/${name}`);
const allPages = [...pcPages, ...mobilePages];
const canonicalBase = "https://jinya.ink";
const pageMeta = {
  "index.html":        { body:"pc-home",        canonical:`${canonicalBase}/`,                 mobile:`${canonicalBase}/mobile/` },
  "labels.html":       { body:"pc-labels",      canonical:`${canonicalBase}/labels.html`,      mobile:`${canonicalBase}/mobile/labels.html` },
  "bags.html":         { body:"pc-bags",        canonical:`${canonicalBase}/bags.html`,        mobile:`${canonicalBase}/mobile/bags.html` },
  "boxes.html":        { body:"pc-boxes",       canonical:`${canonicalBase}/boxes.html`,       mobile:`${canonicalBase}/mobile/boxes.html` },
  "about.html":        { body:"pc-about",       canonical:`${canonicalBase}/about.html`,       mobile:`${canonicalBase}/mobile/about.html` },
  "news.html":         { body:"pc-news",        canonical:`${canonicalBase}/news.html`,        mobile:`${canonicalBase}/mobile/news.html` },
  "news-detail.html":  { body:"pc-news-detail", canonical:`${canonicalBase}/news-detail.html`, mobile:`${canonicalBase}/mobile/news-detail.html` },
  "contact.html":      { body:"pc-contact",     canonical:`${canonicalBase}/contact.html`,     mobile:`${canonicalBase}/mobile/contact.html` }
};

let errors = [];
let warnings = [];

function read(rel) {
  return fs.readFileSync(path.join(root, rel), "utf8");
}

function count(text, regex) {
  return (text.match(regex) || []).length;
}

function fail(file, message) {
  errors.push(`${file}: ${message}`);
}

function warn(file, message) {
  warnings.push(`${file}: ${message}`);
}

function getVersion(html, fileName) {
  const escaped = fileName.replace(/[.*+?^$()|[\]\\]/g, "\\$&");
  const match = html.match(new RegExp(`${escaped}\\?v=(\\d+)`));
  return match ? match[1] : null;
}

function resolveLocalReference(page, raw) {
  if (
    !raw ||
    raw.startsWith("#") ||
    raw.startsWith("http://") ||
    raw.startsWith("https://") ||
    raw.startsWith("tel:") ||
    raw.startsWith("mailto:") ||
    raw.startsWith("javascript:")
  ) return null;

  const clean = raw.split("#")[0].split("?")[0];
  if (!clean) return null;
  return path.normalize(path.join(path.dirname(page), clean));
}


function extractBlock(html, startRegex, endTag) {
  const match = html.match(startRegex);
  if (!match) return null;
  const start = match.index;
  const end = html.indexOf(endTag, start);
  if (end < 0) return null;
  return html.slice(start, end + endTag.length);
}

function normalizeSharedMarkup(markup) {
  if (!markup) return null;
  return markup
    .replace(/\.\.\//g, "")
    .replace(/\?v=\d+/g, "")
    .replace(/\s+site-header--home/g, "")
    .replace(/\s+active\b/g, "")
    .replace(/\s+aria-current="page"/g, "")
    .replace(/\s+/g, " ")
    .trim();
}

const pageData = new Map();

for (const page of allPages) {
  const full = path.join(root, page);
  if (!fs.existsSync(full)) {
    fail(page, "页面文件不存在");
    continue;
  }

  const html = read(page);
  pageData.set(page, html);


  pageData.set(page + ":header", normalizeSharedMarkup(
    extractBlock(html, /<header\b[^>]*class="[^"]*site-header[^"]*">/, "</header>")
  ));
  pageData.set(page + ":nav", normalizeSharedMarkup(
    extractBlock(html, /<nav\b[^>]*class="[^"]*site-nav[^"]*"[^>]*>/, "</nav>")
  ));
  pageData.set(page + ":footer", normalizeSharedMarkup(
    extractBlock(html, /<footer\b[^>]*class="[^"]*site-footer[^"]*">/, "</footer>")
  ));

  if (count(html, /<main\b/g) !== 1) fail(page, "必须且只能有一个 <main>");
  if (count(html, /<header\b[^>]*class="[^"]*site-header/g) !== 1) fail(page, "必须且只能有一个 site-header");
  if (count(html, /<nav\b[^>]*class="[^"]*site-nav/g) !== 1) fail(page, "必须且只能有一个 site-nav");
  if (count(html, /<footer\b[^>]*class="[^"]*site-footer/g) !== 1) fail(page, "必须且只能有一个 site-footer");

  if (!html.includes('aria-controls="main-navigation"')) fail(page, "nav-toggle 缺少 aria-controls");
  if (count(html, /id="main-navigation"/g) !== 1) fail(page, "main-navigation id 必须且只能出现一次");


  const baseName = page.replace(/^mobile\//, "");
  const meta = pageMeta[baseName];
  const isMobile = page.startsWith("mobile/");
  const expectedBodyClass = isMobile ? `mobile-site mobile-${meta.body.replace(/^pc-/, "")}` : meta.body;
  if (!html.includes(`<body class="${expectedBodyClass}">`)) {
    fail(page, `body class 不符合约定，应为 "${expectedBodyClass}"`);
  }

  const mainNavMatch = html.match(/<div class="main-nav" id="main-navigation">([\s\S]*?)<\/div>/);
  if (!mainNavMatch) {
    fail(page, "无法解析主导航");
  } else {
    const navHtml = mainNavMatch[1];
    if (count(navHtml, /<a\b/g) !== 7) fail(page, "主导航必须保持 7 个入口");
    if (count(navHtml, /class="nav-link active"/g) !== 1) fail(page, "主导航必须且只能有一个 active 项");
    if (count(navHtml, /aria-current="page"/g) !== 1) fail(page, "active 导航必须声明 aria-current=\"page\"");
  }

  const canonicalMatch = html.match(/<link rel="canonical" href="([^"]+)">/);
  if (!canonicalMatch) {
    fail(page, "缺少 canonical");
  } else if (canonicalMatch[1] !== meta.canonical) {
    fail(page, `canonical 不正确: ${canonicalMatch[1]}`);
  }

  if (!isMobile) {
    const alternateMatch = html.match(/<link rel="alternate" media="only screen and \(max-width: 768px\)" href="([^"]+)">/);
    if (!alternateMatch) {
      fail(page, "PC 页面缺少 Mobile alternate");
    } else if (alternateMatch[1] !== meta.mobile) {
      fail(page, `Mobile alternate 不正确: ${alternateMatch[1]}`);
    }
  }

  if (!html.includes("邮箱：973123908@qq.com")) {
    fail(page, "Footer 邮箱文案不统一");
  }

  if (baseName === "news.html" && /class="pagination"/.test(html)) {
    fail(page, "新闻数据不足时不得显示无功能分页");
  }


  if (/href="#"[^>]*>MORE\+<\/a>/.test(html)) {
    fail(page, "业务区块 MORE+ 不得使用 href=\"#\" 占位");
  }


  if (html.includes("在线留言")) {
    fail(page, "当前站点没有留言表单，页面不得显示“在线留言”入口");
  }

  if (baseName === "news-detail.html") {
    if (!html.includes("assets/js/news-detail.js?v=1")) {
      fail(page, "新闻详情页必须使用共享 news-detail.js");
    }
    if (/new URLSearchParams/.test(html)) {
      fail(page, "新闻详情页不得重复内联文章切换脚本");
    }
  }

  const images = [...html.matchAll(/<img\b[^>]*>/g)].map((m) => m[0]);
  images.forEach((tag, i) => {
    if (!/\balt="/.test(tag)) fail(page, `第 ${i + 1} 个 <img> 缺少 alt`);
  });

  // Footer social/friend links may remain placeholders until real URLs are supplied.
  // Business content before the footer must never use href="#".
  const footerStart = html.indexOf('<footer class="site-footer">');
  const businessHtml = footerStart >= 0 ? html.slice(0, footerStart) : html;
  const businessPlaceholders = count(businessHtml, /href="#"/g);
  if (businessPlaceholders) {
    fail(page, `业务内容仍有 ${businessPlaceholders} 个 href="#" 占位链接`);
  }

  for (const match of html.matchAll(/(?:src|href)="([^"]+)"/g)) {
    const target = resolveLocalReference(page, match[1]);
    if (!target) continue;

    const absolute = path.join(root, target);
    if (!fs.existsSync(absolute)) {
      fail(page, `本地引用不存在: ${match[1]}`);
    }
  }
}

const shellKinds = ["header", "nav", "footer"];
for (const kind of shellKinds) {
  const variants = new Map();
  for (const page of allPages) {
    const normalized = pageData.get(page + ":" + kind);
    if (!normalized) {
      fail(page, `无法解析公共 ${kind}`);
      continue;
    }
    if (!variants.has(normalized)) variants.set(normalized, []);
    variants.get(normalized).push(page);
  }

  if (variants.size !== 1) {
    const groups = [...variants.values()].map((group) => group.join(", ")).join(" | ");
    fail(`shared-${kind}`, `公共结构发生漂移，共 ${variants.size} 个版本: ${groups}`);
  }
}

const pcStyles = new Set();
const pcScripts = new Set();
const mobileStyles = new Set();
const mobileScripts = new Set();
const mobileOnlyStyles = new Set();

for (const page of pcPages) {
  const html = pageData.get(page);
  if (!html) continue;
  pcStyles.add(getVersion(html, "style.css"));
  pcScripts.add(getVersion(html, "main.js"));
}

for (const page of mobilePages) {
  const html = pageData.get(page);
  if (!html) continue;
  mobileStyles.add(getVersion(html, "style.css"));
  mobileScripts.add(getVersion(html, "main.js"));
  mobileOnlyStyles.add(getVersion(html, "mobile.css"));
}

function validateSingleVersion(label, set) {
  if (set.has(null)) fail(label, "存在缺失版本号的资源引用");
  if (set.size !== 1) fail(label, `版本不统一: ${[...set].join(", ")}`);
}

validateSingleVersion("PC style.css", pcStyles);
validateSingleVersion("PC main.js", pcScripts);
validateSingleVersion("Mobile style.css", mobileStyles);
validateSingleVersion("Mobile main.js", mobileScripts);
validateSingleVersion("Mobile mobile.css", mobileOnlyStyles);

if ([...pcStyles][0] !== [...mobileStyles][0]) {
  fail("PC/Mobile", "style.css 版本必须一致");
}
if ([...pcScripts][0] !== [...mobileScripts][0]) {
  fail("PC/Mobile", "main.js 版本必须一致");
}

const robotsPath = path.join(root, "robots.txt");
const sitemapPath = path.join(root, "sitemap.xml");
if (!fs.existsSync(robotsPath)) {
  fail("robots.txt", "文件不存在");
} else {
  const robots = read("robots.txt");
  if (!robots.includes("Sitemap: https://jinya.ink/sitemap.xml")) {
    fail("robots.txt", "缺少正确的 Sitemap 地址");
  }
}
if (!fs.existsSync(sitemapPath)) {
  fail("sitemap.xml", "文件不存在");
} else {
  const sitemap = read("sitemap.xml");
  for (const url of [
    "https://jinya.ink/",
    "https://jinya.ink/labels.html",
    "https://jinya.ink/bags.html",
    "https://jinya.ink/boxes.html",
    "https://jinya.ink/about.html",
    "https://jinya.ink/news.html",
    "https://jinya.ink/contact.html"
  ]) {
    if (!sitemap.includes(`<loc>${url}</loc>`)) fail("sitemap.xml", `缺少 URL: ${url}`);
  }
}

const css = read("assets/css/style.css");
const js = read("assets/js/main.js");

if (/@media\s*\(max-width:1080px\)/.test(css)) {
  fail("assets/css/style.css", "禁止重新引入 max-width:1080px 手机导航断点；PC/Mobile 分界是 768px");
}
if (/--uw-/.test(css)) {
  fail("assets/css/style.css", "禁止重新引入已废弃的 --uw-* 页面框架变量");
}
for (const marker of ["v99","v100","v101","v106"]) {
  if (css.includes(` ${marker}`) || css.includes(`${marker} */`)) {
    fail("assets/css/style.css", `检测到已废弃响应式框架标记 ${marker}`);
  }
}
if (js.includes("releaseVersion") || js.includes("pageLinkPattern")) {
  fail("assets/js/main.js", "禁止恢复 HTML URL ?v=xx 自动改写逻辑");
}
if (!js.includes("function syncNavState()")) {
  fail("assets/js/main.js", "缺少导航 aria/open 状态同步");
}


const deadCssTokens = [
  ".site-header:not(.site-header--home) + main",
  ".logo-tagline",
  ".hero--gold",
  ".hero--prodbg",
  ".hero-figure",
  ".btn-primary",
  ".btn-outline",
  ".pagination",
  ".pg-next",
  ".pg-last",
  "--gold-deep:",
  "--gold:",
  "--gold-light:",
  "--shadow-lg:",
  "--home-content-max:",
  "--home-section-gap:",
  "--page-gutter:",
  ".boxes-benefit",
  ".boxes-benefit-icon",
  ".boxes-benefits",
  ".boxes-benefits-section",
  ".chip-grid",
  ".cta-banner",
  ".cta-banner-inner",
  ".cta-lines",
  ".cta-lines--muted",
  ".diagram-row",
  ".gradient-bar",
  ".label-print-reference",
  ".labels-material-media--stack-left",
  ".labels-material-media--stack-right",
  ".labels-print-nav-hint",
  ".material-media--stack-left",
  ".pcard",
  ".print-showcase",
  ".print-showcase-text",
  ".source-banner",
  ".team-benefit-icon",
  ".gallery-panel",
  ".source-strip",
  ".icon-card",
  ".step-card",
  ".cta-designer-text",
  ".craft-row",
  ".material-media--stack-right",
  ".print-showcase-photos",
  ".chip",
  ".dir-tile",
  ".thanks-banner",
  ".labels-print-head"
];
for (const token of deadCssTokens) {
  let found;
  if (token.startsWith(".")) {
    const escapedToken = token.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    found = new RegExp(escapedToken + "(?![\\w-])").test(css);
  } else {
    found = css.includes(token);
  }

  if (found) {
    fail("assets/css/style.css", `检测到已清理的废弃选择器/变量: ${token}`);
  }
}

const printTrackStretchCount =
  (css.match(/\.labels-print-swiper \.swiper-track\s*\{\s*align-items:stretch;\s*\}/g) || []).length;
if (printTrackStretchCount !== 1) {
  fail(
    "assets/css/style.css",
    `labels print swiper 的 align-items:stretch 应唯一，当前数量: ${printTrackStretchCount}`
  );
}

const importantCount = (css.match(/!important/g) || []).length;
if (importantCount > 127) {
  fail("assets/css/style.css", `!important 数量回升到 ${importantCount}，当前上限为 127`);
}


const emptyMediaBlocks = css.match(/@media[^\{]+\{\s*\}/g) || [];
if (emptyMediaBlocks.length) {
  fail("assets/css/style.css", `存在 ${emptyMediaBlocks.length} 个空 @media 块`);
}

console.log(`[site-audit] checked ${allPages.length} HTML pages`);
console.log(`[site-audit] style.css v${[...pcStyles][0] || "?"}, main.js v${[...pcScripts][0] || "?"}`);

if (warnings.length) {
  console.log(`[site-audit] warnings: ${warnings.length}`);
  warnings.forEach((item) => console.log(`  WARN ${item}`));
}

if (errors.length) {
  console.error(`[site-audit] errors: ${errors.length}`);
  errors.forEach((item) => console.error(`  ERROR ${item}`));
  process.exit(1);
}

console.log("[site-audit] PASS");
