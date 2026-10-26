define(['jquery'], function ($) {
    var sequence = 0;
    var INPUT_SELECTOR = '[data-media-preview]';
    var RATIO_TOLERANCE = 0.08;

    var boolAttr = function (input, name) {
        var value = input.attr(name);
        return value === '' || value === '1' || value === 'true' || value === true;
    };

    var integerAttr = function (input, name) {
        var value = parseInt(input.attr(name), 10);
        return isNaN(value) || value <= 0 ? 0 : value;
    };

    var IMAGE_PRESETS = {
        logo: {size: '212 × 45', width: 212, height: 45, ratio: '212:45', format: 'PNG / WebP', transparent: true},
        qrcode: {size: '600 × 600', width: 600, height: 600, format: 'PNG / JPG / JPEG'},
        icon: {size: '128 × 128', width: 128, height: 128, format: 'PNG / WebP', transparent: true},
        avatar: {size: '400 × 400', width: 400, height: 400, format: 'JPG / JPEG / PNG / WebP'},
        pcBanner: {size: '1920 × 800', width: 1920, height: 800, format: 'JPG / JPEG / PNG / WebP'},
        mobileBanner: {size: '750 × 422', width: 750, height: 422, format: 'JPG / JPEG / PNG / WebP'},
        category: {size: '600 × 450', width: 600, height: 450, format: 'JPG / JPEG / PNG / WebP'},
        default: {size: '1200 × 900', width: 1200, height: 900, format: 'JPG / JPEG / PNG / WebP'},
        mobile: {size: '750 × 563', width: 750, height: 563, format: 'JPG / JPEG / PNG / WebP'}
    };

    var imageFieldContext = function (input) {
        var group = input.closest('.form-group');
        return [
            input.attr('id') || '',
            input.attr('name') || '',
            input.attr('data-media-usage') || '',
            input.attr('placeholder') || '',
            group.find('label').first().text() || ''
        ].join(' ').toLowerCase();
    };

    var inferImageSpec = function (input) {
        var previewType = String(input.attr('data-media-preview') || 'image').toLowerCase();
        if (previewType === 'video') {
            return {width: 0, height: 0, format: '', transparent: false};
        }
        if (previewType === 'auto') {
            var selector = input.attr('data-media-preview-source');
            var source = selector ? input.closest('form').find(selector).first() : $();
            if (source.length && String(source.val() || '').toLowerCase() === 'video') {
                return {width: 0, height: 0, format: '', transparent: false};
            }
        }
        var context = imageFieldContext(input);
        if (/(?:logo|标志|徽标)/.test(context)) {
            return IMAGE_PRESETS.logo;
        }
        if (/(?:qrcode|qr|二维码)/.test(context)) {
            return IMAGE_PRESETS.qrcode;
        }
        if (/(?:avatar|头像)/.test(context)) {
            return IMAGE_PRESETS.avatar;
        }
        if (/(?:icon|图标)/.test(context)) {
            return IMAGE_PRESETS.icon;
        }
        if (/(?:mobile|移动|手机)/.test(context) && /(?:banner|background|背景|横幅)/.test(context)) {
            return IMAGE_PRESETS.mobileBanner;
        }
        if (/(?:banner|background|背景|横幅)/.test(context)) {
            return IMAGE_PRESETS.pcBanner;
        }
        if (/(?:category|分类)/.test(context)) {
            return IMAGE_PRESETS.category;
        }
        if (/(?:mobile|移动|手机)/.test(context)) {
            return IMAGE_PRESETS.mobile;
        }
        return IMAGE_PRESETS.default;
    };

    var mediaSpec = function (input) {
        var inferred = inferImageSpec(input);
        return {
            usage: $.trim(input.attr('data-media-usage') || ''),
            width: integerAttr(input, 'data-media-recommended-width') || inferred.width,
            height: integerAttr(input, 'data-media-recommended-height') || inferred.height,
            ratio: inferred.ratio || '',
            format: $.trim(input.attr('data-media-recommended-format') || '') || inferred.format,
            maxKb: integerAttr(input, 'data-media-max-kb'),
            transparent: boolAttr(input, 'data-media-transparent') || inferred.transparent
        };
    };

    var formatSize = function (kb) {
        if (!kb) {
            return '';
        }
        return kb >= 1024 && kb % 1024 === 0 ? (kb / 1024) + ' MB' : kb + ' KB';
    };

    var recommendationText = function (input) {
        var spec = mediaSpec(input);
        var parts = [];
        if (spec.width && spec.height) {
            parts.push('最佳图片尺寸：' + spec.width + ' × ' + spec.height + ' px');
        }
        if (spec.ratio) {
            parts.push('推荐比例：' + spec.ratio);
        }
        if (spec.format) {
            parts.push('推荐图片后缀：' + spec.format);
        }
        if (spec.maxKb) {
            parts.push('建议 ≤ ' + formatSize(spec.maxKb));
        }
        if (spec.transparent) {
            parts.push('支持透明背景');
        }
        return parts.join(' · ');
    };

    var ensureId = function (input) {
        var id = input.attr('id');
        if (id) {
            return id;
        }
        sequence += 1;
        id = 'cms-media-input-' + sequence;
        input.attr('id', id);
        return id;
    };

    var declaredType = function (input) {
        var type = String(input.attr('data-media-preview') || 'image').toLowerCase();
        return ['image', 'video', 'auto'].indexOf(type) === -1 ? 'image' : type;
    };

    var resolvedType = function (input) {
        var type = declaredType(input);
        if (type !== 'auto') {
            return type;
        }
        var selector = input.attr('data-media-preview-source');
        var form = input.closest('form');
        var source = selector ? form.find(selector).first() : $();
        return source.length && String(source.val()).toLowerCase() === 'video' ? 'video' : 'image';
    };

    var mimetypeFor = function (input) {
        var configured = input.attr('data-media-mimetype');
        if (configured) {
            return configured;
        }
        var type = declaredType(input);
        if (type === 'video') {
            return 'video/mp4,video/webm,video/ogg';
        }
        if (type === 'auto') {
            return 'image/*,video/mp4,video/webm,video/ogg';
        }
        return 'image/*';
    };

    var applyUploadPolicy = function (upload) {
        var uploadConfig = typeof Config !== 'undefined' && Config.upload ? Config.upload : {};
        if (!uploadConfig.chunking) {
            return upload;
        }
        upload.attr('data-chunking', 'true');
        var chunkSize = parseInt(uploadConfig.chunksize, 10);
        if (!isNaN(chunkSize) && chunkSize > 0) {
            upload.attr('data-chunk-size', chunkSize);
        }
        return upload;
    };

    var fullUrl = function (value) {
        value = $.trim(value || '');
        if (!value) {
            return '';
        }
        if (/^(data:|blob:)/i.test(value)) {
            return value;
        }
        try {
            return Fast.api.cdnurl(value);
        } catch (e) {
            return value;
        }
    };

    var extensionFor = function (value) {
        value = $.trim(value || '').replace(/[?#].*$/, '');
        var match = value.match(/\.([a-z0-9]+)$/i);
        if (!match) {
            return '';
        }
        var extension = match[1].toUpperCase();
        return extension === 'JPEG' ? 'JPG' : extension;
    };

    var ensureActions = function (input) {
        if (!boolAttr(input, 'data-media-upload')) {
            return;
        }
        var id = ensureId(input);
        var form = input.closest('form');
        if (form.find('.faupload[data-input-id="' + id + '"],.fachoose[data-input-id="' + id + '"]').length) {
            return;
        }

        var group = input.closest('.input-group');
        if (!group.length) {
            input.wrap('<div class="input-group cms-media-input-group"></div>');
            group = input.closest('.input-group');
        }
        var mimetype = mimetypeFor(input);
        var addon = $('<div class="input-group-addon no-border no-padding cms-media-actions"></div>');
        var upload = $('<button type="button" class="btn btn-danger faupload"><i class="fa fa-upload"></i> 上传</button>')
            .attr('id', 'faupload-' + id)
            .attr('data-input-id', id)
            .attr('data-mimetype', mimetype)
            .attr('data-multiple', 'false');
        applyUploadPolicy(upload);
        var choose = $('<button type="button" class="btn btn-primary fachoose"><i class="fa fa-list"></i> 选择</button>')
            .attr('id', 'fachoose-' + id)
            .attr('data-input-id', id)
            .attr('data-mimetype', mimetype)
            .attr('data-multiple', 'false');
        addon.append($('<span></span>').append(upload)).append($('<span></span>').append(choose));
        group.append(addon);
    };

    var ensurePreview = function (input) {
        var id = ensureId(input);
        var previewId = id + '-media-preview';
        var form = input.closest('form');
        var group = input.closest('.input-group');
        var afterSelector = $.trim(input.attr('data-media-preview-after') || '');
        var afterAnchor = afterSelector ? form.find(afterSelector).first() : $();
        var nearby = afterAnchor.length
            ? afterAnchor.next('.cms-media-preview')
            : (group.length ? group.next('.cms-media-preview') : input.next('.cms-media-preview'));
        if (nearby.length) {
            nearby.attr('id', previewId);
            input.attr('data-media-preview-id', previewId);
            return nearby;
        }
        var oldPreviewId = input.attr('data-media-preview-id');
        var preview = oldPreviewId ? form.find('#' + oldPreviewId) : $();
        if (preview.length) {
            preview.attr('id', previewId);
            input.attr('data-media-preview-id', previewId);
            return preview;
        }
        input.attr('data-media-preview-id', previewId);
        preview = $('<div class="cms-media-preview"></div>')
            .attr('id', previewId)
            .css({marginTop: '10px', minHeight: '0'});
        if (afterAnchor.length) {
            afterAnchor.after(preview);
        } else if (group.length) {
            group.after(preview);
        } else {
            input.after(preview);
        }
        return preview;
    };

    var renderRecommendation = function (preview, input) {
        var text = recommendationText(input);
        var usage = mediaSpec(input).usage;
        if (usage) {
            preview.append($('<p class="text-muted cms-media-usage" style="margin:0 0 4px"></p>').text('使用位置：' + usage));
        }
        if (text) {
            preview.append($('<p class="text-info cms-media-spec" style="margin:0 0 6px"></p>').text(text));
        }
    };

    var renderImageStatus = function (preview, input, value, imageNode) {
        var width = imageNode.naturalWidth || 0;
        var height = imageNode.naturalHeight || 0;
        if (!width || !height) {
            return;
        }
        var format = extensionFor(value);
        var current = '当前图片：' + width + ' × ' + height + ' px' + (format ? ' · ' + format : '');
        preview.append($('<p class="text-muted cms-media-current" style="margin:6px 0 0"></p>').text(current));

        var spec = mediaSpec(input);
        if (!spec.width || !spec.height) {
            return;
        }
        var expectedRatio = spec.width / spec.height;
        var actualRatio = width / height;
        var delta = Math.abs(actualRatio - expectedRatio) / expectedRatio;
        if (delta <= RATIO_TOLERANCE) {
            preview.append($('<p class="text-success cms-media-status" style="margin:3px 0 0"></p>').text('✓ 比例符合建议'));
        } else if (spec.ratio) {
            preview.append($('<p class="text-warning cms-media-status" style="margin:3px 0 0"></p>').text(
                '⚠ 当前 Logo 比例与推荐 ' + spec.ratio + ' 差异较大；前台会保持原图比例，不会拉伸，但可能造成高度或留白异常。'
            ));
        } else {
            preview.append($('<p class="text-warning cms-media-status" style="margin:3px 0 0"></p>').text(
                '⚠ 当前图片比例与推荐比例差异较大，前台可能发生明显裁切；如已确认展示效果，可继续保存。'
            ));
        }
    };

    var renderImage = function (preview, input, value, url) {
        var link = $('<a class="thumbnail" target="_blank" rel="noopener"></a>')
            .attr('href', url)
            .css({display: 'inline-block', marginBottom: '0', maxWidth: '340px'});
        var image = $('<img class="img-responsive" alt="媒体预览">')
            .attr('src', url)
            .css({
                maxWidth: '320px',
                maxHeight: '180px',
                objectFit: 'contain',
                backgroundColor: '#5f6b76',
                padding: '8px'
            });
        image.on('load', function () {
            preview.find('.cms-media-current,.cms-media-status').remove();
            renderImageStatus(preview, input, value, this);
        });
        image.on('error', function () {
            image.hide();
            if (!preview.find('.cms-media-preview-error').length) {
                preview.append($('<p class="text-danger cms-media-preview-error"></p>').text('图片无法预览：' + value));
            }
        });
        link.append(image);
        preview.append(link);
    };

    var isDirectVideoUrl = function (value) {
        value = $.trim(value || '');
        if (/^(blob:|data:video\/)/i.test(value)) {
            return true;
        }
        var clean = value.replace(/[?#].*$/, '').toLowerCase();
        var extension = clean.indexOf('.') === -1 ? '' : clean.split('.').pop();
        return ['mp4', 'webm', 'ogg'].indexOf(extension) !== -1;
    };

    var renderVideo = function (preview, value, url) {
        var video = $('<video controls preload="metadata" playsinline></video>')
            .attr('src', url)
            .css({display: 'block', width: '480px', maxWidth: '100%', maxHeight: '270px', background: '#000'});
        video.on('error', function () {
            if (!preview.find('.cms-media-preview-error').length) {
                preview.append($('<p class="text-danger cms-media-preview-error"></p>').text('视频无法预览，请检查文件格式或地址：' + value));
            }
        });
        preview.append(video);
        preview.append($('<p style="margin:6px 0 0"></p>').append(
            $('<a target="_blank" rel="noopener">打开原视频</a>').attr('href', url)
        ));
    };

    var renderFrame = function (preview, value, url) {
        var frame = $('<iframe frameborder="0" allowfullscreen></iframe>')
            .attr('src', url)
            .attr('title', '播放器预览')
            .css({display: 'block', width: '480px', maxWidth: '100%', height: '270px', background: '#000'});
        preview.append(frame);
        preview.append($('<p style="margin:6px 0 0"></p>').append(
            $('<a target="_blank" rel="noopener">打开播放器页面</a>').attr('href', url)
        ));
    };

    var render = function (input) {
        input = $(input);
        var preview = ensurePreview(input);
        var value = $.trim(input.val() || '');
        var fallback = $.trim(input.attr('data-media-preview-fallback') || '');
        preview.empty();
        renderRecommendation(preview, input);
        if (!value && fallback) {
            value = fallback;
        }
        if (!value) {
            return;
        }
        var url = fullUrl(value);
        var type = resolvedType(input);
        if (type === 'image') {
            renderImage(preview, input, value, url);
        } else if (type === 'video') {
            if (isDirectVideoUrl(value)) {
                renderVideo(preview, value, url);
            } else {
                renderFrame(preview, value, url);
            }
        }
    };

    var prepare = function (form) {
        form = $(form);
        form.find(INPUT_SELECTOR).each(function () {
            var input = $(this);
            ensureId(input);
            ensureActions(input);
            ensurePreview(input);
        });
        return form;
    };

    var bind = function (form) {
        form = $(form);
        form.off('.cmsMediaPreview');
        form.on('input.cmsMediaPreview change.cmsMediaPreview', INPUT_SELECTOR, function () {
            render(this);
        });
        form.on('change.cmsMediaPreview', 'select,input[type="radio"]', function () {
            var changed = this;
            form.find(INPUT_SELECTOR + '[data-media-preview="auto"]').each(function () {
                var input = $(this);
                var selector = input.attr('data-media-preview-source');
                if (selector && form.find(selector).filter(changed).length) {
                    render(input);
                }
            });
        });
        form.on('reset.cmsMediaPreview', function () {
            setTimeout(function () {
                form.find(INPUT_SELECTOR).each(function () { render(this); });
            }, 0);
        });
        form.find(INPUT_SELECTOR).each(function () { render(this); });
        return form;
    };

    var bindForm = function (form, Form) {
        form = prepare(form);
        Form.api.bindevent(form);
        bind(form);
        return form;
    };

    var refresh = function (form, Form) {
        form = prepare(form);
        Form.events.faupload(form);
        Form.events.faselect(form);
        bind(form);
        return form;
    };

    return {
        prepare: prepare,
        bind: bind,
        bindForm: bindForm,
        refresh: refresh,
        render: render
    };
});
