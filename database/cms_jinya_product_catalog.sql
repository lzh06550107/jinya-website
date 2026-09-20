-- 金亚包装来源审核版产品目录（33 个）
-- 只写入产品分类、产品、产品图片、技术参数、产品内容区块；不修改首页功能块结构/样式/引用。
-- 数据依据：当前 FINAL 的 LabelPageDefaults / BagsPageDefaults / BoxesPageDefaults 与现有 public/uploads 素材。
-- 设计为“只补缺省数据”：重复执行不会覆盖后台已存在的同 slug 产品。
SET NAMES utf8mb4;
SET @jinya_catalog_now := UNIX_TIMESTAMP();

INSERT IGNORE INTO `fa_cms_product_category` (`parent_id`,`name`,`short_name`,`slug`,`image`,`description`,`seo_title`,`seo_keywords`,`seo_description`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (0,'不干胶 / 卷标','不干胶/卷标','labels','/uploads/cms-jinya/label/labels-hero.jpg','不干胶与卷筒标签定制，覆盖材质、胶水、工艺、卷芯、出标方向等配置。','不干胶/卷标定制 - 金亚包装','不干胶,卷标,卷筒标签,标签印刷','金亚包装提供不干胶与卷筒标签定制，支持多种材质、胶水与印刷工艺。',400,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_category` (`parent_id`,`name`,`short_name`,`slug`,`image`,`description`,`seo_title`,`seo_keywords`,`seo_description`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (0,'包装袋 / 卷膜','包装袋/卷膜','bags-roll-film','/uploads/cms-jinya/bags/bags-hero-products.jpg','包装袋与卷膜定制，覆盖多种袋型、卷膜、材质、规格及常用印刷工艺。','包装袋/卷膜定制 - 金亚包装','包装袋,卷膜,八边封袋,拉链袋,包装袋定制','金亚包装提供包装袋与卷膜定制，覆盖多袋型、卷膜及多种印刷工艺。',300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_category` (`parent_id`,`name`,`short_name`,`slug`,`image`,`description`,`seo_title`,`seo_keywords`,`seo_description`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (0,'无版印刷包装','无版印刷','no-plate-packaging','/uploads/cms-jinya/bags/bags-case-3.jpg','无版印刷支持卷膜、拉链袋及包装袋，适合灵活起订和快速交付。','无版印刷包装定制 - 金亚包装','无版印刷,无版卷膜,无版拉链袋,无版包装袋','金亚包装提供无版卷膜、无版拉链袋和无版包装袋定制。',200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_category` (`parent_id`,`name`,`short_name`,`slug`,`image`,`description`,`seo_title`,`seo_keywords`,`seo_description`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (0,'彩盒 / 纸盒','彩盒/纸盒','boxes-paper','/uploads/cms-jinya/boxes/boxes-hero.jpg','彩盒、礼盒与纸盒定制，支持结构设计、纸张选择、印刷工艺和成型交付。','彩盒/纸盒定制 - 金亚包装','彩盒,纸盒,包装盒,礼盒,纸盒定制','金亚包装提供彩盒、纸盒与礼盒定制，支持多种纸张和表面工艺。',100,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-LB-001 铜版纸标签
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='labels' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'铜版纸标签','铜版纸不干胶标签','JY-LB-001','copper-paper-label','/uploads/cms-jinya/label/label-copper.jpg','/uploads/cms-jinya/label/label-copper.jpg','页面产品展示中的铜版纸标签，可结合水胶/热熔胶及多种常用工艺按需定制。','- 铜版纸标签展示
- 支持胶水类型按使用环境选择
- 支持覆膜、模切、过油、印白、烫金等工艺','- 产品标签
- 卷筒标签定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面产品展示中的铜版纸标签，可结合水胶/热熔胶及多种常用工艺按需定制。

## 产品特点

- 铜版纸标签展示
- 支持胶水类型按使用环境选择
- 支持覆膜、模切、过油、印白、烫金等工艺

## 适用范围

- 产品标签
- 卷筒标签定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','铜版纸标签,不干胶/卷标','[]',1,0,1000,'published',@jinya_catalog_now,0,0,'','铜版纸标签_定制印刷-金亚包装','铜版纸标签,金亚包装','页面产品展示中的铜版纸标签，可结合水胶/热熔胶及多种常用工艺按需定制。','','index,follow','seed:jinya:catalog33:copper-paper-label',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='copper-paper-label' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/label/label-copper.jpg','/uploads/cms-jinya/label/label-copper.jpg','铜版纸标签','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/label/label-copper.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'材料与工艺','主材质','铜版纸','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='材料与工艺' AND `parameter_name`='主材质' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'胶水与使用','胶水类型','水胶 / 热熔胶（按使用环境选择）','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='胶水与使用' AND `parameter_name`='胶水类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'工艺与交付','常用工艺','覆膜 / 模切 / 过油 / 印白 / 烫金 / 纳米逆向','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='工艺与交付' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','出标方向','按文件正放方向确认出标方向','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='出标方向' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷芯','3cm / 4cm / 7.5cm','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷芯' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷径','24cm / 26cm / 28cm；长度不大于350米，按需求控制每卷张数','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷径' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','铜版纸不干胶标签','页面产品展示中的铜版纸标签，可结合水胶/热熔胶及多种常用工艺按需定制。','/uploads/cms-jinya/label/label-copper.jpg','/uploads/cms-jinya/label/label-copper.jpg','seed:jinya:catalog33:copper-paper-label:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 铜版纸标签展示
- 支持胶水类型按使用环境选择
- 支持覆膜、模切、过油、印白、烫金等工艺','','','seed:jinya:catalog33:copper-paper-label:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 产品标签
- 卷筒标签定制','','','seed:jinya:catalog33:copper-paper-label:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:copper-paper-label:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-LB-002 亮银逆向标签
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='labels' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'亮银逆向标签','亮银材质逆向效果标签','JY-LB-002','bright-silver-reverse-label','/uploads/cms-jinya/label/label-silver-rev.jpg','/uploads/cms-jinya/label/label-silver-rev.jpg','页面产品展示中的亮银逆向标签，适合需要金属质感和逆向视觉效果的标签定制。','- 亮银材质展示
- 可结合逆向工艺
- 支持卷标参数按设备要求确认','- 产品标签
- 包装标签','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面产品展示中的亮银逆向标签，适合需要金属质感和逆向视觉效果的标签定制。

## 产品特点

- 亮银材质展示
- 可结合逆向工艺
- 支持卷标参数按设备要求确认

## 适用范围

- 产品标签
- 包装标签

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','亮银逆向标签,不干胶/卷标','[]',0,0,990,'published',@jinya_catalog_now,0,0,'','亮银逆向标签_定制印刷-金亚包装','亮银逆向标签,金亚包装','页面产品展示中的亮银逆向标签，适合需要金属质感和逆向视觉效果的标签定制。','','index,follow','seed:jinya:catalog33:bright-silver-reverse-label',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='bright-silver-reverse-label' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/label/label-silver-rev.jpg','/uploads/cms-jinya/label/label-silver-rev.jpg','亮银逆向标签','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/label/label-silver-rev.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'材料与工艺','主材质','亮银材质','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='材料与工艺' AND `parameter_name`='主材质' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'胶水与使用','胶水类型','水胶 / 热熔胶（按使用环境选择）','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='胶水与使用' AND `parameter_name`='胶水类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'工艺与交付','常用工艺','覆膜 / 模切 / 过油 / 印白 / 烫金 / 纳米逆向','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='工艺与交付' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','出标方向','按文件正放方向确认出标方向','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='出标方向' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷芯','3cm / 4cm / 7.5cm','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷芯' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷径','24cm / 26cm / 28cm；长度不大于350米，按需求控制每卷张数','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷径' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','亮银材质逆向效果标签','页面产品展示中的亮银逆向标签，适合需要金属质感和逆向视觉效果的标签定制。','/uploads/cms-jinya/label/label-silver-rev.jpg','/uploads/cms-jinya/label/label-silver-rev.jpg','seed:jinya:catalog33:bright-silver-reverse-label:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 亮银材质展示
- 可结合逆向工艺
- 支持卷标参数按设备要求确认','','','seed:jinya:catalog33:bright-silver-reverse-label:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 产品标签
- 包装标签','','','seed:jinya:catalog33:bright-silver-reverse-label:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:bright-silver-reverse-label:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-LB-003 PVC哑膜标签
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='labels' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'PVC哑膜标签','PVC哑膜不干胶标签','JY-LB-003','pvc-matte-label','/uploads/cms-jinya/label/label-pvc.jpg','/uploads/cms-jinya/label/label-pvc.jpg','页面产品展示中的PVC哑膜产品，胶水对比区同时说明PVC等特殊材质可按使用环境选择胶水。','- PVC材质
- 哑膜视觉效果
- 胶水按使用环境选择','- 特殊材质表面标签
- 产品包装标签','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面产品展示中的PVC哑膜产品，胶水对比区同时说明PVC等特殊材质可按使用环境选择胶水。

## 产品特点

- PVC材质
- 哑膜视觉效果
- 胶水按使用环境选择

## 适用范围

- 特殊材质表面标签
- 产品包装标签

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','PVC哑膜标签,不干胶/卷标','[]',0,0,980,'published',@jinya_catalog_now,0,0,'','PVC哑膜标签_定制印刷-金亚包装','PVC哑膜标签,金亚包装','页面产品展示中的PVC哑膜产品，胶水对比区同时说明PVC等特殊材质可按使用环境选择胶水。','','index,follow','seed:jinya:catalog33:pvc-matte-label',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='pvc-matte-label' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/label/label-pvc.jpg','/uploads/cms-jinya/label/label-pvc.jpg','PVC哑膜标签','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/label/label-pvc.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'材料与工艺','主材质','PVC','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='材料与工艺' AND `parameter_name`='主材质' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'胶水与使用','胶水类型','水胶 / 热熔胶（按使用环境选择）','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='胶水与使用' AND `parameter_name`='胶水类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'工艺与交付','常用工艺','覆膜 / 模切 / 过油 / 印白 / 烫金 / 纳米逆向','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='工艺与交付' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','出标方向','按文件正放方向确认出标方向','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='出标方向' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷芯','3cm / 4cm / 7.5cm','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷芯' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷径','24cm / 26cm / 28cm；长度不大于350米，按需求控制每卷张数','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷径' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','PVC哑膜不干胶标签','页面产品展示中的PVC哑膜产品，胶水对比区同时说明PVC等特殊材质可按使用环境选择胶水。','/uploads/cms-jinya/label/label-pvc.jpg','/uploads/cms-jinya/label/label-pvc.jpg','seed:jinya:catalog33:pvc-matte-label:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- PVC材质
- 哑膜视觉效果
- 胶水按使用环境选择','','','seed:jinya:catalog33:pvc-matte-label:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 特殊材质表面标签
- 产品包装标签','','','seed:jinya:catalog33:pvc-matte-label:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:pvc-matte-label:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-LB-004 亮银标签
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='labels' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'亮银标签','亮银材质不干胶标签','JY-LB-004','bright-silver-label','/uploads/cms-jinya/label/label-bright-silver.jpg','/uploads/cms-jinya/label/label-bright-silver.jpg','页面产品展示中的亮银标签，可用于强调金属视觉质感的包装标签。','- 亮银金属视觉
- 支持多种表面工艺
- 支持卷筒参数定制','- 包装标签
- 产品标签','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面产品展示中的亮银标签，可用于强调金属视觉质感的包装标签。

## 产品特点

- 亮银金属视觉
- 支持多种表面工艺
- 支持卷筒参数定制

## 适用范围

- 包装标签
- 产品标签

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','亮银标签,不干胶/卷标','[]',0,0,970,'published',@jinya_catalog_now,0,0,'','亮银标签_定制印刷-金亚包装','亮银标签,金亚包装','页面产品展示中的亮银标签，可用于强调金属视觉质感的包装标签。','','index,follow','seed:jinya:catalog33:bright-silver-label',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='bright-silver-label' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/label/label-bright-silver.jpg','/uploads/cms-jinya/label/label-bright-silver.jpg','亮银标签','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/label/label-bright-silver.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'材料与工艺','主材质','亮银材质','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='材料与工艺' AND `parameter_name`='主材质' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'胶水与使用','胶水类型','水胶 / 热熔胶（按使用环境选择）','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='胶水与使用' AND `parameter_name`='胶水类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'工艺与交付','常用工艺','覆膜 / 模切 / 过油 / 印白 / 烫金 / 纳米逆向','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='工艺与交付' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','出标方向','按文件正放方向确认出标方向','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='出标方向' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷芯','3cm / 4cm / 7.5cm','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷芯' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷径','24cm / 26cm / 28cm；长度不大于350米，按需求控制每卷张数','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷径' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','亮银材质不干胶标签','页面产品展示中的亮银标签，可用于强调金属视觉质感的包装标签。','/uploads/cms-jinya/label/label-bright-silver.jpg','/uploads/cms-jinya/label/label-bright-silver.jpg','seed:jinya:catalog33:bright-silver-label:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 亮银金属视觉
- 支持多种表面工艺
- 支持卷筒参数定制','','','seed:jinya:catalog33:bright-silver-label:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装标签
- 产品标签','','','seed:jinya:catalog33:bright-silver-label:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:bright-silver-label:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-LB-005 PE透明标签
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='labels' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'PE透明标签','PE透明不干胶标签','JY-LB-005','pe-clear-label','/uploads/cms-jinya/label/label-pe-clear.jpg','/uploads/cms-jinya/label/label-pe-clear.jpg','页面产品展示中的PE透明标签；印白工艺说明明确支持在透明不干胶上使用白墨表现白色图案。','- 透明材质展示
- 可配合印白工艺
- 支持模切与覆膜等工艺','- 透明包装标签
- 产品瓶贴','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面产品展示中的PE透明标签；印白工艺说明明确支持在透明不干胶上使用白墨表现白色图案。

## 产品特点

- 透明材质展示
- 可配合印白工艺
- 支持模切与覆膜等工艺

## 适用范围

- 透明包装标签
- 产品瓶贴

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','PE透明标签,不干胶/卷标','[]',0,0,960,'published',@jinya_catalog_now,0,0,'','PE透明标签_定制印刷-金亚包装','PE透明标签,金亚包装','页面产品展示中的PE透明标签；印白工艺说明明确支持在透明不干胶上使用白墨表现白色图案。','','index,follow','seed:jinya:catalog33:pe-clear-label',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='pe-clear-label' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/label/label-pe-clear.jpg','/uploads/cms-jinya/label/label-pe-clear.jpg','PE透明标签','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/label/label-pe-clear.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'材料与工艺','主材质','PE透明材质','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='材料与工艺' AND `parameter_name`='主材质' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'胶水与使用','胶水类型','水胶 / 热熔胶（按使用环境选择）','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='胶水与使用' AND `parameter_name`='胶水类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'工艺与交付','常用工艺','覆膜 / 模切 / 过油 / 印白 / 烫金 / 纳米逆向','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='工艺与交付' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','出标方向','按文件正放方向确认出标方向','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='出标方向' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷芯','3cm / 4cm / 7.5cm','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷芯' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷径','24cm / 26cm / 28cm；长度不大于350米，按需求控制每卷张数','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷径' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','PE透明不干胶标签','页面产品展示中的PE透明标签；印白工艺说明明确支持在透明不干胶上使用白墨表现白色图案。','/uploads/cms-jinya/label/label-pe-clear.jpg','/uploads/cms-jinya/label/label-pe-clear.jpg','seed:jinya:catalog33:pe-clear-label:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 透明材质展示
- 可配合印白工艺
- 支持模切与覆膜等工艺','','','seed:jinya:catalog33:pe-clear-label:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 透明包装标签
- 产品瓶贴','','','seed:jinya:catalog33:pe-clear-label:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:pe-clear-label:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-LB-006 镭射标签
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='labels' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'镭射标签','镭射不干胶标签','JY-LB-006','laser-label','/uploads/cms-jinya/label/label-laser.jpg','/uploads/cms-jinya/label/label-laser.jpg','页面产品展示中的镭射标签，呈现炫彩金属视觉效果。','- 镭射视觉效果
- 多角度光影表现
- 可结合模切等后道工艺','- 包装标签
- 高视觉识别标签','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面产品展示中的镭射标签，呈现炫彩金属视觉效果。

## 产品特点

- 镭射视觉效果
- 多角度光影表现
- 可结合模切等后道工艺

## 适用范围

- 包装标签
- 高视觉识别标签

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','镭射标签,不干胶/卷标','[]',0,0,950,'published',@jinya_catalog_now,0,0,'','镭射标签_定制印刷-金亚包装','镭射标签,金亚包装','页面产品展示中的镭射标签，呈现炫彩金属视觉效果。','','index,follow','seed:jinya:catalog33:laser-label',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='laser-label' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/label/label-laser.jpg','/uploads/cms-jinya/label/label-laser.jpg','镭射标签','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/label/label-laser.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'材料与工艺','主材质','镭射材质','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='材料与工艺' AND `parameter_name`='主材质' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'胶水与使用','胶水类型','水胶 / 热熔胶（按使用环境选择）','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='胶水与使用' AND `parameter_name`='胶水类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'工艺与交付','常用工艺','覆膜 / 模切 / 过油 / 印白 / 烫金 / 纳米逆向','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='工艺与交付' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','出标方向','按文件正放方向确认出标方向','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='出标方向' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷芯','3cm / 4cm / 7.5cm','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷芯' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷径','24cm / 26cm / 28cm；长度不大于350米，按需求控制每卷张数','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷径' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','镭射不干胶标签','页面产品展示中的镭射标签，呈现炫彩金属视觉效果。','/uploads/cms-jinya/label/label-laser.jpg','/uploads/cms-jinya/label/label-laser.jpg','seed:jinya:catalog33:laser-label:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 镭射视觉效果
- 多角度光影表现
- 可结合模切等后道工艺','','','seed:jinya:catalog33:laser-label:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装标签
- 高视觉识别标签','','','seed:jinya:catalog33:laser-label:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:laser-label:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-LB-007 网红产品不干胶标签
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='labels' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'网红产品不干胶标签','高透光高白度材质标签','JY-LB-007','trendy-label','/uploads/cms-jinya/label/craft-uv.jpg','/uploads/cms-jinya/label/craft-uv.jpg','页面能力展示说明采用高透光高白度材质，强调色彩还原与多种表面工艺组合。','- 色彩还原
- 多种表面工艺
- 强调包装视觉表现','- 网红产品包装
- 品牌展示标签','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面能力展示说明采用高透光高白度材质，强调色彩还原与多种表面工艺组合。

## 产品特点

- 色彩还原
- 多种表面工艺
- 强调包装视觉表现

## 适用范围

- 网红产品包装
- 品牌展示标签

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','网红产品不干胶标签,不干胶/卷标','[]',0,0,940,'published',@jinya_catalog_now,0,0,'','网红产品不干胶标签_定制印刷-金亚包装','网红产品不干胶标签,金亚包装','页面能力展示说明采用高透光高白度材质，强调色彩还原与多种表面工艺组合。','','index,follow','seed:jinya:catalog33:trendy-label',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='trendy-label' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/label/craft-uv.jpg','/uploads/cms-jinya/label/craft-uv.jpg','网红产品不干胶标签','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/label/craft-uv.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'材料与工艺','主材质','高透光高白度材质','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='材料与工艺' AND `parameter_name`='主材质' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'胶水与使用','胶水类型','水胶 / 热熔胶（按使用环境选择）','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='胶水与使用' AND `parameter_name`='胶水类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'工艺与交付','常用工艺','覆膜 / 模切 / 过油 / 印白 / 烫金 / 纳米逆向','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='工艺与交付' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','出标方向','按文件正放方向确认出标方向','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='出标方向' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷芯','3cm / 4cm / 7.5cm','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷芯' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷径','24cm / 26cm / 28cm；长度不大于350米，按需求控制每卷张数','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷径' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','高透光高白度材质标签','页面能力展示说明采用高透光高白度材质，强调色彩还原与多种表面工艺组合。','/uploads/cms-jinya/label/craft-uv.jpg','/uploads/cms-jinya/label/craft-uv.jpg','seed:jinya:catalog33:trendy-label:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 色彩还原
- 多种表面工艺
- 强调包装视觉表现','','','seed:jinya:catalog33:trendy-label:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 网红产品包装
- 品牌展示标签','','','seed:jinya:catalog33:trendy-label:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:trendy-label:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-LB-008 特光/合成不干胶标签
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='labels' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'特光/合成不干胶标签','特种光面与合成材质标签','JY-LB-008','synthetic-special-gloss-label','/uploads/cms-jinya/label/craft-gloss.jpg','/uploads/cms-jinya/label/craft-gloss.jpg','页面能力展示说明特种光面与合成材质具有防水、防油、防撕裂特点，适用于潮湿、油腻等环境。','- 防水
- 防油
- 防撕裂
- 适配严苛使用环境','- 潮湿环境标签
- 油腻环境标签
- 户外及化工类产品标签','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面能力展示说明特种光面与合成材质具有防水、防油、防撕裂特点，适用于潮湿、油腻等环境。

## 产品特点

- 防水
- 防油
- 防撕裂
- 适配严苛使用环境

## 适用范围

- 潮湿环境标签
- 油腻环境标签
- 户外及化工类产品标签

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','特光/合成不干胶标签,不干胶/卷标','[]',0,0,930,'published',@jinya_catalog_now,0,0,'','特光/合成不干胶标签_定制印刷-金亚包装','特光/合成不干胶标签,金亚包装','页面能力展示说明特种光面与合成材质具有防水、防油、防撕裂特点，适用于潮湿、油腻等环境。','','index,follow','seed:jinya:catalog33:synthetic-special-gloss-label',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='synthetic-special-gloss-label' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/label/craft-gloss.jpg','/uploads/cms-jinya/label/craft-gloss.jpg','特光/合成不干胶标签','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/label/craft-gloss.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'材料与工艺','主材质','特种光面 / 合成材质','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='材料与工艺' AND `parameter_name`='主材质' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'胶水与使用','胶水类型','水胶 / 热熔胶（按使用环境选择）','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='胶水与使用' AND `parameter_name`='胶水类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'工艺与交付','常用工艺','覆膜 / 模切 / 过油 / 印白 / 烫金 / 纳米逆向','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='工艺与交付' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','出标方向','按文件正放方向确认出标方向','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='出标方向' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷芯','3cm / 4cm / 7.5cm','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷芯' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷径','24cm / 26cm / 28cm；长度不大于350米，按需求控制每卷张数','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷径' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','特种光面与合成材质标签','页面能力展示说明特种光面与合成材质具有防水、防油、防撕裂特点，适用于潮湿、油腻等环境。','/uploads/cms-jinya/label/craft-gloss.jpg','/uploads/cms-jinya/label/craft-gloss.jpg','seed:jinya:catalog33:synthetic-special-gloss-label:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 防水
- 防油
- 防撕裂
- 适配严苛使用环境','','','seed:jinya:catalog33:synthetic-special-gloss-label:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 潮湿环境标签
- 油腻环境标签
- 户外及化工类产品标签','','','seed:jinya:catalog33:synthetic-special-gloss-label:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:synthetic-special-gloss-label:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-LB-009 编号/可变数据不干胶标签
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='labels' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'编号/可变数据不干胶标签','流水号二维码防伪码标签','JY-LB-009','variable-data-label','/uploads/cms-jinya/label/label-copper.jpg','/uploads/cms-jinya/label/label-copper.jpg','页面能力展示明确支持流水号、二维码、防伪码等可变数据印刷，用于追溯与批次管理。','- 流水号
- 二维码
- 防伪码
- 可变数据印刷','- 医药
- 食品
- 电子
- 批次管理','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面能力展示明确支持流水号、二维码、防伪码等可变数据印刷，用于追溯与批次管理。

## 产品特点

- 流水号
- 二维码
- 防伪码
- 可变数据印刷

## 适用范围

- 医药
- 食品
- 电子
- 批次管理

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','编号/可变数据不干胶标签,不干胶/卷标','[]',0,0,920,'published',@jinya_catalog_now,0,0,'','编号/可变数据不干胶标签_定制印刷-金亚包装','编号/可变数据不干胶标签,金亚包装','页面能力展示明确支持流水号、二维码、防伪码等可变数据印刷，用于追溯与批次管理。','','index,follow','seed:jinya:catalog33:variable-data-label',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='variable-data-label' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/label/label-copper.jpg','/uploads/cms-jinya/label/label-copper.jpg','编号/可变数据不干胶标签','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/label/label-copper.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'材料与工艺','主材质','按用途选择','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='材料与工艺' AND `parameter_name`='主材质' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'胶水与使用','胶水类型','水胶 / 热熔胶（按使用环境选择）','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='胶水与使用' AND `parameter_name`='胶水类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'工艺与交付','常用工艺','覆膜 / 模切 / 过油 / 印白 / 烫金 / 纳米逆向','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='工艺与交付' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','出标方向','按文件正放方向确认出标方向','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='出标方向' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷芯','3cm / 4cm / 7.5cm','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷芯' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷径','24cm / 26cm / 28cm；长度不大于350米，按需求控制每卷张数','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷径' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','流水号二维码防伪码标签','页面能力展示明确支持流水号、二维码、防伪码等可变数据印刷，用于追溯与批次管理。','/uploads/cms-jinya/label/label-copper.jpg','/uploads/cms-jinya/label/label-copper.jpg','seed:jinya:catalog33:variable-data-label:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 流水号
- 二维码
- 防伪码
- 可变数据印刷','','','seed:jinya:catalog33:variable-data-label:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 医药
- 食品
- 电子
- 批次管理','','','seed:jinya:catalog33:variable-data-label:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:variable-data-label:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-LB-010 镭射材不干胶标签
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='labels' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'镭射材不干胶标签','炫彩渐变金属光泽标签','JY-LB-010','laser-material-label','/uploads/cms-jinya/label/craft-laser.jpg','/uploads/cms-jinya/label/craft-laser.jpg','页面能力展示中的镭射材不干胶，强调炫彩渐变金属光泽与立体光影效果。','- 炫彩渐变金属光泽
- 立体光影效果
- 高视觉冲击','- 美妆
- 酒饮
- 礼盒包装标签','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面能力展示中的镭射材不干胶，强调炫彩渐变金属光泽与立体光影效果。

## 产品特点

- 炫彩渐变金属光泽
- 立体光影效果
- 高视觉冲击

## 适用范围

- 美妆
- 酒饮
- 礼盒包装标签

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','镭射材不干胶标签,不干胶/卷标','[]',0,0,910,'published',@jinya_catalog_now,0,0,'','镭射材不干胶标签_定制印刷-金亚包装','镭射材不干胶标签,金亚包装','页面能力展示中的镭射材不干胶，强调炫彩渐变金属光泽与立体光影效果。','','index,follow','seed:jinya:catalog33:laser-material-label',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='laser-material-label' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/label/craft-laser.jpg','/uploads/cms-jinya/label/craft-laser.jpg','镭射材不干胶标签','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/label/craft-laser.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'材料与工艺','主材质','镭射材质','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='材料与工艺' AND `parameter_name`='主材质' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'胶水与使用','胶水类型','水胶 / 热熔胶（按使用环境选择）','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='胶水与使用' AND `parameter_name`='胶水类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'工艺与交付','常用工艺','覆膜 / 模切 / 过油 / 印白 / 烫金 / 纳米逆向','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='工艺与交付' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','出标方向','按文件正放方向确认出标方向','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='出标方向' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷芯','3cm / 4cm / 7.5cm','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷芯' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'卷标参数','卷径','24cm / 26cm / 28cm；长度不大于350米，按需求控制每卷张数','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='卷标参数' AND `parameter_name`='卷径' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','炫彩渐变金属光泽标签','页面能力展示中的镭射材不干胶，强调炫彩渐变金属光泽与立体光影效果。','/uploads/cms-jinya/label/craft-laser.jpg','/uploads/cms-jinya/label/craft-laser.jpg','seed:jinya:catalog33:laser-material-label:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 炫彩渐变金属光泽
- 立体光影效果
- 高视觉冲击','','','seed:jinya:catalog33:laser-material-label:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 美妆
- 酒饮
- 礼盒包装标签','','','seed:jinya:catalog33:laser-material-label:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:laser-material-label:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-001 农肥中转袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'农肥中转袋','农肥中转袋','JY-BG-001','agri-transfer-bag','/uploads/cms-jinya/bags/sample-nongfei.jpg','/uploads/cms-jinya/bags/sample-nongfei.jpg','页面包装样品展示中的农肥中转袋。','- 农肥中转袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面包装样品展示中的农肥中转袋。

## 产品特点

- 农肥中转袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','农肥中转袋,包装袋/卷膜','[]',0,0,900,'published',@jinya_catalog_now,0,0,'','农肥中转袋_定制印刷-金亚包装','农肥中转袋,金亚包装','页面包装样品展示中的农肥中转袋。','','index,follow','seed:jinya:catalog33:agri-transfer-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='agri-transfer-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-nongfei.jpg','/uploads/cms-jinya/bags/sample-nongfei.jpg','农肥中转袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-nongfei.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','农肥中转袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','农肥中转袋','页面包装样品展示中的农肥中转袋。','/uploads/cms-jinya/bags/sample-nongfei.jpg','/uploads/cms-jinya/bags/sample-nongfei.jpg','seed:jinya:catalog33:agri-transfer-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 农肥中转袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:agri-transfer-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:agri-transfer-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:agri-transfer-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-002 大米包装袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'大米包装袋','大米包装袋','JY-BG-002','rice-packaging-bag','/uploads/cms-jinya/bags/sample-dami.jpg','/uploads/cms-jinya/bags/sample-dami.jpg','页面包装样品展示中的大米包装袋。','- 大米包装袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面包装样品展示中的大米包装袋。

## 产品特点

- 大米包装袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','大米包装袋,包装袋/卷膜','[]',1,0,890,'published',@jinya_catalog_now,0,0,'','大米包装袋_定制印刷-金亚包装','大米包装袋,金亚包装','页面包装样品展示中的大米包装袋。','','index,follow','seed:jinya:catalog33:rice-packaging-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='rice-packaging-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-dami.jpg','/uploads/cms-jinya/bags/sample-dami.jpg','大米包装袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-dami.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','大米包装袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','大米包装袋','页面包装样品展示中的大米包装袋。','/uploads/cms-jinya/bags/sample-dami.jpg','/uploads/cms-jinya/bags/sample-dami.jpg','seed:jinya:catalog33:rice-packaging-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 大米包装袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:rice-packaging-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:rice-packaging-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:rice-packaging-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-003 宠物猫粮袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'宠物猫粮袋','宠物猫粮袋','JY-BG-003','pet-cat-food-bag','/uploads/cms-jinya/bags/sample-cat.jpg','/uploads/cms-jinya/bags/sample-cat.jpg','页面包装样品展示中的宠物猫粮袋。','- 宠物猫粮袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面包装样品展示中的宠物猫粮袋。

## 产品特点

- 宠物猫粮袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','宠物猫粮袋,包装袋/卷膜','[]',0,0,880,'published',@jinya_catalog_now,0,0,'','宠物猫粮袋_定制印刷-金亚包装','宠物猫粮袋,金亚包装','页面包装样品展示中的宠物猫粮袋。','','index,follow','seed:jinya:catalog33:pet-cat-food-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='pet-cat-food-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-cat.jpg','/uploads/cms-jinya/bags/sample-cat.jpg','宠物猫粮袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-cat.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','宠物猫粮袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','宠物猫粮袋','页面包装样品展示中的宠物猫粮袋。','/uploads/cms-jinya/bags/sample-cat.jpg','/uploads/cms-jinya/bags/sample-cat.jpg','seed:jinya:catalog33:pet-cat-food-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 宠物猫粮袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:pet-cat-food-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:pet-cat-food-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:pet-cat-food-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-004 硝膜八边封袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'硝膜八边封袋','硝膜八边封袋','JY-BG-004','film-eight-side-bag','/uploads/cms-jinya/bags/sample-baofeng.jpg','/uploads/cms-jinya/bags/sample-baofeng.jpg','页面包装样品展示中的“硝膜八边封袋”（保留页面原展示名称）。','- 硝膜八边封袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面包装样品展示中的“硝膜八边封袋”（保留页面原展示名称）。

## 产品特点

- 硝膜八边封袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','硝膜八边封袋,包装袋/卷膜','[]',0,0,870,'published',@jinya_catalog_now,0,0,'','硝膜八边封袋_定制印刷-金亚包装','硝膜八边封袋,金亚包装','页面包装样品展示中的“硝膜八边封袋”（保留页面原展示名称）。','','index,follow','seed:jinya:catalog33:film-eight-side-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='film-eight-side-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-baofeng.jpg','/uploads/cms-jinya/bags/sample-baofeng.jpg','硝膜八边封袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-baofeng.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','硝膜八边封袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','硝膜八边封袋','页面包装样品展示中的“硝膜八边封袋”（保留页面原展示名称）。','/uploads/cms-jinya/bags/sample-baofeng.jpg','/uploads/cms-jinya/bags/sample-baofeng.jpg','seed:jinya:catalog33:film-eight-side-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 硝膜八边封袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:film-eight-side-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:film-eight-side-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:film-eight-side-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-005 肥料包装袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'肥料包装袋','肥料包装袋','JY-BG-005','fertilizer-packaging-bag','/uploads/cms-jinya/bags/sample-feiliao.jpg','/uploads/cms-jinya/bags/sample-feiliao.jpg','页面产品中心与样品展示均出现肥料类包装。','- 肥料包装袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面产品中心与样品展示均出现肥料类包装。

## 产品特点

- 肥料包装袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','肥料包装袋,包装袋/卷膜','[]',0,0,860,'published',@jinya_catalog_now,0,0,'','肥料包装袋_定制印刷-金亚包装','肥料包装袋,金亚包装','页面产品中心与样品展示均出现肥料类包装。','','index,follow','seed:jinya:catalog33:fertilizer-packaging-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='fertilizer-packaging-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-feiliao.jpg','/uploads/cms-jinya/bags/sample-feiliao.jpg','肥料包装袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-feiliao.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','肥料包装袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','肥料包装袋','页面产品中心与样品展示均出现肥料类包装。','/uploads/cms-jinya/bags/sample-feiliao.jpg','/uploads/cms-jinya/bags/sample-feiliao.jpg','seed:jinya:catalog33:fertilizer-packaging-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 肥料包装袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:fertilizer-packaging-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:fertilizer-packaging-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:fertilizer-packaging-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-006 八边封包装袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'八边封包装袋','八边封包装袋','JY-BG-006','flat-bottom-eight-side-bag','/uploads/cms-jinya/bags/sample-babianfeng.jpg','/uploads/cms-jinya/bags/sample-babianfeng.jpg','页面包装样品展示中的八边封包装袋。','- 八边封包装袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面包装样品展示中的八边封包装袋。

## 产品特点

- 八边封包装袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','八边封包装袋,包装袋/卷膜','[]',0,0,850,'published',@jinya_catalog_now,0,0,'','八边封包装袋_定制印刷-金亚包装','八边封包装袋,金亚包装','页面包装样品展示中的八边封包装袋。','','index,follow','seed:jinya:catalog33:flat-bottom-eight-side-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='flat-bottom-eight-side-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-babianfeng.jpg','/uploads/cms-jinya/bags/sample-babianfeng.jpg','八边封包装袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-babianfeng.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','八边封包装袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','八边封包装袋','页面包装样品展示中的八边封包装袋。','/uploads/cms-jinya/bags/sample-babianfeng.jpg','/uploads/cms-jinya/bags/sample-babianfeng.jpg','seed:jinya:catalog33:flat-bottom-eight-side-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 八边封包装袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:flat-bottom-eight-side-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:flat-bottom-eight-side-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:flat-bottom-eight-side-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-007 自动包装卷膜
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'自动包装卷膜','自动包装卷膜','JY-BG-007','automatic-packaging-roll-film','/uploads/cms-jinya/bags/sample-juanmo1.jpg','/uploads/cms-jinya/bags/sample-juanmo1.jpg','页面包装样品展示中的自动卷膜。','- 自动包装卷膜样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面包装样品展示中的自动卷膜。

## 产品特点

- 自动包装卷膜样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','自动包装卷膜,包装袋/卷膜','[]',0,0,840,'published',@jinya_catalog_now,0,0,'','自动包装卷膜_定制印刷-金亚包装','自动包装卷膜,金亚包装','页面包装样品展示中的自动卷膜。','','index,follow','seed:jinya:catalog33:automatic-packaging-roll-film',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='automatic-packaging-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-juanmo1.jpg','/uploads/cms-jinya/bags/sample-juanmo1.jpg','自动包装卷膜','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-juanmo1.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','自动包装卷膜','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','自动包装卷膜','页面包装样品展示中的自动卷膜。','/uploads/cms-jinya/bags/sample-juanmo1.jpg','/uploads/cms-jinya/bags/sample-juanmo1.jpg','seed:jinya:catalog33:automatic-packaging-roll-film:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 自动包装卷膜样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:automatic-packaging-roll-film:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:automatic-packaging-roll-film:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:automatic-packaging-roll-film:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-008 包装卷膜
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'包装卷膜','包装卷膜','JY-BG-008','packaging-roll-film','/uploads/cms-jinya/bags/sample-juanmo2.jpg','/uploads/cms-jinya/bags/sample-juanmo2.jpg','页面包装样品展示中的卷膜产品。','- 包装卷膜样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面包装样品展示中的卷膜产品。

## 产品特点

- 包装卷膜样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','包装卷膜,包装袋/卷膜','[]',0,0,830,'published',@jinya_catalog_now,0,0,'','包装卷膜_定制印刷-金亚包装','包装卷膜,金亚包装','页面包装样品展示中的卷膜产品。','','index,follow','seed:jinya:catalog33:packaging-roll-film',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='packaging-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-juanmo2.jpg','/uploads/cms-jinya/bags/sample-juanmo2.jpg','包装卷膜','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-juanmo2.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','包装卷膜','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','包装卷膜','页面包装样品展示中的卷膜产品。','/uploads/cms-jinya/bags/sample-juanmo2.jpg','/uploads/cms-jinya/bags/sample-juanmo2.jpg','seed:jinya:catalog33:packaging-roll-film:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 包装卷膜样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:packaging-roll-film:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:packaging-roll-film:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:packaging-roll-film:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-009 斜嘴手提袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'斜嘴手提袋','斜嘴手提袋','JY-BG-009','handle-spout-bag','/uploads/cms-jinya/bags/sample-xiezui.jpg','/uploads/cms-jinya/bags/sample-xiezui.jpg','页面包装样品展示中的斜嘴手提袋。','- 斜嘴手提袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面包装样品展示中的斜嘴手提袋。

## 产品特点

- 斜嘴手提袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','斜嘴手提袋,包装袋/卷膜','[]',0,0,820,'published',@jinya_catalog_now,0,0,'','斜嘴手提袋_定制印刷-金亚包装','斜嘴手提袋,金亚包装','页面包装样品展示中的斜嘴手提袋。','','index,follow','seed:jinya:catalog33:handle-spout-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='handle-spout-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-xiezui.jpg','/uploads/cms-jinya/bags/sample-xiezui.jpg','斜嘴手提袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-xiezui.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','斜嘴手提袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','斜嘴手提袋','页面包装样品展示中的斜嘴手提袋。','/uploads/cms-jinya/bags/sample-xiezui.jpg','/uploads/cms-jinya/bags/sample-xiezui.jpg','seed:jinya:catalog33:handle-spout-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 斜嘴手提袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:handle-spout-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:handle-spout-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:handle-spout-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-010 铝箔站立袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'铝箔站立袋','铝箔站立袋','JY-BG-010','aluminum-foil-stand-up-bag','/uploads/cms-jinya/bags/sample-lvbo.jpg','/uploads/cms-jinya/bags/sample-lvbo.jpg','页面包装样品展示中的铝箔站立袋。','- 铝箔站立袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面包装样品展示中的铝箔站立袋。

## 产品特点

- 铝箔站立袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','铝箔站立袋,包装袋/卷膜','[]',0,0,810,'published',@jinya_catalog_now,0,0,'','铝箔站立袋_定制印刷-金亚包装','铝箔站立袋,金亚包装','页面包装样品展示中的铝箔站立袋。','','index,follow','seed:jinya:catalog33:aluminum-foil-stand-up-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='aluminum-foil-stand-up-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-lvbo.jpg','/uploads/cms-jinya/bags/sample-lvbo.jpg','铝箔站立袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-lvbo.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','铝箔站立袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','铝箔站立袋','页面包装样品展示中的铝箔站立袋。','/uploads/cms-jinya/bags/sample-lvbo.jpg','/uploads/cms-jinya/bags/sample-lvbo.jpg','seed:jinya:catalog33:aluminum-foil-stand-up-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 铝箔站立袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:aluminum-foil-stand-up-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:aluminum-foil-stand-up-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:aluminum-foil-stand-up-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-011 中转袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'中转袋','中转袋','JY-BG-011','transfer-packaging-bag','/uploads/cms-jinya/bags/sample-zhongzhuan.jpg','/uploads/cms-jinya/bags/sample-zhongzhuan.jpg','页面包装样品展示中的中转袋。','- 中转袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面包装样品展示中的中转袋。

## 产品特点

- 中转袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','中转袋,包装袋/卷膜','[]',0,0,800,'published',@jinya_catalog_now,0,0,'','中转袋_定制印刷-金亚包装','中转袋,金亚包装','页面包装样品展示中的中转袋。','','index,follow','seed:jinya:catalog33:transfer-packaging-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='transfer-packaging-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-zhongzhuan.jpg','/uploads/cms-jinya/bags/sample-zhongzhuan.jpg','中转袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-zhongzhuan.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','中转袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','中转袋','页面包装样品展示中的中转袋。','/uploads/cms-jinya/bags/sample-zhongzhuan.jpg','/uploads/cms-jinya/bags/sample-zhongzhuan.jpg','seed:jinya:catalog33:transfer-packaging-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 中转袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:transfer-packaging-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:transfer-packaging-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:transfer-packaging-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BG-012 水果拉链中转袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='bags-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'水果拉链中转袋','水果拉链中转袋','JY-BG-012','fruit-zipper-transfer-bag','/uploads/cms-jinya/bags/sample-shuiguo.jpg','/uploads/cms-jinya/bags/sample-shuiguo.jpg','页面包装样品展示中的水果拉链中转袋。','- 水果拉链中转袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','- 包装袋/卷膜定制','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面包装样品展示中的水果拉链中转袋。

## 产品特点

- 水果拉链中转袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺

## 适用范围

- 包装袋/卷膜定制

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','水果拉链中转袋,包装袋/卷膜','[]',0,0,790,'published',@jinya_catalog_now,0,0,'','水果拉链中转袋_定制印刷-金亚包装','水果拉链中转袋,金亚包装','页面包装样品展示中的水果拉链中转袋。','','index,follow','seed:jinya:catalog33:fruit-zipper-transfer-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='fruit-zipper-transfer-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/sample-shuiguo.jpg','/uploads/cms-jinya/bags/sample-shuiguo.jpg','水果拉链中转袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/sample-shuiguo.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','水果拉链中转袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','材质与规格','按需求、袋型和使用场景确认','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='材质与规格' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','印刷方式','按订单方案确认；页面同时提供专版与无版印刷方案','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='印刷方式' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','尺寸','按需定制','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','数量与交期','按规格、数量和印刷方案确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='数量与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','水果拉链中转袋','页面包装样品展示中的水果拉链中转袋。','/uploads/cms-jinya/bags/sample-shuiguo.jpg','/uploads/cms-jinya/bags/sample-shuiguo.jpg','seed:jinya:catalog33:fruit-zipper-transfer-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 水果拉链中转袋样品展示
- 支持袋型/规格/数量按需确认
- 可结合页面展示的常用印刷工艺','','','seed:jinya:catalog33:fruit-zipper-transfer-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 包装袋/卷膜定制','','','seed:jinya:catalog33:fruit-zipper-transfer-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:fruit-zipper-transfer-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-NP-001 无版卷膜
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='no-plate-packaging' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'无版卷膜','无版卷膜定制','JY-NP-001','no-plate-roll-film','/uploads/cms-jinya/bags/bags-case-1.jpg','/uploads/cms-jinya/bags/bags-case-1.jpg','页面应用案例明确展示无版卷膜：采用膜内印刷工艺，30公斤即可起订，适配立式、卧式包装设备。','- 无版印刷
- 膜内印刷工艺
- 30公斤起订
- 适配立式/卧式包装设备','- 食品
- 中药类
- 农资
- 宠物类','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面应用案例明确展示无版卷膜：采用膜内印刷工艺，30公斤即可起订，适配立式、卧式包装设备。

## 产品特点

- 无版印刷
- 膜内印刷工艺
- 30公斤起订
- 适配立式/卧式包装设备

## 适用范围

- 食品
- 中药类
- 农资
- 宠物类

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','无版卷膜,无版印刷','[]',0,0,800,'published',@jinya_catalog_now,0,0,'','无版卷膜_定制印刷-金亚包装','无版卷膜,金亚包装','页面应用案例明确展示无版卷膜：采用膜内印刷工艺，30公斤即可起订，适配立式、卧式包装设备。','','index,follow','seed:jinya:catalog33:no-plate-roll-film',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='no-plate-roll-film' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/bags-case-1.jpg','/uploads/cms-jinya/bags/bags-case-1.jpg','无版卷膜','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/bags-case-1.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'无版印刷','产品类型','无版卷膜','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='无版印刷' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'无版印刷','版费','无版费','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='无版印刷' AND `parameter_name`='版费' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'无版印刷','起订要求','30公斤起','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='无版印刷' AND `parameter_name`='起订要求' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'适用范围','页面展示适用','食品 / 中药类 / 农资 / 宠物类','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='适用范围' AND `parameter_name`='页面展示适用' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','规格与交期','按袋型、规格、数量及稿件确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='规格与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','无版卷膜定制','页面应用案例明确展示无版卷膜：采用膜内印刷工艺，30公斤即可起订，适配立式、卧式包装设备。','/uploads/cms-jinya/bags/bags-case-1.jpg','/uploads/cms-jinya/bags/bags-case-1.jpg','seed:jinya:catalog33:no-plate-roll-film:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 无版印刷
- 膜内印刷工艺
- 30公斤起订
- 适配立式/卧式包装设备','','','seed:jinya:catalog33:no-plate-roll-film:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 食品
- 中药类
- 农资
- 宠物类','','','seed:jinya:catalog33:no-plate-roll-film:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:no-plate-roll-film:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-NP-002 无版拉链袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='no-plate-packaging' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'无版拉链袋','无版拉链袋定制','JY-NP-002','no-plate-zipper-bag','/uploads/cms-jinya/bags/bags-case-2.jpg','/uploads/cms-jinya/bags/bags-case-2.jpg','页面应用案例明确展示无版拉链袋：拉链袋、站立袋款式可选，没有版费，1000个即可起订。','- 无版费
- 拉链袋/站立袋
- 数码高清印刷
- 1000个起订','- 食品
- 中药类
- 农资
- 宠物类','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面应用案例明确展示无版拉链袋：拉链袋、站立袋款式可选，没有版费，1000个即可起订。

## 产品特点

- 无版费
- 拉链袋/站立袋
- 数码高清印刷
- 1000个起订

## 适用范围

- 食品
- 中药类
- 农资
- 宠物类

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','无版拉链袋,无版印刷','[]',0,0,790,'published',@jinya_catalog_now,0,0,'','无版拉链袋_定制印刷-金亚包装','无版拉链袋,金亚包装','页面应用案例明确展示无版拉链袋：拉链袋、站立袋款式可选，没有版费，1000个即可起订。','','index,follow','seed:jinya:catalog33:no-plate-zipper-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='no-plate-zipper-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/bags-case-2.jpg','/uploads/cms-jinya/bags/bags-case-2.jpg','无版拉链袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/bags-case-2.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'无版印刷','产品类型','无版拉链袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='无版印刷' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'无版印刷','版费','无版费','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='无版印刷' AND `parameter_name`='版费' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'无版印刷','起订要求','1000个起','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='无版印刷' AND `parameter_name`='起订要求' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'适用范围','页面展示适用','食品 / 中药类 / 农资 / 宠物类','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='适用范围' AND `parameter_name`='页面展示适用' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','规格与交期','按袋型、规格、数量及稿件确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='规格与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','无版拉链袋定制','页面应用案例明确展示无版拉链袋：拉链袋、站立袋款式可选，没有版费，1000个即可起订。','/uploads/cms-jinya/bags/bags-case-2.jpg','/uploads/cms-jinya/bags/bags-case-2.jpg','seed:jinya:catalog33:no-plate-zipper-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 无版费
- 拉链袋/站立袋
- 数码高清印刷
- 1000个起订','','','seed:jinya:catalog33:no-plate-zipper-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 食品
- 中药类
- 农资
- 宠物类','','','seed:jinya:catalog33:no-plate-zipper-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:no-plate-zipper-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-NP-003 无版包装袋
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='no-plate-packaging' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'无版包装袋','无版包装袋定制','JY-NP-003','no-plate-packaging-bag','/uploads/cms-jinya/bags/bags-case-3.jpg','/uploads/cms-jinya/bags/bags-case-3.jpg','页面应用案例明确展示无版包装袋：涵盖三边封、站立袋等袋型，免收版费，1000个即可起订。','- 三边封/站立袋等袋型
- 免版费
- 不限图案色彩
- 1000个起订','- 食品
- 农资
- 农牧
- 宠物','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面应用案例明确展示无版包装袋：涵盖三边封、站立袋等袋型，免收版费，1000个即可起订。

## 产品特点

- 三边封/站立袋等袋型
- 免版费
- 不限图案色彩
- 1000个起订

## 适用范围

- 食品
- 农资
- 农牧
- 宠物

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','无版包装袋,无版印刷','[]',1,0,780,'published',@jinya_catalog_now,0,0,'','无版包装袋_定制印刷-金亚包装','无版包装袋,金亚包装','页面应用案例明确展示无版包装袋：涵盖三边封、站立袋等袋型，免收版费，1000个即可起订。','','index,follow','seed:jinya:catalog33:no-plate-packaging-bag',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='no-plate-packaging-bag' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/bags/bags-case-3.jpg','/uploads/cms-jinya/bags/bags-case-3.jpg','无版包装袋','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/bags/bags-case-3.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'无版印刷','产品类型','无版包装袋','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='无版印刷' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'无版印刷','版费','无版费','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='无版印刷' AND `parameter_name`='版费' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'无版印刷','起订要求','1000个起','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='无版印刷' AND `parameter_name`='起订要求' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷与工艺','常用工艺','哑膜 / 光膜 / UV / 镭射膜 / 烫金 / 开窗（按产品与稿件选择）','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷与工艺' AND `parameter_name`='常用工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'适用范围','页面展示适用','食品 / 农资 / 农牧 / 宠物','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='适用范围' AND `parameter_name`='页面展示适用' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','规格与交期','按袋型、规格、数量及稿件确认','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='规格与交期' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','无版包装袋定制','页面应用案例明确展示无版包装袋：涵盖三边封、站立袋等袋型，免收版费，1000个即可起订。','/uploads/cms-jinya/bags/bags-case-3.jpg','/uploads/cms-jinya/bags/bags-case-3.jpg','seed:jinya:catalog33:no-plate-packaging-bag:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 三边封/站立袋等袋型
- 免版费
- 不限图案色彩
- 1000个起订','','','seed:jinya:catalog33:no-plate-packaging-bag:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 食品
- 农资
- 农牧
- 宠物','','','seed:jinya:catalog33:no-plate-packaging-bag:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:no-plate-packaging-bag:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BX-001 饮品类包装盒
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='boxes-paper' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'饮品类包装盒','饮品类包装盒','JY-BX-001','beverage-color-box','/uploads/cms-jinya/boxes/boxes-app-1.jpg','/uploads/cms-jinya/boxes/boxes-app-1.jpg','页面“应用广泛”展示中的饮品类包装。','- 饮品类包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','- 饮品类','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面“应用广泛”展示中的饮品类包装。

## 产品特点

- 饮品类包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺

## 适用范围

- 饮品类

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','饮品类包装盒,彩盒/纸盒','[]',0,0,700,'published',@jinya_catalog_now,0,0,'','饮品类包装盒_定制印刷-金亚包装','饮品类包装盒,金亚包装','页面“应用广泛”展示中的饮品类包装。','','index,follow','seed:jinya:catalog33:beverage-color-box',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='beverage-color-box' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/boxes/boxes-app-1.jpg','/uploads/cms-jinya/boxes/boxes-app-1.jpg','饮品类包装盒','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/boxes/boxes-app-1.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','饮品类包装盒','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','盒型与尺寸','按需定制；页面说明可定做任意盒型和尺寸','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='盒型与尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'纸张材质','可选纸材','白卡纸 / 黑卡纸 / 瓦楞纸 / 亮银卡纸 / 金卡/哑银 / 特种纸','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='纸张材质' AND `parameter_name`='可选纸材' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷工艺','可选工艺','烫金 / 烫银 / 覆膜 / UV / 压纹 / 凹凸','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷工艺' AND `parameter_name`='可选工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制服务','设计与打样','围绕结构、纸张、工艺与数量确认方案','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制服务' AND `parameter_name`='设计与打样' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','生产交付','从设计校对到印刷成型交付','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='生产交付' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','饮品类包装盒','页面“应用广泛”展示中的饮品类包装。','/uploads/cms-jinya/boxes/boxes-app-1.jpg','/uploads/cms-jinya/boxes/boxes-app-1.jpg','seed:jinya:catalog33:beverage-color-box:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 饮品类包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','','','seed:jinya:catalog33:beverage-color-box:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 饮品类','','','seed:jinya:catalog33:beverage-color-box:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:beverage-color-box:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BX-002 玩具/文创包装盒
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='boxes-paper' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'玩具/文创包装盒','玩具/文创包装盒','JY-BX-002','toy-cultural-gift-box','/uploads/cms-jinya/boxes/boxes-app-2.jpg','/uploads/cms-jinya/boxes/boxes-app-2.jpg','页面“应用广泛”展示中的玩具包装。','- 玩具/文创包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','- 玩具/文创','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面“应用广泛”展示中的玩具包装。

## 产品特点

- 玩具/文创包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺

## 适用范围

- 玩具/文创

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','玩具/文创包装盒,彩盒/纸盒','[]',0,0,690,'published',@jinya_catalog_now,0,0,'','玩具/文创包装盒_定制印刷-金亚包装','玩具/文创包装盒,金亚包装','页面“应用广泛”展示中的玩具包装。','','index,follow','seed:jinya:catalog33:toy-cultural-gift-box',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='toy-cultural-gift-box' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/boxes/boxes-app-2.jpg','/uploads/cms-jinya/boxes/boxes-app-2.jpg','玩具/文创包装盒','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/boxes/boxes-app-2.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','玩具/文创包装盒','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','盒型与尺寸','按需定制；页面说明可定做任意盒型和尺寸','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='盒型与尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'纸张材质','可选纸材','白卡纸 / 黑卡纸 / 瓦楞纸 / 亮银卡纸 / 金卡/哑银 / 特种纸','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='纸张材质' AND `parameter_name`='可选纸材' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷工艺','可选工艺','烫金 / 烫银 / 覆膜 / UV / 压纹 / 凹凸','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷工艺' AND `parameter_name`='可选工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制服务','设计与打样','围绕结构、纸张、工艺与数量确认方案','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制服务' AND `parameter_name`='设计与打样' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','生产交付','从设计校对到印刷成型交付','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='生产交付' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','玩具/文创包装盒','页面“应用广泛”展示中的玩具包装。','/uploads/cms-jinya/boxes/boxes-app-2.jpg','/uploads/cms-jinya/boxes/boxes-app-2.jpg','seed:jinya:catalog33:toy-cultural-gift-box:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 玩具/文创包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','','','seed:jinya:catalog33:toy-cultural-gift-box:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 玩具/文创','','','seed:jinya:catalog33:toy-cultural-gift-box:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:toy-cultural-gift-box:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BX-003 食品包装盒
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='boxes-paper' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'食品包装盒','食品包装盒','JY-BX-003','food-color-box','/uploads/cms-jinya/boxes/boxes-app-food2.jpg','/uploads/cms-jinya/boxes/boxes-app-food2.jpg','页面“应用广泛”中两组食品包装示例，合并为一个食品包装盒产品。','- 食品包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','- 食品','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面“应用广泛”中两组食品包装示例，合并为一个食品包装盒产品。

## 产品特点

- 食品包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺

## 适用范围

- 食品

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','食品包装盒,彩盒/纸盒','[]',1,0,680,'published',@jinya_catalog_now,0,0,'','食品包装盒_定制印刷-金亚包装','食品包装盒,金亚包装','页面“应用广泛”中两组食品包装示例，合并为一个食品包装盒产品。','','index,follow','seed:jinya:catalog33:food-color-box',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='food-color-box' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/boxes/boxes-app-food2.jpg','/uploads/cms-jinya/boxes/boxes-app-food2.jpg','食品包装盒','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/boxes/boxes-app-food2.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/boxes/boxes-app-3.jpg','/uploads/cms-jinya/boxes/boxes-app-3.jpg','食品包装盒','gallery',0,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/boxes/boxes-app-3.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','食品包装盒','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','盒型与尺寸','按需定制；页面说明可定做任意盒型和尺寸','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='盒型与尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'纸张材质','可选纸材','白卡纸 / 黑卡纸 / 瓦楞纸 / 亮银卡纸 / 金卡/哑银 / 特种纸','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='纸张材质' AND `parameter_name`='可选纸材' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷工艺','可选工艺','烫金 / 烫银 / 覆膜 / UV / 压纹 / 凹凸','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷工艺' AND `parameter_name`='可选工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制服务','设计与打样','围绕结构、纸张、工艺与数量确认方案','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制服务' AND `parameter_name`='设计与打样' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','生产交付','从设计校对到印刷成型交付','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='生产交付' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','食品包装盒','页面“应用广泛”中两组食品包装示例，合并为一个食品包装盒产品。','/uploads/cms-jinya/boxes/boxes-app-food2.jpg','/uploads/cms-jinya/boxes/boxes-app-food2.jpg','seed:jinya:catalog33:food-color-box:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 食品包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','','','seed:jinya:catalog33:food-color-box:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 食品','','','seed:jinya:catalog33:food-color-box:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:food-color-box:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BX-004 彩妆包装盒
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='boxes-paper' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'彩妆包装盒','彩妆包装盒','JY-BX-004','cosmetic-box','/uploads/cms-jinya/boxes/boxes-app-cosmetic.jpg','/uploads/cms-jinya/boxes/boxes-app-cosmetic.jpg','页面“应用广泛”展示中的彩妆包装。','- 彩妆包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','- 彩妆','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面“应用广泛”展示中的彩妆包装。

## 产品特点

- 彩妆包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺

## 适用范围

- 彩妆

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','彩妆包装盒,彩盒/纸盒','[]',0,0,670,'published',@jinya_catalog_now,0,0,'','彩妆包装盒_定制印刷-金亚包装','彩妆包装盒,金亚包装','页面“应用广泛”展示中的彩妆包装。','','index,follow','seed:jinya:catalog33:cosmetic-box',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='cosmetic-box' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/boxes/boxes-app-cosmetic.jpg','/uploads/cms-jinya/boxes/boxes-app-cosmetic.jpg','彩妆包装盒','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/boxes/boxes-app-cosmetic.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','彩妆包装盒','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','盒型与尺寸','按需定制；页面说明可定做任意盒型和尺寸','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='盒型与尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'纸张材质','可选纸材','白卡纸 / 黑卡纸 / 瓦楞纸 / 亮银卡纸 / 金卡/哑银 / 特种纸','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='纸张材质' AND `parameter_name`='可选纸材' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷工艺','可选工艺','烫金 / 烫银 / 覆膜 / UV / 压纹 / 凹凸','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷工艺' AND `parameter_name`='可选工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制服务','设计与打样','围绕结构、纸张、工艺与数量确认方案','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制服务' AND `parameter_name`='设计与打样' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','生产交付','从设计校对到印刷成型交付','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='生产交付' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','彩妆包装盒','页面“应用广泛”展示中的彩妆包装。','/uploads/cms-jinya/boxes/boxes-app-cosmetic.jpg','/uploads/cms-jinya/boxes/boxes-app-cosmetic.jpg','seed:jinya:catalog33:cosmetic-box:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 彩妆包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','','','seed:jinya:catalog33:cosmetic-box:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 彩妆','','','seed:jinya:catalog33:cosmetic-box:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:cosmetic-box:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BX-005 特产包装盒
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='boxes-paper' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'特产包装盒','特产包装盒','JY-BX-005','specialty-gift-box','/uploads/cms-jinya/boxes/boxes-app-specialty.jpg','/uploads/cms-jinya/boxes/boxes-app-specialty.jpg','页面“应用广泛”展示中的特产包装。','- 特产包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','- 特产','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面“应用广泛”展示中的特产包装。

## 产品特点

- 特产包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺

## 适用范围

- 特产

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','特产包装盒,彩盒/纸盒','[]',0,0,660,'published',@jinya_catalog_now,0,0,'','特产包装盒_定制印刷-金亚包装','特产包装盒,金亚包装','页面“应用广泛”展示中的特产包装。','','index,follow','seed:jinya:catalog33:specialty-gift-box',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='specialty-gift-box' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/boxes/boxes-app-specialty.jpg','/uploads/cms-jinya/boxes/boxes-app-specialty.jpg','特产包装盒','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/boxes/boxes-app-specialty.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','特产包装盒','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','盒型与尺寸','按需定制；页面说明可定做任意盒型和尺寸','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='盒型与尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'纸张材质','可选纸材','白卡纸 / 黑卡纸 / 瓦楞纸 / 亮银卡纸 / 金卡/哑银 / 特种纸','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='纸张材质' AND `parameter_name`='可选纸材' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷工艺','可选工艺','烫金 / 烫银 / 覆膜 / UV / 压纹 / 凹凸','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷工艺' AND `parameter_name`='可选工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制服务','设计与打样','围绕结构、纸张、工艺与数量确认方案','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制服务' AND `parameter_name`='设计与打样' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','生产交付','从设计校对到印刷成型交付','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='生产交付' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','特产包装盒','页面“应用广泛”展示中的特产包装。','/uploads/cms-jinya/boxes/boxes-app-specialty.jpg','/uploads/cms-jinya/boxes/boxes-app-specialty.jpg','seed:jinya:catalog33:specialty-gift-box:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 特产包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','','','seed:jinya:catalog33:specialty-gift-box:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 特产','','','seed:jinya:catalog33:specialty-gift-box:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:specialty-gift-box:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BX-006 宠物产品包装盒
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='boxes-paper' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'宠物产品包装盒','宠物产品包装盒','JY-BX-006','pet-product-box','/uploads/cms-jinya/boxes/boxes-app-pet.jpg','/uploads/cms-jinya/boxes/boxes-app-pet.jpg','页面“应用广泛”展示中的宠物包装。','- 宠物产品包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','- 宠物产品','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面“应用广泛”展示中的宠物包装。

## 产品特点

- 宠物产品包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺

## 适用范围

- 宠物产品

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','宠物产品包装盒,彩盒/纸盒','[]',0,0,650,'published',@jinya_catalog_now,0,0,'','宠物产品包装盒_定制印刷-金亚包装','宠物产品包装盒,金亚包装','页面“应用广泛”展示中的宠物包装。','','index,follow','seed:jinya:catalog33:pet-product-box',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='pet-product-box' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/boxes/boxes-app-pet.jpg','/uploads/cms-jinya/boxes/boxes-app-pet.jpg','宠物产品包装盒','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/boxes/boxes-app-pet.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','宠物产品包装盒','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','盒型与尺寸','按需定制；页面说明可定做任意盒型和尺寸','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='盒型与尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'纸张材质','可选纸材','白卡纸 / 黑卡纸 / 瓦楞纸 / 亮银卡纸 / 金卡/哑银 / 特种纸','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='纸张材质' AND `parameter_name`='可选纸材' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷工艺','可选工艺','烫金 / 烫银 / 覆膜 / UV / 压纹 / 凹凸','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷工艺' AND `parameter_name`='可选工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制服务','设计与打样','围绕结构、纸张、工艺与数量确认方案','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制服务' AND `parameter_name`='设计与打样' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','生产交付','从设计校对到印刷成型交付','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='生产交付' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','宠物产品包装盒','页面“应用广泛”展示中的宠物包装。','/uploads/cms-jinya/boxes/boxes-app-pet.jpg','/uploads/cms-jinya/boxes/boxes-app-pet.jpg','seed:jinya:catalog33:pet-product-box:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 宠物产品包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','','','seed:jinya:catalog33:pet-product-box:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 宠物产品','','','seed:jinya:catalog33:pet-product-box:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:pet-product-box:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BX-007 中药包装盒
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='boxes-paper' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'中药包装盒','中药包装盒','JY-BX-007','traditional-medicine-box','/uploads/cms-jinya/boxes/boxes-app-mid.jpg','/uploads/cms-jinya/boxes/boxes-app-mid.jpg','页面“应用广泛”展示中的中药类包装（页面结构化默认数据原题为“中类包装”，依据页面语境规范为“中药包装盒”）。','- 中药包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','- 中药','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面“应用广泛”展示中的中药类包装（页面结构化默认数据原题为“中类包装”，依据页面语境规范为“中药包装盒”）。

## 产品特点

- 中药包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺

## 适用范围

- 中药

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','中药包装盒,彩盒/纸盒','[]',0,0,640,'published',@jinya_catalog_now,0,0,'','中药包装盒_定制印刷-金亚包装','中药包装盒,金亚包装','页面“应用广泛”展示中的中药类包装（页面结构化默认数据原题为“中类包装”，依据页面语境规范为“中药包装盒”）。','','index,follow','seed:jinya:catalog33:traditional-medicine-box',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='traditional-medicine-box' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/boxes/boxes-app-mid.jpg','/uploads/cms-jinya/boxes/boxes-app-mid.jpg','中药包装盒','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/boxes/boxes-app-mid.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','中药包装盒','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','盒型与尺寸','按需定制；页面说明可定做任意盒型和尺寸','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='盒型与尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'纸张材质','可选纸材','白卡纸 / 黑卡纸 / 瓦楞纸 / 亮银卡纸 / 金卡/哑银 / 特种纸','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='纸张材质' AND `parameter_name`='可选纸材' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷工艺','可选工艺','烫金 / 烫银 / 覆膜 / UV / 压纹 / 凹凸','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷工艺' AND `parameter_name`='可选工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制服务','设计与打样','围绕结构、纸张、工艺与数量确认方案','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制服务' AND `parameter_name`='设计与打样' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','生产交付','从设计校对到印刷成型交付','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='生产交付' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','中药包装盒','页面“应用广泛”展示中的中药类包装（页面结构化默认数据原题为“中类包装”，依据页面语境规范为“中药包装盒”）。','/uploads/cms-jinya/boxes/boxes-app-mid.jpg','/uploads/cms-jinya/boxes/boxes-app-mid.jpg','seed:jinya:catalog33:traditional-medicine-box:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 中药包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','','','seed:jinya:catalog33:traditional-medicine-box:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 中药','','','seed:jinya:catalog33:traditional-medicine-box:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:traditional-medicine-box:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

-- JY-BX-008 茶叶包装盒
SET @jinya_category_id := (SELECT `id` FROM `fa_cms_product_category` WHERE `slug`='boxes-paper' AND `deletetime` IS NULL LIMIT 1);
INSERT IGNORE INTO `fa_cms_product` (`category_id`,`title`,`subtitle`,`product_code`,`slug`,`cover_image`,`mobile_cover_image`,`summary`,`features`,`applications`,`construction`,`precautions`,`content`,`tags`,`download_files`,`is_recommend`,`views`,`weigh`,`status`,`publish_time`,`publish_admin_id`,`audit_admin_id`,`reject_reason`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`robots`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) VALUES (@jinya_category_id,'茶叶包装盒','茶叶包装盒','JY-BX-008','tea-box','/uploads/cms-jinya/boxes/boxes-app-tea.jpg','/uploads/cms-jinya/boxes/boxes-app-tea.jpg','页面“应用广泛”展示中的茶叶包装。','- 茶叶包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','- 茶叶','','具体尺寸、材质、数量、稿件、工艺与交期以双方确认的订单和生产参数为准。','## 产品简介

页面“应用广泛”展示中的茶叶包装。

## 产品特点

- 茶叶包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺

## 适用范围

- 茶叶

## 定制说明

1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','茶叶包装盒,彩盒/纸盒','[]',0,0,630,'published',@jinya_catalog_now,0,0,'','茶叶包装盒_定制印刷-金亚包装','茶叶包装盒,金亚包装','页面“应用广泛”展示中的茶叶包装。','','index,follow','seed:jinya:catalog33:tea-box',0,@jinya_catalog_now,@jinya_catalog_now);
SET @jinya_product_id := (SELECT `id` FROM `fa_cms_product` WHERE `slug`='tea-box' AND `deletetime` IS NULL LIMIT 1);
INSERT INTO `fa_cms_product_image` (`product_id`,`image`,`mobile_image`,`alt`,`image_type`,`is_cover`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'/uploads/cms-jinya/boxes/boxes-app-tea.jpg','/uploads/cms-jinya/boxes/boxes-app-tea.jpg','茶叶包装盒','gallery',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_image` WHERE `product_id`=@jinya_product_id AND `image`='/uploads/cms-jinya/boxes/boxes-app-tea.jpg' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','产品类型','茶叶包装盒','',1,100,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='产品类型' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'基础配置','盒型与尺寸','按需定制；页面说明可定做任意盒型和尺寸','',1,90,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='基础配置' AND `parameter_name`='盒型与尺寸' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'纸张材质','可选纸材','白卡纸 / 黑卡纸 / 瓦楞纸 / 亮银卡纸 / 金卡/哑银 / 特种纸','',1,80,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='纸张材质' AND `parameter_name`='可选纸材' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'印刷工艺','可选工艺','烫金 / 烫银 / 覆膜 / UV / 压纹 / 凹凸','',1,70,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='印刷工艺' AND `parameter_name`='可选工艺' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制服务','设计与打样','围绕结构、纸张、工艺与数量确认方案','',1,60,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制服务' AND `parameter_name`='设计与打样' AND `deletetime` IS NULL);
INSERT INTO `fa_cms_product_parameter` (`product_id`,`parameter_group`,`parameter_name`,`parameter_value`,`unit`,`is_visible`,`weigh`,`createtime`,`updatetime`) SELECT @jinya_product_id,'定制交付','生产交付','从设计校对到印刷成型交付','',1,50,@jinya_catalog_now,@jinya_catalog_now FROM DUAL WHERE @jinya_product_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `fa_cms_product_parameter` WHERE `product_id`=@jinya_product_id AND `parameter_group`='定制交付' AND `parameter_name`='生产交付' AND `deletetime` IS NULL);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'introduction','产品简介','茶叶包装盒','页面“应用广泛”展示中的茶叶包装。','/uploads/cms-jinya/boxes/boxes-app-tea.jpg','/uploads/cms-jinya/boxes/boxes-app-tea.jpg','seed:jinya:catalog33:tea-box:introduction:1',0,1,1,300,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'features','产品特点','','- 茶叶包装盒应用展示
- 多种纸张材质可选
- 支持烫金、烫银、覆膜、UV、压纹、凹凸等工艺','','','seed:jinya:catalog33:tea-box:features:2',0,1,1,200,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'application','适用范围','','- 茶叶','','','seed:jinya:catalog33:tea-box:application:3',0,1,1,100,'normal',@jinya_catalog_now,@jinya_catalog_now);
INSERT IGNORE INTO `fa_cms_product_section` (`product_id`,`section_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (@jinya_product_id,'custom_text','定制说明','从需求确认到生产交付','1. 提交用途、规格、数量和设计稿/参考样。
2. 确认材质、结构及工艺。
3. 沟通报价并确认生产参数。
4. 安排印刷与后道加工。
5. 品检、包装并按约定方式交付。','','','seed:jinya:catalog33:tea-box:custom_text:4',0,1,1,50,'normal',@jinya_catalog_now,@jinya_catalog_now);

