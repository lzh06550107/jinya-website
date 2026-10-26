define(['jquery'], function ($) {
    var FORM = 'form[data-page-content-block-schema-editor]';
    var FIELD = '[data-label-capability-rich-field]';

    var escapeHtml = function (value) {
        return $('<div></div>').text(String(value == null ? '' : value)).html();
    };

    var normalizeColor = function (value) {
        value = String(value || '').toLowerCase();
        if (/^#[0-9a-f]{3}$/.test(value)) {
            return '#' + value[1] + value[1] + value[2] + value[2] + value[3] + value[3];
        }
        return /^#[0-9a-f]{6}$/.test(value) ? value : '';
    };

    var renderSource = function (source) {
        var html = escapeHtml(source);
        html = html.replace(/\[color=(#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?)\]([\s\S]*?)\[\/color\]/g, function (_, rawColor, body) {
            var color = normalizeColor(rawColor);
            return color
                ? '<span data-label-capability-color-value="' + color + '" style="color:' + color + ';">' + body + '</span>'
                : body;
        });
        return html.replace(/\n/g, '<br>');
    };

    var serializeNode = function (node, inheritedColor) {
        if (!node) return '';
        if (node.nodeType === 3) {
            var text = node.nodeValue || '';
            return inheritedColor && text !== ''
                ? '[color=' + inheritedColor + ']' + text + '[/color]'
                : text;
        }
        if (node.nodeType !== 1) return '';

        var tag = String(node.tagName || '').toLowerCase();
        if (tag === 'br') return '\n';

        var color = inheritedColor || '';
        var ownColor = normalizeColor($(node).attr('data-label-capability-color-value'));
        if (ownColor) color = ownColor;
        if ($(node).attr('data-label-capability-color-reset') === '1') color = '';

        var out = '';
        $(node).contents().each(function () {
            out += serializeNode(this, color);
        });
        if ((tag === 'div' || tag === 'p') && out.slice(-1) !== '\n') out += '\n';
        return out;
    };

    var serializeEditor = function (editor) {
        var out = '';
        editor.contents().each(function () {
            out += serializeNode(this, '');
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
        return range;
    };

    var applyColor = function (field) {
        field = $(field);
        var editor = field.find('[data-label-capability-rich-editor]').first();
        var range = restoreSelection(editor);
        if (!range || range.collapsed) {
            editor.focus();
            return;
        }
        var color = normalizeColor(field.find('[data-label-capability-color]').val()) || '#e25042';
        var span = document.createElement('span');
        span.setAttribute('data-label-capability-color-value', color);
        span.style.color = color;
        span.appendChild(range.extractContents());
        range.insertNode(span);
        range.selectNodeContents(span);
        var selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        editor.data('label-capability-rich-range', range.cloneRange());
        syncField(field);
    };

    var clearColor = function (field) {
        field = $(field);
        var editor = field.find('[data-label-capability-rich-editor]').first();
        var range = restoreSelection(editor);
        if (!range || range.collapsed) {
            editor.focus();
            return;
        }
        var span = document.createElement('span');
        span.setAttribute('data-label-capability-color-reset', '1');
        span.style.color = '#333';
        span.appendChild(range.extractContents());
        range.insertNode(span);
        range.selectNodeContents(span);
        var selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        editor.data('label-capability-rich-range', range.cloneRange());
        syncField(field);
    };

    var initField = function (field) {
        field = $(field);
        var source = field.find('[data-label-capability-rich-source]').first();
        var wrapper = field.find('[data-label-capability-rich-wrapper]').first();
        var editor = field.find('[data-label-capability-rich-editor]').first();
        if (!source.length || !wrapper.length || !editor.length) return;
        if (source.data('label-capability-rich-ready')) return;

        editor.html(renderSource(source.val() || ''));
        source.data('label-capability-rich-ready', true).hide();
        wrapper.show();

        editor.on('mouseup.labelCapabilityRich keyup.labelCapabilityRich focus.labelCapabilityRich', function () {
            saveSelection(editor);
        });
        editor.on('input.labelCapabilityRich blur.labelCapabilityRich', function () {
            syncField(field);
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
        form.off('.labelCapabilityRichV3');

        form.on('mouseup.labelCapabilityRichV3 keyup.labelCapabilityRichV3', '[data-label-capability-rich-editor]', function () {
            saveSelection(this);
        });
        form.on('mousedown.labelCapabilityRichV3', '[data-label-capability-apply-color],[data-label-capability-clear-color]', function (event) {
            event.preventDefault();
        });
        form.on('click.labelCapabilityRichV3', '[data-label-capability-apply-color]', function () {
            applyColor($(this).closest(FIELD));
        });
        form.on('click.labelCapabilityRichV3', '[data-label-capability-clear-color]', function () {
            clearColor($(this).closest(FIELD));
        });
        form.on('click.labelCapabilityRichV3', '[data-label-item-add]', function () {
            window.setTimeout(function () { initAll(form); }, 0);
        });
        form.on('submit.labelCapabilityRichV3', function () {
            form.find(FIELD).each(function () { syncField(this); });
        });
        return form;
    };

    return {bind: bind};
});
