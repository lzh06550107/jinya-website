import http from "node:http";
import fs from "node:fs";
import path from "node:path";
import process from "node:process";
import { fileURLToPath } from "node:url";
import { chromium } from "playwright";

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, "..");
const artifactRoot = path.join(root, "artifacts", "viewport-audit");
fs.mkdirSync(artifactRoot, { recursive: true });

const pcPages = [
  "index.html",
  "labels.html",
  "bags.html",
  "boxes.html",
  "about.html",
  "news.html",
  "news-detail.html",
  "contact.html"
];

const mobilePages = pcPages.map((page) => "mobile/" + page);

const pcViewports = [
  { name: "pc-1366x768", width: 1366, height: 768 },
  { name: "pc-1440x900", width: 1440, height: 900 },
  { name: "pc-1600x900", width: 1600, height: 900 },
  { name: "pc-1920x1080", width: 1920, height: 1080 },
  { name: "pc-2560x1440", width: 2560, height: 1440 },
  { name: "pc-3840x2160", width: 3840, height: 2160 }
];

const mobileViewports = [
  { name: "mobile-390x844", width: 390, height: 844 },
  { name: "mobile-430x932", width: 430, height: 932 },
  { name: "mobile-768x1024", width: 768, height: 1024 }
];

const screenshotViewports = new Set([
  "pc-1366x768",
  "pc-1920x1080",
  "pc-3840x2160",
  "mobile-390x844",
  "mobile-768x1024"
]);

const mimeTypes = {
  ".html": "text/html; charset=utf-8",
  ".css": "text/css; charset=utf-8",
  ".js": "text/javascript; charset=utf-8",
  ".json": "application/json; charset=utf-8",
  ".svg": "image/svg+xml",
  ".png": "image/png",
  ".jpg": "image/jpeg",
  ".jpeg": "image/jpeg",
  ".webp": "image/webp",
  ".gif": "image/gif",
  ".ico": "image/x-icon",
  ".mp4": "video/mp4",
  ".webm": "video/webm"
};

function localFileForRequest(requestUrl) {
  const url = new URL(requestUrl, "http://127.0.0.1");
  let pathname = decodeURIComponent(url.pathname);
  if (pathname === "/") pathname = "/index.html";
  const candidate = path.resolve(root, "." + pathname);
  if (candidate !== root && !candidate.startsWith(root + path.sep)) return null;
  return candidate;
}

const server = http.createServer((req, res) => {
  const file = localFileForRequest(req.url || "/");
  if (!file || !fs.existsSync(file) || !fs.statSync(file).isFile()) {
    res.writeHead(404, { "Content-Type": "text/plain; charset=utf-8" });
    res.end("Not found");
    return;
  }

  const ext = path.extname(file).toLowerCase();
  res.writeHead(200, {
    "Content-Type": mimeTypes[ext] || "application/octet-stream",
    "Cache-Control": "no-store"
  });
  fs.createReadStream(file).pipe(res);
});

await new Promise((resolve) => server.listen(0, "127.0.0.1", resolve));
const address = server.address();
const baseUrl = `http://127.0.0.1:${address.port}`;

const browser = await chromium.launch({ headless: true });
const failures = [];
const checks = [];

function addFailure(pageName, viewport, message) {
  failures.push({ page: pageName, viewport: viewport.name, message });
}

async function scrollThrough(page) {
  await page.evaluate(async () => {
    const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
    const max = Math.max(
      document.documentElement.scrollHeight,
      document.body ? document.body.scrollHeight : 0
    );
    const step = Math.max(500, Math.floor(window.innerHeight * 0.8));
    for (let y = 0; y < max; y += step) {
      window.scrollTo(0, y);
      await delay(25);
    }
    window.scrollTo(0, max);
    await delay(80);
    window.scrollTo(0, 0);
    await delay(40);
  });
}

async function auditPage(browserContext, pageName, viewport, mobile) {
  const page = await browserContext.newPage();
  await page.setViewportSize({ width: viewport.width, height: viewport.height });

  const consoleErrors = [];
  const pageErrors = [];
  const badResponses = [];

  page.on("console", (msg) => {
    if (msg.type() === "error") consoleErrors.push(msg.text());
  });
  page.on("pageerror", (error) => pageErrors.push(String(error)));
  page.on("response", (response) => {
    const url = response.url();
    if (url.startsWith(baseUrl) && response.status() >= 400) {
      badResponses.push(`${response.status()} ${url.replace(baseUrl, "")}`);
    }
  });

  const target = `${baseUrl}/${pageName}`;
  await page.goto(target, { waitUntil: "networkidle" });

  // Probe every image URL independently. This validates actual decoding
  // without depending on lazy-loading position or an inactive swiper slide.
  const imageProbeFailures = await page.evaluate(async () => {
    const urls = Array.from(new Set(
      Array.from(document.images)
        .map((img) => img.currentSrc || img.src)
        .filter(Boolean)
    ));

    const results = await Promise.all(urls.map((src) => new Promise((resolve) => {
      const probe = new Image();
      probe.onload = () => resolve(null);
      probe.onerror = () => resolve(src);
      probe.src = src;
    })));

    return results.filter(Boolean);
  });

  await scrollThrough(page);

  const state = await page.evaluate((isMobile) => {
    const style = (el) => el ? getComputedStyle(el) : null;
    const header = document.querySelector(".site-header");
    const shellNav = document.querySelector(".site-nav");
    const main = document.querySelector("main");
    const footer = document.querySelector(".site-footer");
    const mainNav = document.querySelector(".main-nav");
    const toggle = document.querySelector(".nav-toggle");
    const hero = document.querySelector(".hero--home, .hero--inner");

    const heroRect = hero ? hero.getBoundingClientRect() : null;

    function rectInfo(el) {
      if (!el) return null;
      const r = el.getBoundingClientRect();
      return {
        left: r.left,
        top: r.top,
        right: r.right,
        bottom: r.bottom,
        width: r.width,
        height: r.height
      };
    }

    function visible(el) {
      if (!el) return false;
      const s = getComputedStyle(el);
      return s.display !== "none" && s.visibility !== "hidden" && Number(s.opacity || 1) !== 0;
    }

    function overlap(a, b) {
      if (!a || !b) return 0;
      const w = Math.max(0, Math.min(a.right, b.right) - Math.max(a.left, b.left));
      const h = Math.max(0, Math.min(a.bottom, b.bottom) - Math.max(a.top, b.top));
      return w * h;
    }

    const logo = document.querySelector(".logo");
    const phone = document.querySelector(".header-phone");
    const floatSide = document.querySelector(".float-side");

    const logoRect = visible(logo) ? rectInfo(logo) : null;
    const phoneRect = visible(phone) ? rectInfo(phone) : null;
    const navRect = visible(mainNav) ? rectInfo(mainNav) : null;
    const navLinkRects = mainNav
      ? Array.from(mainNav.querySelectorAll("a")).filter(visible).map(rectInfo)
      : [];
    const toggleRect = visible(toggle) ? rectInfo(toggle) : null;
    const floatRect = visible(floatSide) ? rectInfo(floatSide) : null;

    const clippedText = Array.from(document.querySelectorAll(
      "h1,h2,h3,h4,.main-nav a,.header-phone .label,.hero-cta,.btn,.app-tabs button,.gallery-tabs button"
    ))
      .filter((el) => visible(el))
      .filter((el) => el.clientWidth > 0 && el.clientHeight > 0)
      .filter((el) => el.scrollWidth > el.clientWidth + 2 || el.scrollHeight > el.clientHeight + 2)
      .map((el) => {
        const text = (el.textContent || "").trim().replace(/\s+/g, " ").slice(0, 80);
        return `${el.tagName.toLowerCase()}.${String(el.className || "").replace(/\s+/g, ".")}: ${text}`;
      });

    const htmlWidth = document.documentElement.scrollWidth;
    const bodyWidth = document.body ? document.body.scrollWidth : htmlWidth;

    const shellOrderOk = !!(
      header && shellNav && main && footer &&
      (header.compareDocumentPosition(shellNav) & Node.DOCUMENT_POSITION_FOLLOWING) &&
      (shellNav.compareDocumentPosition(main) & Node.DOCUMENT_POSITION_FOLLOWING) &&
      (main.compareDocumentPosition(footer) & Node.DOCUMENT_POSITION_FOLLOWING)
    );

    return {
      mainCount: document.querySelectorAll("main").length,
      shellPresent: !!(header && shellNav && main && footer),
      shellOrderOk,
      overflow: Math.max(htmlWidth, bodyWidth) - window.innerWidth,
      heroLeft: heroRect ? heroRect.left : null,
      heroRight: heroRect ? heroRect.right : null,
      toggleDisplay: toggle ? style(toggle).display : null,
      toggleExpanded: toggle ? toggle.getAttribute("aria-expanded") : null,
      navDisplay: mainNav ? style(mainNav).display : null,
      navAriaHidden: mainNav ? mainNav.getAttribute("aria-hidden") : null,
      expectedMobile: isMobile,
      currentPath: location.pathname,
      logoRect,
      phoneRect,
      navRect,
      navLinkRects,
      toggleRect,
      floatRect,
      headerOverlap: {
        logoNav: Math.max(0, ...navLinkRects.map((rect) => overlap(logoRect, rect))),
        navPhone: Math.max(0, ...navLinkRects.map((rect) => overlap(rect, phoneRect))),
        logoPhone: overlap(logoRect, phoneRect),
        logoToggle: overlap(logoRect, toggleRect),
        phoneToggle: overlap(phoneRect, toggleRect)
      },
      clippedText
    };
  }, mobile);

  if (!state.shellPresent) addFailure(pageName, viewport, "缺少 header/nav/main/footer 公共壳");
  if (!state.shellOrderOk) addFailure(pageName, viewport, "header → nav → main → footer DOM 顺序异常");
  if (state.mainCount !== 1) addFailure(pageName, viewport, `main 数量异常: ${state.mainCount}`);
  if (state.overflow > 2) addFailure(pageName, viewport, `存在横向溢出: ${state.overflow}px`);

  if (state.heroLeft !== null && (state.heroLeft < -2 || state.heroRight > viewport.width + 2)) {
    addFailure(
      pageName,
      viewport,
      `Banner 越界: left=${state.heroLeft.toFixed(1)}, right=${state.heroRight.toFixed(1)}`
    );
  }

  function rectOutsideViewport(rect) {
    if (!rect) return false;
    return rect.left < -2 || rect.right > viewport.width + 2 || rect.top < -2;
  }

  [state.logoRect, state.phoneRect, state.toggleRect].forEach((rect, idx) => {
    if (rectOutsideViewport(rect)) {
      const names = ["Logo", "电话区", "菜单按钮"];
      addFailure(pageName, viewport, `${names[idx]} 超出可视区域`);
    }
  });

  if (!mobile || state.navAriaHidden !== "true") {
    state.navLinkRects.forEach((rect, idx) => {
      if (rectOutsideViewport(rect)) {
        addFailure(pageName, viewport, `第 ${idx + 1} 个导航链接超出可视区域`);
      }
    });
  }

  if (state.floatRect && state.floatRect.right > viewport.width + 2) {
    addFailure(pageName, viewport, "右侧浮动工具栏超出视口");
  }

  state.clippedText.forEach((item) => {
    addFailure(pageName, viewport, `关键文字可能被裁切: ${item}`);
  });

  if (mobile) {
    if (state.headerOverlap.logoToggle > 1) {
      addFailure(pageName, viewport, "Mobile Logo 与菜单按钮发生重叠");
    }
    if (state.headerOverlap.phoneToggle > 1) {
      addFailure(pageName, viewport, "Mobile 电话入口与菜单按钮发生重叠");
    }
    if (state.headerOverlap.logoPhone > 1) {
      addFailure(pageName, viewport, "Mobile Logo 与电话入口发生重叠");
    }

    if (state.toggleDisplay === "none") addFailure(pageName, viewport, "Mobile 导航按钮不可见");
    if (state.toggleExpanded !== "false") {
      addFailure(pageName, viewport, `Mobile 初始 aria-expanded 异常: ${state.toggleExpanded}`);
    }
    if (state.navAriaHidden !== "true") {
      addFailure(pageName, viewport, `Mobile 初始导航 aria-hidden 异常: ${state.navAriaHidden}`);
    }
  } else {
    if (state.headerOverlap.logoNav > 1) {
      addFailure(pageName, viewport, "PC Logo 与主导航发生重叠");
    }
    if (state.headerOverlap.navPhone > 1) {
      addFailure(pageName, viewport, "PC 主导航与电话区发生重叠");
    }
    if (state.headerOverlap.logoPhone > 1) {
      addFailure(pageName, viewport, "PC Logo 与电话区发生重叠");
    }

    if (state.toggleDisplay !== "none") addFailure(pageName, viewport, "PC 导航按钮不应显示");
    if (state.navDisplay === "none") addFailure(pageName, viewport, "PC 主导航不可见");
    if (state.navAriaHidden === "true") addFailure(pageName, viewport, "PC 主导航不应 aria-hidden=true");
  }

  imageProbeFailures.forEach((src) => {
    addFailure(pageName, viewport, `图片解码失败: ${src.replace(baseUrl, "")}`);
  });
  consoleErrors.forEach((message) => addFailure(pageName, viewport, `console.error: ${message}`));
  pageErrors.forEach((message) => addFailure(pageName, viewport, `pageerror: ${message}`));
  badResponses.forEach((message) => addFailure(pageName, viewport, `本地资源请求失败: ${message}`));

  if (screenshotViewports.has(viewport.name)) {
    const safePage = pageName.replace(/[/?=&]/g, "_").replace(/\.html$/i, "");
    const dir = path.join(artifactRoot, safePage);
    fs.mkdirSync(dir, { recursive: true });
    await page.screenshot({
      path: path.join(dir, viewport.name + ".jpg"),
      type: "jpeg",
      quality: 82,
      fullPage: true
    });
  }

  checks.push({ page: pageName, viewport: viewport.name, state });
  await page.close();
}

try {
  for (const viewport of pcViewports) {
    const context = await browser.newContext({
      viewport: { width: viewport.width, height: viewport.height },
      reducedMotion: "reduce"
    });
    for (const pageName of pcPages) {
      await auditPage(context, pageName, viewport, false);
    }
    await context.close();
  }

  for (const viewport of mobileViewports) {
    const context = await browser.newContext({
      viewport: { width: viewport.width, height: viewport.height },
      isMobile: true,
      hasTouch: true,
      reducedMotion: "reduce"
    });
    for (const pageName of mobilePages) {
      await auditPage(context, pageName, viewport, true);
    }
    await context.close();
  }
} finally {
  await browser.close();
  await new Promise((resolve) => server.close(resolve));
}

const report = {
  generatedAt: new Date().toISOString(),
  pcPages,
  mobilePages,
  pcViewports,
  mobileViewports,
  checkCount: checks.length,
  failureCount: failures.length,
  failures,
  checks
};

fs.writeFileSync(
  path.join(artifactRoot, "report.json"),
  JSON.stringify(report, null, 2),
  "utf8"
);

console.log(`[viewport-audit] checked ${checks.length} page/viewport combinations`);
console.log(`[viewport-audit] failures: ${failures.length}`);
for (const failure of failures) {
  console.error(
    `  ERROR ${failure.page} @ ${failure.viewport}: ${failure.message}`
  );
}

if (failures.length) process.exit(1);
