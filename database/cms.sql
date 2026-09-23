-- FastAdmin 企业 CMS 安装脚本
-- 适用：FastAdmin 1.6.5 / ThinkPHP 5.0 / MySQL 5.7+
-- 默认表前缀 fa_。如果项目使用其他前缀，请在导入前全文替换 `fa_`。

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- v29: URL重定向与内容版本功能已彻底移除。
DELETE FROM `fa_auth_rule` WHERE `name`='cms/url_redirect' OR `name` LIKE 'cms/url_redirect/%' OR `name`='cms/content_revision' OR `name` LIKE 'cms/content_revision/%';
DROP TABLE IF EXISTS `fa_cms_url_redirect`;
DROP TABLE IF EXISTS `fa_cms_content_revision`;


CREATE TABLE IF NOT EXISTS `fa_cms_product_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父分类',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分类名称',
  `short_name` varchar(100) NOT NULL DEFAULT '' COMMENT '简称',
  `slug` varchar(150) NOT NULL DEFAULT '' COMMENT 'URL标识',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '分类图片',
  `description` text COMMENT '分类说明',
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '',
  `seo_description` varchar(500) NOT NULL DEFAULT '',
  `weigh` int(10) NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_parent_status_weigh` (`parent_id`,`status`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS产品分类';

CREATE TABLE IF NOT EXISTS `fa_cms_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品分类',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '产品名称',
  `subtitle` varchar(255) NOT NULL DEFAULT '' COMMENT '副标题',
  `product_code` varchar(100) NOT NULL DEFAULT '' COMMENT '产品编号',
  `slug` varchar(180) NOT NULL DEFAULT '' COMMENT 'URL标识',
  `cover_image` varchar(255) NOT NULL DEFAULT '',
  `mobile_cover_image` varchar(255) NOT NULL DEFAULT '' COMMENT '封面',
  `summary` varchar(1000) NOT NULL DEFAULT '' COMMENT '摘要',
  `features` text COMMENT '核心特点',
  `applications` text COMMENT '适用范围',
  `construction` mediumtext COMMENT '施工说明',
  `precautions` mediumtext COMMENT '注意事项',
  `content` mediumtext COMMENT '详情',
  `tags` varchar(500) NOT NULL DEFAULT '' COMMENT '标签',
  `download_files` text COMMENT '资料附件JSON',
  `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `weigh` int(10) NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `publish_time` bigint(16) DEFAULT NULL,
  `publish_admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `audit_admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `reject_reason` varchar(500) NOT NULL DEFAULT '',
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '',
  `seo_description` varchar(500) NOT NULL DEFAULT '',
  `canonical_url` varchar(255) NOT NULL DEFAULT '',
  `robots` varchar(30) NOT NULL DEFAULT 'index,follow',
  `source_key` varchar(190) NOT NULL DEFAULT '',
  `edited_by_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_category_status` (`category_id`,`status`),
  KEY `idx_recommend_status_weigh` (`is_recommend`,`status`,`weigh`),
  KEY `idx_publish_time` (`publish_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS产品';



CREATE TABLE IF NOT EXISTS `fa_cms_product_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL DEFAULT '',
  `mobile_image` varchar(255) NOT NULL DEFAULT '',
  `alt` varchar(255) NOT NULL DEFAULT '',
  `image_type` varchar(30) NOT NULL DEFAULT 'gallery' COMMENT 'gallery/color/scene/report',
  `is_cover` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `weigh` int(10) NOT NULL DEFAULT '0',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_weigh` (`product_id`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS产品图片';

CREATE TABLE IF NOT EXISTS `fa_cms_product_parameter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parameter_group` varchar(100) NOT NULL DEFAULT '基本参数',
  `parameter_name` varchar(150) NOT NULL DEFAULT '',
  `parameter_value` varchar(500) NOT NULL DEFAULT '',
  `unit` varchar(50) NOT NULL DEFAULT '',
  `is_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `weigh` int(10) NOT NULL DEFAULT '0',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_weigh` (`product_id`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS产品参数';

CREATE TABLE IF NOT EXISTS `fa_cms_article_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `slug` varchar(150) NOT NULL DEFAULT '',
  `description` text,
  `weigh` int(10) NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_parent_status_weigh` (`parent_id`,`status`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS新闻分类';

CREATE TABLE IF NOT EXISTS `fa_cms_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL DEFAULT '',
  `slug` varchar(180) NOT NULL DEFAULT '',
  `cover_image` varchar(255) NOT NULL DEFAULT '',
  `mobile_cover_image` varchar(255) NOT NULL DEFAULT '',
  `summary` varchar(1000) NOT NULL DEFAULT '',
  `content` mediumtext,
  `author` varchar(100) NOT NULL DEFAULT '',
  `source` varchar(200) NOT NULL DEFAULT '',
  `tags` varchar(500) NOT NULL DEFAULT '',
  `is_top` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `weigh` int(10) NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `publish_time` bigint(16) DEFAULT NULL,
  `publish_admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `audit_admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `reject_reason` varchar(500) NOT NULL DEFAULT '',
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '',
  `seo_description` varchar(500) NOT NULL DEFAULT '',
  `canonical_url` varchar(255) NOT NULL DEFAULT '',
  `robots` varchar(30) NOT NULL DEFAULT 'index,follow',
  `source_key` varchar(190) NOT NULL DEFAULT '',
  `edited_by_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_category_status` (`category_id`,`status`),
  KEY `idx_top_publish` (`is_top`,`publish_time`),
  KEY `idx_publish_time` (`publish_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS新闻';

CREATE TABLE IF NOT EXISTS `fa_cms_page` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '',
  `slug` varchar(180) NOT NULL DEFAULT '',
  `template` varchar(100) NOT NULL DEFAULT 'default',
  `page_type` varchar(30) NOT NULL DEFAULT 'general',
  `cover_image` varchar(255) NOT NULL DEFAULT '',
  `mobile_cover_image` varchar(255) NOT NULL DEFAULT '',
  `summary` varchar(1000) NOT NULL DEFAULT '',
  `content` mediumtext,
  `mobile_content` mediumtext,
  `weigh` int(10) NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `publish_time` bigint(16) DEFAULT NULL,
  `publish_admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `audit_admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `reject_reason` varchar(500) NOT NULL DEFAULT '',
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '',
  `seo_description` varchar(500) NOT NULL DEFAULT '',
  `canonical_url` varchar(255) NOT NULL DEFAULT '',
  `robots` varchar(30) NOT NULL DEFAULT 'index,follow',
  `source_key` varchar(190) NOT NULL DEFAULT '',
  `edited_by_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_status_publish` (`status`,`publish_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS单页';

CREATE TABLE IF NOT EXISTS `fa_cms_case` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '',
  `slug` varchar(180) NOT NULL DEFAULT '',
  `case_type` varchar(100) NOT NULL DEFAULT '',
  `region` varchar(150) NOT NULL DEFAULT '',
  `customer_name` varchar(200) NOT NULL DEFAULT '',
  `construction_area` varchar(100) NOT NULL DEFAULT '',
  `started_at` bigint(16) DEFAULT NULL,
  `completed_at` bigint(16) DEFAULT NULL,
  `cover_image` varchar(255) NOT NULL DEFAULT '',
  `mobile_cover_image` varchar(255) NOT NULL DEFAULT '',
  `gallery` text COMMENT '相册JSON或逗号分隔路径',
  `summary` varchar(1000) NOT NULL DEFAULT '',
  `content` mediumtext,
  `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `weigh` int(10) NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `publish_time` bigint(16) DEFAULT NULL,
  `publish_admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `audit_admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `reject_reason` varchar(500) NOT NULL DEFAULT '',
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '',
  `seo_description` varchar(500) NOT NULL DEFAULT '',
  `canonical_url` varchar(255) NOT NULL DEFAULT '',
  `robots` varchar(30) NOT NULL DEFAULT 'index,follow',
  `source_key` varchar(190) NOT NULL DEFAULT '',
  `edited_by_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_type_status` (`case_type`,`status`),
  KEY `idx_recommend_status` (`is_recommend`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS工程案例';


CREATE TABLE IF NOT EXISTS `fa_cms_navigation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `slug` varchar(150) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `link_type` varchar(30) NOT NULL DEFAULT 'url',
  `link_value` varchar(255) NOT NULL DEFAULT '',
  `target` varchar(30) NOT NULL DEFAULT '_self',
  `icon` varchar(100) NOT NULL DEFAULT '',
  `position` varchar(30) NOT NULL DEFAULT 'header' COMMENT 'header/footer',
  `pc_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `mobile_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `weigh` int(10) NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_position_parent_weigh` (`position`,`parent_id`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS导航';

CREATE TABLE IF NOT EXISTS `fa_cms_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_key` varchar(100) NOT NULL DEFAULT 'home',
  `position` varchar(100) NOT NULL DEFAULT 'hero',
  `title` varchar(150) NOT NULL DEFAULT '',
  `mobile_title` varchar(150) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `mobile_subtitle` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(1000) NOT NULL DEFAULT '',
  `mobile_description` varchar(1000) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `mobile_image` varchar(255) NOT NULL DEFAULT '',
  `media_type` varchar(20) NOT NULL DEFAULT 'image' COMMENT 'image/video',
  `mobile_media_type` varchar(20) NOT NULL DEFAULT '',
  `video_url` varchar(500) NOT NULL DEFAULT '',
  `mobile_video_url` varchar(500) NOT NULL DEFAULT '',
  `overlay_image` varchar(255) NOT NULL DEFAULT '',
  `link_url` varchar(255) NOT NULL DEFAULT '',
  `mobile_link_url` varchar(255) NOT NULL DEFAULT '',
  `button_text` varchar(100) NOT NULL DEFAULT '',
  `highlights_json` text COMMENT 'Banner 卖点标签 JSON',
  `start_time` bigint(16) DEFAULT NULL,
  `end_time` bigint(16) DEFAULT NULL,
  `pc_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `mobile_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `source_key` varchar(190) NOT NULL DEFAULT '',
  `edited_by_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `weigh` int(10) NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_page_position_status` (`page_key`,`position`,`status`,`weigh`),
  KEY `idx_time` (`start_time`,`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS轮播图';

CREATE TABLE IF NOT EXISTS `fa_cms_home_section` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section_key` varchar(100) NOT NULL DEFAULT '',
  `section_name` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(150) NOT NULL DEFAULT '',
  `mobile_title` varchar(150) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `mobile_subtitle` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(1000) NOT NULL DEFAULT '',
  `mobile_description` varchar(1000) NOT NULL DEFAULT '',
  `content` text,
  `image` varchar(255) NOT NULL DEFAULT '',
  `background_image` varchar(255) NOT NULL DEFAULT '',
  `mobile_background_image` varchar(255) NOT NULL DEFAULT '',
  `more_text` varchar(100) NOT NULL DEFAULT '',
  `more_url` varchar(255) NOT NULL DEFAULT '',
  `pc_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `mobile_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `pc_display_count` int unsigned NOT NULL DEFAULT '0',
  `mobile_display_count` int unsigned NOT NULL DEFAULT '0',
  `config_json` text COMMENT '模块配置JSON',
  `source_key` varchar(190) NOT NULL DEFAULT '',
  `edited_by_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `weigh` int(10) NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_section_key` (`section_key`),
  KEY `idx_status_weigh` (`status`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS首页模块';

CREATE TABLE IF NOT EXISTS `fa_cms_layout_component` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `component_key` varchar(100) NOT NULL DEFAULT '',
  `component_name` varchar(150) NOT NULL DEFAULT '',
  `component_type` varchar(30) NOT NULL DEFAULT '' COMMENT 'header/footer/floating_service/mobile_toolbar',
  `device` varchar(20) NOT NULL DEFAULT 'all' COMMENT 'pc/mobile/all',
  `title` varchar(150) NOT NULL DEFAULT '',
  `content` text,
  `image` varchar(255) NOT NULL DEFAULT '',
  `link_text` varchar(100) NOT NULL DEFAULT '',
  `link_url` varchar(255) NOT NULL DEFAULT '',
  `pc_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `mobile_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `config_json` longtext COMMENT '结构化布局配置JSON',
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_component_key` (`component_key`),
  KEY `idx_type_device_status` (`component_type`,`device`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS公共布局组件';

CREATE TABLE IF NOT EXISTS `fa_cms_page_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_key` varchar(100) NOT NULL DEFAULT '',
  `page_name` varchar(150) NOT NULL DEFAULT '',
  `page_type` varchar(50) NOT NULL DEFAULT '',
  `route_pattern` varchar(255) NOT NULL DEFAULT '',
  `pc_header_key` varchar(100) NOT NULL DEFAULT 'layout.header.pc',
  `pc_footer_key` varchar(100) NOT NULL DEFAULT 'layout.footer.pc',
  `mobile_header_key` varchar(100) NOT NULL DEFAULT 'layout.header.mobile',
  `mobile_footer_key` varchar(100) NOT NULL DEFAULT 'layout.footer.mobile',
  `config_json` longtext COMMENT '页面级配置JSON',
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_key` (`page_key`),
  KEY `idx_page_type_status` (`page_type`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS固定页面配置';

CREATE TABLE IF NOT EXISTS `fa_cms_page_block` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_key` varchar(100) NOT NULL DEFAULT '',
  `block_key` varchar(100) NOT NULL DEFAULT '',
  `block_name` varchar(150) NOT NULL DEFAULT '',
  `block_type` varchar(50) NOT NULL DEFAULT '',
  `source_type` varchar(50) NOT NULL DEFAULT 'page',
  `config_json` longtext COMMENT '功能块结构化配置JSON',
  `pc_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `mobile_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `weigh` int(10) NOT NULL DEFAULT '0',
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_block` (`page_key`,`block_key`),
  KEY `idx_page_weigh_status` (`page_key`,`weigh`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS固定页面功能块';

CREATE TABLE IF NOT EXISTS `fa_cms_page_block_reference` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_block_id` int(10) unsigned NOT NULL DEFAULT '0',
  `content_type` varchar(50) NOT NULL DEFAULT '',
  `content_id` int(10) unsigned NOT NULL DEFAULT '0',
  `weigh` int(10) NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_block_reference` (`page_block_id`,`content_type`,`content_id`),
  KEY `idx_content_reference` (`content_type`,`content_id`),
  KEY `idx_block_weigh` (`page_block_id`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS页面功能块业务内容引用';

CREATE TABLE IF NOT EXISTS `fa_cms_home_section_reference` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `section_key` varchar(100) NOT NULL DEFAULT '',
  `content_type` varchar(30) NOT NULL DEFAULT '',
  `content_id` int unsigned NOT NULL DEFAULT 0,
  `pc_image` varchar(255) NOT NULL DEFAULT '' COMMENT '首页 PC 专用展示图',
  `mobile_image` varchar(255) NOT NULL DEFAULT '' COMMENT '首页移动端专用展示图',
  `terminal` varchar(20) NOT NULL DEFAULT 'all',
  `weigh` int NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint DEFAULT NULL,
  `updatetime` bigint DEFAULT NULL,
  `deletetime` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_section_content_terminal` (`section_key`,`content_type`,`content_id`,`terminal`),
  KEY `idx_section_terminal_weigh` (`section_key`,`terminal`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS首页终端内容引用';



CREATE TABLE IF NOT EXISTS `fa_cms_product_section` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL DEFAULT 0,
  `section_type` varchar(30) NOT NULL DEFAULT '',
  `title` varchar(150) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `content` mediumtext,
  `image` varchar(255) NOT NULL DEFAULT '',
  `mobile_image` varchar(255) NOT NULL DEFAULT '',
  `source_key` varchar(190) NOT NULL DEFAULT '',
  `edited_by_admin` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `pc_visible` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `mobile_visible` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `weigh` int NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint DEFAULT NULL,
  `updatetime` bigint DEFAULT NULL,
  `deletetime` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_source` (`product_id`,`source_key`),
  KEY `idx_product_weigh` (`product_id`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS产品结构化区块';

CREATE TABLE IF NOT EXISTS `fa_cms_page_content_block` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int unsigned NOT NULL DEFAULT 0,
  `block_key` varchar(100) NOT NULL DEFAULT '',
  `block_type` varchar(30) NOT NULL DEFAULT 'text',
  `title` varchar(150) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `content` mediumtext,
  `image` varchar(255) NOT NULL DEFAULT '',
  `mobile_image` varchar(255) NOT NULL DEFAULT '',
  `link_text` varchar(100) NOT NULL DEFAULT '',
  `link_url` varchar(255) NOT NULL DEFAULT '',
  `extra_json` longtext,
  `source_key` varchar(190) NOT NULL DEFAULT '',
  `edited_by_admin` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `pc_visible` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `mobile_visible` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `weigh` int NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'normal',
  `createtime` bigint DEFAULT NULL,
  `updatetime` bigint DEFAULT NULL,
  `deletetime` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_block_source` (`page_id`,`source_key`),
  KEY `idx_page_weigh` (`page_id`,`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS单页结构化区块';


CREATE TABLE IF NOT EXISTS `fa_cms_inquiry` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `mobile` varchar(30) NOT NULL DEFAULT '',
  `company` varchar(200) NOT NULL DEFAULT '',
  `province` varchar(100) NOT NULL DEFAULT '',
  `city` varchar(100) NOT NULL DEFAULT '',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `content` varchar(2000) NOT NULL DEFAULT '',
  `source_url` varchar(500) NOT NULL DEFAULT '',
  `source_title` varchar(255) NOT NULL DEFAULT '',
  `utm_source` varchar(100) NOT NULL DEFAULT '',
  `utm_medium` varchar(100) NOT NULL DEFAULT '',
  `utm_campaign` varchar(100) NOT NULL DEFAULT '',
  `assigned_admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `department_id` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'new',
  `next_follow_time` bigint(16) DEFAULT NULL,
  `last_follow_time` bigint(16) DEFAULT NULL,
  `ip` varchar(50) NOT NULL DEFAULT '',
  `user_agent` varchar(500) NOT NULL DEFAULT '',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_assigned` (`status`,`assigned_admin_id`),
  KEY `idx_next_follow` (`next_follow_time`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_createtime` (`createtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS客户咨询';

CREATE TABLE IF NOT EXISTS `fa_cms_inquiry_followup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inquiry_id` int(10) unsigned NOT NULL DEFAULT '0',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `follow_type` varchar(30) NOT NULL DEFAULT 'phone',
  `content` varchar(2000) NOT NULL DEFAULT '',
  `next_follow_time` bigint(16) DEFAULT NULL,
  `attachment` varchar(500) NOT NULL DEFAULT '',
  `createtime` bigint(16) DEFAULT NULL,
  `updatetime` bigint(16) DEFAULT NULL,
  `deletetime` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_inquiry_time` (`inquiry_id`,`createtime`),
  KEY `idx_admin` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS咨询跟进';




-- 企业网站配置。导入后可在“内容运营 → 公共布局”中的页头、页尾等聚合编辑入口修改。
INSERT IGNORE INTO `fa_config` (`name`,`group`,`title`,`tip`,`type`,`visible`,`value`,`content`,`rule`,`extend`,`setting`) VALUES
('cms_company','basic','公司全称','显示在企业站页头和页脚','string','','安徽科创美涂料科技股份有限公司','','required','',''),
('cms_slogan','basic','网站口号','显示在首页和页脚','string','','专业涂料产品与工程解决方案','','','',''),
('cms_logo','basic','网站 Logo','建议上传透明背景图片','image','','','','','',''),
('cms_wechat_qr','basic','微信公众号二维码','PC页脚社交关注','image','','','','','',''),
('cms_service_wecom_url','basic','企业微信在线客服链接','右侧浮动客服：在线咨询','string','','','','','',''),
('cms_service_wechat_qr','basic','客服企业微信二维码','右侧浮动客服：微信咨询','image','','','','','',''),
('cms_service_wechat_name','basic','客服名称','右侧浮动客服微信卡片','string','','金亚包装业务客服','','','',''),
('cms_service_wechat_tip','basic','微信咨询提示语','右侧浮动客服微信卡片','string','','扫码添加客服，获取包装解决方案','','','',''),
('cms_service_hours','basic','客服工作时间','右侧浮动客服展示','string','','8:30-18:00','','','',''),
('cms_douyin_qr','basic','抖音二维码','PC首页及页脚','image','','','','','',''),
('cms_kuaishou_qr','basic','快手二维码','PC页脚','image','','','','','',''),
('cms_xiaohongshu_qr','basic','小红书二维码','PC页脚','image','','','','','',''),
('cms_video_qr','basic','视频号二维码','PC首页及页脚','image','','','','','',''),
('cms_bilibili_qr','basic','B站二维码','PC页脚','image','','','','','',''),
('cms_phone','basic','CMS 服务电话','显示在企业站顶部和页脚','string','','400-006-8683','','','',''),
('cms_email','basic','CMS 联系邮箱','显示在联系区域','string','','772422624@qq.com','','email','',''),
('cms_address','basic','CMS 企业地址','显示在企业站页脚','string','','安徽省合肥市肥西经济开发区万佛山路18号','','','','');

-- 默认导航：仅保留当前站点 7 个正式一级入口。重复导入不会覆盖运营数据。
INSERT IGNORE INTO `fa_cms_navigation` (`parent_id`,`title`,`slug`,`url`,`link_type`,`link_value`,`target`,`position`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES
(0,'首页','','/','url','/','_self','header',1,1,1000,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(0,'不干胶/卷标','label','/page/label','url','/page/label','_self','header',1,1,990,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(0,'包装袋无版印刷','bags','/page/bags','url','/page/bags','_self','header',1,1,980,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(0,'彩盒','boxes','/page/boxes','url','/page/boxes','_self','header',1,1,970,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(0,'走进金亚','about','/page/about','url','/page/about','_self','header',1,1,960,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(0,'新闻动态','news','/news','url','/news','_self','header',1,1,950,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(0,'联系我们','contact','/page/contact','url','/page/contact','_self','header',1,1,940,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 默认单页：只创建当前保留的企业页面。不干胶/卷标、包装袋无版印刷、彩盒等结构化页面由 Installer 补齐。
INSERT IGNORE INTO `fa_cms_page`
(`title`,`slug`,`template`,`cover_image`,`summary`,`content`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`createtime`,`updatetime`)
VALUES
('走进金亚','about','about','','金亚包装企业介绍','',90,'published',UNIX_TIMESTAMP(),0,0,'','走进金亚','','金亚包装企业介绍','','index,follow',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('联系我们','contact','contact','','企业联系方式','',80,'published',UNIX_TIMESTAMP(),0,0,'','联系我们','','金亚包装联系方式','','index,follow',UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

INSERT IGNORE INTO `fa_cms_home_section` (`section_key`,`section_name`,`title`,`subtitle`,`background_image`,`weigh`,`status`,`createtime`,`updatetime`)
VALUES
('about','公司简介','关于科创美','专注涂料研发、生产与应用服务','',800,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('products','推荐产品','产品中心','专业涂料产品与施工体系','',700,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('service','服务能力','涂料生产施工一体化服务','从产品选型、方案设计到施工交付全程协同','/uploads/cms-clone/pc/images/td_bg.jpg',600,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('workshop','生产车间','生产车间','一人一份责任，全员一份口碑 严谨务实，高效协作','',550,'hidden',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('cases','工程案例','工程案例','用真实项目验证产品与服务','',500,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('advantages','企业优势','我们的优势','标准化管理、专业交付和持续售后服务','',400,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('news','新闻动态','新闻动态','了解企业与行业最新信息','',200,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('company','企业介绍','走进企业','了解企业实力、产品体系与服务能力','',100,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('culture','企业文化','企业文化','','',0,'hidden',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('inquiry','在线咨询','获取产品与工程方案','提交需求，我们将尽快联系您','',50,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

UPDATE `fa_cms_home_section`
SET `config_json`='{"manager_value":"1v1","manager_text":"专属项目经理 全程跟进","team_value":"专业","team_text":"标准化施工管理","warranty_value":"2+1","warranty_text":"质保与售后服务","response_value":"24","response_text":"服务响应"}'
WHERE `section_key`='service' AND (`config_json` IS NULL OR `config_json`='');

UPDATE `fa_cms_home_section`
SET `config_json`='{"title1":"工程按时交付","text1":"严格依约组织施工，持续跟踪项目进度。","title2":"标准施工团队","text2":"施工流程、材料用量和现场管理形成标准。","title3":"完整产品体系","text3":"根据墙面、地面及功能场景配置产品。","title4":"售后服务保障","text4":"建立项目回访、问题响应和售后处理机制。"}'
WHERE `section_key`='advantages' AND (`config_json` IS NULL OR `config_json`='');

-- 页面化 CMS 默认公共布局。仅在唯一键不存在时创建，不覆盖运营配置。
INSERT IGNORE INTO `fa_cms_layout_component`
(`component_key`,`component_name`,`component_type`,`device`,`config_json`,`version`,`status`,`createtime`,`updatetime`)
VALUES
('layout.header.pc','PC 默认页头','header','pc','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('layout.header.mobile','移动端默认页头','header','mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('layout.footer.pc','PC 默认页尾','footer','pc','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('layout.footer.mobile','移动端默认页尾','footer','mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('layout.floating_service.pc','PC 浮动客服','floating_service','pc','{"show_online_consult":1,"show_online_message":1,"show_wechat_consult":1,"show_back_top":1}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('layout.hot_search','内页热搜','search','all','{"placeholder":"请输入您要搜索的关键词","button_text":"搜索","items":[{"title":"不干胶/卷标","url":"/page/label"},{"title":"包装袋无版印刷","url":"/page/bags"},{"title":"新闻动态","url":"/news"},{"title":"联系我们","url":"/page/contact"}]}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP());


-- v22：补齐公共布局日常统一编辑器的三个页脚子组件。仅缺失时创建，不覆盖运营内容。
INSERT IGNORE INTO `fa_cms_layout_component`
(`component_key`,`component_name`,`component_type`,`device`,`title`,`content`,`config_json`,`version`,`status`,`createtime`,`updatetime`)
VALUES
('layout.footer.company','页脚公司信息','footer','pc','关于科创美','科创美是一家专门从事建筑墙面涂料生产与施工的高新技术企业，提供墙面、地面、屋面的综合涂料解决方案','{"copyright_suffix":"。未经许可，不得转载。"}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('layout.footer.contact','页脚联系方式','footer','pc','联系科创美','','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('layout.footer.qrcode','页脚社交关注','footer','pc','关注科创美','','{"items":[{"title":"微信公众号","icon":"/assets/kcm-pc-strict/images/foot1.png","qr_key":"wechat_qr"},{"title":"抖音","icon":"/assets/kcm-pc-strict/images/foot2.png","qr_key":"douyin_qr"},{"title":"快手","icon":"/assets/kcm-pc-strict/images/foot3.png","qr_key":"kuaishou_qr"},{"title":"小红书","icon":"/assets/kcm-pc-strict/images/foot4.png","qr_key":"xiaohongshu_qr"},{"title":"视频号","icon":"/assets/kcm-pc-strict/images/foot5.png","qr_key":"video_qr"},{"title":"B站","icon":"/assets/kcm-pc-strict/images/foot6.png","qr_key":"bilibili_qr"}]}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('layout.friend_links','友情链接','footer','all','友情链接：','','{"items":[]}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 固定页面注册：仅保留当前实际可访问页面。
INSERT IGNORE INTO `fa_cms_page_config`
(`page_key`,`page_name`,`page_type`,`route_pattern`,`pc_header_key`,`pc_footer_key`,`mobile_header_key`,`mobile_footer_key`,`config_json`,`version`,`status`,`createtime`,`updatetime`)
VALUES
('home','首页','fixed','/','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('news.index','新闻总列表','list','/news','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('news.category','新闻分类列表','dynamic_list','/news-list/{category}','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('news.detail','新闻详情','dynamic_detail','/news/{slug}','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('page.label','不干胶/卷标','fixed_page','/page/label','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('page.bags','包装袋无版印刷','fixed_page','/page/bags','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('page.boxes','彩盒','fixed_page','/page/boxes','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('page.about','走进金亚','fixed_page','/page/about','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('page.contact','联系我们','fixed_page','/page/contact','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('search','搜索结果','system','/search','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('sitemap','网站地图','system','/sitemap','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('error.404','404 页面','system','404','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- FastAdmin 菜单及权限节点
SET @now = UNIX_TIMESTAMP();
INSERT IGNORE INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`)
VALUES ('file',0,'cms','内容运营','fa fa-newspaper-o','','','','1',NULL,'','nryy','neirongyunying',@now,@now,130,'normal');
SET @cms_pid = (SELECT `id` FROM `fa_auth_rule` WHERE `name`='cms' LIMIT 1);

INSERT IGNORE INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`) VALUES
('file',@cms_pid,'cms/page_config','页面管理','fa fa-object-group','','','','1',NULL,'','ymgl','yemianguanli',@now,@now,120,'normal'),
('file',@cms_pid,'cms/layout_component','公共布局','fa fa-columns','','','','1',NULL,'','ggbj','gonggongbuju',@now,@now,115,'normal'),
('file',@cms_pid,'cms/site_config','网站配置','fa fa-cogs','','','','1',NULL,'','wzpz','wangzhanpeizhi',@now,@now,110,'normal'),
('file',@cms_pid,'cms/article','新闻管理','fa fa-file-text-o','','','','1',NULL,'','xwgl','xinwenguanli',@now,@now,90,'normal'),
('file',@cms_pid,'cms/article_category','新闻分类','fa fa-list','','','','1',NULL,'','xwfl','xinwenfenlei',@now,@now,85,'normal'),
('file',@cms_pid,'cms/page','页面内容库','fa fa-file-o','','','','1',NULL,'','ymnrk','yemianneirongku',@now,@now,80,'hidden'),
('file',@cms_pid,'cms/navigation','导航管理','fa fa-bars','','','','1',NULL,'','dhgl','daohangguanli',@now,@now,70,'normal'),
('file',@cms_pid,'cms/banner','轮播图','fa fa-picture-o','','','','1',NULL,'','lbt','lunbotu',@now,@now,65,'normal'),
('file',@cms_pid,'cms/home_section','首页模块','fa fa-th-large','','','','1',NULL,'','symk','shouyemokuai',@now,@now,60,'normal'),
('file',@cms_pid,'cms/inquiry','客户咨询','fa fa-comments-o','','','','1',NULL,'','khzx','kehuzixun',@now,@now,55,'normal');


-- v27：页面管理/公共布局成为日常统一入口。重复的高级入口继续保留路由与权限，只隐藏父菜单。
UPDATE `fa_auth_rule`
SET `status`='hidden', `updatetime`=UNIX_TIMESTAMP()
WHERE `name` IN ('cms/site_config','cms/navigation','cms/banner','cms/home_section');


-- v28：单页正文能力并入“页面管理”的日常入口。保留 cms/page 全部路由/权限，只隐藏并改名父菜单。
UPDATE `fa_auth_rule`
SET `title`='页面内容库', `status`='hidden', `updatetime`=UNIX_TIMESTAMP()
WHERE `name`='cms/page';

-- 页面化 CMS 权限：固定页面和固定功能块不提供新增、删除权限。
SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/page_config' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES
('file',@p,'cms/page_config/index','查看页面','fa fa-circle-o',0,@now,@now,60,'normal'),
('file',@p,'cms/page_config/edit','编辑页面','fa fa-circle-o',0,@now,@now,50,'normal'),
('file',@p,'cms/page_block/index','查看功能块','fa fa-circle-o',0,@now,@now,50,'normal'),
('file',@p,'cms/page_block/edit','编辑功能块','fa fa-circle-o',0,@now,@now,40,'normal'),
('file',@p,'cms/page_block/reorder','排序功能块','fa fa-circle-o',0,@now,@now,30,'normal'),
('file',@p,'cms/page_block/resetorder','恢复功能块顺序','fa fa-circle-o',0,@now,@now,20,'normal');
SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/layout_component' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES
('file',@p,'cms/layout_component/index','查看公共布局','fa fa-circle-o',0,@now,@now,50,'normal'),
('file',@p,'cms/layout_component/edit','编辑公共布局','fa fa-circle-o',0,@now,@now,40,'normal');

-- 网站配置权限
SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/site_config' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES
('file',@p,'cms/site_config/index','查看','fa fa-circle-o',0,@now,@now,50,'normal'),
('file',@p,'cms/site_config/edit','保存','fa fa-circle-o',0,@now,@now,40,'normal');

-- 为每个模块写入通用 CRUD 节点
SET @module_names = 'cms/product,cms/product_category,cms/article,cms/article_category,cms/page,cms/navigation,cms/banner,cms/home_section,cms/inquiry';

-- MySQL 无循环语法，逐模块插入，使用 INSERT IGNORE 保证可重复执行。
SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/article' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES
('file',@p,'cms/article/index','查看','fa fa-circle-o',0,@now,@now,100,'normal'),('file',@p,'cms/article/add','新增','fa fa-circle-o',0,@now,@now,90,'normal'),('file',@p,'cms/article/edit','编辑','fa fa-circle-o',0,@now,@now,80,'normal'),('file',@p,'cms/article/del','删除','fa fa-circle-o',0,@now,@now,70,'normal'),('file',@p,'cms/article/recyclebin','回收站','fa fa-circle-o',0,@now,@now,60,'normal'),('file',@p,'cms/article/restore','恢复','fa fa-circle-o',0,@now,@now,50,'normal'),('file',@p,'cms/article/submit','提交审核','fa fa-circle-o',0,@now,@now,45,'normal'),('file',@p,'cms/article/publish','发布','fa fa-circle-o',0,@now,@now,40,'normal'),('file',@p,'cms/article/reject','驳回','fa fa-circle-o',0,@now,@now,35,'normal'),('file',@p,'cms/article/offline','下架','fa fa-circle-o',0,@now,@now,30,'normal'),('file',@p,'cms/article/duplicate','复制','fa fa-circle-o',0,@now,@now,25,'normal'),('file',@p,'cms/article/preview','预览','fa fa-circle-o',0,@now,@now,20,'normal');

SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/page' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES
('file',@p,'cms/page/index','查看','fa fa-circle-o',0,@now,@now,100,'normal'),('file',@p,'cms/page/add','新增','fa fa-circle-o',0,@now,@now,90,'normal'),('file',@p,'cms/page/edit','编辑','fa fa-circle-o',0,@now,@now,80,'normal'),('file',@p,'cms/page/del','删除','fa fa-circle-o',0,@now,@now,70,'normal'),('file',@p,'cms/page/submit','提交审核','fa fa-circle-o',0,@now,@now,45,'normal'),('file',@p,'cms/page/publish','发布','fa fa-circle-o',0,@now,@now,40,'normal'),('file',@p,'cms/page/reject','驳回','fa fa-circle-o',0,@now,@now,35,'normal'),('file',@p,'cms/page/offline','下架','fa fa-circle-o',0,@now,@now,30,'normal'),('file',@p,'cms/page/duplicate','复制','fa fa-circle-o',0,@now,@now,25,'normal'),('file',@p,'cms/page/preview','预览','fa fa-circle-o',0,@now,@now,20,'normal');

-- 普通 CRUD 节点
SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/article_category' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES ('file',@p,'cms/article_category/index','查看','fa fa-circle-o',0,@now,@now,50,'normal'),('file',@p,'cms/article_category/add','新增','fa fa-circle-o',0,@now,@now,40,'normal'),('file',@p,'cms/article_category/edit','编辑','fa fa-circle-o',0,@now,@now,30,'normal'),('file',@p,'cms/article_category/del','删除','fa fa-circle-o',0,@now,@now,20,'normal'),('file',@p,'cms/article_category/multi','批量更新','fa fa-circle-o',0,@now,@now,10,'normal');
SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/navigation' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES ('file',@p,'cms/navigation/index','查看','fa fa-circle-o',0,@now,@now,50,'normal'),('file',@p,'cms/navigation/add','新增','fa fa-circle-o',0,@now,@now,40,'normal'),('file',@p,'cms/navigation/edit','编辑','fa fa-circle-o',0,@now,@now,30,'normal'),('file',@p,'cms/navigation/del','删除','fa fa-circle-o',0,@now,@now,20,'normal'),('file',@p,'cms/navigation/multi','批量更新','fa fa-circle-o',0,@now,@now,10,'normal');
SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/banner' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES ('file',@p,'cms/banner/index','查看','fa fa-circle-o',0,@now,@now,50,'normal'),('file',@p,'cms/banner/add','新增','fa fa-circle-o',0,@now,@now,40,'normal'),('file',@p,'cms/banner/edit','编辑','fa fa-circle-o',0,@now,@now,30,'normal'),('file',@p,'cms/banner/del','删除','fa fa-circle-o',0,@now,@now,20,'normal'),('file',@p,'cms/banner/multi','批量更新','fa fa-circle-o',0,@now,@now,10,'normal');
SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/home_section' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES ('file',@p,'cms/home_section/index','查看','fa fa-circle-o',0,@now,@now,50,'normal'),('file',@p,'cms/home_section/add','新增','fa fa-circle-o',0,@now,@now,40,'normal'),('file',@p,'cms/home_section/edit','编辑','fa fa-circle-o',0,@now,@now,30,'normal'),('file',@p,'cms/home_section/del','删除','fa fa-circle-o',0,@now,@now,20,'normal'),('file',@p,'cms/home_section/multi','批量更新','fa fa-circle-o',0,@now,@now,10,'normal');
SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/inquiry' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES ('file',@p,'cms/inquiry/index','查看','fa fa-circle-o',0,@now,@now,60,'normal'),('file',@p,'cms/inquiry/view_all','查看全部客户','fa fa-circle-o',0,@now,@now,58,'normal'),('file',@p,'cms/inquiry/detail','详情','fa fa-circle-o',0,@now,@now,55,'normal'),('file',@p,'cms/inquiry/assign','分配','fa fa-circle-o',0,@now,@now,50,'normal'),('file',@p,'cms/inquiry/follow','跟进','fa fa-circle-o',0,@now,@now,45,'normal'),('file',@p,'cms/inquiry/change_status','修改状态','fa fa-circle-o',0,@now,@now,40,'normal'),('file',@p,'cms/inquiry/view_mobile','查看完整手机号','fa fa-circle-o',0,@now,@now,35,'normal'),('file',@p,'cms/inquiry/export','导出','fa fa-circle-o',0,@now,@now,30,'normal'),('file',@p,'cms/inquiry/del','删除','fa fa-circle-o',0,@now,@now,20,'normal');





-- 产品体系恢复：后台五个产品模块 + 产品列表/详情。顶部 Header 仍严格保持 7 个正式入口。
INSERT IGNORE INTO `fa_cms_page_config`
(`page_key`,`page_name`,`page_type`,`route_pattern`,`pc_header_key`,`pc_footer_key`,`mobile_header_key`,`mobile_footer_key`,`config_json`,`version`,`status`,`createtime`,`updatetime`) VALUES
('product.index','产品总列表','list','/products','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('product.category','产品分类列表','dynamic_list','/products?category={category}','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('product.detail','产品详情','dynamic_detail','/product/{slug}','layout.header.pc','layout.footer.pc','layout.header.mobile','layout.footer.mobile','{}',1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

INSERT IGNORE INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`) VALUES
('file',@cms_pid,'cms/product','产品管理','fa fa-cube','','','','1',NULL,'','cpgl','chanpinguanli',@now,@now,100,'normal'),
('file',@cms_pid,'cms/product_category','产品分类','fa fa-sitemap','','','','1',NULL,'','cpfl','chanpinfenlei',@now,@now,95,'normal');
UPDATE `fa_auth_rule` SET `title`='产品管理',`status`='normal',`updatetime`=UNIX_TIMESTAMP() WHERE `name`='cms/product';
UPDATE `fa_auth_rule` SET `title`='产品分类',`status`='normal',`updatetime`=UNIX_TIMESTAMP() WHERE `name`='cms/product_category';

SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/product' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES
('file',@p,'cms/product/index','查看','fa fa-circle-o',0,@now,@now,100,'normal'),('file',@p,'cms/product/add','新增','fa fa-circle-o',0,@now,@now,90,'normal'),('file',@p,'cms/product/edit','编辑','fa fa-circle-o',0,@now,@now,80,'normal'),('file',@p,'cms/product/del','删除','fa fa-circle-o',0,@now,@now,70,'normal'),('file',@p,'cms/product/recyclebin','回收站','fa fa-circle-o',0,@now,@now,60,'normal'),('file',@p,'cms/product/restore','恢复','fa fa-circle-o',0,@now,@now,50,'normal'),('file',@p,'cms/product/submit','提交审核','fa fa-circle-o',0,@now,@now,45,'normal'),('file',@p,'cms/product/publish','发布','fa fa-circle-o',0,@now,@now,40,'normal'),('file',@p,'cms/product/reject','驳回','fa fa-circle-o',0,@now,@now,35,'normal'),('file',@p,'cms/product/offline','下架','fa fa-circle-o',0,@now,@now,30,'normal'),('file',@p,'cms/product/duplicate','复制','fa fa-circle-o',0,@now,@now,25,'normal'),('file',@p,'cms/product/preview','预览','fa fa-circle-o',0,@now,@now,20,'normal'),
('file',@p,'cms/product_image/index','产品相册','fa fa-circle-o',0,@now,@now,19,'normal'),('file',@p,'cms/product_image/add','新增产品图片','fa fa-circle-o',0,@now,@now,18,'normal'),('file',@p,'cms/product_image/edit','编辑产品图片','fa fa-circle-o',0,@now,@now,17,'normal'),('file',@p,'cms/product_image/del','删除产品图片','fa fa-circle-o',0,@now,@now,16,'normal'),('file',@p,'cms/product_image/multi','批量更新产品图片','fa fa-circle-o',0,@now,@now,15,'normal'),
('file',@p,'cms/product_parameter/index','产品技术参数','fa fa-circle-o',0,@now,@now,14,'normal'),('file',@p,'cms/product_parameter/add','新增技术参数','fa fa-circle-o',0,@now,@now,13,'normal'),('file',@p,'cms/product_parameter/edit','编辑技术参数','fa fa-circle-o',0,@now,@now,12,'normal'),('file',@p,'cms/product_parameter/del','删除技术参数','fa fa-circle-o',0,@now,@now,11,'normal'),('file',@p,'cms/product_parameter/multi','批量更新技术参数','fa fa-circle-o',0,@now,@now,10,'normal'),
('file',@p,'cms/product_section/index','产品内容区块','fa fa-circle-o',0,@now,@now,9,'normal'),('file',@p,'cms/product_section/add','新增产品区块','fa fa-circle-o',0,@now,@now,8,'normal'),('file',@p,'cms/product_section/edit','编辑产品区块','fa fa-circle-o',0,@now,@now,7,'normal'),('file',@p,'cms/product_section/del','删除产品区块','fa fa-circle-o',0,@now,@now,6,'normal'),('file',@p,'cms/product_section/multi','批量更新产品区块','fa fa-circle-o',0,@now,@now,5,'normal');

SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/product_category' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES
('file',@p,'cms/product_category/index','查看','fa fa-circle-o',0,@now,@now,50,'normal'),('file',@p,'cms/product_category/add','新增','fa fa-circle-o',0,@now,@now,40,'normal'),('file',@p,'cms/product_category/edit','编辑','fa fa-circle-o',0,@now,@now,30,'normal'),('file',@p,'cms/product_category/del','删除','fa fa-circle-o',0,@now,@now,20,'normal'),('file',@p,'cms/product_category/multi','批量更新','fa fa-circle-o',0,@now,@now,10,'normal');

SET FOREIGN_KEY_CHECKS = 1;

-- 结构化内容子模块权限（当前保留页面与首页引用）
SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/page' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES
('file',@p,'cms/page_content_block/index','单页内容区块','fa fa-circle-o',0,@now,@now,9,'normal'),('file',@p,'cms/page_content_block/add','新增单页区块','fa fa-circle-o',0,@now,@now,8,'normal'),('file',@p,'cms/page_content_block/edit','编辑单页区块','fa fa-circle-o',0,@now,@now,7,'normal'),('file',@p,'cms/page_content_block/del','删除单页区块','fa fa-circle-o',0,@now,@now,6,'normal'),('file',@p,'cms/page_content_block/multi','批量更新单页区块','fa fa-circle-o',0,@now,@now,5,'normal');
SET @p = (SELECT id FROM fa_auth_rule WHERE name='cms/home_section' LIMIT 1);
INSERT IGNORE INTO fa_auth_rule(type,pid,name,title,icon,ismenu,createtime,updatetime,weigh,status) VALUES
('file',@p,'cms/home_section_reference/index','首页内容引用','fa fa-circle-o',0,@now,@now,9,'normal'),('file',@p,'cms/home_section_reference/add','新增首页引用','fa fa-circle-o',0,@now,@now,8,'normal'),('file',@p,'cms/home_section_reference/edit','编辑首页引用','fa fa-circle-o',0,@now,@now,7,'normal'),('file',@p,'cms/home_section_reference/del','删除首页引用','fa fa-circle-o',0,@now,@now,6,'normal'),('file',@p,'cms/home_section_reference/multi','批量更新首页引用','fa fa-circle-o',0,@now,@now,5,'normal');
