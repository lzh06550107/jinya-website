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
  "application/index/view/cms/product/detail.html",
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

// FastAdmin loads one RequireJS controller per CMS backend route. If these files
// disappear, the PHP view can render while the content area remains uninitialized/blank.
const cmsBackendControllers = [
  "about_page_block_editor.js",
  "advantages_items_editor.js",
  "article.js",
  "article_category.js",
  "bags_page_block_editor.js",
  "banner.js",
  "banner_highlight_editor.js",
  "boxes_page_block_editor.js",
  "contact_page_block_editor.js",
  "content.js",
  "culture_items_editor.js",
  "home_section.js",
  "home_section_reference.js",
  "icon_picker.js",
  "inquiry.js",
  "label_page_block_editor.js",
  "layout_component.js",
  "markdown_editor.js",
  "media_items_editor.js",
  "media_preview.js",
  "metrics_editor.js",
  "navigation.js",
  "page.js",
  "page_block.js",
  "page_config.js",
  "page_content_block.js",
  "page_content_block_editor_schema.js",
  "product.js",
  "product_category.js",
  "product_image.js",
  "product_parameter.js",
  "product_section.js",
  "site_config.js",
  "workshop_items_editor.js",
];
for (const file of cmsBackendControllers) {
  expect(
    fs.existsSync(path.join(root, "public", "assets", "js", "backend", "cms", file)),
    `Missing CMS backend RequireJS controller: public/assets/js/backend/cms/${file}`,
  );
}

const iconPickerCssPath = path.join(root, "public", "assets", "css", "cms-icon-picker.css");
expect(fs.existsSync(iconPickerCssPath), "Missing CMS icon picker stylesheet: public/assets/css/cms-icon-picker.css");
if (fs.existsSync(iconPickerCssPath)) {
  const iconPickerCss = fs.readFileSync(iconPickerCssPath, "utf8");
  expect(iconPickerCss.includes(".cms-icon-picker-font-grid"), "CMS icon picker stylesheet missing font grid styles");
  expect(iconPickerCss.includes(".cms-icon-picker-tab-pane"), "CMS icon picker stylesheet missing tab pane styles");
}
const iconPickerJs = read("public/assets/js/backend/cms/icon_picker.js");
expect(iconPickerJs.includes("input.parent('.input-group')"), "CMS icon picker must reuse an existing Bootstrap input-group");
expect(iconPickerJs.includes("cms-icon-picker.css' + version"), "CMS icon picker stylesheet URL must be cache-busted with the site version");

const route = read("application/route.php");
expect(route.includes("'product/:slug' => 'index/product/detail'"), "Generic product detail route /product/:slug must remain available site-wide");
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
const productFactory = read("application/common/service/cms/render/PageBlockViewModelFactory.php");
for (const blockKey of ["bags_products", "boxes_products"]) {
  expect(
    productFactory.includes("'" + blockKey + "'") && productFactory.includes("$extra['items'][0]['group'] = 'main'"),
    `${blockKey} must promote the first historical item to the main card when group=main is missing`,
  );
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

const productDetailView = read("application/index/view/cms/product/detail.html");
for (const token of [
  'cms/layout/header',
  'cms/layout/footer',
  'data-product-detail',
  'jpd-product-hero',
  'jpd-summary-card',
  'jpd-detail-nav',
  'jpd-related'
]) {
  expect(productDetailView.includes(token), `Product detail view missing modern layout token ${token}`);
}
expect(!productDetailView.includes("cms/common/strict_header"), "Product detail must not use legacy strict_header");
expect(!productDetailView.includes("cms/common/strict_footer"), "Product detail must not use legacy strict_footer");
expect(!productDetailView.includes("jpd-product-nav"), "Product detail must not render previous/next product navigation");
expect(!productDetailView.includes("上一产品"), "Product detail must not render previous-product copy");
expect(!productDetailView.includes("下一产品"), "Product detail must not render next-product copy");
const productDetailRender = read("application/common/service/cms/render/ProductDetailRenderService.php");
expect(!productDetailRender.includes("previousNext("), "Product detail render service must be independent from sequential product navigation");
expect(!productDetailRender.includes("'previous' =>"), "Product detail ViewModel must not expose previous product data");
expect(!productDetailRender.includes("'next' =>"), "Product detail ViewModel must not expose next product data");
for (const token of ["publicCategory", "detailBreadcrumb", "html-home-display", "HTML 首页展示"]) {
  expect(productDetailRender.includes(token), `Product detail render missing internal-category filter token ${token}`);
}

const productDetailCss = read("public/assets/jinya/css/style.css");
for (const token of ["Product detail v40", ".jpd-product-hero", ".jpd-detail-nav", ".jpd-media-preview", ".jpd-related-grid"]) {
  expect(productDetailCss.includes(token), `Product detail stylesheet missing ${token}`);
}
const productDetailJs = read("public/assets/jinya/js/main.js");
for (const token of ["Product detail gallery, media preview", "data-jpd-thumb", "openProductPreview", "showDetailMedia"]) {
  expect(productDetailJs.includes(token), `Product detail interaction missing ${token}`);
}

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
for (const token of ["config_social_url_1", "config_social_qr_1", "config_social_url_2", "config_social_qr_2", "'subtitle', 'mobile_subtitle'"]) {
  expect(homeSchema.includes(token), `Home section schema missing html-baseline field ${token}`);
}
const structuredCodec = read("application/common/service/cms/StructuredConfigCodec.php");
for (const token of ["config_social_url_1", "config_social_qr_1", "config_social_url_2", "config_social_qr_2", "['title', 'value', 'unit', 'text', 'icon', 'prefix']"]) {
  expect(structuredCodec.includes(token), `Structured home codec missing ${token}`);
}
const homeAboutForm = read("application/admin/view/cms/common/_home_about_fields.html");
for (const token of [
  'name="[inputPrefix][subtitle]"',
  'name="[inputPrefix][config_social_icon_1]"',
  'name="[inputPrefix][config_social_url_1]"',
  'name="[inputPrefix][config_social_qr_1]"',
  'name="[inputPrefix][config_social_icon_2]"',
  'name="[inputPrefix][config_social_url_2]"',
  'name="[inputPrefix][config_social_qr_2]"'
]) {
  expect(homeAboutForm.includes(token), `Home about admin form missing ${token}`);
}
const homeSectionSave = read("application/common/service/cms/HomeSectionConfigService.php");
for (const token of ["config_social_url_1", "config_social_qr_1", "config_social_url_2", "config_social_qr_2"]) {
  expect(homeSectionSave.includes(token), `Home section save service missing ${token}`);
}
const homeSectionModel = read("application/common/model/cms/HomeSection.php");
for (const method of ["getConfigSocialIcon_1Attr", "getConfigSocialUrl_1Attr", "getConfigSocialQr_1Attr", "getConfigSocialIcon_2Attr", "getConfigSocialUrl_2Attr", "getConfigSocialQr_2Attr"]) {
  expect(homeSectionModel.includes("function " + method + "("), `HomeSection model missing social accessor ${method}`);
}
const homeRenderService = read("application/common/service/cms/render/HomeRenderService.php");
for (const token of ["social_qr_1_url", "social_qr_2_url"]) {
  expect(homeRenderService.includes(token), `Home render service missing QR URL ${token}`);
}
const homeMetricsForm = read("application/admin/view/cms/common/_metrics_editor.html");
for (const field of ["title", "text", "icon"]) {
  expect(homeMetricsForm.includes('data-metric-field="' + field + '"'), `Home service editor must expose ${field}`);
}
for (const legacyField of ["prefix", "value", "unit"]) {
  expect(!homeMetricsForm.includes('data-metric-field="' + legacyField + '"'), `Home service editor must not expose legacy field ${legacyField}`);
}
const homeServiceForm = read("application/admin/view/cms/common/_home_service_fields.html");
expect(homeServiceForm.includes("按钮跳转 URL"), "Home service form must label the real button URL");
expect(!homeServiceForm.includes("备用跳转 URL"), "Home service form must not expose obsolete fallback URL wording");
expect(!homeServiceForm.includes("优先复用全局微信客服配置"), "Home service form must not describe nonexistent global-wechat fallback behavior");
const homeView = read("application/index/view/cms/index/index.html");
const serviceMarkupStart = homeView.indexOf('{notempty name="service"}');
const serviceMarkupEnd = homeView.indexOf('{/notempty}', serviceMarkupStart);
const serviceMarkupWindow = homeView.slice(serviceMarkupStart, serviceMarkupStart + 3200);
for (const legacyToken of ["metric.prefix", "metric.value", "metric.unit"]) {
  expect(!serviceMarkupWindow.includes(legacyToken), `Homepage service markup must not consume legacy metric field ${legacyToken}`);
}

const homeHeroEnd = homeView.indexOf("</section>", homeView.indexOf("hero-swiper"));
const homeHeroMarkup = homeView.slice(homeView.indexOf("hero-swiper"), homeHeroEnd);
expect(!homeHeroMarkup.includes("swiper-prev"), "Homepage hero must not render a previous arrow");
expect(!homeHeroMarkup.includes("swiper-next"), "Homepage hero must not render a next arrow");
expect(homeHeroMarkup.includes("swiper-dots"), "Homepage hero must keep pagination dots");
const mobileHomeView = read("application/mobile/view/cms/index/index.html");
const mobileHeroEnd = mobileHomeView.indexOf("</section>", mobileHomeView.indexOf("hero-swiper"));
const mobileHeroMarkup = mobileHomeView.slice(mobileHomeView.indexOf("hero-swiper"), mobileHeroEnd);
expect(!mobileHeroMarkup.includes("swiper-prev"), "Mobile homepage hero must not render a previous arrow");
expect(!mobileHeroMarkup.includes("swiper-next"), "Mobile homepage hero must not render a next arrow");
for (const token of ["about.config.social_url_1", "about.config.social_qr_1_url", "about.config.social_url_2", "about.config.social_qr_2_url", "about.config.social_icon_1_view", "about.config.social_icon_2_view", "social-qr-popover", "metric.title", "company.subtitle"]) {
  expect(homeView.includes(token), `Homepage view missing dynamic html-baseline field ${token}`);
}
for (const token of ["hero-title-primary", "hero-title-secondary", "hero-badges"]) {
  expect(homeView.includes(token), `Homepage hero missing screenshot-matched structure ${token}`);
}
const pcStyle = read("public/assets/jinya/css/style.css");
for (const token of ["R48: screenshot-matched PC homepage hero copy.", ".hero-title-primary", ".hero-title-secondary", "flex-direction:column", ".social-qr-popover", "bottom:calc(100% + 12px)"]) {
  expect(pcStyle.includes(token), `PC homepage hero stylesheet missing ${token}`);
}
const staticHome = read("html/index.html");
const staticHeroEnd = staticHome.indexOf("</section>", staticHome.indexOf("hero-swiper"));
const staticHeroMarkup = staticHome.slice(staticHome.indexOf("hero-swiper"), staticHeroEnd);
expect(!staticHeroMarkup.includes("swiper-prev"), "Static homepage hero must not render a previous arrow");
expect(!staticHeroMarkup.includes("swiper-next"), "Static homepage hero must not render a next arrow");
for (const token of ["高质量无版印刷", "不干胶·包装袋 一站式按需定制", "品质为先&nbsp;省心高效&nbsp;合作共赢"]) {
  expect(staticHome.includes(token), `Static homepage hero missing approved screenshot copy ${token}`);
}

const bannerCodec = read("application/common/service/cms/BannerHighlightCodec.php");
for (const token of ["home-highlight-team.png", "home-highlight-quality.png", "home-highlight-delivery.png", "applyIconOverrides"]) {
  expect(bannerCodec.includes(token), `Banner highlight codec missing editable icon contract ${token}`);
}
const bannerForm = read("application/admin/view/cms/banner/_form.html");
for (const token of ['name="row[highlight_icon_1]"', 'name="row[highlight_icon_2]"', 'name="row[highlight_icon_3]"']) {
  expect(bannerForm.includes(token), `Banner admin form missing editable highlight icon field ${token}`);
}
const bannerModel = read("application/common/model/cms/Banner.php");
for (const token of ["highlight_icon_1", "highlight_icon_2", "highlight_icon_3"]) {
  expect(bannerModel.includes(token), `Banner model missing admin highlight icon accessor ${token}`);
}
// ThinkPHP 5 Loader::parseName only camel-cases underscores followed by letters.
// Numeric suffixes stay underscored, so highlight_icon_1 resolves to
// getHighlightIcon_1Attr (not getHighlightIcon1Attr).
for (const method of ["getHighlightIcon_1Attr", "getHighlightIcon_2Attr", "getHighlightIcon_3Attr"]) {
  expect(bannerModel.includes("function " + method + "("), `Banner model missing ThinkPHP 5 virtual accessor ${method}`);
}
for (const invalidMethod of ["getHighlightIcon1Attr", "getHighlightIcon2Attr", "getHighlightIcon3Attr"]) {
  expect(!bannerModel.includes("function " + invalidMethod + "("), `Banner model must not use incompatible accessor ${invalidMethod}`);
}
const abstractRender = read("application/common/service/cms/render/AbstractRenderService.php");
expect(abstractRender.includes("bannerHighlights->homeHero"), "Home hero render must upgrade legacy banner highlight icons");
expect(
  !abstractRender.includes("trim((string)$title) === '高品质包装印刷 一站式按需定制'"),
  "Homepage render must not silently rewrite persisted Banner title/subtitle",
);
expect(
  !bannerCodec.includes("$items[1]['text'] = '品质为先 省心高效 合作共赢'"),
  "Banner highlight render must not silently rewrite persisted selling-point text",
);
const installerService = read("application/common/service/cms/InstallerService.php");
for (const token of [
  "migrateLegacyHomeHeroBannerDefaults",
  "高质量无版印刷",
  "不干胶·包装袋 一站式按需定制",
  "品质为先 省心高效 合作共赢"
]) {
  expect(installerService.includes(token), `Installer missing persisted home hero migration token ${token}`);
}
for (const token of ["ensureHomeAboutSocialIconDefaults", "social-wechat.png", "home-video-channels.png"]) {
  expect(installerService.includes(token), `Installer missing home-about social icon migration token ${token}`);
}
for (const icon of ["home-highlight-team.png", "home-highlight-quality.png", "home-highlight-delivery.png"]) {
  expect(fs.existsSync(path.join(root, "html", "assets", "img", icon)), `Missing static home banner icon ${icon}`);
  expect(fs.existsSync(path.join(root, "public", "assets", "jinya", "img", icon)), `Missing published home banner icon ${icon}`);
}


// HTML baseline seed is part of the runtime contract.
{
  const baselineSql = read("database/cms_html_baseline.sql");
  expect(
    baselineSql.includes('"image":"/assets/jinya/img/bags-prod-1.jpg","subtitle":"","badge":"","group":"main"'),
    "HTML baseline must mark the first bags product image as the main card",
  );
  expect(
    baselineSql.includes('"image":"/assets/jinya/img/boxes-prod-1.jpg","subtitle":"","badge":"","group":"main"'),
    "HTML baseline must mark the first boxes product image as the main card",
  );
  for (const icon of ["/assets/jinya/img/social-wechat.png", "/assets/jinya/img/home-video-channels.png"]) {
    expect(baselineSql.includes(icon), `HTML baseline must persist home-about social icon ${icon}`);
  }
  const installer = read("application/common/service/cms/InstallerService.php");
  for (const token of [
    "/assets/jinya/img/home-banner-01.png",
    "/assets/jinya/img/labels-banner-clean-v83.webp",
    "/assets/jinya/img/bags-banner-clean-v85.webp",
    "/assets/jinya/img/boxes-banner-clean-v87.webp",
    "/assets/jinya/img/about-banner-upload-20260919.jpg",
    "/assets/jinya/img/contact-banner-clean-v78.jpg",
    "html-baseline:news:",
    "高质量无版印刷",
    "不干胶·包装袋 一站式按需定制",
    "品质为先 省心高效 合作共赢"
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
