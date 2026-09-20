define(['jquery','form'], function ($, Form) {
    var SELECTOR = '[data-cms-icon-picker="true"]';
    var sequence = 0;
    var iconList = null;
    var iconLoading = false;
    var iconWaiters = [];

    var ensureStyle = function () {
        if ($('#cms-icon-picker-style').length) { return; }
        var base = (window.Config && Config.site && Config.site.cdnurl) ? Config.site.cdnurl : '';
        $('<link id="cms-icon-picker-style" rel="stylesheet">').attr('href', base + '/assets/css/cms-icon-picker.css').appendTo('head');
    };

    var ensureId = function (input) {
        var id = input.attr('id');
        if (id) { return id; }
        sequence += 1;
        id = 'cms-icon-input-' + sequence;
        input.attr('id', id);
        return id;
    };

    var canonicalFont = function (value) {
        value = $.trim(String(value || '')).replace(/\s+/g, ' ');
        return /^fa\s+fa-[a-z0-9-]+$/.test(value) ? value : '';
    };

    // Historical structured-page data stores semantic icon keys rather than
    // Font Awesome classes. Keep those raw values untouched for frontend
    // compatibility, but map them to representative FA icons for admin echo.
    var legacySemanticFont = function (value) {
        var map = {
            'custom':'fa fa-crop',
            'design':'fa fa-crop',
            'team':'fa fa-users',
            'batch':'fa fa-clone',
            'layers':'fa fa-clone',
            'urgent':'fa fa-truck',
            'truck':'fa fa-truck',
            'support':'fa fa-headphones',
            'service':'fa fa-headphones',
            'headset':'fa fa-headphones',
            'material':'fa fa-cube',
            'produce':'fa fa-cogs',
            'payment':'fa fa-money',
            'price':'fa fa-money',
            'quote':'fa fa-money',
            'confirm':'fa fa-check-square-o',
            'shield':'fa fa-shield',
            'quality':'fa fa-shield',
            'clock':'fa fa-clock-o',
            'print':'fa fa-print',
            'chat':'fa fa-comments',
            'deal':'fa fa-handshake-o',
            'compass':'fa fa-compass',
            'target':'fa fa-bullseye',
            'phone':'fa fa-phone',
            'email':'fa fa-envelope',
            'map':'fa fa-map-marker'
        };
        value = $.trim(String(value || '')).toLowerCase();
        return Object.prototype.hasOwnProperty.call(map, value) ? map[value] : '';
    };

    var fullUrl = function (value) {
        value = $.trim(String(value || ''));
        if (!value) { return ''; }
        try { return Fast.api.cdnurl(value); } catch (e) { return value; }
    };

    var previewFor = function (input) {
        var id = ensureId(input);
        var group = input.closest('.cms-icon-picker-input-group');
        var preview = group.next('.cms-icon-picker-preview[data-icon-input-id="' + id + '"]');
        if (!preview.length) {
            preview = $('<div class="cms-icon-picker-preview"></div>').attr('data-icon-input-id', id);
            group.after(preview);
        }
        return preview;
    };

    var render = function (input) {
        input = $(input);
        var preview = previewFor(input).empty();
        var raw = $.trim(String(input.val() || ''));
        var previewValue = raw;
        var previewLabel = raw;
        var isDefault = false;
        if (!previewValue) {
            previewValue = $.trim(String(input.attr('data-cms-icon-default-preview') || ''));
            previewLabel = $.trim(String(input.attr('data-cms-icon-default-label') || '当前默认图标'));
            if (!previewValue) { return; }
            isDefault = true;
        }
        var box = $('<span class="cms-icon-picker-preview-box"></span>');
        var font = canonicalFont(previewValue);
        var legacyFont = legacySemanticFont(previewValue);
        if (font) {
            box.append($('<i aria-hidden="true"></i>').attr('class', font));
        } else if (legacyFont) {
            box.addClass('cms-icon-picker-legacy');
            box.append($('<i aria-hidden="true"></i>').attr('class', legacyFont));
        } else {
            var image = $('<img alt="图标预览">').attr('src', fullUrl(previewValue));
            image.on('error', function () { $(this).hide(); box.append($('<i class="fa fa-picture-o text-muted"></i>')); });
            box.append(image);
        }
        if (isDefault) { preview.addClass('cms-icon-picker-preview-default'); }
        preview.append(box).append($('<span class="cms-icon-picker-value"></span>').text(previewLabel));
    };

    var prepareInput = function (input) {
        input = $(input);
        if (input.attr('data-cms-icon-picker-ready') === '1') { render(input); return; }
        ensureStyle();
        var id = ensureId(input);
        var group = input.closest('.input-group');
        if (!group.length || !group.hasClass('cms-icon-picker-input-group')) {
            input.wrap('<div class="input-group cms-icon-picker-input-group"></div>');
            group = input.closest('.input-group');
        } else {
            group.addClass('cms-icon-picker-input-group');
        }
        var groupMaxWidth = $.trim(String(input.attr('data-cms-icon-picker-group-max-width') || ''));
        if (groupMaxWidth) {
            group.css({width: '100%', maxWidth: groupMaxWidth});
        }
        if (!group.find('.cms-icon-picker-open[data-input-id="' + id + '"]').length) {
            var button = $('<button type="button" class="btn btn-primary cms-icon-picker-open"><i class="fa fa-th"></i> 选择图标</button>')
                .attr('data-input-id', id);
            group.append($('<span class="input-group-btn"></span>').append(button));
        }
        input.attr('data-cms-icon-picker-ready', '1');
        render(input);
    };

    var parseIconList = function (css) {
        var found = [], seen = {}, exp = /\.fa-([a-z0-9-]+):before\s*\{/ig, match;
        while ((match = exp.exec(css)) !== null) {
            if (!seen[match[1]]) { seen[match[1]] = true; found.push(match[1]); }
        }
        found.sort();
        return found;
    };

    var loadIconList = function (callback) {
        if ($.isArray(iconList)) { callback(iconList, null); return; }
        iconWaiters.push(callback);
        if (iconLoading) { return; }
        iconLoading = true;
        var base = (window.Config && Config.site && Config.site.cdnurl) ? Config.site.cdnurl : '';
        $.get(base + '/assets/libs/font-awesome/css/font-awesome.css')
            .done(function (css) { iconList = parseIconList(css || ''); })
            .fail(function () { iconList = []; })
            .always(function () {
                iconLoading = false;
                var waiters = iconWaiters.splice(0);
                $.each(waiters, function (_, fn) { fn(iconList, iconList.length ? null : 'Font Awesome 图标库加载失败，请使用图片图标或稍后重试。'); });
            });
    };

    var switchTab = function (dialog, name) {
        dialog.find('[data-icon-picker-tab]').removeClass('active');
        dialog.find('[data-icon-picker-tab="' + name + '"]').addClass('active');
        dialog.find('.cms-icon-picker-tab-pane').removeClass('active');
        dialog.find('[data-icon-picker-pane="' + name + '"]').addClass('active');
    };

    var fillGrid = function (dialog, list, error) {
        var grid = dialog.find('.cms-icon-picker-font-grid').empty();
        if (error) { grid.append($('<div class="cms-icon-picker-empty"></div>').text(error)); return; }
        $.each(list, function (_, name) {
            var item = $('<button type="button" class="cms-icon-picker-font-item"></button>')
                .attr('data-font-name', name).attr('title', name)
                .append($('<i aria-hidden="true"></i>').attr('class', 'fa fa-' + name));
            grid.append(item);
        });
    };

    var openPicker = function (input) {
        input = $(input);
        var inputId = ensureId(input);
        var dialog = $('<div class="cms-icon-picker-dialog"></div>');
        var tabs = $('<ul class="nav nav-tabs"></ul>');
        tabs.append('<li class="active" data-icon-picker-tab="font"><a href="javascript:;"><i class="fa fa-font"></i> Font Awesome</a></li>');
        tabs.append('<li data-icon-picker-tab="image"><a href="javascript:;"><i class="fa fa-picture-o"></i> 图片图标</a></li>');
        tabs.append('<li data-icon-picker-tab="upload"><a href="javascript:;"><i class="fa fa-upload"></i> 上传自定义图标</a></li>');
        dialog.append(tabs);
        var fontPane = $('<div class="cms-icon-picker-tab-pane active" data-icon-picker-pane="font"></div>');
        fontPane.append('<div class="cms-icon-picker-search-wrap"><input class="form-control cms-icon-picker-search" type="search" placeholder="搜索 Font Awesome，例如 shield、user、phone"></div>');
        fontPane.append('<div class="cms-icon-picker-font-grid"><div class="cms-icon-picker-empty">正在加载图标库…</div></div>');
        dialog.append(fontPane);
        var imagePane = $('<div class="cms-icon-picker-tab-pane" data-icon-picker-pane="image"></div>');
        var choose = $('<button type="button" class="btn btn-primary fachoose"><i class="fa fa-folder-open-o"></i> 从已上传图片选择</button>')
            .attr('id', 'cms-icon-choose-' + inputId).attr('data-input-id', inputId).attr('data-multiple', 'false').attr('data-mimetype', 'image/*');
        imagePane.append($('<div class="cms-icon-picker-image-actions"></div>').append(choose).append('<p class="cms-icon-picker-help">选择已经上传过的 PNG / WebP 图片图标。</p>'));
        dialog.append(imagePane);
        var uploadPane = $('<div class="cms-icon-picker-tab-pane" data-icon-picker-pane="upload"></div>');
        var upload = $('<button type="button" class="btn btn-danger faupload"><i class="fa fa-upload"></i> 上传自定义图标</button>')
            .attr('id', 'cms-icon-upload-' + inputId).attr('data-input-id', inputId).attr('data-multiple', 'false').attr('data-mimetype', 'image/*');
        uploadPane.append($('<div class="cms-icon-picker-image-actions"></div>').append(upload).append('<p class="cms-icon-picker-help">图库没有合适图标时再上传。推荐透明背景 PNG / WebP。</p>'));
        dialog.append(uploadPane);

        Layer.open({
            type: 1,
            title: '选择图标',
            area: ['86%', '82%'],
            content: dialog.prop('outerHTML'),
            success: function (layero, layerIndex) {
                var renderedDialog = layero.find('.cms-icon-picker-dialog');
                Form.events.faselect(renderedDialog);
                Form.events.faupload(renderedDialog);
                loadIconList(function (list, error) { fillGrid(renderedDialog, list, error); });

                renderedDialog.on('click', '[data-icon-picker-tab]', function () {
                    switchTab(renderedDialog, $(this).attr('data-icon-picker-tab'));
                });
                renderedDialog.on('click', '.cms-icon-picker-font-item', function () {
                    input.val('fa fa-' + $(this).attr('data-font-name')).trigger('input').trigger('change').trigger('validate');
                    Layer.close(layerIndex);
                });
                renderedDialog.on('input', '.cms-icon-picker-search', function () {
                    var query = $.trim(String($(this).val() || '')).toLowerCase();
                    renderedDialog.find('.cms-icon-picker-font-item').each(function () {
                        var name = String($(this).attr('data-font-name') || '').toLowerCase();
                        $(this).toggle(query === '' || name.indexOf(query) !== -1);
                    });
                });
            }
        });
    };

    var prepare = function (form) {
        form = $(form);
        form.find(SELECTOR).each(function () { prepareInput(this); });
        return form;
    };

    var bind = function (form) {
        form = prepare(form);
        form.off('.cmsIconPicker');
        form.on('click.cmsIconPicker', '.cms-icon-picker-open', function () {
            var id = $(this).attr('data-input-id');
            var input = form.find('#' + id);
            if (input.length) { openPicker(input); }
        });
        form.on('input.cmsIconPicker change.cmsIconPicker', SELECTOR, function () { render(this); });
        var oldObserver = form.data('cmsIconPickerObserver');
        if (oldObserver && oldObserver.disconnect) { oldObserver.disconnect(); }
        if (window.MutationObserver && form.length) {
            var observer = new MutationObserver(function (mutations) {
                $.each(mutations, function (_, mutation) {
                    $.each(mutation.addedNodes || [], function (_, node) {
                        if (node.nodeType !== 1) { return; }
                        var added = $(node);
                        if (added.is(SELECTOR)) { prepareInput(added); }
                        added.find(SELECTOR).each(function () { prepareInput(this); });
                    });
                });
            });
            observer.observe(form.get(0), {childList:true, subtree:true});
            form.data('cmsIconPickerObserver', observer);
        }
        return form;
    };

    var bindForm = function (form) {
        Form.api.bindevent($(form));
        return bind(form);
    };

    var refresh = function (form) {
        prepare(form);
        return bind(form);
    };

    return {prepare:prepare, bind:bind, bindForm:bindForm, refresh:refresh, render:render, canonicalFont:canonicalFont, legacySemanticFont:legacySemanticFont};
});
