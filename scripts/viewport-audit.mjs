import fs from "node:fs";
import path from "node:path";
import http from "node:http";
import { chromium } from "playwright";

const root = path.join(process.cwd(), "html");
const out = path.join(process.cwd(), "artifacts", "viewport-audit");
fs.mkdirSync(out, { recursive: true });

const mime = {
  ".html":"text/html; charset=utf-8", ".css":"text/css; charset=utf-8", ".js":"text/javascript; charset=utf-8",
  ".png":"image/png", ".jpg":"image/jpeg", ".jpeg":"image/jpeg", ".webp":"image/webp", ".svg":"image/svg+xml",
  ".gif":"image/gif", ".ico":"image/x-icon", ".mp4":"video/mp4", ".woff":"font/woff", ".woff2":"font/woff2"
};

const server = http.createServer((req, res) => {
  const raw = decodeURIComponent((req.url || "/").split("?")[0]);
  let rel = raw === "/" ? "index.html" : raw.replace(/^\/+/, "");
  const file = path.resolve(root, rel);
  if (!(file === root || file.startsWith(root + path.sep)) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) {
    res.writeHead(404, {"content-type":"text/plain; charset=utf-8"}); res.end("Not found"); return;
  }
  res.writeHead(200, {"content-type": mime[path.extname(file).toLowerCase()] || "application/octet-stream"});
  fs.createReadStream(file).pipe(res);
});
await new Promise((resolve) => server.listen(4173, "127.0.0.1", resolve));

const jobs = [
  ...["index.html","labels.html","bags.html","boxes.html","about.html","news.html","news-detail.html","contact.html"]
    .map(page => ({page, width:2048, height:1536, tag:"desktop-2048"})),
  {page:"index.html", width:3840, height:2160, tag:"desktop-4k"},
  ...["mobile/index.html","mobile/labels.html","mobile/bags.html","mobile/boxes.html","mobile/about.html","mobile/news.html","mobile/news-detail.html","mobile/contact.html"]
    .map(page => ({page, width:430, height:932, tag:"mobile-430"})),
];

const browser = await chromium.launch({ headless:true });
let failed = false;
for (const job of jobs) {
  const context = await browser.newContext({ viewport:{width:job.width,height:job.height}, deviceScaleFactor:1 });
  const page = await context.newPage();
  const problems = [];
  page.on("pageerror", err => problems.push(`pageerror: ${err.message}`));
  page.on("console", msg => { if (msg.type() === "error") problems.push(`console: ${msg.text()}`); });
  page.on("response", response => {
    if (response.status() >= 400 && new URL(response.url()).origin === "http://127.0.0.1:4173") {
      problems.push(`HTTP ${response.status()}: ${response.url()}`);
    }
  });
  const response = await page.goto(`http://127.0.0.1:4173/${job.page}`, { waitUntil:"networkidle" });
  if (!response || response.status() !== 200) problems.push(`document status: ${response?.status()}`);
  await page.screenshot({ path:path.join(out, `${job.tag}-${job.page.replaceAll("/","-")}.png`), fullPage:true });
  const metrics = await page.evaluate(() => ({
    scrollWidth: document.documentElement.scrollWidth,
    clientWidth: document.documentElement.clientWidth,
    mainCount: document.querySelectorAll("main").length,
    header: !!document.querySelector(".site-header"),
    footer: !!document.querySelector(".site-footer"),
  }));
  if (metrics.scrollWidth > metrics.clientWidth + 2) problems.push(`horizontal overflow ${metrics.scrollWidth} > ${metrics.clientWidth}`);
  if (metrics.mainCount !== 1) problems.push(`main count = ${metrics.mainCount}`);
  if (!metrics.header || !metrics.footer) problems.push("missing site header/footer");
  if (problems.length) {
    failed = true;
    console.error(`VIEWPORT FAIL [${job.tag} ${job.page}]\n - ${problems.join("\n - ")}`);
  } else {
    console.log(`VIEWPORT OK [${job.tag} ${job.page}]`);
  }
  await context.close();
}
await browser.close();
await new Promise(resolve => server.close(resolve));
if (failed) process.exit(1);
