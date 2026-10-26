define(['jquery'], function ($) {
    var FORM = 'form[data-page-content-block-schema-editor]';
    var FIELD = '[data-label-capability-rich-field]';

    var escapeHtml = function (value) {
        return $('<div></div>').text(String(value == null ? '' : value)).html();
    };

    var componentToHex = function (value) {
        var hex = Math.max(0, Math.min(255, parseInt(value, 10) || 0)).toString(16);
        return hex.length === 1 ? '0' + hex : hex;
    };

    var normalizeColor = function (value) {
        value = String(value || '').trim().toLowerCase();
        if (/^#[0-9a-f]{3}$/.test(value)) {
            return '#' + value[1] + value[1] + value[2] + value[2] + value[3] + value[3];
        }
        if (/^#[0-9a-f]{6}$/.test(value)) return value;
        var rgb = value.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/);
        if (rgb) return '#' + componentToHex(rgb[1]) + componentToHex(rgb[2]) + componentToHex(rgb[3]);
        return '';
    };

    var renderSource = function (source) {
        var html = escapeHtml(source);
        html = html.replace(/\[color=(#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?)\]([\s\S]*?)\[\/color\]/g, function (_, rawColor, body) {
            var color = normalizeColor(rawColor);
            return color ? '<span style="color:' + color + ';">' + body + '</span>' : body;
        });
        html = html.replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/__([^_\n]+)__/g, '<strong>$1</strong>');
        html = html.replace(/~~([^~\n]+)~~/g, '<s>$1</s>');
        html = html.replace(/(^|[^*])\*([^*\n]+)\*(?!\*)/g, '$1<em>$2</em>');
        html = html.replace(/(^|[^_])_([^_\n]+)_(?!_)/g, '$1<em>$2</em>');
        return html.replace(/\n/g, '<br>');
    };

    var nodeColor = function (node) {
        if (!node || node.nodeType !== 1) return '';
        var el = $(node);
        return normalizeColor(
            (node.style && node.style.color) ||
            el.attr('color') ||
            el.attr('data-label-capability-color-value') ||
            ''
        );
    };

    var serializeNode = function (node) {
        if (!node) return '';
        if (node.nodeType === 3) return node.nodeValue || '';
        if (node.nodeType !== 1) return '';

        var tag = String(node.tagName || '').toLowerCase();
        if (tag === 'br') return '\n';

        var content = '';
        $(node).contents().each(function () {
            content += serializeNode(this);
        });

        if (tag === 'strong' || tag === 'b') content = '**' + content + '**';
        else if (tag === 'em' || tag === 'i') content = '*' + content + '*';
        else if (tag === 's' || tag === 'strike' || tag === 'del') content = '~~' + content + '~~';

        var color = nodeColor(node);
        if (color && content !== '') content = '[color=' + color + ']' + content + '[/color]';

        if ((tag === 'div' || tag === 'p') && content.slice(-1) !== '\n') content += '\n';
        return content;
    };

    var serializeEditor = function (editor) {
        var out = '';
        editor.contents().each(function () {
            out += serializeNode(this);
        });
        return out.replace(/\n{3,}/g, '\n\n').replace(/\n$/, '');
    };

    var syncField = function (field) {
        field = $(field);
        var source = field.find('[data-label-capability-rich-source]').first();
        var editor = field.find('[data-label-capability-rich-editor]').first();
        if (!source.length || !editor.length) return;
        var value = serializeEditor(editor);
        if (String(source.val() || '') !== value) {
            source.val(value).trigger('input').trigger('change');
        }
    };

    var saveSelection = function (editor) {
        editor = $(editor);
        var el = editor.get(0);
        if (!el || !window.getSelection) return;
        var selection = window.getSelection();
        if (!selection || selection.rangeCount < 1) return;
        var range = selection.getRangeAt(0);
        var container = range.commonAncestorContainer;
        var owner = container.nodeType === 1 ? container : container.parentNode;
        if (container !== el && !$.contains(el, owner)) return;
        editor.data('label-capability-rich-range', range.cloneRange());
    };

    var restoreSelection = function (editor) {
        editor = $(editor);
        var range = editor.data('label-capability-rich-range');
        if (!range || !window.getSelection) return null;
        var selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        editor.focus();
        return range;
    };

    var refreshToolbar = function (field) {
        field = $(field);
        field.find('[data-label-capability-command]').each(function () {
            var button = $(this);
            var command = String(button.attr('data-label-capability-command') || '');
            var active = false;
            try { active = !!document.queryCommandState(command); } catch (e) {}
            button.toggleClass('active', active);
        });
    };

    var execCommand = function (field, command, value) {
        field = $(field);
        var editor = field.find('[data-label-capability-rich-editor]').first();
        var range = restoreSelection(editor);
        if (!range) return;
        try {
            document.execCommand('styleWithCSS', false, false);
            document.execCommand(command, false, value == null ? null : value);
        } catch (e) {}
        saveSelection(editor);
        syncField(field);
        refreshToolbar(field);
    };

    var buildUi = function (field) {
        field = $(field);
        var source = field.find('[data-label-capability-rich-source]').first();
        var wrapper = field.find('[data-label-capability-rich-wrapper]').first();
        if (!source.length || !wrapper.length) return $();

        wrapper
            .addClass('cms-label-capability-richbox')
            .empty()
            .append(
                '<div class="cms-label-capability-rich-toolbar" data-label-capability-rich-toolbar>' +
                    '<div class="btn-group btn-group-sm" role="group">' +
                        '<button type="button" class="btn btn-default" data-label-capability-command="bold" title="加粗"><strong>B</strong></button>' +
                        '<button type="button" class="btn btn-default" data-label-capability-command="italic" title="斜体"><em>I</em></button>' +
                        '<button type="button" class="btn btn-default" data-label-capability-command="strikeThrough" title="删除线"><s>S</s></button>' +
                    '</div>' +
                    '<label class="cms-label-capability-rich-color-wrap" title="选中文字后修改字体颜色">' +
                        '<i class="fa fa-font"></i><span>文字颜色</span>' +
                        '<input type="color" class="cms-label-capability-rich-color" data-label-capability-rich-color value="#e25042">' +
                    '</label>' +
                    '<div class="btn-group btn-group-sm" role="group">' +
                        '<button type="button" class="btn btn-default" data-label-capability-clear-format title="清除选中文字格式"><i class="fa fa-eraser"></i> 清除格式</button>' +
                        '<button type="button" class="btn btn-default" data-label-capability-command="undo" title="撤销"><i class="fa fa-undo"></i></button>' +
                        '<button type="button" class="btn btn-default" data-label-capability-command="redo" title="重做"><i class="fa fa-repeat"></i></button>' +
                    '</div>' +
                '</div>' +
                '<div class="form-control cms-label-capability-rich-editor" contenteditable="true" spellcheck="false" data-label-capability-rich-editor></div>' +
                '<div class="cms-label-capability-rich-footer">富文本编辑：选中部分文字后，可直接设置加粗、斜体、删除线或文字颜色。</div>'
            );

        var editor = wrapper.find('[data-label-capability-rich-editor]').first();
        editor.html(renderSource(source.val() || ''));
        source.hide();
        wrapper.show();
        return editor;
    };

    var initField = function (field) {
        field = $(field);
        var source = field.find('[data-label-capability-rich-source]').first();
        if (!source.length || source.data('label-capability-rich-v2-ready')) return;
        var editor = buildUi(field);
        if (!editor.length) return;

        source.data('label-capability-rich-v2-ready', true);
        editor.on('mouseup.labelCapabilityRich keyup.labelCapabilityRich focus.labelCapabilityRich', function () {
            saveSelection(editor);
            refreshToolbar(field);
        });
        editor.on('input.labelCapabilityRich blur.labelCapabilityRich', function () {
            syncField(field);
            saveSelection(editor);
            refreshToolbar(field);
        });
    };

    var initAll = function (form) {
        form.find(FIELD).each(function () { initField(this); });
    };

    var bind = function (form) {
        form = $(form || FORM);
        if (!form.length) return form;
        var blockKey = String(form.find('[name="row[block_key]"]').first().val() || '');
        if (blockKey !== 'label_capability') return form;

        initAll(form);
        form.off('.labelCapabilityRichV2');

        form.on('mousedown.labelCapabilityRichV2', '[data-label-capability-command],[data-label-capability-clear-format]', function (event) {
            event.preventDefault();
            var field = $(this).closest(FIELD);
            saveSelection(field.find('[data-label-capability-rich-editor]').first());
        });

        form.on('click.labelCapabilityRichV2', '[data-label-capability-command]', function () {
            var button = $(this);
            execCommand(button.closest(FIELD), button.attr('data-label-capability-command'));
        });

        form.on('mousedown.labelCapabilityRichV2', '[data-label-capability-rich-color]', function () {
            var field = $(this).closest(FIELD);
            saveSelection(field.find('[data-label-capability-rich-editor]').first());
        });

        form.on('change.labelCapabilityRichV2', '[data-label-capability-rich-color]', function () {
            var input = $(this);
            var color = normalizeColor(input.val()) || '#e25042';
            execCommand(input.closest(FIELD), 'foreColor', color);
        });

        form.on('click.labelCapabilityRichV2', '[data-label-capability-clear-format]', function () {
            execCommand($(this).closest(FIELD), 'removeFormat');
        });

        form.on('click.labelCapabilityRichV2', '[data-label-item-add]', function () {
            window.setTimeout(function () { initAll(form); }, 0);
        });

        form.on('submit.labelCapabilityRichV2', function () {
            form.find(FIELD).each(function () { syncField(this); });
        });
        return form;
    };

    return {bind: bind};
});
