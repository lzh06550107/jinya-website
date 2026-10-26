# 金亚包装网站部署文档

> 项目：`lzh06550107/jinya-website`  
> 当前验收分支：`feature/dynamic-html-views-20260920`  
> 技术栈：FastAdmin + ThinkPHP 5 + MySQL + Nginx/Apache  
> 后台入口：`/admin1.php`

---

## 1. 部署目标与原则

本项目不是纯静态站点。前台 PC / Mobile 页面、Banner、产品、新闻、公共页头页脚、客户咨询等数据均由 FastAdmin CMS 动态渲染。

生产部署必须同时保证以下内容完整：

1. Git 仓库代码；
2. PHP / Composer 依赖；
3. FastAdmin 前端依赖；
4. MySQL 数据库；
5. `public/uploads/` 用户上传文件；
6. `.env` 数据库配置；
7. `application/extra/site.php` 生成的站点配置；
8. `runtime/` 可写目录。

**Web Server 的站点根目录必须指向 `public/`，不能指向仓库根目录。**

---

## 2. 当前项目的关键部署事实

### 2.1 PHP 要求

仓库 `composer.json` 和 FastAdmin 安装器要求：

```text
PHP >= 7.4.0
```

Composer 明确要求以下扩展：

```text
json
curl
pdo
bcmath
```

生产环境还应安装 MySQL PDO 驱动及 FastAdmin 常用扩展，例如：

```text
pdo_mysql
mbstring
fileinfo
gd
zip
xml
openssl
```

部署前检查：

```bash
php -v
php -m
composer --version
```

---

### 2.2 数据库

数据库配置来源于根目录 `.env`。

参考：

```ini
[app]
debug = false
trace = false

[database]
hostname = 127.0.0.1
database = jinya
username = jinya
password = YOUR_DB_PASSWORD
hostport = 3306
prefix = fa_
```

生产环境必须保持：

```ini
debug = false
trace = false
```

数据库默认字符集建议使用：

```text
utf8mb4
```

---

### 2.3 CMS 安装命令

项目内置：

```bash
php think cms:install
```

该命令会：

- 创建/补齐 CMS 数据表；
- 更新 CMS 字段；
- 初始化页面配置；
- 同步当前 HTML 基线；
- 同步后台菜单和配置；
- 刷新 `application/extra/site.php`；
- 清理已经退役的 CMS 数据；
- 删除 hidden 历史新闻；
- 删除当前前台未引用的旧产品；
- 清理已退役分类、页面块和其它历史数据。

因此：

> **生产环境执行 `php think cms:install` 前必须先备份数据库。**

它可以重复执行，但它不是单纯的“建表命令”。

---

### 2.4 上传目录不在 Git 中

仓库的 `.gitignore` 排除了：

```text
/public/uploads/*
```

所以执行：

```bash
git clone
```

或者：

```bash
git pull
```

不会恢复后台已经上传的 Banner、二维码、产品图、新闻图等文件。

生产环境必须把：

```text
public/uploads/
```

作为持久化目录单独备份。

---

### 2.5 Composer 版本锁定风险

当前仓库忽略：

```text
composer.lock
```

因此干净服务器执行：

```bash
composer install
```

时，Composer 可能重新解析符合 `composer.json` 的依赖版本。

生产建议：

1. 先在测试环境验证 Composer 安装结果；
2. 再部署到生产；
3. 后续建议将经过验证的 `composer.lock` 纳入版本控制，提高部署可重复性。

Node 依赖存在 `package-lock.json`，因此前端依赖优先使用：

```bash
npm ci
```

而不是 `npm install`。

---

## 3. 推荐服务器目录

示例：

```text
/srv/www/jinya-website
├── application
├── database
├── docs
├── public
│   ├── admin1.php
│   ├── index.php
│   ├── assets
│   └── uploads
├── runtime
├── vendor
├── thinkphp
├── .env
├── composer.json
└── think
```

Nginx / Apache DocumentRoot 必须设置为：

```text
/srv/www/jinya-website/public
```

不能设置为：

```text
/srv/www/jinya-website
```

---

# 4. 新服务器部署

## 4.1 创建部署目录

```bash
sudo mkdir -p /srv/www
sudo chown -R $USER:www-data /srv/www
cd /srv/www
```

克隆当前验收分支：

```bash
git clone -b feature/dynamic-html-views-20260920 \
  https://github.com/lzh06550107/jinya-website.git

cd jinya-website
```

正式合并到 `main` 后可改为：

```bash
git clone https://github.com/lzh06550107/jinya-website.git
```

---

## 4.2 安装 PHP 依赖

```bash
composer install \
  --no-dev \
  --prefer-dist \
  --optimize-autoloader \
  --no-interaction
```

安装完成后应存在：

```text
vendor/
thinkphp/
```

如果缺少这两个目录，FastAdmin 无法启动。

---

## 4.3 安装前端依赖

建议在首次干净部署执行：

```bash
npm ci
npm run build
```

`npm run build` 会执行 FastAdmin Grunt 构建，包括：

- 生成/同步 `public/assets/libs/`；
- 构建 frontend JS/CSS；
- 构建 backend JS/CSS。

项目自己的金亚页面资源位于：

```text
public/assets/jinya/
```

这些资源本身已纳入 Git。

如果服务器不希望安装 Node，也可以在 CI/构建机生成完整 `public/assets/libs/` 后随发布制品一起部署。

---

## 4.4 配置 .env

如果不存在：

```bash
cp .env.sample .env
```

编辑：

```bash
vim .env
```

示例：

```ini
[app]
debug = false
trace = false

[database]
hostname = 127.0.0.1
database = jinya
username = jinya
password = REPLACE_WITH_REAL_PASSWORD
hostport = 3306
prefix = fa_
```

不要把生产 `.env` 提交到 Git。

---

# 5. 数据库初始化方式

## 5.1 推荐方案：恢复已验收数据库

生产部署最稳妥的方式是恢复当前已经验收过的 FastAdmin + CMS 数据库备份。

创建数据库：

```sql
CREATE DATABASE jinya
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
```

恢复：

```bash
mysql -u jinya -p jinya < backup.sql
```

然后执行：

```bash
php think cms:install
```

该命令会把数据库结构和 CMS 数据同步到当前代码版本。

---

## 5.2 完全空数据库安装

只有在没有任何 FastAdmin 基础数据库时才使用此方式。

项目的 `cms:install` 会检查：

```text
fa_auth_rule
```

如果 FastAdmin 基础表不存在，会直接提示：

```text
FastAdmin 基础数据表不存在，请先完成 FastAdmin 安装。
```

当前仓库已经包含 `install.lock`，因此空库 CLI 初始化需要明确使用 `--force=true`。

**仅限空数据库执行：**

```bash
php think install \
  --hostname=127.0.0.1 \
  --hostport=3306 \
  --database=jinya \
  --prefix=fa_ \
  --username=DB_USER \
  --password='DB_PASSWORD' \
  --force=true
```

该命令会：

1. 安装 FastAdmin 基础表；
2. 自动调用 CMS Installer；
3. 写入 `.env`；
4. 生成后台管理员随机密码；
5. 生成安装锁；
6. 更新 Token 密钥；
7. 尝试删除 `public/install.php`。

命令输出中会显示：

```text
Admin username: ...
Admin password: ...
```

必须立即保存管理员密码。

### 注意

FastAdmin CLI 初始化会修改本地文件，例如：

```text
.env
application/config.php
application/admin/command/Install/install.lock
public/install.php
```

因此**不要在已有生产数据库上使用 `install --force=true`**。

对于正式生产环境，优先使用“恢复已验收数据库”的方式。

---

# 6. 恢复上传资源

将备份的上传目录恢复到：

```text
public/uploads/
```

例如：

```bash
tar -xzf jinya-uploads.tar.gz -C public/
```

确认：

```bash
find public/uploads -maxdepth 2 -type f | head
```

如果数据库中的图片 URL 是：

```text
/uploads/...
```

但服务器没有对应文件，前台就会出现 404。

---

# 7. 文件权限

推荐项目代码归部署用户所有，Web Server 使用同组访问。

示例：

```bash
sudo chown -R deploy:www-data /srv/www/jinya-website

sudo find /srv/www/jinya-website -type d -exec chmod 755 {} \;
sudo find /srv/www/jinya-website -type f -exec chmod 644 {} \;
```

需要写权限的目录：

```bash
sudo chmod -R 775 runtime
sudo chmod -R 775 public/uploads
sudo chmod -R 775 application/extra
```

至少以下目录必须可写：

```text
runtime/
public/uploads/
application/extra/
```

其中 `application/extra/site.php` 会被 CMS 安装/配置同步重新生成。

首次运行 FastAdmin 安装器时，安装进程还需要能够写：

```text
.env
application/config.php
application/admin/command/Install/
```

完成安装后应收紧这些文件权限。

---

# 8. Nginx 配置

下面配置以：

```text
/srv/www/jinya-website
```

为项目目录。

根据实际 PHP-FPM 版本调整 socket。

```nginx
server {
    listen 80;
    server_name example.com www.example.com;

    root /srv/www/jinya-website/public;
    index index.php index.html;

    charset utf-8;
    client_max_body_size 50m;

    # 前台 ThinkPHP 路由
    location / {
        try_files $uri $uri/ /index.php?s=$uri&$query_string;
    }

    # 支持 index.php/path 和 admin1.php/path 形式的 PATH_INFO
    location ~ [^/]\.php(/|$) {
        fastcgi_split_path_info ^(.+?\.php)(/.*)$;

        if (!-f $document_root$fastcgi_script_name) {
            return 404;
        }

        include fastcgi_params;

        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;

        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
    }

    # 安装完成后禁止 Web 安装入口
    location = /install.php {
        return 404;
    }

    # 静态资源
    location ~* \.(?:css|js|jpg|jpeg|gif|png|webp|svg|ico|woff|woff2|ttf)$ {
        expires 7d;
        access_log off;
        try_files $uri =404;
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }
}
```

检查：

```bash
sudo nginx -t
sudo systemctl reload nginx
```

如果使用 HTTPS，在此配置外再增加证书和 80 → 443 跳转即可。

---

# 9. Apache 配置

项目已经包含：

```text
public/.htaccess
```

Apache 必须：

1. DocumentRoot 指向 `public/`；
2. 开启 `mod_rewrite`；
3. 允许 `.htaccess` 生效。

示例：

```apache
<VirtualHost *:80>
    ServerName example.com
    DocumentRoot /srv/www/jinya-website/public

    <Directory /srv/www/jinya-website/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

启用 rewrite：

```bash
sudo a2enmod rewrite
sudo systemctl reload apache2
```

---

# 10. 首次 CMS 同步

数据库和代码准备完成后：

```bash
cd /srv/www/jinya-website

php think cms:install
```

成功时类似：

```text
CMS 安装完成：20 张数据表，表前缀 fa_
请退出后台重新登录，或刷新后台菜单缓存。
```

然后清理 ThinkPHP 缓存：

```bash
rm -rf runtime/cache/*
rm -rf runtime/temp/*
```

不要删除：

```text
runtime/log/
```

除非确定不需要历史日志。

---

# 11. CMS 健康检查

项目提供：

```bash
php think cms:health
```

正式上线前必须执行。

如需保存报告：

```bash
php think cms:health --json=artifacts/cms-health.json
```

如果命令返回 ERROR，应先修复再上线。

---

# 12. 静态审计

如果服务器已安装 Node.js，可执行：

```bash
node scripts/fastadmin-package-audit.mjs
node scripts/site-audit.mjs
node scripts/dynamic-view-audit.mjs
php tests/cms_html_baseline_template_compile_test.php
```

GitHub Actions 的 Site Audit 也是基于这些检查。

推荐发布门禁：

```text
代码审计通过
        ↓
模板编译通过
        ↓
数据库备份
        ↓
部署代码
        ↓
cms:install
        ↓
cms:health
        ↓
人工验收
```

---

# 13. 定时发布任务

项目包含：

```bash
php think cms:publish-scheduled
```

如果后台会使用“定时发布”，服务器需要配置 Cron。

例如每分钟执行一次：

```cron
* * * * * cd /srv/www/jinya-website && /usr/bin/php think cms:publish-scheduled >> runtime/log/cms-publish-scheduled.log 2>&1
```

如果项目不使用定时发布，可以不配置。

---

# 14. 生产环境增量更新流程

这是日常发布最重要的一部分。

假设代码目录：

```text
/srv/www/jinya-website
```

进入项目：

```bash
cd /srv/www/jinya-website
```

## 14.1 发布前备份数据库

```bash
mkdir -p /srv/backup/jinya

mysqldump \
  --single-transaction \
  --routines \
  --triggers \
  --default-character-set=utf8mb4 \
  -u jinya -p jinya \
  > /srv/backup/jinya/db-$(date +%Y%m%d-%H%M%S).sql
```

---

## 14.2 备份上传文件

```bash
tar -czf \
  /srv/backup/jinya/uploads-$(date +%Y%m%d-%H%M%S).tar.gz \
  public/uploads
```

---

## 14.3 检查工作区

```bash
git status
```

特别注意：

```text
application/extra/site.php
```

该文件会被 `cms:install` 从数据库重新生成，所以生产环境可能显示为已修改。

在数据库已经备份的前提下，可以先保存一份：

```bash
cp application/extra/site.php \
  /srv/backup/jinya/site.php-$(date +%Y%m%d-%H%M%S)
```

再恢复 Git 版本：

```bash
git restore application/extra/site.php
```

不要对整个项目直接执行：

```bash
git reset --hard
```

否则可能误删服务器本地需要保留的修改。

---

## 14.4 拉取代码

当前阶段：

```bash
git fetch origin
git checkout feature/dynamic-html-views-20260920
git pull --ff-only
```

正式合并到 main 后：

```bash
git checkout main
git pull --ff-only
```

---

## 14.5 更新 Composer 依赖

如果 `composer.json` 有变化，或者首次部署：

```bash
composer install \
  --no-dev \
  --prefer-dist \
  --optimize-autoloader \
  --no-interaction
```

由于当前仓库没有版本控制 `composer.lock`，生产执行前建议先在测试环境验证。

---

## 14.6 更新前端依赖

如果 `package.json` / `package-lock.json` / FastAdmin 基础资源发生变化：

```bash
npm ci
npm run build
```

普通只修改：

```text
application/
public/assets/jinya/
public/assets/js/backend/cms/
```

的发布，不一定每次都需要重新 npm build。

---

## 14.7 同步 CMS

**先确认数据库备份已经完成。**

然后：

```bash
php think cms:install
```

---

## 14.8 清缓存

```bash
rm -rf runtime/cache/*
rm -rf runtime/temp/*
```

后台 JS/CSS 更新后，客户端浏览器建议：

```text
Ctrl + F5
```

---

## 14.9 健康检查

```bash
php think cms:health
```

---

# 15. 上线后验收

至少检查以下入口。

## 15.1 PC 前台

```text
/
 /page/label
 /page/bags
 /page/boxes
 /page/about
 /news
 /page/contact
```

重点检查：

- Header / Logo / 电话区；
- 首页 Banner；
- PC / Mobile 路由；
- 产品中心；
- 产品详情；
- 新闻列表和新闻详情；
- 公共 Footer；
- 微信二维码；
- 在线留言；
- 客户咨询后台。

---

## 15.2 移动端

用真实手机访问首页，检查：

- 顶部完整手机号；
- Banner 轮播和文字；
- 手机菜单；
- 不干胶、包装袋、彩盒页面；
- 在线留言；
- 微信咨询二维码；
- 浮动栏；
- 不应因为浏览器窗口宽度变化自动切换 PC/Mobile URL。

---

## 15.3 后台

后台地址：

```text
https://YOUR_DOMAIN/admin1.php
```

重点验收：

- CMS 页面管理；
- Banner 管理；
- 产品分类；
- 产品管理；
- 新闻管理；
- 客户咨询；
- 公共布局配置；
- 图片上传；
- 微信二维码上传。

---

# 16. PHP 上传限制

后台需要上传 Banner、产品图、二维码等文件。

建议生产 PHP 配置至少：

```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 256M
max_execution_time = 120
```

修改后重启 PHP-FPM：

```bash
sudo systemctl restart php7.4-fpm
```

实际服务名按服务器版本调整。

同时 Nginx：

```nginx
client_max_body_size 50m;
```

---

# 17. 日志位置

ThinkPHP 日志：

```text
runtime/log/
```

Nginx 常见日志：

```text
/var/log/nginx/access.log
/var/log/nginx/error.log
```

PHP-FPM 日志位置取决于系统发行版和 PHP-FPM 配置。

出现 500 时优先检查：

```bash
tail -f runtime/log/*/*.log
tail -f /var/log/nginx/error.log
```

---

# 18. 常见故障

## 18.1 页面 404 / RouteNotFoundException

检查：

1. Nginx DocumentRoot 是否指向 `public/`；
2. Nginx rewrite 是否正确；
3. PHP PATH_INFO 是否传递；
4. 是否把仓库根目录错误配置成站点目录。

---

## 18.2 后台样式完全异常

常见原因：

```text
public/assets/libs/
```

不完整。

重新执行：

```bash
npm ci
npm run build
```

然后浏览器：

```text
Ctrl + F5
```

---

## 18.3 图片大量 404

检查：

```text
public/uploads/
public/assets/jinya/img/
```

尤其是 `public/uploads/` 不由 Git 管理。

---

## 18.4 cms:install 报数据库连接错误

检查：

```bash
cat .env
php -r "new PDO('mysql:host=127.0.0.1;dbname=jinya','USER','PASSWORD'); echo 'OK';"
```

同时确认：

```text
database.prefix = fa_
```

与现有表前缀一致。

---

## 18.5 cms:install 提示 FastAdmin 基础表不存在

提示：

```text
FastAdmin 基础数据表不存在，请先完成 FastAdmin 安装。
```

说明数据库中没有：

```text
fa_auth_rule
```

此时应：

- 恢复完整 FastAdmin 数据库备份；或
- 在真正空库上执行一次 FastAdmin 安装。

不要只导入 CMS 表。

---

## 18.6 后台修改配置后前台没有更新

依次执行：

```bash
php think cms:install
rm -rf runtime/cache/*
rm -rf runtime/temp/*
```

然后浏览器强制刷新。

注意：不要为了普通 CSS/HTML 修改反复执行 `cms:install`；只有数据库结构、默认 CMS 数据、页面 Schema 等发生变化时才需要执行。

---

## 18.7 application/extra/site.php 与 Git 冲突

该文件会根据数据库中的 `fa_config` 自动生成。

安全更新方式：

```bash
# 1. 先备份数据库
# 2. 备份当前文件
cp application/extra/site.php /tmp/site.php.backup

# 3. 恢复仓库版本后拉代码
git restore application/extra/site.php
git pull --ff-only

# 4. 重新从数据库生成
php think cms:install
```

数据库才是生产站点配置的真实数据来源。

---

# 19. 数据备份策略

最低建议：

| 数据 | 建议 |
|---|---|
| MySQL | 每日备份 |
| `public/uploads/` | 每日增量 / 定期全量 |
| Git 代码 | GitHub |
| `.env` | 加密离线备份 |
| Nginx 配置 | 纳入服务器配置备份 |
| SSL 证书 | 由证书系统管理 |

至少保留：

```text
7 天每日备份
4 周每周备份
3 个月月度备份
```

---

# 20. 回滚流程

如果发布后出现严重问题：

## 20.1 回滚代码

查看：

```bash
git log --oneline -20
```

切回上一稳定提交：

```bash
git checkout STABLE_COMMIT
```

或者生产使用固定发布分支/tag。

---

## 20.2 回滚数据库

**如果本次执行过 `php think cms:install`，仅回滚代码可能不够。**

恢复发布前数据库：

```bash
mysql -u jinya -p jinya < /srv/backup/jinya/db-YYYYMMDD-HHMMSS.sql
```

再清缓存：

```bash
rm -rf runtime/cache/*
rm -rf runtime/temp/*
```

---

## 20.3 回滚上传资源

```bash
rm -rf public/uploads
tar -xzf /srv/backup/jinya/uploads-YYYYMMDD-HHMMSS.tar.gz
```

---

# 21. 推荐生产发布脚本顺序

人工执行时建议严格使用：

```bash
cd /srv/www/jinya-website

# 1. 检查当前代码
git status
git rev-parse --short HEAD

# 2. 备份 DB / uploads
# 必须完成后才能继续

# 3. 拉取代码
git fetch origin
git checkout feature/dynamic-html-views-20260920
git pull --ff-only

# 4. 如依赖变化则更新依赖
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 如 package-lock/package.json 有变化
npm ci
npm run build

# 5. CMS 数据同步
php think cms:install

# 6. 清缓存
rm -rf runtime/cache/*
rm -rf runtime/temp/*

# 7. 健康检查
php think cms:health

# 8. 人工验收首页、移动端、后台
```

如果 `cms:health` 返回错误，不应继续把本次发布视为成功。

---

# 22. 推荐后续改进

当前部署已经可以稳定使用，但为了进一步提高生产发布可控性，建议后续逐步完成：

1. 将经过验证的 `composer.lock` 纳入 Git；
2. 使用 GitHub Actions 构建完整发布制品；
3. 将 `public/uploads/` 改成独立持久卷或对象存储；
4. 增加一键部署脚本；
5. 增加 maintenance/健康探针；
6. 以 Git tag 标记生产版本；
7. 数据库迁移与“默认内容同步”进一步拆分，降低 `cms:install` 在生产执行时的变更面；
8. 将服务器 Nginx / PHP-FPM 配置纳入基础设施版本管理。

---

# 23. 最简部署检查表

发布前：

- [ ] 数据库已备份
- [ ] `public/uploads/` 已备份
- [ ] Git 工作区已检查
- [ ] 当前 Commit 已记录
- [ ] 测试环境已通过

部署：

- [ ] `git pull --ff-only`
- [ ] Composer 依赖正常
- [ ] FastAdmin assets 正常
- [ ] `php think cms:install`
- [ ] 清理 `runtime/cache`、`runtime/temp`
- [ ] `php think cms:health`

上线验收：

- [ ] PC 首页
- [ ] 手机首页
- [ ] Header / Footer
- [ ] Banner
- [ ] 产品
- [ ] 新闻
- [ ] 联系我们
- [ ] 在线留言
- [ ] 微信二维码
- [ ] 后台 `/admin1.php`
- [ ] 图片上传
- [ ] 无明显 404 / 500
