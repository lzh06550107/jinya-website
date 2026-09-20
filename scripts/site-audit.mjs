import fs from "node:fs";
import path from "node:path";

const root = path.join(process.cwd(), "html");
const pages = [
  "index.html", "labels.html", "bags.html", "boxes.html",
  "about.html", "news.html", "news-detail.html", "contact.html",
  "mobile/index.html", "mobile/labels.html", "mobile/bags.html", "mobile/boxes.html",
  "mobile/about.html", "mobile/news.html", "mobile/news-detail.html", "mobile/contact.html",
];

let failed = false;
const fail = (m) => { failed = true; console.error("SITE AUDIT FAIL:", m); };

const strip = (url) => url.split("#")[0].split("?")[0].trim();
const localAsset = (url) => {
  if (!url || /^(?:https?:|data:|tel:|mailto:|javascript:|#)/i.test(url)) return null;
  return strip(url);
};

for (const rel of pages) {
  const file = path.join(root, rel);
  if (!fs.existsSync(file)) { fail(`Missing page: html/${rel}`); continue; }
  const html = fs.readFileSync(file, "utf8");
  for (const token of ["<header", "<nav", "<main", "</main>", "<footer"]) {
    if (!html.includes(token)) fail(`html/${rel} missing semantic token ${token}`);
  }
  if ((html.match(/<main\b/g) || []).length !== 1) fail(`html/${rel} must contain exactly one <main>`);

  const attrRe = /\b(?:src|href)=["']([^"']+)["']/gi;
  let match;
  while ((match = attrRe.exec(html))) {
    const local = localAsset(match[1]);
    if (!local || local.endsWith(".html") || local.startsWith("/")) continue;
    const target = path.resolve(path.dirname(file), local);
    if (!target.startsWith(root + path.sep) && target !== root) continue;
    if (!fs.existsSync(target)) fail(`html/${rel} references missing local asset: ${match[1]}`);
  }
}

const footerSocialIcons = [
  "social-wechat.png",
  "social-douyin.png",
  "social-xiaohongshu.png",
  "social-channels.png",
];

const validPng = (buffer) => {
  const signature = [0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a];
  if (buffer.length < 24 || !signature.every((byte, index) => buffer[index] === byte)) return null;
  const width = buffer.readUInt32BE(16);
  const height = buffer.readUInt32BE(20);
  let offset = 8;
  let hasIend = false;
  while (offset + 12 <= buffer.length) {
    const chunkLength = buffer.readUInt32BE(offset);
    const type = buffer.toString("ascii", offset + 4, offset + 8);
    const next = offset + 12 + chunkLength;
    if (next > buffer.length) return null;
    if (type === "IEND") {
      hasIend = true;
      break;
    }
    offset = next;
  }
  return hasIend ? { width, height } : null;
};

for (const name of footerSocialIcons) {
  const source = path.join(root, "assets", "img", name);
  const published = path.join(process.cwd(), "public", "assets", "jinya", "img", name);
  if (!fs.existsSync(source)) {
    fail(`Missing footer social icon: html/assets/img/${name}`);
    continue;
  }
  if (!fs.existsSync(published)) {
    fail(`Missing published footer social icon: public/assets/jinya/img/${name}`);
    continue;
  }
  const sourceBytes = fs.readFileSync(source);
  const publishedBytes = fs.readFileSync(published);
  const png = validPng(sourceBytes);
  if (!png) {
    fail(`Footer social icon is not a complete PNG: html/assets/img/${name}`);
    continue;
  }
  if (png.width < 60 || png.height < 60) {
    fail(`Footer social icon resolution is too small: ${name} (${png.width}x${png.height})`);
  }
  if (!sourceBytes.equals(publishedBytes)) {
    fail(`Footer social icon differs between html/assets and public/assets: ${name}`);
  }
}

if (failed) process.exit(1);
console.log(`Static html audit: OK (${pages.length} pages)`);
