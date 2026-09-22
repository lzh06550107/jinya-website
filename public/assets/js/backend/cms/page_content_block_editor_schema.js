define(['jquery','backend/cms/icon_picker'], function ($, IconPicker) {
    var ROOT = '[data-page-content-block-schema-editor]';
    var PAGE_EDITORS = {
        label: '[data-label-block-editor]',
        bags: '[data-bags-block-editor]',
        boxes: '[data-boxes-block-editor]',
        about: '[data-about-block-editor]',
        contact: '[data-contact-block-editor]'
    };

    var EDITABLE_UNIT_OPTIONS = ['m²','㎡','万+','条','小时','台','个','人','年','%','家','套','吨','件/天'];

    var ensureEditableUnitCombobox = function (input) {
        input = $(input);
        if (!input.length || !input.is('input')) return;
        if (input.closest('[data-metric-unit-combobox]').length) {
            input.attr('data-metric-field', 'unit');
            return;
        }
        input.attr('data-metric-field', 'unit')
            .attr('placeholder', '可输入或选择单位');
        input.wrap('<div class="input-group" data-metric-unit-combobox></div>');
        var group = input.parent();
        var button = $('<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" data-metric-unit-toggle title="选择常用单位" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>');
        var menu = $('<ul class="dropdown-menu dropdown-menu-right"></ul>');
        $.each(EDITABLE_UNIT_OPTIONS, function (_, unit) {
            menu.append($('<li></li>').append($('<a href="javascript:;"></a>').attr('data-metric-unit-option', unit).text(unit)));
        });
        group.append($('<span class="input-group-btn"></span>').append(button).append(menu));
    };

    var snapshotInlineColorSelection = function (textarea) {
        textarea = $(textarea);
        var el = textarea.get(0);
        if (!el || typeof el.selectionStart !== 'number' || typeof el.selectionEnd !== 'number') return;
        textarea.attr('data-inline-color-start', el.selectionStart);
        textarea.attr('data-inline-color-end', el.selectionEnd);
    };

    var applyInlineColor = function (textarea, toolbar) {
        textarea = $(textarea);
        toolbar = $(toolbar);
        var el = textarea.get(0);
        if (!el) return;
        var value = String(textarea.val() || '');
        var start = parseInt(textarea.attr('data-inline-color-start'), 10);
        var end = parseInt(textarea.attr('data-inline-color-end'), 10);
        if (isNaN(start) || isNaN(end)) {
            start = typeof el.selectionStart === 'number' ? el.selectionStart : value.length;
            end = typeof el.selectionEnd === 'number' ? el.selectionEnd : start;
        }
        start = Math.max(0, Math.min(start, value.length));
        end = Math.max(start, Math.min(end, value.length));
        if (end <= start) {
            el.focus();
            return;
        }
        var color = String(toolbar.find('[data-inline-color-picker]').val() || '#e25042').toLowerCase();
        if (!/^#[0-9a-f]{6}$/.test(color)) color = '#e25042';
        var selected = value.substring(start, end);
        var before = '[color=' + color + ']';
        var after = '[/color]';
        var replacement = before + selected + after;
        textarea.val(value.substring(0, start) + replacement + value.substring(end)).trigger('input').trigger('change');
        var selectedStart = start + before.length;
        var selectedEnd = selectedStart + selected.length;
        textarea.attr('data-inline-color-start', selectedStart).attr('data-inline-color-end', selectedEnd);
        el.focus();
        if (typeof el.setSelectionRange === 'function') el.setSelectionRange(selectedStart, selectedEnd);
    };

    var ensureInlineColorToolbar = function (textarea) {
        textarea = $(textarea);
        if (!textarea.is('textarea')) return;
        textarea.attr('data-inline-color-enabled', '1');
        var toolbar = textarea.siblings('[data-inline-color-toolbar]').first();
        if (toolbar.length) return;
        toolbar = $('<div class="form-inline" data-inline-color-toolbar style="margin-top:6px;"></div>');
        toolbar.append('<span class="text-muted" style="margin-right:6px;">局部文字颜色</span>');
        toolbar.append('<input type="color" class="form-control" data-inline-color-picker value="#e25042" title="选择文字颜色" style="width:54px;height:30px;padding:2px 5px;margin-right:6px;">');
        toolbar.append('<button type="button" class="btn btn-default btn-sm" data-inline-color-apply><i class="fa fa-paint-brush"></i> 应用到选中文字</button>');
        toolbar.append('<span class="help-block" style="display:inline;margin-left:8px;">先在说明框中选中文字，再选择颜色并点击应用；同一段可设置多种颜色。</span>');
        textarea.after(toolbar);
        snapshotInlineColorSelection(textarea);
    };

    var toMap = function (items) {
        var map = {};
        $.each(items || [], function (_, key) { map[String(key)] = true; });
        return map;
    };

    var setVisible = function (el, visible) {
        el = $(el);
        if (!el.length) return;
        el.toggle(!!visible);
        el.attr('data-schema-visible', visible ? '1' : '0');
    };

    var labelText = function (container, text) {
        if (!text) return;
        var label = $(container).find('label').first();
        if (label.length) label.text(text + ':');
    };

    var fieldHelp = function (container, text) {
        container = $(container);
        var help = container.find('[data-schema-field-help]').first();
        if (!text) {
            if (help.length) help.remove();
            return;
        }
        if (!help.length) {
            help = $('<p class="help-block" data-schema-field-help></p>');
            var target = container.find('input,textarea,select').last().parent();
            (target.length ? target : container).append(help);
        }
        help.text(text);
    };

    var scalarContainer = function (input) {
        input = $(input);
        var group = input.closest('.form-group');
        if (!group.length) return input;
        var col = group.parent();
        if (col.is('[class*="col-sm-"]') && col.parent().hasClass('row')) return col;
        return group;
    };

    var itemColumn = function (input) {
        var col = $(input).closest('[class*="col-sm-"]');
        return col.length ? col : $(input);
    };

    var normalizeColumns = function (row) {
        row = $(row);
        var cols = row.children('[class*="col-sm-"]');
        if (!cols.length) return;
        var visible = cols.filter(function () { return $(this).css('display') !== 'none'; });
        var count = visible.length;
        if (!count) return;
        var width = count === 1 ? 12 : (count === 2 ? 6 : (count === 3 ? 4 : 3));
        visible.each(function () {
            var col = $(this);
            var cls = String(col.attr('class') || '').replace(/\bcol-sm-\d+\b/g, '').replace(/\s+/g, ' ').trim();
            col.attr('class', (cls ? cls + ' ' : '') + 'col-sm-' + width);
        });
    };

    var ensureGroupSelect = function (input, groups, applyItem) {
        input = $(input);
        if (!input.length || !groups || $.isEmptyObject(groups)) return;
        var current = String(input.val() == null ? '' : input.val());
        var select = input.siblings('select[data-schema-group-select]').first();
        if (!select.length) {
            select = $('<select class="form-control" data-schema-group-select></select>');
            input.after(select);
            input.hide().attr('data-schema-preserved-input', '1');
            select.on('change.pageContentBlockSchema', function () {
                input.val($(this).val());
                applyItem();
            });
        }
        select.empty();
        $.each(groups, function (value, text) {
            select.append($('<option></option>').attr('value', value).text(text));
        });
        if (!Object.prototype.hasOwnProperty.call(groups, current)) {
            select.append($('<option></option>').attr('value', current).text(current || '普通/默认'));
        }
        select.val(current);
    };

    var protectContract = function (form, schema) {
        var key = form.find('[name="row[block_key]"]').first();
        key.prop('readonly', true).attr('aria-readonly', 'true');
        var keyHelp = key.siblings('.help-block');
        if (keyHelp.length) keyHelp.text('固定功能块标识，由页面模板使用；为避免前台区块失效，不允许修改。');

        var type = form.find('[name="row[block_type]"]').first();
        if (type.length) {
            var expected = String(schema.block_type || type.val() || '');
            type.attr('aria-readonly', 'true').attr('data-schema-fixed-value', expected);
            type.off('.pageContentBlockSchemaLock').on('change.pageContentBlockSchemaLock', function () {
                if (String(type.val() || '') !== expected) {
                    type.val(expected);
                    if ($.fn.selectpicker && type.hasClass('selectpicker')) type.selectpicker('refresh');
                }
            });
            var picker = type.parent().find('.bootstrap-select');
            picker.css('pointer-events', 'none').attr('aria-disabled', 'true');
            var help = type.parent().find('.help-block[data-schema-type-help]');
            if (!help.length) {
                help = $('<p class="help-block" data-schema-type-help></p>').appendTo(type.parent());
            }
            help.text('固定类型：' + expected + '。该类型与当前页面模板绑定。');
        }
    };

    var applyBase = function (form, schema) {
        var allowed = toMap(schema.base_fields);
        form.find('[data-page-content-block-base-field]').each(function () {
            var row = $(this), field = String(row.attr('data-page-content-block-base-field') || '');
            if (field === 'block_key' || field === 'block_type') {
                setVisible(row, true);
                return;
            }
            var visible = field === 'link'
                ? !!(allowed.link || allowed.link_text || allowed.link_url)
                : !!allowed[field];
            setVisible(row, visible);
            if (visible && schema.base_labels && schema.base_labels[field]) {
                labelText(row, schema.base_labels[field]);
            }
            fieldHelp(row, visible && schema.base_help ? schema.base_help[field] : '');
        });
    };

    var applyScalars = function (editor, schema) {
        var allowed = toMap(schema.scalar_fields);
        editor.find('input[name],textarea[name],select[name]').each(function () {
            var input = $(this), name = String(input.attr('name') || '');
            var match = /^row\[((?:label|bags|boxes|about|contact)_[^\]]+)\]$/.exec(name);
            if (!match || /_items$/.test(match[1])) return;
            var field = match[1], container = scalarContainer(input);
            var visible = !!allowed[field];
            setVisible(container, visible);
            if (visible && schema.scalar_labels && schema.scalar_labels[field]) {
                labelText(container, schema.scalar_labels[field]);
            }
            fieldHelp(container, visible && schema.scalar_help ? schema.scalar_help[field] : '');
        });
    };

    var activeItemFields = function (schema, item, page) {
        var fields = schema.item_fields || [];
        var overrides = schema.group_item_fields || {};
        if ($.isEmptyObject(overrides)) return fields;
        var groupInput = item.find('[data-' + page + '-field="group"]').first();
        var group = String(groupInput.val() == null ? '' : groupInput.val());
        return Object.prototype.hasOwnProperty.call(overrides, group) ? overrides[group] : fields;
    };

    var itemFieldContainer = function (item, page, field) {
        var input = item.find('[data-' + page + '-field="' + field + '"]').first();
        if (!input.length) return $();
        var cell = input.closest('[data-schema-field-cell]');
        if (cell.length) return cell;
        cell = itemColumn(input);
        cell.attr('data-schema-field-cell', field);
        return cell;
    };

    var hideOriginalFieldLabels = function (cell) {
        cell = $(cell);
        cell.children('label').not('.checkbox-inline').hide().attr('data-schema-original-label', '1');
    };

    var mobileInheritanceLabel = function (text) {
        text = String(text || '移动端设置');
        return text.indexOf('留空继承 PC') === -1 ? text + '（留空继承 PC）' : text;
    };

    var itemDisplayLabel = function (schema, field, paired) {
        var labels = schema.item_labels || {};
        var text = labels[field] || field;
        if (/^mobile_/.test(field)) return mobileInheritanceLabel(text);
        if (paired && ['title','text','subtitle','image','image_top','image_bottom','badge','url'].indexOf(field) !== -1 && text.indexOf('PC ') !== 0) {
            return 'PC ' + text;
        }
        return text;
    };

    var cleanFieldCell = function (cell, width) {
        cell = $(cell);
        hideOriginalFieldLabels(cell);
        var cls = String(cell.attr('class') || '')
            .replace(/\bcol-(?:xs|sm|md|lg)-\d+\b/g, '')
            .replace(/\s+/g, ' ').trim();
        cell.attr('class', (cls ? cls + ' ' : '') + 'col-xs-12 col-sm-' + width);
        cell.show();
        return cell;
    };

    var appendSingleItemRow = function (layout, schema, field, cell) {
        if (!cell || !cell.length || cell.css('display') === 'none') return false;
        var row = $('<div class="form-group cms-schema-item-row"></div>').attr('data-schema-item-row', field);
        row.append($('<label class="control-label col-xs-12 col-sm-2"></label>').text(itemDisplayLabel(schema, field, false) + ':'));
        row.append(cleanFieldCell(cell, 10));
        layout.append(row);
        return true;
    };

    var appendPairedItemRow = function (layout, schema, leftField, leftCell, rightField, rightCell) {
        var leftVisible = leftCell && leftCell.length && leftCell.css('display') !== 'none';
        var rightVisible = rightCell && rightCell.length && rightCell.css('display') !== 'none';
        if (!leftVisible && !rightVisible) return false;
        if (!leftVisible || !rightVisible) {
            return appendSingleItemRow(layout, schema, leftVisible ? leftField : rightField, leftVisible ? leftCell : rightCell);
        }
        var row = $('<div class="form-group cms-schema-item-row cms-schema-item-row-paired"></div>')
            .attr('data-schema-item-row', leftField + '+' + rightField);
        row.append($('<label class="control-label col-xs-12 col-sm-2"></label>').text(itemDisplayLabel(schema, leftField, true) + ':'));
        row.append(cleanFieldCell(leftCell, 4));
        row.append($('<label class="control-label col-xs-12 col-sm-2"></label>').text(itemDisplayLabel(schema, rightField, true) + ':'));
        row.append(cleanFieldCell(rightCell, 4));
        layout.append(row);
        return true;
    };

    var setCheckboxCaption = function (cell, caption) {
        var label = $(cell).find('label.checkbox-inline').first();
        if (!label.length) return;
        label.contents().filter(function () { return this.nodeType === 3; }).remove();
        label.append(document.createTextNode(' ' + caption));
    };

    var appendTerminalRow = function (layout, pcCell, mobileCell) {
        var pcVisible = pcCell && pcCell.length && pcCell.css('display') !== 'none';
        var mobileVisible = mobileCell && mobileCell.length && mobileCell.css('display') !== 'none';
        if (!pcVisible && !mobileVisible) return false;
        var row = $('<div class="form-group cms-schema-item-row cms-schema-terminal-row"></div>').attr('data-schema-item-row', 'terminal');
        row.append('<label class="control-label col-xs-12 col-sm-2">终端显示:</label>');
        if (pcVisible) {
            setCheckboxCaption(pcCell, 'PC 显示');
            row.append(cleanFieldCell(pcCell, 4));
        }
        if (mobileVisible) {
            setCheckboxCaption(mobileCell, '移动端显示');
            row.append(cleanFieldCell(mobileCell, 4));
        }
        layout.append(row);
        return true;
    };

    var reflowItem = function (item, schema, page) {
        item = $(item);
        var body = item.children('.panel-body').first();
        if (!body.length) return;

        var preserved = body.children('[data-schema-preserved-fields]').first();
        if (!preserved.length) {
            preserved = $('<div data-schema-preserved-fields class="cms-schema-preserved-fields"></div>').appendTo(body);
        }

        var seen = [];
        item.find('[data-' + page + '-field]').each(function () {
            var cell = itemFieldContainer(item, page, String($(this).attr('data-' + page + '-field') || ''));
            if (!cell.length || seen.indexOf(cell.get(0)) !== -1) return;
            seen.push(cell.get(0));
            cell.detach().appendTo(preserved);
        });

        body.children('[data-schema-item-layout]').remove();
        body.children('.row').filter(function () { return $(this).find('[data-' + page + '-field]').length === 0; }).remove();

        var layout = $('<div class="cms-schema-item-layout" data-schema-item-layout></div>');
        preserved.before(layout);
        var allowed = toMap(activeItemFields(schema, item, page));
        var used = {};
        var cellFor = function (field) {
            if (!allowed[field]) return $();
            return itemFieldContainer(item, page, field);
        };

        if (allowed.group) {
            appendSingleItemRow(layout, schema, 'group', cellFor('group'));
            used.group = true;
        }

        var pairedRows = schema.item_pair_rows || [
            ['title','mobile_title'],
            ['text','mobile_text'],
            ['subtitle','mobile_subtitle'],
            ['image','mobile_image'],
            ['image_top','mobile_image_top'],
            ['image_bottom','mobile_image_bottom'],
            ['badge','mobile_badge'],
            ['url','mobile_url']
        ];
        var pairMap = {}, pairRight = {};
        $.each(pairedRows, function (_, pair) {
            pairMap[pair[0]] = pair[1];
            pairRight[pair[1]] = pair[0];
        });

        $.each(schema.item_order || [], function (_, field) {
            if (used[field] || field === 'group' || field === 'pc_visible' || field === 'mobile_visible') return;
            if (pairMap[field]) {
                var right = pairMap[field];
                if (appendPairedItemRow(layout, schema, field, cellFor(field), right, cellFor(right))) {
                    used[field] = true;
                    used[right] = true;
                }
                return;
            }
            if (pairRight[field]) return;
            if (!allowed[field]) return;
            if (appendSingleItemRow(layout, schema, field, cellFor(field))) used[field] = true;
        });

        appendTerminalRow(layout, cellFor('pc_visible'), cellFor('mobile_visible'));
        preserved.hide();
    };

    var updateItemHeading = function (item, schema, page, index) {
        item = $(item);
        var label = item.find('[data-' + page + '-item-label]').first();
        if (!label.length) return;
        var segments = [(schema.item_noun || '项目') + ' #' + (index + 1)];
        var groupInput = item.find('[data-' + page + '-field="group"]').first();
        var groupValue = String(groupInput.val() == null ? '' : groupInput.val());
        if (groupValue && schema.groups && Object.prototype.hasOwnProperty.call(schema.groups, groupValue)) {
            segments.push(schema.groups[groupValue]);
        }
        var titleInput = item.find('[data-' + page + '-field="title"]').filter(':input').first();
        var title = $.trim(String(titleInput.val() || ''));
        if (!title) {
            var valueInput = item.find('[data-' + page + '-field="value"]').filter(':input').first();
            title = $.trim(String(valueInput.val() || ''));
        }
        if (title) segments.push(title);
        label.text(segments.join(' · '));
    };

    var applyOneItem = function (item, schema, page) {
        item = $(item);
        var allowed = toMap(activeItemFields(schema, item, page));
        var iconFields = toMap(schema.item_icon_fields || []);
        var editableUnitFields = toMap(schema.item_editable_unit_fields || []);
        var inlineColorFields = toMap(schema.item_inline_color_fields || []);
        item.find('[data-' + page + '-field]').each(function () {
            var input = $(this), field = String(input.attr('data-' + page + '-field') || '');
            var col = itemColumn(input), visible = !!allowed[field];
            setVisible(col, visible);
            if (input.is('input:not([type="hidden"])')) {
                if (visible && iconFields[field]) {
                    input.attr('data-cms-icon-picker', 'true')
                        .attr('placeholder', '优先从图标库选择；没有合适图标时再上传图片图标');
                } else {
                    input.removeAttr('data-cms-icon-picker');
                }
            }
            if (visible && schema.item_labels && schema.item_labels[field]) {
                labelText(col, schema.item_labels[field]);
            }
            if (visible && editableUnitFields[field]) {
                ensureEditableUnitCombobox(input);
            }
            if (visible && inlineColorFields[field]) {
                ensureInlineColorToolbar(input);
            } else if (input.is('textarea')) {
                input.removeAttr('data-inline-color-enabled data-inline-color-start data-inline-color-end');
                input.siblings('[data-inline-color-toolbar]').remove();
            }
            var helpText = visible && schema.item_help ? (schema.item_help[field] || '') : '';
            if (visible && iconFields[field]) {
                helpText = field === 'mobile_badge'
                    ? '点击“选择图标”从首页同款图标库选择；留空时移动端沿用 PC 图标。'
                    : '点击“选择图标”从首页同款 Font Awesome / 图片图标库选择。';
            }
            fieldHelp(col, helpText);
        });

        if (allowed.group && schema.groups && !$.isEmptyObject(schema.groups)) {
            var groupInput = item.find('[data-' + page + '-field="group"]').filter('input,textarea').first();
            ensureGroupSelect(groupInput, schema.groups, function () {
                applyOneItem(item, schema, page);
                IconPicker.refresh(item.closest(ROOT));
            });
        }
        reflowItem(item, schema, page);
        updateItemHeading(item, schema, page, item.index());
    };

    var applyItems = function (editor, schema, page) {
        var list = editor.find('[data-' + page + '-items-list]');
        var add = editor.find('[data-' + page + '-item-add]');
        var section = editor.find('[data-structured-section="items"]').first();
        var heading = section.find('h4').first();
        var visible = !!schema.has_items;
        if (heading.length) heading.text(schema.item_section_label || '功能项目');
        setVisible(list, visible);
        setVisible(add, visible);
        if (!visible) return;
        add.html('<i class="fa fa-plus"></i> ' + (schema.item_add_label || '添加项目'));
        list.children('[data-' + page + '-item]').each(function (index) {
            applyOneItem(this, schema, page);
            updateItemHeading(this, schema, page, index);
        });
    };

    var updateSummary = function (form, schema) {
        var summary = form.find('[data-page-content-block-summary]').first();
        if (!summary.length) return;
        summary.find('[data-page-content-block-summary-page]').text(schema.page_label || '结构化页面');
        summary.find('[data-page-content-block-summary-block]').text(schema.label || '功能块');
    };

    var refreshSections = function (form) {
        form.find('[data-structured-section]').each(function () {
            var header = $(this);
            var name = String(header.attr('data-structured-section') || '');
            if (!name) return;
            var body = header.nextAll('[data-structured-section-body="' + name + '"]').first();
            if (!body.length) return;
            var hasVisibleControl = body.find('input:visible:not([type="hidden"]),textarea:visible,select:visible,button:visible').length > 0;
            header.toggle(hasVisibleControl);
            body.toggle(hasVisibleControl);
        });
    };

    var apply = function (form) {
        form = $(form);
        var schemas = (window.Config && Config.pageContentBlockEditorSchemas) || {};
        var blockKey = String(form.find('[name="row[block_key]"]').first().val() || '');
        var schema = schemas[blockKey];
        if (!schema) return form;

        updateSummary(form, schema);
        protectContract(form, schema);
        applyBase(form, schema);
        form.find('[data-page-content-block-technical-fields]').hide();

        $.each(PAGE_EDITORS, function (page, selector) {
            var editor = form.find(selector);
            if (!editor.length || page !== schema.page) return;
            var alert = editor.find('.alert-info').first();
            if (alert.length) alert.text(schema.help || ('当前编辑：' + schema.label));
            applyScalars(editor, schema);
            applyItems(editor, schema, page);
        });
        IconPicker.refresh(form);
        refreshSections(form);
        return form;
    };

    var bind = function (form) {
        form = $(form || ROOT);
        if (!form.length) return form;
        form.off('.pageContentBlockSchema');
        form.on('select.pageContentBlockSchema keyup.pageContentBlockSchema mouseup.pageContentBlockSchema', '[data-inline-color-enabled]', function () {
            snapshotInlineColorSelection(this);
        });
        form.on('click.pageContentBlockSchema', '[data-inline-color-apply]', function () {
            var toolbar = $(this).closest('[data-inline-color-toolbar]');
            var textarea = toolbar.siblings('textarea[data-inline-color-enabled]').first();
            applyInlineColor(textarea, toolbar);
        });
        form.on('click.pageContentBlockSchema', '[data-label-item-add],[data-bags-item-add],[data-boxes-item-add],[data-about-item-add],[data-contact-item-add]', function () {
            window.setTimeout(function () { apply(form); }, 0);
        });
        form.on('click.pageContentBlockSchema', '[data-label-item-up],[data-label-item-down],[data-label-item-remove],[data-bags-item-up],[data-bags-item-down],[data-bags-item-remove],[data-boxes-item-up],[data-boxes-item-down],[data-boxes-item-remove],[data-about-item-up],[data-about-item-down],[data-about-item-remove],[data-contact-item-up],[data-contact-item-down],[data-contact-item-remove]', function () {
            window.setTimeout(function () { apply(form); }, 0);
        });
        form.on('input.pageContentBlockSchema change.pageContentBlockSchema', '[data-label-field],[data-bags-field],[data-boxes-field],[data-about-field],[data-contact-field]', function () {
            var input = $(this), item = input.closest('[data-label-item],[data-bags-item],[data-boxes-item],[data-about-item],[data-contact-item]');
            if (!item.length) return;
            var schemas = (window.Config && Config.pageContentBlockEditorSchemas) || {};
            var blockKey = String(form.find('[name="row[block_key]"]').first().val() || '');
            var schema = schemas[blockKey];
            if (!schema) return;
            updateItemHeading(item, schema, schema.page, item.index());
        });
        apply(form);
        return form;
    };

    return {bind: bind, apply: apply};
});
