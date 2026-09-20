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
if (failed) process.exit(1);
console.log(`Static html audit: OK (${pages.length} pages)`);
