# AGENT.md

## 1. 文档用途

本文件用于约束在本项目中工作的 AI 编程代理和开发人员。

项目目标是基于 **FastAdmin 1.6.5 + ThinkPHP 5.0** 建设金亚企业 CMS 网站，并严格使用 html 目录中已提供的 PC 端与移动端页面。所有新增功能、修复和重构必须遵守本文件。

---

## 2. 项目目标

项目同时包含：

1. FastAdmin 管理后台；
2. PC 端企业官网；
3. 移动端企业官网；
4. 产品、新闻、案例、单页、轮播图、导航等 CMS 数据管理；
5. 内容审核、发布、下架、版本和定时发布；
6. 客户咨询、负责人分配和跟进记录；
7. PC 与移动端独立模板，但共用同一套 CMS 数据；
9. 严格保留上传克隆页面的 DOM、class、CSS 和 JavaScript。

本项目不是商城，不应擅自增加购物车、支付、会员积分或复杂营销功能。

---

## 3. 技术栈与运行环境

### 3.1 后端

- FastAdmin 1.6.5；
- ThinkPHP 5.0；
- PHP 7.4～8.1；
- MySQL 5.7 或 MySQL 8.0；
- FastAdmin Auth 权限；
- ThinkPHP 服务端模板渲染。

### 3.2 前端

- 前端页面包括PC和mobile 都在 html 目录中
- PC 模块：`application/index`；
- 移动模块：`application/mobile`；
- 不引入新的前端框架替换原网站脚本；
- 不擅自将页面改造成 Vue、React 或前后端分离项目。

### 3.3 环境限制

ThinkPHP 5.0 与 PHP 8.4 存在兼容问题。不要使用 PHP 8.4 的运行结果判断项目在目标环境中不可用。

推荐环境：

```text
PHP 7.4～8.1
MySQL 5.7 / 8.0
Web 根目录指向 public
```

---

## 4. 核心架构

项目采用 MVC＋Service 分层：

```text
浏览器请求
    ↓
index / mobile Controller
    ↓
RenderService
    ↓
Repository
    ↓
Model / MySQL
    ↓
ViewModel
    ↓
PC / 移动 HTML Template
```

生产公开页面必须遵循：

```text
Controller → RenderService → Repository → Model → ViewModel → Template
```

后台链路：

```text
FastAdmin Admin Controller
    ↓
CMS Service
    ↓
CMS Model
    ↓
MySQL
```

### 4.1 Controller 规则

Controller 只负责：

- 接收请求参数；
- 权限检查；
- 调用 Service；
- 向模板传递数据；
- 返回 HTML 或 JSON。

禁止在 Controller 中堆放：

- 多表事务；
- 复杂发布逻辑；
- 缓存联动；
- 版本快照；
- 大量直接 `Db::name()` 查询。

### 4.2 Service 规则

Service 负责：

- 产品、文章、案例、单页业务；
- 审核与发布；
- 内容版本；
- 定时发布；
- 客户咨询和跟进；
- 缓存清理；
- Sitemap 和 URL 重定向；
- 安装与升级。

### 4.3 Model 规则

Model 负责：

- 表映射；
- 字段转换；
- 关联关系；
- 查询作用域；
- 获取器和修改器；
- 软删除。

Model 不负责完整发布流程。

---

## 5. 主要目录

```text
application/
├── admin/
│   ├── controller/cms/
│   ├── model/cms/
│   ├── validate/cms/
│   └── view/cms/
├── index/
│   ├── controller/
│   └── view/cms/
├── mobile/
│   ├── controller/
│   └── view/cms/
└── common/
    ├── behavior/
    ├── enum/
    ├── exception/
    ├── model/cms/
    ├── repository/cms/
    └── service/cms/

public/assets/
├── kcm-pc-strict/
└── kcm-mobile-strict/

database/
└── cms.sql

docs/
```

不得修改：

```text
thinkphp/
vendor/
```

除非已经证明是框架缺陷，并得到明确授权。

---

## 6. PC 与移动端模块边界

### 6.1 PC 端

PC 请求由：

```text
application/index
```

处理。

PC 模板必须使用：

```text
application/index/view/cms
```

PC 原始资源必须位于：

```text
public/assets/kcm-pc-strict
```

### 6.2 移动端

移动请求由：

```text
application/mobile
```

处理。

移动模板必须使用：

```text
application/mobile/view/cms
```

移动原始资源必须位于：

```text
public/assets/kcm-mobile-strict
```

### 6.3 共享规则

PC 与移动端必须共用：

- 产品；
- 产品分类；
- 新闻；
- 新闻分类；
- 案例；
- 单页；
- 导航；
- Banner；
- 网站配置；
- 客户咨询；
- 审核发布；
- 内容版本；
- SEO 和重定向。

禁止为移动端复制一套 `mobile_product`、`mobile_article` 等业务表。

---

## 7. 严格克隆规则

这是本项目最重要的约束。

### 7.1 基准来源

严格还原 sites 目录中 PC 和移动端克隆源码为基准。

当线上原站无法访问时，不得凭主观重新设计页面。

### 7.2 必须保留

- 原始 DOM 层级；
- 原始 class 名称；
- 原始模块顺序；
- 原始 CSS；
- 原始 JavaScript；
- 原始页面宽度、高度和间距体系；
- 原始轮播、菜单和交互行为；
- 原始旧 URL 结构。

### 7.3 动态化方法

正确方法：

```text
冻结 HTML（仅供离线分析）
    ↓
模板保留 DOM 和 class
    ↓
Controller 调用 RenderService
    ↓
Repository / Model 从数据库取数
    ↓
ViewModel 提供标题、图片、列表和正文
    ↓
模板动态渲染
```

参考 html 目录中 PC 和 mobile 中的页面，抽取公共部分形成 公共模板，把页面中内容替换为动态参数，渲染后形成最终页面。

### 7.4 原始资源锁定

PC 和移动端严格资源应维护哈希清单。

修改严格资源前必须说明原因。若只是增加动态数据，不应改动原始 CSS 和 JavaScript。

### 7.5 字体规则

不得向用户交付或分发容器中的字体文件。

如果上传源码缺少字体：

- 保留字体引用说明；
- 使用系统字体回退；
- 在文档中记录缺失项；
- 不从不明来源下载替代字体。

---

## 8. 移动端请求分发

支持三种移动端访问方式：

1. 手机访问普通 URL，自动交给 `mobile` 模块；
2. 独立 `/mobile/...` URL；
3. `?view=mobile` 和 `?view=desktop` 手动切换。

手动选择可使用 `cms_view` Cookie 保存。

自动分发只能处理 CMS 控制器：

```text
index
product
news
cases
page
inquiry
preview
legacy
```

不得接管：

```text
admin
api
index/user
index/ajax
验证码
FastAdmin 登录
```

---

## 9. CMS 后台模块

后台主菜单为“内容运营”，主要包含：

```text
网站配置
导航管理
轮播图
首页模块
产品分类
产品管理
新闻分类
新闻管理
单页管理
工程案例
客户咨询
页面管理
页面功能块
公共布局
URL 重定向
内容版本
```

### 9.1 网站配置

至少支持：

- 网站名称；
- 公司全称；
- Logo；
- 网站口号；
- 电话；
- 邮箱；
- 地址；
- 备案号；
- PC 产品、新闻、案例、企业栏目 Banner；
- 移动产品、新闻、案例、企业栏目 Banner；
- 微信、抖音、快手、小红书、视频号和 B 站二维码。

### 9.2 产品

产品至少支持：

- 多级分类；
- 封面；
- 相册；
- 技术参数；
- 产品特点；
- 适用范围；
- 施工说明；
- 注意事项；
- 资料下载；
- 相关产品；
- 关联案例；
- SEO；
- 复制；
- 审核和发布。

产品复制时必须同步复制：

- 相册；
- 技术参数。

设置主图时必须同步更新产品封面。

### 9.3 新闻和案例

新闻和案例必须独立建模，不得强行共用一张万能内容表。

案例支持关联多个产品。

### 9.4 单页

主要 slug：

```text
about
contact
honor
patent
gallery
video
construction
```

### 9.5 客户咨询

咨询至少支持：

- 姓名；
- 手机号；
- 公司；
- 地区；
- 咨询产品；
- 来源 URL；
- 来源标题；
- 负责人；
- 跟进记录；
- 下次跟进时间；
- 状态；
- CSV 导出；
- 手机号脱敏和权限控制。

不要在控制器中定义名为 `assign()` 的业务方法，因为它会与 ThinkPHP 控制器的 `assign()` 冲突。客户分配操作使用 `allocate()`。

### 9.6 页面、功能块与公共布局

页面配置采用固定注册表，不是通用页面构建器：

- 页面类型、功能块类型、字段类型和主要顺序由 `PageSchemaRegistry` 固定；
- 默认公共页头、页尾由 `LayoutSchemaRegistry` 固定；
- 运营人员不能新增页面类型、布局类型或任意字段；
- 页面配置只保存公共布局引用、SEO 和固定功能块配置；
- 产品、新闻、案例和单页只保存稳定 ID 引用，不复制完整业务内容；
- 只有注册表明确允许的图片字段才显示 PC/移动端覆盖；
- 页面、功能块和公共布局保存后立即生效，不经过内容审核、草稿或定时发布；
- 保存必须使用事务、版本号和引用校验，禁止静默覆盖和部分成功；
- 固定页面、核心功能块和默认公共布局不能删除；
- 被页面功能块引用的业务内容不能永久删除，但可以下架。

---

## 10. 发布状态和工作流

统一状态建议：

```text
draft       草稿
scheduled   发布
offline     已下架
```

不需要下面的状态：

```text
pending     待审核
rejected    已驳回
published   已发布
```

标准流程：

```text
草稿
  ↓
立即发布
  ↓
下架
```

业务内容要求：

- 本节状态流只适用于产品、新闻、案例、单页等业务内容；
- 页面配置、固定功能块和公共布局不使用该状态流，点击保存后立即生效；
- 发布和下架必须记录操作日志；
- URL 修改应生成 301 重定向；
- 发布后清除相关缓存；

---

## 11. 数据库与安装

CMS 安装命令：

```bash
php think cms:install
```

安装器必须：

- 兼容 ThinkPHP 5 懒连接；
- 在调用 PDO 前主动初始化连接；
- 可重复执行；
- 不删除已有业务数据；
- 自动补充缺失表、字段、默认配置和权限菜单；
- 刷新 `application/extra/site.php`；
- 清理必要缓存。

ThinkPHP 5 懒连接注意事项：

```php
$connection = Db::connect();
$connection->execute('SELECT 1');
```

直接调用尚未初始化连接的 `getPdo()` 可能返回 `false`，不得对其直接调用 `prepare()`。

### 11.1 表前缀

默认表前缀：

```text
fa_
```

安装器必须读取当前数据库配置中的实际前缀，不能在业务逻辑中写死。

### 11.2 主要 CMS 表

包括但不限于：

```text
cms_navigation
cms_banner
cms_home_section
cms_product_category
cms_product
cms_product_image
cms_product_parameter
cms_article_category
cms_article
cms_page
cms_case
cms_case_image
cms_case_product
cms_inquiry
cms_inquiry_followup
cms_url_redirect
cms_revision
```

实际名称应自动带数据库前缀。

---

## 12. SEO 规则

页面支持：

- SEO 标题；
- SEO 关键词；
- SEO 描述；
- Canonical；
- 自定义 slug；
- Sitemap；
- 301 重定向；
- 图片 Alt；
- 是否允许收录。

PC 与移动端共用内容 URL 时，应优先采用内部模块分发，不要无意义地制造两套可被搜索引擎收录的重复页面。

预览 URL 必须：

- 使用短期随机 Token；
- 默认有效期约 5 分钟；
- 禁止搜索引擎收录。

---

## 14. 安全要求

必须处理：

- FastAdmin Auth 权限；
- Controller 与 Service 双重权限校验；
- CSRF；
- 咨询接口频率限制；
- 富文本 XSS 过滤；
- `<script>` 过滤；
- `onerror`、`onclick` 等事件属性过滤；
- `javascript:` 协议过滤；
- 上传文件白名单；
- 图片大小限制；
- 客户手机号脱敏；
- 查看完整手机号日志；
- 客户导出日志；
- 数据软删除；
- 危险操作确认和恢复。

不得仅依靠前端隐藏按钮实现权限控制。

---

## 15. FastAdmin 兼容注意事项

### 15.1 Controller 方法签名

继承 FastAdmin `Backend` 的方法必须保持兼容签名。

例如：

```php
public function edit($ids = null)
```

不要写成：

```php
public function edit()
```

### 15.2 保留方法名

不要使用可能与框架冲突的方法名，例如控制器中的：

```text
assign
```

### 15.3 列表排序字段

JavaScript 表格的 `sortName` 必须对应真实数据库字段。

例如 URL 重定向表没有 `weigh` 时，应使用：

```javascript
sortName: 'id'
```

服务端也应对非法排序字段做白名单回退。

### 15.4 后台菜单

CMS 菜单存储在 FastAdmin 权限表中。安装后如果普通管理员看不到菜单，需要：

1. 退出后台重新登录；
2. 清理缓存；
3. 给对应角色勾选“内容运营”权限。

---

## 16. 缓存规则

建议缓存键：

```text
cms:site:config
cms:navigation:header
cms:navigation:footer
cms:home:published
cms:product:detail:{slug}
cms:product:category:{categoryId}:{page}
cms:article:detail:{slug}
cms:case:detail:{slug}
cms:page-config:{pageKey}:pc
cms:page-config:{pageKey}:mobile
```

修改或发布业务内容时必须清理关联缓存；保存页面、功能块或公共布局时，必须自动清理受影响的 PC/移动端页面配置缓存。

例如产品发布：

```text
产品详情缓存
产品分类列表缓存
首页推荐缓存
Sitemap
```

禁止通过“每次请求全部清缓存”解决缓存一致性问题。

---

## 17. 开发流程

### 17.1 修改前

1. 确认当前项目基线版本；
2. 阅读相关 Controller、Service、Model 和模板；
3. 找到相同功能的现有实现；
4. 明确是否会影响 PC、移动端、后台或安装器；
5. 创建最小失败测试或复现脚本。

### 17.2 实现时

1. 优先修复根因；
2. 一次只解决一个问题；
3. 不在修 bug 时顺便大规模重构；
4. 不修改严格克隆资源，除非问题确实来自原资源；
5. 不把业务逻辑塞进模板；
6. 不在模板中直接查询数据库。

### 17.3 完成前

必须提供新的验证证据，不能仅说“应该可以”。

---

## 18. 测试和验收

每次改动至少执行相关测试。

典型测试集合：

```text
CMS bootstrap tests
CMS domain tests
CMS installer lazy connection test
CMS integration tests
CMS cloned mobile theme tests
CMS mobile module tests
CMS strict mobile clone tests
CMS mobile template compile tests
CMS cloned PC theme tests
CMS strict PC clone tests
CMS PC template compile tests
CMS V4 regression tests
CMS structure tests
```

还应执行：

### 18.1 PHP 语法

```bash
find application tests -name '*.php' -print0 | xargs -0 -n1 php -l
```

Windows 环境可逐文件运行 `php -l`。

### 18.2 JavaScript 语法

对新增或修改的 JavaScript 使用 Node.js 做语法检查。

### 18.3 模板编译

PC 和移动端 ThinkPHP 模板都必须实际编译，不得只做文本扫描。

### 18.4 静态资源

检查：

- CSS 引用的文件是否存在；
- 是否残留原网站域名；
- 是否错误打包字体文件；
- 严格资源哈希是否变化；
- PC 改动是否影响移动端；
- 移动端改动是否影响 PC。

### 18.5 安装器

至少验证：

```bash
php think cms:install
```

能够在：

- 全新数据库；
- 已安装旧版 CMS 的数据库；
- 使用非默认表前缀的数据库；

安全执行。

---

## 19. 部署步骤

常规升级：

```bat
cd /d D:\code\yinshua2
php think cms:install
rmdir /s /q runtime\cache
rmdir /s /q runtime\temp
```

然后：

1. 退出后台重新登录；
2. 浏览器 `Ctrl + F5`；
3. 手机浏览器清理站点缓存；
4. 检查 PC：`/?view=desktop`；
5. 检查移动：`/?view=mobile`；
6. 检查后台内容运营菜单；
7. 检查首页、产品、新闻、案例、关于、联系；
8. 检查咨询提交；
9. 检查旧 URL。

---

## 20. 已知历史问题

后续开发不得重新引入以下问题：

1. CMS 表未安装导致 `fa_cms_navigation` 不存在；
2. 安装器对未初始化 PDO 调用 `prepare()`；
3. 网站配置 `edit()` 签名不兼容；
4. 客户咨询使用 `assign()` 与框架冲突；
5. URL 重定向按不存在的 `weigh` 字段排序；
6. 安装脚本创建导航但未创建 `about/contact` 单页；
7. 产品相册或参数编辑时把 `product_id` 写成 0；
8. 定时发布只有状态没有执行命令；
9. 富文本未经统一 XSS 过滤；
10. 自动移动分发误接管用户登录或 AJAX；
11. 近似重写前端而破坏严格克隆要求；
12. 修改 PC 模板时破坏移动模板，或反之。

---

## 21. 文档要求

重要功能必须在项目根目录 `docs` 中记录。

文档至少说明：

- 设计目标；
- 文件变更；
- 数据库变更；
- 安装或升级步骤；
- 后台配置入口；
- 测试命令；
- 验证结果；
- 已知限制；
- 回滚方法。

禁止写入：

```text
TODO
TBD
以后处理
大概如此
```

交付文档必须能让不了解上下文的开发者独立部署。

---

## 22. 交付要求

每次向用户交付补丁或完整项目时：

1. 先在副本中应用补丁；
2. 比较副本与目标完整项目；
3. 重新解压最终 ZIP；
4. 在解压副本中运行测试；
5. 提供补丁包；
6. 提供完整包；
7. 提供安装说明；
8. 提供验证报告；
9. 提供 SHA-256；
10. 明确说明无法完成的验证。

不得声称“严格一致”却没有说明比较基准。

---

## 23. 禁止事项

未经用户明确批准，不得：

- 修改 `thinkphp` 和 `vendor`；
- 更换 FastAdmin 或 ThinkPHP 大版本；
- 改成前后端分离；
- 删除旧 URL 兼容；
- 合并 PC 和移动模板；
- 为移动端复制业务表；
- 删除内容审核和版本功能；
- 自动覆盖运营人员已有内容；
- 从原网站或不明来源下载受版权保护的素材；
- 打包或分享字体文件；
- 在模板中直接写 SQL；
- 为了“代码整洁”改变原站 DOM；
- 在没有测试证据时宣称修复完成。

---

## 24. 完成检查清单

提交前逐项确认：

```text
[ ] Controller 没有新增复杂业务逻辑
[ ] Service 事务完整
[ ] Model 关联正确
[ ] 数据库升级可重复执行
[ ] FastAdmin 方法签名兼容
[ ] 没有框架保留方法冲突
[ ] PC 严格 DOM 未被破坏
[ ] 移动严格 DOM 未被破坏
[ ] PC 原始资源哈希未意外变化
[ ] 移动原始资源哈希未意外变化
[ ] 没有原域名残留
[ ] 没有字体文件被分发
[ ] 旧 URL 可访问
[ ] SEO 数据正确
[ ] 咨询表单可提交
[ ] 权限在 Controller 和 Service 均校验
[ ] PHP 语法检查通过
[ ] JavaScript 语法检查通过
[ ] PC 模板编译通过
[ ] 移动模板编译通过
[ ] CMS 回归测试通过
[ ] 最终 ZIP 重新解压验证通过
[ ] docs 文档已更新
```

---

## 25. 最终原则

本项目的优先级从高到低为：

```text
数据安全
> 后台可操作性
> PC/移动端严格还原
> 旧 URL 与 SEO 兼容
> 代码边界清晰
> 开发便利性
```

当“代码更优雅”与“页面严格一致”冲突时，严格克隆页面优先；当“页面效果”与“数据安全”冲突时，数据安全优先。
