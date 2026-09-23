define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/cms/media_preview', 'backend/cms/icon_picker'], function ($, undefined, Backend, Table, Form, MediaPreview, IconPicker) {
    var escapeHtml = function (value) {
        return $('<div>').text(value == null ? '' : value).html();
    };

    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'cms/layout_component/index',
                    edit_url: 'cms/layout_component/edit'
                }
            });
            var table = $('#table');
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'asc',
                columns: [[
                    {field: 'id', title: 'ID', operate: false},
                    {field: 'component_name', title: '组件名称', operate: 'LIKE'},
                    {field: 'component_key', title: '组件标识', operate: 'LIKE'},
                    {field: 'component_type_text', title: '类型', operate: false},
                    {field: 'device_text', title: '终端', operate: false},
                    {field: 'affected_page_count', title: '引用页面数', operate: false},
                    {field: 'version', title: '版本', operate: false},
                    {field: 'updatetime', title: '最后修改', formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange'},
                    {
                        field: 'operate', title: '操作', table: table,
                        events: Table.api.events.operate,
                        formatter: function (value, row) {
                            return '<a href="cms/layout_component/edit?ids=' + row.id + '" class="btn btn-xs btn-success btn-dialog" title="编辑公共布局"><i class="fa fa-pencil"></i></a>';
                        }
                    }
                ]]
            });
            Table.api.bindevent(table);
        },

        edit: function () {
            var form = $('#layout-component-form');
            var socialRowSequence = 0;

            var tableFor = function (type) {
                return form.find('table[data-editor-list="' + type + '"]');
            };

            var bindLogoAspectRatio = function () {
                var logoInput = form.find('#layout-site-cms_logo');
                var topWidthInput = form.find('[name="row[config][logo_top_width]"]');
                var scrolledWidthInput = form.find('[name="row[config][logo_scrolled_width]"]');
                if (!logoInput.length || (!topWidthInput.length && !scrolledWidthInput.length)) {
                    return;
                }

                var defaultRatio = 212 / 45;
                var currentRatio = defaultRatio;
                var currentDimensions = {width: 212, height: 45, fallback: true};

                var updateReadOnlyHeights = function () {
                    var updateOne = function (input, state) {
                        if (!input.length) {
                            return;
                        }
                        var width = parseFloat(input.val());
                        if (!width || width <= 0 || !currentRatio) {
                            form.find('[data-logo-auto-height="' + state + '"]').val('自动');
                            return;
                        }
                        var height = Math.round((width / currentRatio) * 10) / 10;
                        form.find('[data-logo-auto-height="' + state + '"]').val(height + ' px');
                    };
                    updateOne(topWidthInput, 'top');
                    updateOne(scrolledWidthInput, 'scrolled');
                    var ratioText = currentDimensions.fallback
                        ? '默认参考比例：212:45；上传 Logo 后按原图比例自动更新'
                        : '当前 Logo：' + currentDimensions.width + ' × ' + currentDimensions.height + ' px；前台强制保持原图比例';
                    form.find('[data-logo-ratio-summary]').text(ratioText);
                };

                var resolveLogoUrl = function (value) {
                    value = $.trim(value || '');
                    if (!value) {
                        return '';
                    }
                    try {
                        return Fast.api.cdnurl(value);
                    } catch (e) {
                        return value;
                    }
                };

                var refreshRatioFromLogo = function () {
                    var url = resolveLogoUrl(logoInput.val());
                    if (!url) {
                        currentRatio = defaultRatio;
                        currentDimensions = {width: 212, height: 45, fallback: true};
                        updateReadOnlyHeights();
                        return;
                    }
                    var image = new Image();
                    image.onload = function () {
                        if (image.naturalWidth > 0 && image.naturalHeight > 0) {
                            currentRatio = image.naturalWidth / image.naturalHeight;
                            currentDimensions = {width: image.naturalWidth, height: image.naturalHeight, fallback: false};
                        } else {
                            currentRatio = defaultRatio;
                            currentDimensions = {width: 212, height: 45, fallback: true};
                        }
                        updateReadOnlyHeights();
                    };
                    image.onerror = function () {
                        currentRatio = defaultRatio;
                        currentDimensions = {width: 212, height: 45, fallback: true};
                        updateReadOnlyHeights();
                    };
                    image.src = url;
                };

                topWidthInput.add(scrolledWidthInput).on('input.cmsLogoRatio change.cmsLogoRatio', updateReadOnlyHeights);
                logoInput.on('input.cmsLogoRatio change.cmsLogoRatio', function () {
                    window.setTimeout(refreshRatioFromLogo, 0);
                });
                refreshRatioFromLogo();
            };

            var bindHotlineAspectRatio = function () {
                var topWidthInput = form.find('[name="row[config][hotline_top_width]"]');
                var scrolledWidthInput = form.find('[name="row[config][hotline_scrolled_width]"]');
                if (!topWidthInput.length && !scrolledWidthInput.length) {
                    return;
                }

                var HOTLINE_RATIO = 5.5;
                var updateReadOnlyHeights = function () {
                    var updateOne = function (input, state) {
                        if (!input.length) {
                            return;
                        }
                        var width = parseFloat(input.val());
                        var output = form.find('[data-hotline-auto-height="' + state + '"]');
                        if (!width || width <= 0) {
                            output.val('自动');
                            return;
                        }
                        var height = Math.round((width / HOTLINE_RATIO) * 10) / 10;
                        output.val(height + ' px');
                    };
                    updateOne(topWidthInput, 'top');
                    updateOne(scrolledWidthInput, 'scrolled');
                };

                topWidthInput.add(scrolledWidthInput).on('input.cmsHotlineRatio change.cmsHotlineRatio', updateReadOnlyHeights);
                updateReadOnlyHeights();
            };

            var reindex = function (type) {
                var table = tableFor(type);
                table.find('tbody > tr.editor-row').each(function (index) {
                    var row = $(this);
                    row.find('[name]').each(function () {
                        var input = $(this);
                        var name = input.attr('name');
                        if (!name) {
                            return;
                        }
                        var prefixes = {
                            navigation: 'row[navigation]',
                            hot_search: 'row[hot_search_items]',
                            social: 'row[social_items]',
                            friend_links: 'row[friend_link_items]'
                        };
                        var prefix = prefixes[type] || '';
                        if (!prefix) {
                            return;
                        }
                        var pattern = /row\[(?:navigation|hot_search_items|social_items|friend_link_items)\]\[\d+\]/;
                        input.attr('name', name.replace(pattern, prefix + '[' + index + ']'));
                    });
                });
            };

            var updateWeights = function (type) {
                var rows = tableFor(type).find('tbody > tr.editor-row');
                var weight = rows.length * 10;
                rows.each(function () {
                    $(this).find('.editor-weigh').val(weight);
                    weight -= 10;
                });
            };

            var navigationRowId = function (row) {
                return parseInt($(row).find('input[name$="[id]"]').val(), 10) || 0;
            };

            var navigationParentId = function (row) {
                return parseInt($(row).find('select.navigation-parent').val(), 10) || 0;
            };

            var sameNavigationParent = function (left, right) {
                return navigationParentId(left) === navigationParentId(right);
            };

            var updateNavigationOrderNumbers = function () {
                var counters = {};
                tableFor('navigation').find('tbody > tr.editor-row').each(function () {
                    var parentId = String(navigationParentId(this));
                    counters[parentId] = (counters[parentId] || 0) + 1;
                    $(this).find('.navigation-order-number').text(counters[parentId]);
                });
            };

            var arrangeNavigationTree = function () {
                var table = tableFor('navigation');
                var tbody = table.find('tbody').first();
                var rows = tbody.children('tr.editor-row').toArray();
                var children = {};
                var visited = [];
                var ordered = [];

                $.each(rows, function (index, element) {
                    var parentId = String(navigationParentId(element));
                    children[parentId] = children[parentId] || [];
                    children[parentId].push(element);
                });

                var appendChildren = function (parentId) {
                    var group = children[String(parentId)] || [];
                    $.each(group, function (index, element) {
                        if ($.inArray(element, visited) !== -1) {
                            return;
                        }
                        visited.push(element);
                        ordered.push(element);
                        var id = navigationRowId(element);
                        if (id > 0) {
                            appendChildren(id);
                        }
                    });
                };

                appendChildren(0);
                $.each(rows, function (index, element) {
                    if ($.inArray(element, visited) === -1) {
                        visited.push(element);
                        ordered.push(element);
                        var id = navigationRowId(element);
                        if (id > 0) {
                            appendChildren(id);
                        }
                    }
                });
                $.each(ordered, function (index, element) {
                    tbody.append(element);
                });
                return table;
            };

            var updateNavigationWeights = function () {
                var rows = tableFor('navigation').find('tbody > tr.editor-row');
                var weight = rows.length * 10;
                rows.each(function () {
                    $(this).find('.navigation-weigh').val(weight);
                    weight -= 10;
                });
                updateNavigationOrderNumbers();
            };

            var refreshNavigationOrder = function () {
                arrangeNavigationTree();
                reindex('navigation');
                updateNavigationWeights();
            };

            var persistedNavigationOptions = function () {
                var options = [];
                tableFor('navigation').find('tbody > tr.editor-row').each(function () {
                    var row = $(this);
                    var id = parseInt(row.find('input[name$="[id]"]').val(), 10) || 0;
                    var title = row.find('input[name$="[title]"]').val() || '';
                    if (id > 0) {
                        options.push({id: id, title: title});
                    }
                });
                return options;
            };

            var rebuildNavigationParents = function () {
                var options = persistedNavigationOptions();
                tableFor('navigation').find('tbody > tr.editor-row').each(function () {
                    var row = $(this);
                    var ownId = parseInt(row.find('input[name$="[id]"]').val(), 10) || 0;
                    var select = row.find('select.navigation-parent');
                    var selected = parseInt(select.val(), 10) || 0;
                    select.empty().append('<option value="0">顶级导航</option>');
                    $.each(options, function (i, option) {
                        if (option.id === ownId) {
                            return;
                        }
                        select.append('<option value="' + option.id + '">' + escapeHtml(option.title) + '</option>');
                    });
                    if (selected > 0 && select.find('option[value="' + selected + '"]').length) {
                        select.val(String(selected));
                    } else {
                        select.val('0');
                    }
                });
            };

            var actionButtons = function () {
                return '<button type="button" class="btn btn-xs btn-default move-editor-row" data-direction="up"><i class="fa fa-arrow-up"></i></button> ' +
                    '<button type="button" class="btn btn-xs btn-default move-editor-row" data-direction="down"><i class="fa fa-arrow-down"></i></button> ' +
                    '<button type="button" class="btn btn-xs btn-danger delete-editor-row"><i class="fa fa-trash"></i></button>';
            };

            var navigationActionButtons = function () {
                return '<button type="button" class="btn btn-xs btn-danger delete-editor-row" title="删除导航"><i class="fa fa-trash"></i></button>';
            };

            var addNavigationRow = function () {
                var table = tableFor('navigation');
                if (!table.length) {
                    return;
                }
                var index = table.find('tbody > tr').length;
                var parentHtml = '<option value="0">顶级导航</option>';
                $.each(persistedNavigationOptions(), function (i, option) {
                    parentHtml += '<option value="' + option.id + '">' + escapeHtml(option.title) + '</option>';
                });
                var prefix = 'row[navigation][' + index + ']';
                var html = '<tr class="editor-row navigation-editor-row" draggable="false">' +
                    '<td class="text-center"><button type="button" class="btn btn-xs btn-primary navigation-drag-handle" data-navigation-drag-handle title="按住拖动，仅可调整同级导航" style="cursor:move;"><i class="fa fa-arrows"></i></button></td>' +
                    '<td><input type="hidden" name="' + prefix + '[id]" value="0"><input class="form-control input-sm" name="' + prefix + '[title]" type="text" value="" data-rule="required"></td>' +
                    '<td><input class="form-control input-sm" name="' + prefix + '[url]" type="text" value="/"></td>' +
                    '<td><select class="form-control input-sm navigation-parent" name="' + prefix + '[parent_id]">' + parentHtml + '</select></td>' +
                    '<td><select class="form-control input-sm" name="' + prefix + '[target]"><option value="_self">当前窗口</option><option value="_blank">新窗口</option></select></td>' +
                    '<td class="text-center"><input type="hidden" name="' + prefix + '[pc_visible]" value="0"><input type="checkbox" name="' + prefix + '[pc_visible]" value="1" checked></td>' +
                    '<td class="text-center"><input type="hidden" name="' + prefix + '[mobile_visible]" value="0"><input type="checkbox" name="' + prefix + '[mobile_visible]" value="1" checked></td>' +
                    '<td class="text-center"><input class="navigation-weigh" name="' + prefix + '[weigh]" type="hidden" value="0"><span class="label label-default navigation-order-number">1</span></td>' +
                    '<td><select class="form-control input-sm" name="' + prefix + '[status]"><option value="normal">显示</option><option value="hidden">隐藏</option></select></td>' +
                    '<td class="text-nowrap">' + navigationActionButtons() + '</td></tr>';
                table.find('tbody').append(html);
                refreshNavigationOrder();
                rebuildNavigationParents();
            };

            var addHotSearchRow = function () {
                var table = tableFor('hot_search');
                if (!table.length) {
                    return;
                }
                var index = table.find('tbody > tr').length;
                var prefix = 'row[hot_search_items][' + index + ']';
                var html = '<tr class="editor-row">' +
                    '<td><input class="form-control input-sm" name="' + prefix + '[title]" type="text" value="" data-rule="required"></td>' +
                    '<td><input class="form-control input-sm" name="' + prefix + '[url]" type="text" value="/"></td>' +
                    '<td><input class="form-control input-sm editor-weigh" name="' + prefix + '[weigh]" type="number" value="0"></td>' +
                    '<td><select class="form-control input-sm" name="' + prefix + '[status]"><option value="normal">显示</option><option value="hidden">隐藏</option></select></td>' +
                    '<td class="text-nowrap">' + actionButtons() + '</td></tr>';
                table.find('tbody').append(html);
                reindex('hot_search');
                updateWeights('hot_search');
            };

            var addFriendLinkRow = function () {
                var table = tableFor('friend_links');
                if (!table.length) {
                    return;
                }
                var index = table.find('tbody > tr').length;
                var prefix = 'row[friend_link_items][' + index + ']';
                var html = '<tr class="editor-row">' +
                    '<td><input class="form-control input-sm" name="' + prefix + '[title]" type="text" value="" data-rule="required"></td>' +
                    '<td><input class="form-control input-sm" name="' + prefix + '[url]" type="text" value="" placeholder="https://example.com"></td>' +
                    '<td><select class="form-control input-sm" name="' + prefix + '[target]"><option value="_blank" selected>新窗口</option><option value="_self">当前窗口</option></select></td>' +
                    '<td><input class="form-control input-sm editor-weigh" name="' + prefix + '[weigh]" type="number" value="0"></td>' +
                    '<td><select class="form-control input-sm" name="' + prefix + '[status]"><option value="normal">显示</option><option value="hidden">隐藏</option></select></td>' +
                    '<td class="text-nowrap">' + actionButtons() + '</td></tr>';
                table.find('tbody').append(html);
                reindex('friend_links');
                updateWeights('friend_links');
            };

            var addSocialRow = function () {
                var table = tableFor('social');
                if (!table.length) {
                    return;
                }
                var index = table.find('tbody > tr').length;
                var prefix = 'row[social_items][' + index + ']';
                socialRowSequence += 1;
                var iconId = 'social-icon-new-' + (new Date().getTime()) + '-' + socialRowSequence;
                var html = '<tr class="editor-row">' +
                    '<td><input class="form-control input-sm" name="' + prefix + '[title]" type="text" value="" data-rule="required"><input type="hidden" name="' + prefix + '[qr_key]" value=""></td>' +
                    '<td><input id="' + iconId + '" class="form-control" data-cms-icon-picker="true" name="' + prefix + '[icon]" type="text" value="" placeholder="优先从图标库选择；没有合适图标时再上传"></td>' +
                    '<td><span class="text-muted">自定义项不绑定全局二维码</span></td>' +
                    '<td><input class="form-control input-sm" name="' + prefix + '[link_url]" type="text" value=""></td>' +
                    '<td><input class="form-control input-sm editor-weigh" name="' + prefix + '[weigh]" type="number" value="0"></td>' +
                    '<td><select class="form-control input-sm" name="' + prefix + '[status]"><option value="normal">显示</option><option value="hidden">隐藏</option></select></td>' +
                    '<td class="text-nowrap">' + actionButtons() + '</td></tr>';
                table.find('tbody').append(html);
                reindex('social');
                updateWeights('social');
                MediaPreview.refresh(form, Form);
            };

            form.on('click', '.add-editor-row', function () {
                var type = $(this).data('list-type');
                if (type === 'navigation') {
                    addNavigationRow();
                } else if (type === 'hot_search') {
                    addHotSearchRow();
                } else if (type === 'social') {
                    addSocialRow();
                } else if (type === 'friend_links') {
                    addFriendLinkRow();
                }
            });

            form.on('click', '.delete-editor-row', function () {
                var row = $(this).closest('tr.editor-row');
                var table = row.closest('table[data-editor-list]');
                var type = table.data('editor-list');
                row.remove();
                if (type === 'navigation') {
                    refreshNavigationOrder();
                    rebuildNavigationParents();
                } else {
                    reindex(type);
                    updateWeights(type);
                }
            });

            form.on('click', '.move-editor-row', function () {
                var button = $(this);
                var row = button.closest('tr.editor-row');
                var table = row.closest('table[data-editor-list]');
                var type = table.data('editor-list');
                if (type === 'navigation') {
                    var candidates = button.data('direction') === 'up' ? row.prevAll('tr.editor-row') : row.nextAll('tr.editor-row');
                    var sibling = null;
                    candidates.each(function () {
                        if (sameNavigationParent(row, this)) {
                            sibling = $(this);
                            return false;
                        }
                    });
                    if (sibling && sibling.length) {
                        if (button.data('direction') === 'up') {
                            row.insertBefore(sibling);
                        } else {
                            row.insertAfter(sibling);
                        }
                        refreshNavigationOrder();
                    }
                    return;
                }
                if (button.data('direction') === 'up') {
                    var previous = row.prev('tr.editor-row');
                    if (previous.length) {
                        row.insertBefore(previous);
                    }
                } else {
                    var next = row.next('tr.editor-row');
                    if (next.length) {
                        row.insertAfter(next);
                    }
                }
                reindex(type);
                updateWeights(type);
            });

            var draggedNavigationRow = null;

            form.on('mousedown.cmsNavigationOrder', '[data-navigation-drag-handle]', function () {
                $(this).closest('tr.editor-row').attr('draggable', 'true');
            });

            form.on('dragstart.cmsNavigationOrder', 'table[data-editor-list="navigation"] tbody > tr.editor-row', function (event) {
                draggedNavigationRow = $(this);
                draggedNavigationRow.addClass('navigation-row-dragging');
                var original = event.originalEvent;
                if (original && original.dataTransfer) {
                    original.dataTransfer.effectAllowed = 'move';
                    original.dataTransfer.setData('text/plain', 'cms-navigation-row');
                }
            });

            form.on('dragover.cmsNavigationOrder', 'table[data-editor-list="navigation"] tbody > tr.editor-row', function (event) {
                if (!draggedNavigationRow || draggedNavigationRow[0] === this) {
                    return;
                }
                event.preventDefault();
                var allowed = sameNavigationParent(draggedNavigationRow, this);
                if (event.originalEvent && event.originalEvent.dataTransfer) {
                    event.originalEvent.dataTransfer.dropEffect = allowed ? 'move' : 'none';
                }
            });

            form.on('drop.cmsNavigationOrder', 'table[data-editor-list="navigation"] tbody > tr.editor-row', function (event) {
                if (!draggedNavigationRow || draggedNavigationRow[0] === this) {
                    return;
                }
                event.preventDefault();
                var target = $(this);
                if (!sameNavigationParent(draggedNavigationRow, target)) {
                    if (typeof Layer !== 'undefined' && Layer.msg) {
                        Layer.msg('跨父级不能拖拽排序');
                    }
                    return;
                }
                var original = event.originalEvent || event;
                var midpoint = target.offset().top + target.outerHeight() / 2;
                if (typeof original.pageY !== 'undefined' && original.pageY > midpoint) {
                    draggedNavigationRow.insertAfter(target);
                } else {
                    draggedNavigationRow.insertBefore(target);
                }
                refreshNavigationOrder();
            });

            form.on('dragend.cmsNavigationOrder', 'table[data-editor-list="navigation"] tbody > tr.editor-row', function () {
                $(this).attr('draggable', 'false').removeClass('navigation-row-dragging');
                draggedNavigationRow = null;
            });

            form.on('mouseup.cmsNavigationOrder', '[data-navigation-drag-handle]', function () {
                var row = $(this).closest('tr.editor-row');
                window.setTimeout(function () {
                    if (!row.hasClass('navigation-row-dragging')) {
                        row.attr('draggable', 'false');
                    }
                }, 0);
            });

            form.on('change.cmsNavigationOrder', 'table[data-editor-list="navigation"] select.navigation-parent', function () {
                refreshNavigationOrder();
            });

            form.on('input', 'table[data-editor-list="navigation"] input[name$="[title]"]', rebuildNavigationParents);



            updateNavigationOrderNumbers();
            MediaPreview.bindForm(form, Form);
            bindLogoAspectRatio();
            bindHotlineAspectRatio();
            IconPicker.bind(form);
        }
    };
    return Controller;
});
