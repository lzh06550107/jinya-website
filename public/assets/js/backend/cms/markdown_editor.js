define(['jquery'], function ($) {
    var sequence = 0;
    var SELECTOR = 'textarea.cms-markdown';

    var ensureId = function (textarea) {
        var id = textarea.attr('id');
        if (id) return id;
        sequence += 1;
        id = 'cms-markdown-' + sequence;
        textarea.attr('id', id);
        return id;
    };

    var insertText = function (textarea, before, after, fallback) {
        var el = textarea.get(0);
        if (!el) return;
        var value = textarea.val() || '';
        var start = typeof el.selectionStart === 'number' ? el.selectionStart : value.length;
        var end = typeof el.selectionEnd === 'number' ? el.selectionEnd : start;
        var selected = value.substring(start, end) || fallback || '';
        var replacement = before + selected + after;
        textarea.val(value.substring(0, start) + replacement + value.substring(end)).trigger('input').trigger('change');
        el.focus();
        if (typeof el.setSelectionRange === 'function') {
            var cursorStart = start + before.length;
            el.setSelectionRange(cursorStart, cursorStart + selected.length);
        }
    };

    var toolbarButton = function (label, title, before, after, fallback) {
        return $('<button type="button" class="btn btn-default btn-xs cms-md-tool"></button>')
            .text(label).attr('title', title)
            .data({before: before, after: after, fallback: fallback});
    };

    var prepareOne = function (textarea) {
        textarea = $(textarea);
        if (textarea.attr('data-markdown-ready') === '1') return;
        textarea.attr('data-markdown-ready', '1').attr('data-markdown-upload', '1');
        var id = ensureId(textarea);
        var toolbar = $('<div class="btn-toolbar cms-markdown-toolbar" role="toolbar" style="margin-bottom:8px"></div>');
        var group = $('<div class="btn-group btn-group-sm"></div>');
        group.append(toolbarButton('H2', '二级标题', '## ', '', '标题'));
        group.append(toolbarButton('B', '粗体', '**', '**', '粗体文字'));
        group.append(toolbarButton('I', '斜体', '*', '*', '斜体文字'));
        group.append(toolbarButton('•', '无序列表', '- ', '', '列表项'));
        group.append(toolbarButton('1.', '有序列表', '1. ', '', '列表项'));
        group.append(toolbarButton('链接', '插入链接', '[', '](/path)', '链接文字'));
        group.append(toolbarButton('引用', '引用', '> ', '', '引用内容'));
        group.append(toolbarButton('代码', '行内代码', '`', '`', 'code'));
        toolbar.append(group);

        var mediaGroup = $('<div class="btn-group btn-group-sm"></div>');
        var hiddenId = id + '-image';
        var hidden = $('<input type="hidden" class="cms-markdown-image-value">').attr('id', hiddenId);
        var upload = $('<button type="button" class="btn btn-danger faupload"><i class="fa fa-upload"></i> 上传图片</button>')
            .attr('data-input-id', hiddenId).attr('data-mimetype', 'image/*').attr('data-multiple', 'false');
        var choose = $('<button type="button" class="btn btn-primary fachoose"><i class="fa fa-list"></i> 选择图片</button>')
            .attr('data-input-id', hiddenId).attr('data-mimetype', 'image/*').attr('data-multiple', 'false');
        mediaGroup.append(upload).append(choose);
        toolbar.append(mediaGroup).append(hidden);
        textarea.before(toolbar);

        toolbar.on('click', '.cms-md-tool', function () {
            var btn = $(this);
            insertText(textarea, String(btn.data('before') || ''), String(btn.data('after') || ''), String(btn.data('fallback') || ''));
        });
        hidden.on('change.cmsMarkdown input.cmsMarkdown', function () {
            var url = $.trim($(this).val() || '');
            if (!url) return;
            insertText(textarea, '![', '](' + url + ')', '图片');
            $(this).val('');
        });
    };

    var prepare = function (form) {
        form = $(form);
        form.find(SELECTOR).each(function () { prepareOne(this); });
        return form;
    };

    var bind = function (form) {
        form = $(form);
        form.find(SELECTOR).each(function () {
            $(this).attr('spellcheck', 'false');
        });
        return form;
    };

    return {prepare: prepare, bind: bind};
});
