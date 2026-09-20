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

const pageData = new Map();

for (const page of allPages) {
  const full = path.join(root, page);
  if (!fs.existsSync(full)) {
    fail(page, "页面文件不存在");
    continue;
  }

  const html = read(page);
  pageData.set(page, html);

  if (count(html, /<main\b/g) !== 1) fail(page, "必须且只能有一个 <main>");
  if (count(html, /<header\b[^>]*class="[^"]*site-header/g) !== 1) fail(page, "必须且只能有一个 site-header");
  if (count(html, /<nav\b[^>]*class="[^"]*site-nav/g) !== 1) fail(page, "必须且只能有一个 site-nav");
  if (count(html, /<footer\b[^>]*class="[^"]*site-footer/g) !== 1) fail(page, "必须且只能有一个 site-footer");

  if (!html.includes('aria-controls="main-navigation"')) fail(page, "nav-toggle 缺少 aria-controls");
  if (count(html, /id="main-navigation"/g) !== 1) fail(page, "main-navigation id 必须且只能出现一次");

  const images = [...html.matchAll(/<img\b[^>]*>/g)].map((m) => m[0]);
  images.forEach((tag, i) => {
    if (!/\balt="/.test(tag)) fail(page, `第 ${i + 1} 个 <img> 缺少 alt`);
    if (!/\bwidth="/.test(tag) || !/\bheight="/.test(tag)) {
      warn(page, `第 ${i + 1} 个 <img> 未显式声明 width/height`);
    }
  });

  const placeholders = count(html, /href="#"/g);
  if (placeholders) warn(page, `仍有 ${placeholders} 个 href="#" 占位链接`);

  for (const match of html.matchAll(/(?:src|href)="([^"]+)"/g)) {
    const target = resolveLocalReference(page, match[1]);
    if (!target) continue;

    const absolute = path.join(root, target);
    if (!fs.existsSync(absolute)) {
      fail(page, `本地引用不存在: ${match[1]}`);
    }
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
