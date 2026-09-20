# HTML 基线动态化映射规范

## 核心原则

`html/` 是 PC 与移动端页面视觉效果、DOM 层级、class 命名和区块顺序的唯一基线。动态化时不能让静态前端迁就旧 CMS 字段；当后台缺少页面所需数据时，应扩展页面专用结构化字段，再由 View 读取。

运行时链路：

```
html/ 页面基线
  -> application/index|mobile/view（保持 DOM/class）
  -> Controller assign
  -> 现有 RenderService / Repository（仅准备数据）
  -> Page Block extra_json / 页面字段
  -> 后台结构化编辑器
```

RenderService 不负责拼 HTML。模板中不查询数据库。

## 公共模板

PC：

- `application/index/view/cms/layout/header.html`
- `application/index/view/cms/layout/nav.html`
- `application/index/view/cms/layout/footer.html`

Mobile：

- `application/mobile/view/cms/layout/header.html`
- `application/mobile/view/cms/layout/nav.html`
- `application/mobile/view/cms/layout/footer.html`

前端资源由 `html/assets/` 同步发布到 `public/assets/jinya/`，运行时 View 使用 `/assets/jinya/...`。

## 页面映射

| HTML 基线 | 动态 View | 后台数据 |
| --- | --- | --- |
| `html/index.html` | `cms/index/index.html` | 首页 section + banner + layout |
| `html/labels.html` | `cms/page/label.html` | label_* 页面区块 |
| `html/bags.html` | `cms/page/bags.html` | bags_* 页面区块 |
| `html/boxes.html` | `cms/page/boxes.html` | boxes_* 页面区块 |
| `html/about.html` | `cms/page/about.html` | about_* 页面区块 |
| `html/contact.html` | `cms/page/contact.html` | contact_* 页面区块 |
| `html/news.html` | `cms/news/index.html` | 新闻列表 ViewModel |
| `html/news-detail.html` | `cms/news/detail.html` | 新闻详情 ViewModel |

PC 与 mobile 使用同一 CMS 内容源，但各自拥有独立 View。

## 字段扩展规则

页面专用字段优先存入 `cms_page_content_block.extra_json`，通过以下三层形成完整契约：

1. **Codec 白名单**：负责保存/读取结构化字段。
2. **PageContentBlockEditorSchema + 后台表单**：负责让运营人员可见、可录入、可排序、可控制 PC/Mobile。
3. **PageBlockViewModelFactory + View**：负责终端覆盖、Markdown 渲染和最终模板消费。

只有当数据明确属于数据库实体本身、且 `extra_json` 无法合理表达时，才新增数据库列。

## 当前已补的关键字段

### labels

- Banner：`subtitle`、卖点 title/text/icon。
- 胶水对比：视觉图、第二组标题。
- 不干胶印刷：`print_title`、`print_points`、`print-image` 轮播分组。
- 服务流程：流程说明文本。
- 五大保障 / 专业团队：结构化项目。

### bags

- 专版/无版对比：标题、副标题、说明、背景图、品牌小标题、底部提示、两套方案项目。
- 应用案例：可配置背景图与 Tabs。
- 定制流程：步骤项目 + 场景图项目。
- 底部保障：六个结构化保障项目。

### boxes

- Hero：行业标签 + CTA。
- 品质承诺：面板标题/说明/徽标。
- 细节区：编号标签、说明、左右版式 + 底部采购 CTA。
- 工艺/材质：英文副标题。
- 服务保障：标题 + 说明 + 图标。

### contact

- Hero CTA 复用区块 `link_text/link_url`。
- 联系信息继续使用结构化联系方式与百度地图配置。
- 感谢区使用动态标题、副标题、正文、背景、电话。

## 验收门禁

PR 必须至少通过：

- `node scripts/site-audit.mjs`：静态 HTML 基线及资源完整性。
- `node scripts/dynamic-view-audit.mjs`：动态模板、路由、Codec、Schema、后台字段契约。
- `node scripts/viewport-audit.mjs`：2048×1536 PC、多页面移动端以及首页 4K 视口截图与溢出检查。

新增或修改 HTML 区块时，应同步更新对应动态字段契约与审计规则。
