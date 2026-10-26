define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/cms/media_preview', 'backend/cms/media_items_editor', 'backend/cms/metrics_editor', 'backend/cms/advantages_items_editor', 'backend/cms/workshop_items_editor', 'backend/cms/culture_items_editor', 'backend/cms/icon_picker', 'backend/cms/markdown_editor', 'backend/cms/banner_highlight_editor'], function ($, undefined, Backend, Table, Form, MediaPreview, MediaItemsEditor, MetricsEditor, AdvantagesItemsEditor, WorkshopItemsEditor, CultureItemsEditor, IconPicker, MarkdownEditor, BannerHighlightEditor) {
    var Controller = {
        index: function () {
            var isHome = String(Config.pageKey || 'home') === 'home';
            Table.api.init({
                extend: {
                    index_url: 'cms/page_block/index?page_key=' + encodeURIComponent(Config.pageKey || 'home'),
                    edit_url: 'cms/page_block/edit',
                    dragsort_url: isHome ? Fast.api.fixurl('cms/page_block/reorder') : ''
                }
            });
            var table = $('#table');
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                sortOrder: 'desc',
                pagination: false,
                commonSearch: false,
                search: false,
                columns: [[
                    {
                        field: '_drag', title: '拖拽', width: 60, align: 'center', operate: false,
                        formatter: function (value, row) {
                            if (!isHome) {
                                return '—';
                            }
                            if (row.block_key === 'hero') {
                                return '<span class="label label-default"><i class="fa fa-lock"></i> 固定</span>';
                            }
                            return '<a href="javascript:;" class="btn btn-xs btn-primary btn-dragsort" title="拖动调整首页显示顺序"><i class="fa fa-arrows"></i></a>';
                        }
                    },
                    {field: 'id', title: 'ID', operate: false},
                    {field: 'block_name', title: '功能块名称', operate: false},
                    {field: 'block_key', title: '功能块标识', operate: false},
                    {field: 'block_type', title: '类型', operate: false},
                    {field: 'source_type_text', title: '数据来源', operate: false},
                    {field: 'core_text', title: '重要性', operate: false},
                    {field: 'pc_visible_text', title: 'PC', operate: false},
                    {field: 'mobile_visible_text', title: '移动端', operate: false},
                    {field: 'status_text', title: '状态', operate: false},
                    {field: 'version', title: '版本', operate: false},
                    {
                        field: 'weigh', title: '显示顺序', operate: false, align: 'center',
                        formatter: function (value, row, index) {
                            return index + 1;
                        }
                    },
                    {
                        field: 'operate', title: '操作', table: table,
                        events: Table.api.events.operate,
                        formatter: function (value, row) {
                            return '<a href="cms/page_block/edit?ids=' + row.id + '" class="btn btn-xs btn-success btn-dialog" title="配置功能块"><i class="fa fa-pencil"></i></a>';
                        }
                    }
                ]]
            });
            Table.api.bindevent(table);

            $('#btn-reset-home-order').on('click', function () {
                var button = $(this);
                Layer.confirm('确定恢复首页功能块默认顺序吗？将按 ID 升序排列，首页轮播固定第一位。', {icon: 3, title: '恢复默认顺序'}, function (index) {
                    Layer.close(index);
                    Fast.api.ajax({
                        url: Fast.api.fixurl(button.data('url') || 'cms/page_block/resetorder'),
                        data: {page_key: 'home'}
                    }, function () {
                        table.bootstrapTable('refresh');
                    });
                });
            });
        },
        edit: function () {
            var form = $('#page-block-form');
            MarkdownEditor.prepare(form);
            var realSource = String(form.attr('data-real-source') || '');

            if (realSource === 'banner_collection') {
                var firstRow = form.find('[data-banner-row]').first();
                var bannerTemplate = firstRow.length ? firstRow.prop('outerHTML') : '';
                var nextIndex = 0;
                form.find('[data-banner-row]').each(function () {
                    var value = parseInt($(this).attr('data-banner-index'), 10);
                    if (!isNaN(value)) {
                        nextIndex = Math.max(nextIndex, value + 1);
                    }
                });

                form.on('click', '#btn-add-real-banner', function () {
                    if (!bannerTemplate) {
                        return;
                    }
                    var row = $(bannerTemplate);
                    var index = nextIndex++;
                    row.attr('data-banner-index', index);
                    row.find('.banner-row-number').text(index + 1);
                    row.find('[name]').each(function () {
                        var input = $(this);
                        input.attr('name', String(input.attr('name')).replace(/real\[banners\]\[\d+\]/, 'real[banners][' + index + ']'));
                    });
                    row.find('[id]').each(function () {
                        var input = $(this);
                        input.attr('id', String(input.attr('id')).replace(/real-banner-\d+-/, 'real-banner-' + index + '-'));
                    });
                    row.find('[data-media-preview-after]').each(function () {
                        var input = $(this);
                        input.attr('data-media-preview-after', String(input.attr('data-media-preview-after')).replace(/real-banner-\d+-/, 'real-banner-' + index + '-'));
                    });
                    row.find('input[type="text"],input[type="number"],textarea').val('');
                    row.find('input[type="hidden"][name$="[id]"]').val('0');
                    row.find('[name$="[highlights_json]"]').val('[]');
                    row.find('input[type="number"][name$="[weigh]"]').val('0');
                    row.find('select[name$="[media_type]"]').val('image');
                    row.find('select[name$="[mobile_media_type]"]').val('');
                    row.find('select[name$="[status]"]').val('normal');
                    row.find('input[type="checkbox"][name$="[pc_visible]"],input[type="checkbox"][name$="[mobile_visible]"]').prop('checked', true);
                    $('#real-banner-list').append(row);
                    BannerHighlightEditor.bind(form, Form);
                    MediaPreview.refresh(form, Form);
                });

                form.on('click', '.btn-remove-banner', function () {
                    $(this).closest('[data-banner-row]').remove();
                });

                BannerHighlightEditor.bind(form, Form);
                MediaPreview.bindForm(form, Form); IconPicker.bind(form); MarkdownEditor.bind(form);
                return;
            }

            if (realSource === 'home_section') {
                MediaItemsEditor.bind(form, Form);
                MetricsEditor.bind(form, Form);
                AdvantagesItemsEditor.bind(form, Form);
                WorkshopItemsEditor.bind(form, Form);
                CultureItemsEditor.bind(form, Form);
                MediaPreview.bindForm(form, Form); IconPicker.bind(form); MarkdownEditor.bind(form);
                return;
            }

            form.find('.source-mode-group').attr('data-source-mode-ready', '1');
            var toggleSourceMode = function () {
                var mode = form.find('[data-role="source-mode"]').val();
                form.find('.source-manual-group').toggle(mode === 'manual');
                form.find('.source-auto-group').toggle(mode === 'auto');
            };
            form.on('change', '[data-role="source-mode"]', toggleSourceMode);
            toggleSourceMode();

            form.find('.device-image-fields').each(function () {
                $(this).attr('data-device-image-ready', '1');
            });
            MediaPreview.bindForm(form, Form); IconPicker.bind(form); MarkdownEditor.bind(form);
        }
    };
    return Controller;
});
