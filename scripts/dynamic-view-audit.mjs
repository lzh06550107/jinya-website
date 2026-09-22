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

const layoutUnifiedEditor = read("application/common/service/cms/layout_editor/LayoutUnifiedEditorService.php");
for (const token of ["defaultSocialQrKey", "'微信' => 'wechat_qr'", "'抖音' => 'douyin_qr'", "'小红书' => 'xiaohongshu_qr'", "'微信视频号' => 'video_qr'"]) {
  expect(layoutUnifiedEditor.includes(token), `Social QR editor binding missing ${token}`);
}
const layoutEditorView = read("application/admin/view/cms/layout_component/edit.html");
expect(layoutEditorView.includes('name="row[social_items][{$socialIndex}][qr_value]"'), "Social editor must expose QR upload value");
expect(layoutEditorView.includes("PC 前台鼠标悬停对应图标时"), "Social editor must explain hover QR behavior");
const layoutEditorRegistry = read("application/common/service/cms/layout_editor/LayoutEditorRegistry.php");
expect(layoutEditorRegistry.includes("cms_factory_address"), "Footer editor must expose factory address");
expect(layoutEditorRegistry.includes("cms_service_wechat_qr"), "Footer/floating editor must expose the QR actually rendered by the footer");
for (const unusedField of ["cms_service_wechat_name", "cms_service_wechat_tip", "cms_service_hours"]) {
  const floatingStart = layoutEditorRegistry.indexOf("$floatingServiceSiteFields");
  const floatingEnd = layoutEditorRegistry.indexOf("];", floatingStart);
  expect(!layoutEditorRegistry.slice(floatingStart, floatingEnd).includes(unusedField), `Floating-service editor must not expose unused field ${unusedField}`);
}
expect(!layoutEditorRegistry.includes("'show_search', 'show_online_service'"), "Header editor must not expose switches without rendered DOM");
const siteConfigDefinitions = read("application/common/service/cms/SiteConfigDefinitionRegistry.php");
expect(siteConfigDefinitions.includes("'cms_factory_address'"), "Factory address must be a registered site field");
const pcHeader = read("application/index/view/cms/layout/header.html");
const mobileHeader = read("application/mobile/view/cms/layout/header.html");
const pcNav = read("application/index/view/cms/layout/nav.html");
const mobileNav = read("application/mobile/view/cms/layout/nav.html");
expect(pcNav.includes("--cms-nav-active-bg:{$cmsPcNavActiveBackgroundColor"), "PC navigation must receive configured active background color on the nav subtree");
expect(mobileNav.includes("--cms-nav-active-bg:{$layout.header.config.nav_active_background_color"), "Mobile navigation must receive configured active background color on the nav subtree");
expect(pcHeader.includes("site-header--layout-"), "PC header must consume backend layout_mode");
for (const [file, body] of [["PC header", pcHeader], ["mobile header", mobileHeader]]) {
  for (const token of ["show_logo", "show_navigation", "show_phone", "hotline_icon_view"]) {
    expect(body.includes(token), `${file} must consume backend header config ${token}`);
  }
  expect(body.includes("/assets/jinya/css/style.css"), `${file} must load html/assets-derived style.css`);
  expect(body.includes("/assets/jinya/js/device-router.js"), `${file} must load html/assets-derived device-router.js`);
}
expect(mobileHeader.includes("/assets/jinya/css/mobile.css"), "mobile header must load mobile.css");
const pcFooter = read("application/index/view/cms/layout/footer.html");
const mobileFooter = read("application/mobile/view/cms/layout/footer.html");
for (const [file, body] of [["PC footer", pcFooter], ["mobile footer", mobileFooter]]) {
  for (const token of ["footer-social-item", "footer-social-qr-popover", "item.qr_image"]) {
    expect(body.includes(token), `${file} must render hover social QR markup ${token}`);
  }
  for (const token of ["show_company", "show_contact", "show_navigation", "show_qrcode", "show_beian", "show_online_consult", "show_online_message", "show_wechat_consult", "show_back_top"]) {
    expect(body.includes(token), `${file} must consume backend footer/floating config ${token}`);
  }
  expect(body.includes("factory_address"), `${file} must render configurable factory address`);
}
expect(mobileFooter.includes("layout.navigation.footer"), "Mobile footer must consume footer navigation instead of header navigation");
const headerSchema = read("application/common/service/cms/LayoutSchemaRegistry.php");
expect(headerSchema.includes("'hotline_top_width' => ['title' => '电话区顶部状态宽度', 'type' => 'number', 'default' => 300, 'min' => 240, 'max' => 300]"), "PC top hotline width must be constrained to 240-300px");
const pcCmsBase = read("application/index/controller/CmsBase.php");
expect(pcCmsBase.includes("min(300, (int)LayoutSchemaRegistry::sanitizeField('header', 'hotline_top_width'"), "Existing oversized hotline width values must be capped at 300px");
const headerMotionJs = read("public/assets/jinya/js/main.js");
for (const token of ["mixHeaderValue", "Smoothstep", "--cms-hotline-current-width", "--cms-hotline-current-number-size", "--cms-hotline-current-icon-size", "--cms-hotline-current-badge-size", 'setProperty("--phone-scroll-scale", "1")']) {
  expect(headerMotionJs.includes(token), `Header scroll motion missing smooth hotline interpolation token ${token}`);
}
expect(!headerMotionJs.includes("var phoneScale = 1 + progress * 0.08"), "Hotline must not combine scroll interpolation with a second scale animation");
const footerRenderService = read("application/common/service/cms/render/LayoutRenderService.php");
for (const token of ["defaultFooterSocialQrKey", "qr_image", "'微信视频号' => 'video_qr'"]) {
  expect(footerRenderService.includes(token), `Footer QR render missing ${token}`);
}
const runtimeStyle = read("public/assets/jinya/css/style.css");
for (const token of [".footer-social-qr-popover", ".footer-social-item:hover .footer-social-qr-popover", "bottom:calc(100% + 12px)"]) {
  expect(runtimeStyle.includes(token), `Footer social QR CSS missing ${token}`);
}
for (const token of ["Header/footer backend configuration bridge v110", "--cms-nav-active-bg", "--cms-logo-top-width", "--cms-hotline-top-width", "--cms-hotline-current-width", ".site-header--layout-balanced", ".site-header--layout-compact"]) {
  expect(runtimeStyle.includes(token), `Runtime stylesheet missing layout config bridge ${token}`);
}
expect(!runtimeStyle.includes(".site-header.is-scrolled .header-phone .label"), "Hotline label size must not switch discretely on is-scrolled");
expect(!runtimeStyle.includes(".site-header.is-scrolled .header-phone .cms-hotline-icon"), "Hotline icon size must not switch discretely on is-scrolled");
expect(!runtimeStyle.includes(".site-header.is-scrolled .header-phone .tag"), "Hotline badge size must not switch discretely on is-scrolled");

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

const pageSchemaRegistry = read("application/common/service/cms/PageSchemaRegistry.php");
const homePageSchemaStart = pageSchemaRegistry.indexOf("'home' => self::pageDefinition");
const homePageSchemaEnd = pageSchemaRegistry.indexOf("'product.index' =>", homePageSchemaStart);
const homePageSchemaWindow = pageSchemaRegistry.slice(homePageSchemaStart, homePageSchemaEnd);
for (const retiredKey of ["cases", "advantages", "news"]) {
  expect(!homePageSchemaWindow.includes("'" + retiredKey + "' => self::blockDefinition"), `Home PageSchema must not register retired block ${retiredKey}`);
  expect(pageSchemaRegistry.includes("'" + retiredKey + "'"), `Retired home block list must contain ${retiredKey}`);
}
const pageBlockController = read("application/admin/controller/cms/PageBlock.php");
expect(pageBlockController.includes("PageSchemaRegistry::retiredBlockKeys("), "PageBlock admin list must filter retired identifiers for every page");
const homeOrderService = read("application/common/service/cms/HomePageBlockOrderService.php");
expect(homeOrderService.includes("PageSchemaRegistry::retiredHomeBlockKeys()"), "Home block ordering must ignore retired identifiers");
const installerPageBlocks = read("application/common/service/cms/InstallerService.php");
for (const token of ["retireRemovedPageBlocks", "retireUnusedBannerRows", "cms_page_block_reference", "product.detail.%"]) {
  expect(installerPageBlocks.includes(token), `Installer missing retired Banner/PageBlock cleanup token ${token}`);
}
for (const pageKey of ["product.detail", "page.label", "page.bags", "page.boxes", "page.about", "page.contact"]) {
  expect(pageSchemaRegistry.includes("'" + pageKey + "' => ['banner']"), `${pageKey} must retire its legacy generic banner block`);
}

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
for (const token of ["boxes_badge_logo", "boxes_mobile_badge_logo", "'badge_logo'", "'mobile_badge_logo'"]) {
  expect(boxesCodec.includes(token), `Boxes codec missing promise logo field: ${token}`);
}
const boxesPromiseSchemaSource = read("application/common/service/cms/PageContentBlockEditorSchema.php");
const boxesPromiseSchemaPos = boxesPromiseSchemaSource.indexOf("$schemas['boxes_promise']");
const boxesPromiseSchemaWindow = boxesPromiseSchemaSource.slice(boxesPromiseSchemaPos, boxesPromiseSchemaPos + 1200);
for (const token of ["boxes_badge_logo", "boxes_mobile_badge_logo", "面板 Logo", "移动端 Logo"]) {
  expect(boxesPromiseSchemaWindow.includes(token), `boxes_promise backend missing logo contract: ${token}`);
}
for (const file of [
  "application/index/view/cms/page/boxes/promise.html",
  "application/mobile/view/cms/page/boxes/promise.html",
]) {
  const promiseView = read(file);
  expect(promiseView.includes("block.extra.badge_logo"), `${file} must consume configured promise logo`);
}
expect(
  read("application/mobile/view/cms/page/boxes/promise.html").includes("block.extra.mobile_badge_logo"),
  "mobile boxes promise must consume mobile logo override",
);
const boxesPromiseLogoPath = "public/assets/jinya/img/boxes-promise-logo.svg";
expect(fs.existsSync(path.join(root, boxesPromiseLogoPath)), "boxes promise current frontend logo asset must exist");
expect(
  read("application/admin/view/cms/page_content_block/_form.html").includes("/assets/jinya/img/boxes-promise-logo.svg"),
  "boxes_promise admin Logo field must echo the current frontend logo by default",
);
expect(
  read("database/cms_html_baseline.sql").includes('\"badge_logo\":\"/assets/jinya/img/boxes-promise-logo.svg\"'),
  "boxes_promise baseline must persist the current frontend logo path",
);
expect(
  read("application/common/service/cms/InstallerService.php").includes("ensureBoxesPromiseLogoDefault"),
  "cms:install must backfill the current boxes promise logo without overwriting custom uploads",
);
const boxesDefaultsSource = read("application/common/service/cms/BoxesPageDefaults.php");
expect(
  !boxesDefaultsSource.includes('"block_key":"boxes_purchase"'),
  "retired boxes_purchase must not be recreated by BoxesPageDefaults",
);
const installerBoxesSource = read("application/common/service/cms/InstallerService.php");
expect(
  installerBoxesSource.includes("['body','boxes_purchase']"),
  "boxes installer must explicitly retire legacy boxes_purchase",
);
expect(
  installerBoxesSource.includes("(`source_key`=? OR (`deletetime` IS NULL AND `block_key`=?))") &&
  installerBoxesSource.includes("`deletetime`=NULL"),
  "boxes default upsert must reuse soft-deleted source_key rows instead of violating uk_page_block_source",
);

const pageSchemaRegistry = read("application/common/service/cms/PageSchemaRegistry.php");
expect(
  pageSchemaRegistry.includes("'news.index' => self::pageDefinition('新闻总列表', 'list', '/news', self::newsIndexPageBlocks())"),
  "news.index must use its cleaned page-block schema",
);
const newsIndexBlocksPos = pageSchemaRegistry.indexOf("protected static function newsIndexPageBlocks()");
const newsIndexBlocksWindow = pageSchemaRegistry.slice(newsIndexBlocksPos, newsIndexBlocksPos + 1400);
for (const token of [
  "unset($blocks['category_navigation'], $blocks['pagination'])",
  "'page_size'",
  "'show_date'",
  "'summary_length'",
  "'pc_visible'",
  "'mobile_visible'",
  "'enabled'",
]) {
  expect(newsIndexBlocksWindow.includes(token), `news.index cleanup contract missing: ${token}`);
}
expect(
  pageSchemaRegistry.includes("'news.index' => ['category_navigation', 'pagination']"),
  "news.index automatic category navigation/pagination blocks must be retired from admin",
);
const pageBlockAdminController = read("application/admin/controller/cms/PageBlock.php");
expect(
  pageBlockAdminController.includes("admin_hidden_fields") &&
  pageBlockAdminController.includes("in_array($name, $adminHiddenFields, true)"),
  "page-block admin must honor schema hidden fields",
);

const schema = read("application/common/service/cms/PageContentBlockEditorSchema.php");
expect(
  schema.includes("$schema['section_order'] = ['items','specific'];") &&
  schema.includes("$schema['page'] === 'boxes'"),
  "boxes admin sections must place 功能项目 before 页面专用配置",
);
expect(
  schema.includes("$schemas[$key]['strict_groups'] = true;") &&
  schema.includes("$schemas[$key]['default_group'] = 'normal';"),
  "boxes_value/boxes_details layout direction must use strict real frontend groups",
);
const boxesDetailsOrderPos = schema.indexOf("$schemas['boxes_details']['item_order'] = [");
const boxesDetailsOrderWindow = schema.slice(boxesDetailsOrderPos, boxesDetailsOrderPos + 420);
expect(
  boxesDetailsOrderWindow.indexOf("'subtitle','mobile_subtitle'") <
    boxesDetailsOrderWindow.indexOf("'title','mobile_title'") &&
  boxesDetailsOrderWindow.indexOf("'title','mobile_title'") <
    boxesDetailsOrderWindow.indexOf("'text','mobile_text'"),
  "boxes_details backend fields must follow frontend text order: tag -> title -> description",
);
const richSchemaEditor = read("public/assets/js/backend/cms/page_content_block_editor_schema_rich_v6.js");
expect(
  richSchemaEditor.includes("var strict = !!(schema && schema.strict_groups);") &&
  richSchemaEditor.includes("if (!strict && !Object.prototype.hasOwnProperty.call(groups, current))"),
  "strict group editors must not append the fake 普通/默认 option",
);
const aboutValuesSchemaPos = schema.indexOf("$schemas['about_values'] = self::make");
const aboutValuesSchemaWindow = schema.slice(aboutValuesSchemaPos, aboutValuesSchemaPos + 900);
expect(
  aboutValuesSchemaWindow.includes("$schemas['about_values']['strict_groups'] = true;") &&
  aboutValuesSchemaWindow.includes("$schemas['about_values']['default_group'] = 'normal';"),
  "about_values layout direction must not expose 普通/默认",
);
const boxesCraftMaterialPos = schema.indexOf("$schemas['boxes_craft_material']['item_order'] = [");
const boxesCraftMaterialWindow = schema.slice(boxesCraftMaterialPos, boxesCraftMaterialPos + 900);
for (const token of ["split_craft_material", "global_item_numbering", "strict_groups", "default_group"]) {
  expect(
    boxesCraftMaterialWindow.includes(token),
    `boxes_craft_material backend must declare split editor metadata: ${token}`,
  );
}
expect(
  richSchemaEditor.includes("arrangeBoxesCraftMaterial") &&
  richSchemaEditor.includes('data-boxes-items-list="craft"') &&
  richSchemaEditor.includes('data-boxes-items-list="material"') &&
  richSchemaEditor.includes("data-boxes-material-title-row") &&
  richSchemaEditor.includes("添加印刷工艺") &&
  richSchemaEditor.includes("添加产品材质"),
  "boxes craft/material editor must place material items below 材质区标题",
);
const boxesEditorSource = read("public/assets/js/backend/cms/boxes_page_block_editor.js");
expect(
  boxesEditorSource.includes("data-boxes-item-target") &&
  boxesEditorSource.includes("data-boxes-item-default-group") &&
  boxesEditorSource.includes("allItems"),
  "boxes item editor must support targeted craft/material lists with continuous indexing",
);
const bagsCompareSchemaPos = schema.indexOf("$schemas['bags_compare'] = self::make");
const bagsCompareSchemaWindow = schema.slice(bagsCompareSchemaPos, bagsCompareSchemaPos + 700);
expect(
  bagsCompareSchemaWindow.includes("['image','mobile_image'], [], [], []") &&
  bagsCompareSchemaWindow.includes("'image'=>'PC 整图'") &&
  bagsCompareSchemaWindow.includes("'mobile_image'=>'移动整图'"),
  "bags_compare backend must be a complete-image-only editor",
);
for (const retiredToken of ["bags_compare_brand", "bags_compare_footer", "'text'=>'方案要点'", "'title'=>'主标题'"]) {
  expect(!bagsCompareSchemaWindow.includes(retiredToken), `bags_compare image-only schema must hide legacy field: ${retiredToken}`);
}
expect(
  schema.includes("$schemas['bags_hero']['base_help']['content'] = '支持 Markdown 和换行；后台每次换行都会在 PC/移动 Banner 前端原样显示。';"),
  "bags_hero backend must explain that Banner description line breaks are preserved",
);
expect(
  schema.includes("$schemas['label_hero']['base_help']['content'] = '支持 Markdown 和换行；后台每次换行都会在 PC/移动 Banner 前端原样显示。';"),
  "label_hero backend must explain that Banner description line breaks are preserved",
);
expect(
  schema.includes("$schemas['boxes_hero']['base_help']['content'] = '支持 Markdown 和换行；后台每次换行都会在 PC/移动 Banner 前端原样显示。';"),
  "boxes_hero backend must explain that Banner description line breaks are preserved",
);
expect(
  schema.includes("$schemas['contact_thanks']['base_help']['content'] = '支持 Markdown 和换行；后台每次换行都会在 PC/移动端前端原样显示。';"),
  "contact_thanks backend must explain that thanks text line breaks are preserved",
);
const contactThanksPcView = read("application/index/view/cms/page/contact/thanks.html");
const contactThanksMobileView = read("application/mobile/view/cms/page/contact/thanks.html");
expect(
  contactThanksPcView.includes("$block.content_inline_html") &&
  contactThanksMobileView.includes("$block.content_inline_html"),
  "contact_thanks PC/mobile templates must render preserved inline HTML",
);
const pageBlockFactoryForContactBreaks = read("application/common/service/cms/render/PageBlockViewModelFactory.php");
expect(
  pageBlockFactoryForContactBreaks.includes("['label_hero', 'bags_hero', 'boxes_hero', 'contact_thanks']") &&
  pageBlockFactoryForContactBreaks.includes("MarkdownRenderer::renderInlinePreserveLineBreaks($content)"),
  "contact_thanks must preserve every backend line break",
);
const contactThanksCss = read("public/assets/jinya/css/style.css");
expect(
  contactThanksCss.includes("width:min(100%,960px)") &&
  contactThanksCss.includes("width:min(100%,920px)") &&
  contactThanksCss.includes("overflow-wrap:break-word") &&
  !contactThanksCss.includes("white-space:nowrap;\n}"),
  "contact thanks copy must use readable width and natural wrapping",
);
const contactDefaults = read("application/common/service/cms/ContactPageDefaults.php");
expect(
  contactDefaults.includes("感恩一路携手相伴，并肩奋进的岁月！\\n每一份订单，承载着您对终端客户的责任与信赖。\\n经由您推向市场的，不只是包装产品，更是精工造物的初心。"),
  "contact thanks defaults must use sentence-level semantic line breaks",
);
const pageBlockFactoryForHeroBreaks = read("application/common/service/cms/render/PageBlockViewModelFactory.php");
expect(
  pageBlockFactoryForHeroBreaks.includes("['label_hero', 'bags_hero', 'boxes_hero']") &&
  pageBlockFactoryForHeroBreaks.includes("MarkdownRenderer::renderInlinePreserveLineBreaks($content)"),
  "label/bags/boxes Banner descriptions must preserve every backend line break",
);
const labelServiceGroupFieldsPos = schema.indexOf("$schemas['label_service']['group_item_fields']");
const labelServiceGroupFieldsWindow = schema.slice(labelServiceGroupFieldsPos, labelServiceGroupFieldsPos + 520);
expect(
  labelServiceGroupFieldsWindow.includes("'process' => ['title','text','group','mobile_title','mobile_text','pc_visible','mobile_visible']"),
  "label_service process editor must not expose unused badge/mobile_badge fields",
);
expect(
  labelServiceGroupFieldsWindow.includes("'guarantee' => ['title','text','badge','group','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible']"),
  "label_service guarantee editor must keep badge/mobile_badge icon fields",
);
const labelServicePcView = read("application/index/view/cms/page/label/service.html");
const labelServiceMobileView = read("application/mobile/view/cms/page/label/service.html");
for (const view of [labelServicePcView, labelServiceMobileView]) {
  const processStart = view.indexOf("$item.group eq 'process'");
  const guaranteeStart = view.indexOf("$item.group eq 'guarantee'");
  const processWindow = view.slice(processStart, guaranteeStart);
  const guaranteeWindow = view.slice(guaranteeStart);
  expect(!processWindow.includes('cms/page/label/icon'), "label_service process frontend must not consume icons");
  expect(guaranteeWindow.includes('cms/page/label/icon'), "label_service guarantee frontend must consume its configured icons");
}
for (const token of [
  "$schemas['label_service']['item_scope'] = 'form';",
  "'process' => 'content'",
  "'guarantee' => 'specific'",
  "$schemas['label_service']['hide_item_section'] = true;",
  "$schemas['label_service']['process_section_label'] = '定制流程';",
  "$schemas['label_service']['guarantee_section_label'] = '五大保障';"
]) {
  expect(schema.includes(token), `label_service split-section contract missing: ${token}`);
}
expect(
  schema.includes("$schemas['label_materials']['section_order'] = ['items','specific'];") &&
  schema.includes("$schemas['label_materials']['item_section_label'] = '常用工艺配置';") &&
  schema.includes("$schemas['label_materials']['specific_section_label'] = '不干胶印刷配置';"),
  "label_materials backend editor must separate 常用工艺配置 from 不干胶印刷配置",
);
const labelHeroSchemaPos = schema.indexOf("$schemas['label_hero']");
const labelHeroSchemaWindow = schema.slice(labelHeroSchemaPos, labelHeroSchemaPos + 900);
for (const token of ["'title','subtitle','content','image','mobile_image'", "'title','text','badge','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'"]) {
  expect(labelHeroSchemaWindow.includes(token), `label_hero backend contract missing ${token}`);
}
const labelHeroView = read("application/index/view/cms/page/label/hero.html");
for (const token of ["block.title", "block.subtitle", "block.content_inline_html", "block.image", "block.extra.items"]) {
  expect(labelHeroView.includes(token), `label hero template missing backend field consumer ${token}`);
}
const aboutHeroSchemaPos = schema.indexOf("$schemas['about_hero']");
const aboutHeroSchemaWindow = schema.slice(aboutHeroSchemaPos, aboutHeroSchemaPos + 500);
expect(aboutHeroSchemaWindow.includes("['image','mobile_image']"), "about_hero must expose only images rendered by the current design");
expect(!aboutHeroSchemaWindow.includes("'content'"), "about_hero must not expose unused copy");
const contactHeroSchemaPos = schema.indexOf("$schemas['contact_hero']");
const contactHeroSchemaWindow = schema.slice(contactHeroSchemaPos, contactHeroSchemaPos + 700);
expect(contactHeroSchemaWindow.includes("['title','content','image','mobile_image','link'], [], [], []"), "contact_hero must not expose unused selling-point items");
for (const token of [
  "label_print_title", "label_print_points", "print-image",
  "bags_compare_brand", "bags_compare_footer", "'photo'=>'流程场景图'",
  "六项服务保障", "boxes_badge_text", "英文副标题", "'link'=>'CTA 按钮'",
]) {
  expect(schema.includes(token), `Editor schema missing required html-baseline contract: ${token}`);
}

const htmlBaselineSqlForBagsCompare = read("database/cms_html_baseline.sql");
const bagsCompareBaselinePos = htmlBaselineSqlForBagsCompare.indexOf("'bags_compare','bags_section','专版和无版印刷怎么选'");
const bagsCompareBaselineWindow = htmlBaselineSqlForBagsCompare.slice(bagsCompareBaselinePos, bagsCompareBaselinePos + 650);
expect(
  bagsCompareBaselinePos >= 0 &&
  bagsCompareBaselineWindow.includes("/assets/jinya/img/bags-compare-full-v2.webp") &&
  !bagsCompareBaselineWindow.includes("/assets/jinya/img/bags-tech-compare-bg-v2.jpg"),
  "bags_compare baseline must install the approved uploaded complete artwork, not the old CSS background",
);
const bagsCompareArtworkPath = path.join(root, "public/assets/jinya/img/bags-compare-full-v2.webp");
expect(fs.existsSync(bagsCompareArtworkPath), "bags_compare approved full artwork file must exist");
if (fs.existsSync(bagsCompareArtworkPath)) {
  const bagsCompareArtwork = fs.readFileSync(bagsCompareArtworkPath);
  const isWebp =
    bagsCompareArtwork.length >= 12 &&
    bagsCompareArtwork.toString("ascii", 0, 4) === "RIFF" &&
    bagsCompareArtwork.toString("ascii", 8, 12) === "WEBP";
  expect(isWebp, "bags_compare approved full artwork must be a valid RIFF/WEBP file, not only a .webp filename");
}
const labelsHtmlBaselineSql = read("database/cms_html_baseline.sql");
const labelsAccentMarkers = labelsHtmlBaselineSql.match(/\[color=#e25042\]/g) || [];
expect(labelsAccentMarkers.length >= 14, "labels html baseline must store the approved #e25042 rich-text emphasis markers");
for (const token of [
  "[color=#e25042]特点：表面光滑细腻",
  "[color=#e25042]优点：性价比高",
  "[color=#e25042]特点：自带细腻珠光柔光质感",
  "[color=#e25042]特点：耐高温",
  "[color=#e25042]用途：食品、农化、中药、日化等",
  "[color=#e25042]特点：光感特性能呈现幻彩感"
]) {
  expect(labelsHtmlBaselineSql.includes(token), `labels html baseline missing reference text color: ${token}`);
}
const installerSourceForBagsCompare = read("application/common/service/cms/InstallerService.php");
for (const token of [
  "ensureBagsCompareFullImageDefaults",
  "bags-tech-compare-bg-v2.jpg",
  "bags-compare-full.webp",
  "bags-compare-full-v2.webp",
  "IN (?,?)",
  "bags_compare"
]) {
  expect(installerSourceForBagsCompare.includes(token), `bags_compare full-image default migration missing: ${token}`);
}
const installerSourceForLabelColors = read("application/common/service/cms/InstallerService.php");
for (const token of [
  "LABEL_REFERENCE_ACCENT_COLOR = '#e25042'",
  "applyLabelCapabilityReferenceTextColors",
  "decorateReferenceTextColorLines",
  "'铜版纸不干胶' => ['特点：', '优点：']",
  "'亮银/哑银/合成银不干胶' => ['特点：', '优点：', '用途：']"
]) {
  expect(installerSourceForLabelColors.includes(token), `label capability color migration missing: ${token}`);
}
const markdownRenderer = read("application/common/service/cms/MarkdownRenderer.php");
expect(
  markdownRenderer.includes("renderInlinePreserveLineBreaks") &&
  markdownRenderer.includes("$preserveEveryLineBreak") &&
  markdownRenderer.includes("str_replace(\"\\n\", '<br>'"),
  "Markdown renderer must expose a dedicated editor-inline mode that preserves every visual line break",
);
for (const token of ["[color=", "data-cms-text-color", "restoreTextColors"]) {
  expect(markdownRenderer.includes(token), `Markdown renderer missing partial text color support: ${token}`);
}
expect(
  schema.includes("$schemas['label_capability']['item_help']['text'] = '';") &&
  schema.includes("$schemas['label_capability']['item_help']['mobile_text'] = '';"),
  "label_capability PC/mobile rich editors must not inject duplicate schema help copy into their toolbar areas",
);
const capabilityPairPos = schema.indexOf("$schemas['label_capability']['item_pair_rows']");
const capabilityPairWindow = schema.slice(capabilityPairPos, capabilityPairPos + 420);
expect(
  !capabilityPairWindow.includes("['text','mobile_text']"),
  "label_capability PC rich editor and mobile description must render on separate full-width rows",
);
const pageContentController = read("application/admin/controller/cms/PageContentBlock.php");
expect(
  pageContentController.includes("backend/cms/page_content_block_rich_v8"),
  "PageContentBlock must use the immutable rich-v8 RequireJS entry to bypass stale editor caches",
);
expect(
  pageContentController.includes("cmsPageContentBlockEditorBuild") && pageContentController.includes("rich-v8"),
  "PageContentBlock must expose the active editor build marker",
);
const pageContentRichEntry = read("public/assets/js/backend/cms/page_content_block_rich_v8.js");
expect(
  pageContentRichEntry.includes("backend/cms/label_page_block_editor_v3") &&
  pageContentRichEntry.includes("backend/cms/page_content_block_editor_schema_rich_v6"),
  "Rich-v8 entry must load label split editor v3 and schema v6",
);
expect(
  pageContentRichEntry.includes("backend/cms/label_page_block_editor_v3"),
  "Rich-v8 entry must load the multi-list label editor",
);
expect(
  pageContentRichEntry.includes("backend/cms/page_content_block_editor_schema_rich_v6"),
  "Rich-v8 PageContentBlock entry must require the rich-v8 schema module",
);
const pageContentRichSchema = read("public/assets/js/backend/cms/page_content_block_editor_schema_rich_v6.js");
for (const token of ["schema.item_scope === 'form'", "schema.hide_item_section", "header.hide()", "sectionBody.hide()"]) {
  expect(pageContentRichSchema.includes(token), `Rich-v8 schema editor missing external-item section support: ${token}`);
}
expect(
  pageContentRichSchema.includes("schema.specific_section_label") &&
  pageContentRichSchema.includes("specificHeading.text"),
  "Rich-v8 schema editor must support a semantic page-specific section heading",
);
for (const token of ["reorderSections", "schema.section_order", "insertAfter(anchor)", "reorderSections(editor, schema)"]) {
  expect(pageContentRichSchema.includes(token), `Rich-v8 editor missing section-order token: ${token}`);
}
for (const token of ["data-inline-rich-wrapper", "data-inline-rich-editor", "contenteditable", "应用颜色", "清除颜色"]) {
  expect(pageContentRichSchema.includes(token), `Rich-v8 editor missing WYSIWYG token: ${token}`);
}
expect(
  pageContentRichSchema.includes("inlineColorFields = {}"),
  "Rich-v8 schema must leave label_capability PC rich text to the dedicated editor",
);
const capabilityRichText = read("public/assets/js/backend/cms/label_capability_richtext_v3.js");
for (const token of [
  "data-label-capability-rich-editor",
  "data-label-capability-command",
  "bold",
  "italic",
  "strikeThrough",
  "data-label-capability-rich-color",
  "foreColor",
  "data-label-capability-clear-format",
  "removeFormat",
  "[color=",
  "label_capability"
]) {
  expect(capabilityRichText.includes(token), `Capability PC rich-text editor missing token: ${token}`);
}

const pageContentSchemaJs = read("public/assets/js/backend/cms/page_content_block_editor_schema.js");
for (const token of ["data-inline-rich-wrapper", "data-inline-rich-editor", "contenteditable", "应用颜色", "清除颜色", "[color="]) {
  expect(pageContentSchemaJs.includes(token), `Structured editor missing WYSIWYG partial text color UI: ${token}`);
}
expect(!pageContentSchemaJs.includes("data-inline-color-toolbar"), "Legacy textarea color toolbar must be retired");

const adminForm = read("application/admin/view/cms/page_content_block/_form.html");
for (const token of [
  'data-label-items-list="main"',
  'data-label-items-list="print"',
  'data-label-materials-print-config',
  'data-label-item-target="print"',
  'data-label-item-default-group="print-image"',
  '不干胶印刷轮播图'
]) {
  expect(adminForm.includes(token), `label_materials form missing print-module grouping token: ${token}`);
}
const labelEditorV3 = read("public/assets/js/backend/cms/label_page_block_editor_v3.js");
for (const token of [
  "splitService",
  "data-label-service-section=\"process\"",
  "data-label-service-section=\"guarantee\"",
  "data-label-items-list=\"service-process\"",
  "data-label-items-list=\"service-guarantee\"",
  "data-label-item-default-group=\"process\"",
  "data-label-item-default-group=\"guarantee\"",
  "项目 #'+(i+6)+' · 五大保障"
]) {
  expect(labelEditorV3.includes(token), `label_service backend grouping missing token: ${token}`);
}
for (const token of [
  "splitMaterials",
  "group==='print-image'",
  "data-label-items-list",
  "轮播图 #",
  "data-label-item-default-group",
  "siblings=item.parent().children(ITEM)"
]) {
  expect(labelEditorV3.includes(token), `label materials multi-list editor missing token: ${token}`);
}
const capabilityRichSourceMatches = adminForm.match(/data-label-capability-rich-source/g) || [];
const capabilityMobileRichMatches = adminForm.match(/data-label-field="mobile_text" data-label-capability-rich-source/g) || [];
const capabilityPcRoleMatches = adminForm.match(/data-label-capability-rich-role="pc"/g) || [];
const capabilityMobileRoleMatches = adminForm.match(/data-label-capability-rich-role="mobile"/g) || [];
expect(capabilityRichSourceMatches.length >= 4, "label_capability existing/template PC+mobile descriptions must all expose rich-text sources");
expect(capabilityMobileRichMatches.length >= 2, "label_capability existing/template mobile descriptions must both use rich-text sources");
expect(capabilityPcRoleMatches.length >= 2 && capabilityMobileRoleMatches.length >= 2, "label_capability existing/template items must expose both PC and mobile rich editor roles");

for (const token of [
  "data-label-capability-rich-field",
  "data-label-capability-rich-source",
  "data-label-capability-rich-wrapper",
  "cms-label-capability-richbox",
  "cms-label-capability-rich-toolbar",
  "cms-label-capability-rich-editor",
  "flex-wrap:nowrap",
  "white-space:nowrap",
  "min-height:168px"
]) {
  expect(adminForm.includes(token), `label_capability PC description form missing integrated rich-text token: ${token}`);
}
for (const token of [
  'name="row[label_print_title]"',
  'name="row[label_print_points]"',
  'name="row[bags_compare_brand]"',
  'name="row[bags_compare_footer]"',
  'name="row[boxes_badge_text]"',
]) {
  expect(adminForm.includes(token), `Admin form missing editable field ${token}`);
}

const bagsHeroPcView = read("application/index/view/cms/page/bags/hero.html");
const bagsHeroMobileView = read("application/mobile/view/cms/page/bags/hero.html");
for (const view of [bagsHeroPcView, bagsHeroMobileView]) {
  expect(view.includes("block.content_inline_html"), "bags_hero PC/mobile template must render normalized Banner description HTML");
}
const labelHeroPcLineBreakView = read("application/index/view/cms/page/label/hero.html");
const labelHeroMobileLineBreakView = read("application/mobile/view/cms/page/label/hero.html");
for (const view of [labelHeroPcLineBreakView, labelHeroMobileLineBreakView]) {
  expect(view.includes("block.content_inline_html"), "label_hero PC/mobile template must render normalized Banner description HTML");
}
const bagsComparePcImageView = read("application/index/view/cms/page/bags/compare.html");
const bagsCompareMobileImageView = read("application/mobile/view/cms/page/bags/compare.html");
for (const view of [bagsComparePcImageView, bagsCompareMobileImageView]) {
  expect(view.includes('{notempty name="block.image"}'), "bags_compare must render only when a complete image is configured");
  expect(view.includes('bags-compare-full-image'), "bags_compare complete-image wrapper missing");
  for (const retiredToken of [
    'data-bags-compare-legacy-fallback',
    'tech-compare',
    'block.extra.compare_brand',
    'block.extra.compare_footer',
    'item.text_inline_html',
    'cms/page/bags/icon'
  ]) {
    expect(!view.includes(retiredToken), `bags_compare image-only template must not render legacy token: ${retiredToken}`);
  }
}
const mobileLabelCapabilityView = read("application/mobile/view/cms/page/label/capability.html");
expect(mobileLabelCapabilityView.includes("item.text_inline_html"), "mobile label capability template must render normalized rich description HTML");
const factory = read("application/common/service/cms/render/PageBlockViewModelFactory.php");
expect(
  factory.includes("in_array($blockKey, ['label_hero', 'bags_hero'], true)") &&
  factory.includes("MarkdownRenderer::renderInlinePreserveLineBreaks($content)") &&
  factory.includes("'content_inline_html' => $contentInlineHtml"),
  "label_hero and bags_hero Banner descriptions must preserve every backend-authored newline on the frontend",
);
expect(
  factory.includes("$blockKey === 'label_capability' && $field === 'text'") &&
  factory.includes("MarkdownRenderer::renderInlinePreserveLineBreaks($entry[$field])") &&
  !factory.includes("['label_capability', 'bags_compare']"),
  "only label_capability item text should use preserved inline line breaks; bags_compare is image-only",
);
expect(
  factory.includes("foreach (['title','text','image','image_top','image_bottom','subtitle','badge','url'] as $field)") &&
  factory.includes("$entry[$field] = $entry[$mobileField]"),
  "mobile label capability ViewModel must substitute mobile_text before Markdown rendering",
);

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
expect(!productDetailRender.includes("$this->banners('product.detail"), "Product detail render must not query an unused Banner");
expect(!productDetailRender.includes("'previous' =>"), "Product detail ViewModel must not expose previous product data");
expect(!productDetailRender.includes("'next' =>"), "Product detail ViewModel must not expose next product data");
expect(productDetailRender.includes("detailBreadcrumb"), "Product detail render must keep normal category breadcrumbs");
expect(!productDetailRender.includes("publicCategory"), "Product detail must not keep retired homepage-category special handling");
expect(!productDetailRender.includes("HTML 首页展示"), "Product detail must not know about retired technical category");

const productCategoryAdmin = read("application/admin/controller/cms/ProductCategory.php");
expect(
  productCategoryAdmin.includes("where('slug', '<>', 'html-home-display')"),
  "product category admin must hide the retired homepage technical category before migration",
);
const productCategoryForm = read("application/admin/view/cms/product_category/_form.html");
expect(
  !productCategoryForm.includes('name="row[image]"') &&
  !productCategoryForm.includes("分类图片:"),
  "product category editor must not expose unused category image",
);
const productCategoryJs = read("public/assets/js/backend/cms/product_category.js");
expect(
  !productCategoryJs.includes("backend/cms/media_preview") &&
  productCategoryJs.includes("Form.api.bindevent"),
  "product category editor must not load image-upload dependency after removing category image",
);
const productCategoryRepository = read("application/common/repository/cms/ThinkProductCategoryRepository.php");
expect(
  productCategoryRepository.includes("where('slug','<>','html-home-display')") &&
  productCategoryRepository.includes("if((string)$slug==='html-home-display')return null;"),
  "frontend product category repository must exclude retired homepage technical category",
);
const htmlBaselineSql = read("database/cms_html_baseline.sql");
expect(
  !htmlBaselineSql.includes("'HTML 首页展示','首页展示','html-home-display'") &&
  htmlBaselineSql.includes("SET @home_cat := 0;"),
  "HTML baseline must stop creating the fake homepage product category",
);
const installerProductCategoryCleanup = read("application/common/service/cms/InstallerService.php");
expect(
  installerProductCategoryCleanup.includes("retireLegacyHomeProductCategory") &&
  installerProductCategoryCleanup.includes("SET \`category_id\`=0") &&
  installerProductCategoryCleanup.includes("DELETE FROM \`{$categoryTable}\`"),
  "cms:install must detach homepage products and delete the legacy technical category",
);

const productDetailCss = read("public/assets/jinya/css/style.css");
for (const token of ["Product detail v40", ".jpd-product-hero", ".jpd-detail-nav", ".jpd-media-preview", ".jpd-related-grid"]) {
  expect(productDetailCss.includes(token), `Product detail stylesheet missing ${token}`);
}
const productDetailJs = read("public/assets/jinya/js/main.js");
for (const token of ["Product detail gallery, media preview", "data-jpd-thumb", "openProductPreview", "showDetailMedia"]) {
  expect(productDetailJs.includes(token), `Product detail interaction missing ${token}`);
}

const articleIndexView = read("application/admin/view/cms/article/index.html");
expect(
  !articleIndexView.includes("data-operate-preview"),
  "news management operations must not expose preview",
);
expect(
  articleIndexView.includes("build_toolbar('refresh,add')") &&
  !articleIndexView.includes("build_toolbar('refresh,add,edit')") &&
  !articleIndexView.includes("btn-cms-batch") &&
  !articleIndexView.includes("btn-recyclebin"),
  "news management toolbar must only expose refresh and add",
);

const installerHiddenArticleCleanup = read("application/common/service/cms/InstallerService.php");
expect(
  installerHiddenArticleCleanup.includes("deleteHiddenArticles") &&
  installerHiddenArticleCleanup.includes("DELETE FROM \`{$articleTable}\` WHERE \`status\`='hidden'") &&
  installerHiddenArticleCleanup.includes("cms_page_block_reference") &&
  installerHiddenArticleCleanup.includes("cms_home_section_reference"),
  "cms:install must physically delete hidden articles and stale references",
);
const publishStateMachine = read("application/common/service/cms/PublishStateMachine.php");
expect(
  !publishStateMachine.includes("const HIDDEN") &&
  !publishStateMachine.includes("'hidden' =>"),
  "hidden must remain outside the valid article publishing states",
);

const articleCategoryAdmin = read("application/admin/controller/cms/ArticleCategory.php");
for (const name of ["常见问答", "科创美新闻", "新闻动态"]) {
  expect(
    articleCategoryAdmin.includes(name),
    `article category admin must retire legacy category: ${name}`,
  );
}
expect(
  articleCategoryAdmin.includes("where('name', 'not in', ['常见问答', '科创美新闻', '新闻动态'])"),
  "article category admin must hide retired legacy categories",
);
const articleCategoryRepository = read("application/common/repository/cms/ThinkArticleCategoryRepository.php");
expect(
  articleCategoryRepository.includes("where('name','not in',['常见问答','科创美新闻','新闻动态'])") &&
  articleCategoryRepository.includes("in_array((string)$row['name'],['常见问答','科创美新闻','新闻动态'],true)"),
  "frontend article category repository must exclude retired legacy categories",
);
const articleCategoryInstaller = read("application/common/service/cms/InstallerService.php");
expect(
  articleCategoryInstaller.includes("retireLegacyArticleCategories") &&
  articleCategoryInstaller.includes("SET \`category_id\`=0") &&
  articleCategoryInstaller.includes("SET \`parent_id\`=0") &&
  articleCategoryInstaller.includes("常见问答") &&
  articleCategoryInstaller.includes("科创美新闻") &&
  articleCategoryInstaller.includes("新闻动态"),
  "cms:install must preserve articles/children while deleting retired article categories",
);

const pcNewsDetailView = read("application/index/view/cms/news/detail.html");
const mobileNewsDetailView = read("application/mobile/view/cms/news/detail.html");
for (const [file, body] of [["PC news detail", pcNewsDetailView], ["mobile news detail", mobileNewsDetailView]]) {
  expect(body.includes("banner.0.title"), `${file} must consume configured Banner title`);
  expect(body.includes("banner.0.subtitle"), `${file} must consume configured Banner subtitle`);
}
const singlePageRender = read("application/common/service/cms/render/SinglePageRenderService.php");
expect(!singlePageRender.includes("$this->banners($key, 'channel'"), "Structured single pages must use *_hero blocks instead of duplicate cms_banner data");
const realBannerService = read("application/common/service/cms/PageBlockRealSourceService.php");
expect(realBannerService.includes("normalizeBanner(array $row, array $existing = [])"), "Banner save must preserve fields hidden by page-specific editor profiles");
const bannerEditorController = read("application/admin/controller/cms/PageBlock.php");
for (const token of ["bannerEditorProfile", "news_channel", "image_channel", "pc_image", "allow_multiple"]) {
  expect(bannerEditorController.includes(token), `Banner editor profile missing ${token}`);
}
const bannerCollectionView = read("application/admin/view/cms/page_block/_banner_collection.html");
for (const token of ["bannerProfile.name", "后台可填但前台不生效", "PC Banner 图片", "移动 Banner 图片"]) {
  expect(bannerCollectionView.includes(token), `Banner collection editor missing profile token ${token}`);
}

// Banner configuration must have one runtime source of truth.
const mobileLabelHeroView = read("application/mobile/view/cms/page/label/hero.html");
for (const [file, body] of [
  ["PC labels hero", labelHeroView],
  ["mobile labels hero", mobileLabelHeroView],
]) {
  expect(!body.includes("labels-banner-clean-v83"), `${file} must not inject a hardcoded fallback image`);
}

const pageBlockRepository = read("application/common/repository/cms/ThinkPageContentBlockRepository.php");
expect(
  pageBlockRepository.includes("$terminal==='mobile'&&!empty($r['mobile_image'])?$r['mobile_image']:$r['image']"),
  "Structured page repository must resolve mobile_image before exposing block.image",
);

const runtimeBannerCss = read("public/assets/jinya/css/style.css");
for (const token of [
  "about-banner-upload-20260919.jpg",
  "news-banner-generated-clean-v77.jpg",
  "contact-banner-clean-v78.jpg",
]) {
  expect(!runtimeBannerCss.includes(token), `Dynamic runtime CSS must not provide Banner fallback asset ${token}`);
}

const legacySiteBannerKeys = [
  "cms_pc_product_banner",
  "cms_pc_news_banner",
  "cms_pc_case_banner",
  "cms_pc_about_banner",
  "cms_mobile_product_banner",
  "cms_mobile_news_banner",
  "cms_mobile_case_banner",
  "cms_mobile_about_banner",
];
const checkedInSiteConfig = read("application/extra/site.php");
expect(checkedInSiteConfig.includes("'version' => '1.0.2.20260922'"), "Checked-in site config must bust backend asset cache after editor changes");
const installerServiceSource = read("application/common/service/cms/InstallerService.php");
expect(installerServiceSource.includes("CMS_ASSET_VERSION = '1.0.2.20260922'"), "cms:install must synchronize the backend asset cache version");
expect(installerServiceSource.includes("syncAssetVersion"), "cms:install must update fa_config.version before refreshing site.php");
const cmsSchemaSql = read("database/cms.sql");
const mobileCmsBase = read("application/mobile/controller/CmsBase.php");
for (const key of legacySiteBannerKeys) {
  expect(!siteConfigDefinitions.includes(key), `Site config registry must retire duplicate Banner field ${key}`);
  expect(!checkedInSiteConfig.includes(key), `Checked-in site config must not retain duplicate Banner field ${key}`);
  expect(!cmsSchemaSql.includes(key), `CMS schema seed must not recreate duplicate Banner field ${key}`);
  expect(!pcCmsBase.includes(key), `PC runtime must not read legacy Banner field ${key}`);
  expect(!mobileCmsBase.includes(key), `Mobile runtime must not read legacy Banner field ${key}`);
  expect(installerPageBlocks.includes(key), `Installer must physically clean legacy Banner field ${key}`);
}
expect(installerPageBlocks.includes("retireLegacySiteBannerConfig"), "Installer must retire legacy site Banner configuration");
expect(pcCmsBase.includes("channelBannerVisible', $channelBanner !== ''"), "PC channel Banner visibility must follow the resolved ViewModel image");
expect(mobileCmsBase.includes("channelBannerVisible', $mobileChannelBanner !== ''"), "Mobile channel Banner visibility must follow the resolved ViewModel image");

const strictChannelHeader = read("application/index/view/cms/common/strict_channel_header.html");
expect(!strictChannelHeader.includes("abt_bg.jpg"), "Dynamic strict channel header must not inject a static fallback Banner");

for (const file of [
  "application/index/view/cms/news/index.html",
  "application/index/view/cms/news/detail.html",
  "application/mobile/view/cms/news/index.html",
  "application/mobile/view/cms/news/detail.html",
]) {
  const body = read(file);
  expect(body.includes('{notempty name="banner.0"}'), `${file} must hide the hero when no active Banner exists`);
  expect(body.includes("banner.0.title"), `${file} must consume configured Banner title`);
  expect(body.includes("banner.0.subtitle"), `${file} must consume configured Banner subtitle`);
  expect(!body.includes("default='每一次匠心坚守"), `${file} must not inject default list Banner copy`);
  expect(!body.includes("default='新闻动态'"), `${file} must not inject default detail Banner copy`);
}
for (const file of [
  "application/index/view/cms/news/index.html",
  "application/mobile/view/cms/news/index.html",
]) {
  expect(!read(file).includes('class="hero-cta"'), `${file} must not render a hardcoded Banner CTA outside backend configuration`);
}

const representativeViews = {
  "application/index/view/cms/page/label/materials.html": ["block.extra.print_title", "block.extra.print_points_html", "print-image"],
  "application/index/view/cms/page/bags/compare.html": ["bags-compare-full-image", "block.image"],
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
for (const token of ["config_social_url_1", "config_social_qr_1", "config_social_url_2", "config_social_qr_2", "['title', 'text', 'icon']"]) {
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
const homeCultureForm = read("application/admin/view/cms/common/_home_culture_fields.html");
expect(!homeCultureForm.includes('name="[inputPrefix][background_image]"'), "Home culture editor must not expose obsolete PC background image");
expect(!homeCultureForm.includes('name="[inputPrefix][mobile_background_image]"'), "Home culture editor must not expose obsolete mobile background image");
const cultureSchemaPos = homeSchema.indexOf("'culture' => [");
const cultureSchemaWindow = homeSchema.slice(cultureSchemaPos, cultureSchemaPos + 520);
expect(!cultureSchemaWindow.includes("'background_image'"), "Home culture schema must not allow obsolete background_image");
expect(!cultureSchemaWindow.includes("'mobile_background_image'"), "Home culture schema must not allow obsolete mobile_background_image");
const workshopItemsEditor = read("application/admin/view/cms/common/_workshop_items_editor.html");
expect(workshopItemsEditor.includes("详情说明:"), "Workshop editor must expose a detail description field");
expect(workshopItemsEditor.includes('textarea class="form-control" rows="3" data-workshop-field="text"'), "Workshop detail description must be an editable textarea");
expect(!workshopItemsEditor.includes('type="hidden" data-workshop-field="text"'), "Workshop detail description must not remain hidden");
const homeServiceForm = read("application/admin/view/cms/common/_home_service_fields.html");
expect(homeServiceForm.includes("按钮跳转 URL"), "Home service form must label the real button URL");
expect(!homeServiceForm.includes("备用跳转 URL"), "Home service form must not expose obsolete fallback URL wording");
expect(!homeServiceForm.includes("优先复用全局微信客服配置"), "Home service form must not describe nonexistent global-wechat fallback behavior");
const homeView = read("application/index/view/cms/index/index.html");
const cultureMarkupStart = homeView.indexOf('{notempty name="culture"}');
const cultureMarkupWindow = homeView.slice(cultureMarkupStart, cultureMarkupStart + 1800);
expect(!cultureMarkupWindow.includes("culture.background"), "Homepage culture light layout must not consume a background image");
const mobileCultureView = read("application/mobile/view/cms/index/index.html");
const mobileCultureMarkupStart = mobileCultureView.indexOf('{notempty name="culture"}');
const mobileCultureMarkupWindow = mobileCultureView.slice(mobileCultureMarkupStart, mobileCultureMarkupStart + 1800);
expect(!mobileCultureMarkupWindow.includes("culture.background"), "Mobile homepage culture light layout must not consume a background image");

const serviceMarkupStart = homeView.indexOf('{notempty name="service"}');
const serviceMarkupEnd = homeView.indexOf('{/notempty}', serviceMarkupStart);
const serviceMarkupWindow = homeView.slice(serviceMarkupStart, serviceMarkupStart + 3200);
for (const legacyToken of ["metric.prefix", "metric.value", "metric.unit"]) {
  expect(!serviceMarkupWindow.includes(legacyToken), `Homepage service markup must not consume legacy metric field ${legacyToken}`);
}

expect(!homeView.includes("hero.items.0.title"), "Homepage hero copy must not be pinned to the first Banner row");
expect(homeView.includes('notempty name="banner.title"'), "Homepage each slide must own its configured title");
expect(homeView.includes('name="banner.highlights"'), "Homepage each slide must own its configured selling points");
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
expect(staticHome.includes("hero-slide-copy"), "Static homepage copy must live inside its Banner slide");
const firstStaticSlide = staticHome.slice(staticHome.indexOf('home-banner-01.png'), staticHome.indexOf('home-banner-02.png'));
expect(firstStaticSlide.includes("高质量无版印刷"), "Static first Banner slide must carry its own copy");
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
const bannerCollectionEditor = read("application/admin/view/cms/page_block/_banner_collection.html");
for (const unused of ["overlay_image", "button_text", "mobile_link_url"]) {
  expect(!bannerCollectionEditor.includes('name="real[banners][{$bannerIndex}][' + unused + ']"'), `PageBlock Banner editor must not expose unused homepage field ${unused}`);
}
expect(!bannerCollectionEditor.includes('name="real[banners][{$bannerIndex}][link_url]"'), "PageBlock Banner editor must not expose unused homepage link");
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
