import fs from "node:fs";
import path from "node:path";

const root = process.cwd();
const read = (p) => fs.readFileSync(path.join(root, p), "utf8");
const fail = (msg) => { console.error("DYNAMIC VIEW AUDIT FAIL:", msg); process.exitCode = 1; };
const expect = (condition, msg) => { if (!condition) fail(msg); };

const entryViews = [
  "application/index/view/cms/index/index.html",
  "application/mobile/view/cms/index/index.html",
  "application/index/view/cms/page/label.html",
  "application/mobile/view/cms/page/label.html",
  "application/index/view/cms/page/bags.html",
  "application/mobile/view/cms/page/bags.html",
  "application/index/view/cms/page/boxes.html",
  "application/mobile/view/cms/page/boxes.html",
  "application/index/view/cms/page/about.html",
  "application/mobile/view/cms/page/about.html",
  "application/index/view/cms/page/contact.html",
  "application/mobile/view/cms/page/contact.html",
  "application/index/view/cms/news/index.html",
  "application/mobile/view/cms/news/index.html",
  "application/index/view/cms/news/detail.html",
  "application/mobile/view/cms/news/detail.html",
];

for (const file of entryViews) {
  const body = read(file);
  expect(body.includes('cms/layout/header'), `${file} must use the html-baseline shared header`);
  expect(body.includes('cms/layout/footer'), `${file} must use the html-baseline shared footer`);
  expect(!body.includes('cms/common/strict_header'), `${file} must not use legacy strict_header`);
  expect(!body.includes('cms/common/strict_footer'), `${file} must not use legacy strict_footer`);
}

for (const file of [
  "application/index/view/cms/layout/header.html",
  "application/index/view/cms/layout/nav.html",
  "application/index/view/cms/layout/footer.html",
  "application/mobile/view/cms/layout/header.html",
  "application/mobile/view/cms/layout/nav.html",
  "application/mobile/view/cms/layout/footer.html",
]) {
  expect(fs.existsSync(path.join(root, file)), `${file} must exist`);
}

const pcHeader = read("application/index/view/cms/layout/header.html");
const mobileHeader = read("application/mobile/view/cms/layout/header.html");
for (const [file, body] of [["PC header", pcHeader], ["mobile header", mobileHeader]]) {
  expect(body.includes("/assets/jinya/css/style.css"), `${file} must load html/assets-derived style.css`);
  expect(body.includes("/assets/jinya/js/device-router.js"), `${file} must load html/assets-derived device-router.js`);
}
expect(mobileHeader.includes("/assets/jinya/css/mobile.css"), "mobile header must load mobile.css");

for (const file of [
  "public/assets/jinya/css/style.css",
  "public/assets/jinya/css/mobile.css",
  "public/assets/jinya/js/main.js",
  "public/assets/jinya/js/device-router.js",
]) {
  expect(fs.existsSync(path.join(root, file)), `${file} must be published from html/assets`);
}

const route = read("application/route.php");
for (const token of [
  "'index.html$'", "'labels.html$'", "'bags.html$'", "'boxes.html$'",
  "'about.html$'", "'contact.html$'", "'news.html$'",
  "'mobile/index.html$'", "'mobile/labels.html$'", "'mobile/bags.html$'",
  "'mobile/boxes.html$'", "'mobile/about.html$'", "'mobile/contact.html$'", "'mobile/news.html$'",
]) {
  expect(route.includes(token), `application/route.php missing static-style route ${token}`);
}

const labelCodec = read("application/common/service/cms/LabelPageBlockConfigCodec.php");
for (const token of ["label_print_title", "label_print_points", "label_secondary_title"]) {
  expect(labelCodec.includes(token), `Label codec missing ${token}`);
}
const bagsCodec = read("application/common/service/cms/BagsPageBlockConfigCodec.php");
for (const token of ["bags_compare_brand", "bags_compare_footer"]) {
  expect(bagsCodec.includes(token), `Bags codec missing ${token}`);
}
const boxesCodec = read("application/common/service/cms/BoxesPageBlockConfigCodec.php");
expect(boxesCodec.includes("boxes_badge_text"), "Boxes codec missing boxes_badge_text");

const schema = read("application/common/service/cms/PageContentBlockEditorSchema.php");
for (const token of [
  "label_print_title", "label_print_points", "print-image",
  "bags_compare_brand", "bags_compare_footer", "'photo'=>'流程场景图'",
  "六项服务保障", "boxes_badge_text", "英文副标题", "'link'=>'CTA 按钮'",
]) {
  expect(schema.includes(token), `Editor schema missing required html-baseline contract: ${token}`);
}

const adminForm = read("application/admin/view/cms/page_content_block/_form.html");
for (const token of [
  'name="row[label_print_title]"',
  'name="row[label_print_points]"',
  'name="row[bags_compare_brand]"',
  'name="row[bags_compare_footer]"',
  'name="row[boxes_badge_text]"',
]) {
  expect(adminForm.includes(token), `Admin form missing editable field ${token}`);
}

const factory = read("application/common/service/cms/render/PageBlockViewModelFactory.php");
expect(factory.includes("['print_points']"), "PageBlockViewModelFactory must register print_points for scalar markdown rendering");
expect(factory.includes("$scalarMarkdownKey . '_html'"), "PageBlockViewModelFactory must expose scalar markdown HTML keys");

const representativeViews = {
  "application/index/view/cms/page/label/materials.html": ["block.extra.print_title", "block.extra.print_points_html", "print-image"],
  "application/index/view/cms/page/bags/compare.html": ["block.extra.compare_brand", "block.extra.compare_footer", "tech-compare"],
  "application/index/view/cms/page/bags/process.html": ["bags-process-flow", "item.group eq 'photo'"],
  "application/index/view/cms/page/bags/cta.html": ["bags-benefits-section", "block.extra.items"],
  "application/index/view/cms/page/boxes/hero.html": ["block.link_text", "hero-cta--singleline"],
  "application/index/view/cms/page/boxes/details.html": ["boxes-detail-section", "block.extra.secondary_title", "block.extra.badge_text"],
  "application/index/view/cms/page/boxes/craft_material.html": ["boxes-mini-card", "item.subtitle"],
  "application/index/view/cms/page/about/culture.html": ["about-culture-section", "block.extra.items"],
  "application/index/view/cms/page/contact/hero.html": ["contact-generated-hero", "block.link_text"],
  "application/index/view/cms/page/contact/thanks.html": ["contact-thanks-section", "block.extra.phone"],
};
for (const [file, tokens] of Object.entries(representativeViews)) {
  const body = read(file);
  for (const token of tokens) expect(body.includes(token), `${file} missing ${token}`);
}


const homeSchema = read("application/common/service/cms/HomeSectionEditorSchema.php");
for (const token of ["config_social_url_1", "config_social_url_2", "'subtitle', 'mobile_subtitle'"]) {
  expect(homeSchema.includes(token), `Home section schema missing html-baseline field ${token}`);
}
const structuredCodec = read("application/common/service/cms/StructuredConfigCodec.php");
for (const token of ["config_social_url_1", "config_social_url_2", "['title', 'value', 'unit', 'text', 'icon', 'prefix']"]) {
  expect(structuredCodec.includes(token), `Structured home codec missing ${token}`);
}
const homeAboutForm = read("application/admin/view/cms/common/_home_about_fields.html");
for (const token of ['name="[inputPrefix][subtitle]"', 'name="[inputPrefix][config_social_url_1]"', 'name="[inputPrefix][config_social_url_2]"']) {
  expect(homeAboutForm.includes(token), `Home about admin form missing ${token}`);
}
const homeMetricsForm = read("application/admin/view/cms/common/_metrics_editor.html");
expect(homeMetricsForm.includes('data-metric-field="title"'), "Home service metric editor must expose a title field");
const homeView = read("application/index/view/cms/index/index.html");
for (const token of ["about.config.social_url_1", "about.config.social_url_2", "metric.title", "company.subtitle"]) {
  expect(homeView.includes(token), `Homepage view missing dynamic html-baseline field ${token}`);
}


// HTML baseline seed is part of the runtime contract.
{
  const baselineSql = read("database/cms_html_baseline.sql");
  const installer = read("application/common/service/cms/InstallerService.php");
  for (const token of [
    "/assets/jinya/img/home-banner-01.png",
    "/assets/jinya/img/labels-banner-clean-v83.webp",
    "/assets/jinya/img/bags-banner-clean-v85.webp",
    "/assets/jinya/img/boxes-banner-clean-v87.webp",
    "/assets/jinya/img/about-banner-upload-20260919.jpg",
    "/assets/jinya/img/contact-banner-clean-v78.jpg",
    "html-baseline:news:"
  ]) {
    expect(baselineSql.includes(token), `HTML baseline SQL missing ${token}`);
  }
  expect(installer.includes("renderHtmlBaselineSql($prefix)"), "Installer must execute the HTML baseline seed");
  expect(installer.includes("cms_html_baseline.sql"), "Installer must load database/cms_html_baseline.sql");

  const baselineAssetUrls = [...new Set(
    [...baselineSql.matchAll(/\/assets\/jinya\/[A-Za-z0-9._?=&/%+-]+/g)].map((match) => match[0].split("?")[0])
  )];
  expect(baselineAssetUrls.length > 0, "HTML baseline SQL must reference published /assets/jinya resources");
  for (const assetUrl of baselineAssetUrls) {
    const publicPath = path.join(root, "public", assetUrl.replace(/^\//, "").replace(/^assets[\\/]/, "assets/"));
    expect(fs.existsSync(publicPath), `HTML baseline SQL references missing asset ${assetUrl}`);
  }
  expect(!baselineSql.includes("/uploads/cms-jinya/"), "HTML baseline SQL must not fall back to legacy /uploads/cms-jinya assets");
}

if (!process.exitCode) console.log("Dynamic view contract audit: OK");
