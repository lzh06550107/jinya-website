define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/cms/media_preview', 'backend/cms/markdown_editor'], function ($, undefined, Backend, Table, Form, MediaPreview, MarkdownEditor) {
    var adminUrl = function (url) {
        return Fast.api.fixurl(url);
    };

    var showActionError = function (data, ret) {
        var message = ret && ret.msg ? String(ret.msg) : '操作失败，服务器没有返回具体错误信息';
        var safeMessage = $('<div>').text(message).html();
        Layer.alert(safeMessage, {icon: 2, title: '操作失败'});
        return false;
    };

    var statusFormatter = function (value) {
        var styles = {
            draft: 'default',
            pending: 'warning',
            published: 'success',
            rejected: 'danger',
            offline: 'default',
            scheduled: 'info'
        };
        var labels = Config.statusList || {};
        var label = labels[value] || value || '-';
        return '<span class="label label-' + (styles[value] || 'default') + '">' + $('<div>').text(label).html() + '</span>';
    };

    var publishTimeFormatter = function (value, row, index) {
        if (!value || value === '0' || value === 0) {
            return '<span class="text-muted">未发布</span>';
        }
        return Table.api.formatter.datetime.call(this, value, row, index);
    };

    var batchActionRules = {
        submit: {
            statuses: ['draft', 'rejected', 'offline'],
            label: '提交审核',
            confirm: '确认将选中的可提交内容批量提交审核？'
        },
        publish: {
            statuses: ['pending', 'offline'],
            label: '批量发布'
        },
        offline: {
            statuses: ['published', 'scheduled'],
            label: '批量下架',
            confirm: '确认将选中的已发布/定时内容批量下架？'
        }
    };

    var batchSelection = function (table, action) {
        var rule = batchActionRules[action];
        var rows = table.bootstrapTable('getSelections') || [];
        if (!rows.length) {
            Toastr.warning('请先选择要操作的内容');
            return null;
        }
        var eligible = rows.filter(function (row) {
            return rule && rule.statuses.indexOf(row.status) !== -1;
        });
        if (!eligible.length) {
            Toastr.warning('所选内容当前状态均不能执行“' + (rule ? rule.label : '该操作') + '”');
            return null;
        }
        return {
            ids: eligible.map(function (row) { return row.id; }),
            skipped: rows.length - eligible.length
        };
    };

    var runBatchAction = function (table, route, action, selection, extra) {
        var data = $.extend({ids: selection.ids.join(',')}, extra || {});
        Fast.api.ajax({url: adminUrl(route + '/' + action), data: data}, function () {
            if (selection.skipped > 0) {
                Toastr.info('另有 ' + selection.skipped + ' 条因当前状态不适用而自动跳过');
            }
            table.bootstrapTable('refresh');
            return false;
        }, function (data, ret) {
            table.bootstrapTable('refresh');
            return showActionError(data, ret);
        });
    };

    var Content = {
        create: function (options) {
            var route = options.route;
            return {
                index: function () {
                    Table.api.init({extend: {
                        index_url: route + '/index',
                        add_url: route + '/add',
                        edit_url: route + '/edit',
                        del_url: route + '/del',
                        multi_url: route + '/multi',
                        recyclebin_url: route + '/recyclebin',
                        dragsort_url: adminUrl('ajax/weigh'),
                        table: options.tableName || ''
                    }});
                    var table = $('#table');
                    var columns = options.columns.slice(0);

                    if (options.compactOperations) {
                        columns.unshift({
                            field: '_drag',
                            title: '排序',
                            width: 58,
                            align: 'center',
                            operate: false,
                            formatter: function () {
                                return '<a href="javascript:;" class="btn btn-xs btn-primary btn-dragsort" title="拖动排序"><i class="fa fa-arrows"></i></a>';
                            }
                        });
                        columns.unshift({checkbox: true});
                    }

                    columns.push({field: 'status', title: '发布状态', searchList: Config.statusList || {}, formatter: statusFormatter});
                    if (!options.compactOperations) {
                        columns.push({field: 'weigh', title: '排序', operate: false});
                    }
                    columns.push({field: 'publish_time', title: '发布时间', formatter: publishTimeFormatter, operate: false});
                    columns.push({field: 'updatetime', title: '最后修改', formatter: Table.api.formatter.datetime, operate: false});
                    columns.push({field: 'operate', title: '操作', table: table, events: Table.api.events.operate, formatter: function (value, row, index) {
                        var id = row.id;
                        if (!options.compactOperations) {
                            var html = Table.api.formatter.operate.call(this, value, row, index);
                            if (table.data('operate-preview')) html += ' <a href="' + adminUrl(route + '/preview?ids=' + id) + '" target="_blank" class="btn btn-xs btn-info" title="预览"><i class="fa fa-eye"></i></a>';
                            if (table.data('operate-submit') && ['draft', 'rejected', 'offline'].indexOf(row.status) !== -1) html += ' <a href="javascript:;" class="btn btn-xs btn-primary btn-cms-action" data-url="' + adminUrl(route + '/submit') + '" data-id="' + id + '" title="提交审核"><i class="fa fa-send"></i></a>';
                            if (table.data('operate-publish') && ['pending', 'offline'].indexOf(row.status) !== -1) html += ' <a href="javascript:;" class="btn btn-xs btn-success btn-cms-publish" data-url="' + adminUrl(route + '/publish') + '" data-id="' + id + '" title="立即或定时发布"><i class="fa fa-check"></i></a>';
                            if (table.data('operate-reject') && row.status === 'pending') html += ' <a href="javascript:;" class="btn btn-xs btn-warning btn-cms-reject" data-url="' + adminUrl(route + '/reject') + '" data-id="' + id + '" title="驳回"><i class="fa fa-reply"></i></a>';
                            if (table.data('operate-offline') && ['published', 'scheduled'].indexOf(row.status) !== -1) html += ' <a href="javascript:;" class="btn btn-xs btn-danger btn-cms-action" data-url="' + adminUrl(route + '/offline') + '" data-id="' + id + '" title="' + (row.status === 'scheduled' ? '取消定时' : '下架') + '"><i class="fa fa-arrow-down"></i></a>';
                            if (table.data('operate-duplicate')) html += ' <a href="javascript:;" class="btn btn-xs btn-default btn-cms-action" data-url="' + adminUrl(route + '/duplicate') + '" data-id="' + id + '" title="复制"><i class="fa fa-copy"></i></a>';
                            return html;
                        }

                        var direct = [];
                        var more = [];
                        if (table.data('operate-edit')) {
                            direct.push('<a href="javascript:;" class="btn btn-xs btn-success btn-editone" title="编辑"><i class="fa fa-pencil"></i> 编辑</a>');
                        }
                        if (table.data('operate-preview')) {
                            direct.push('<a href="' + adminUrl(route + '/preview?ids=' + id) + '" target="_blank" class="btn btn-xs btn-info" title="预览"><i class="fa fa-eye"></i> 预览</a>');
                        }
                        if (table.data('operate-submit') && ['draft', 'rejected', 'offline'].indexOf(row.status) !== -1) {
                            direct.push('<a href="javascript:;" class="btn btn-xs btn-primary btn-cms-action" data-url="' + adminUrl(route + '/submit') + '" data-id="' + id + '" title="提交审核"><i class="fa fa-send"></i> 提交</a>');
                        }
                        if (table.data('operate-publish') && ['pending', 'offline'].indexOf(row.status) !== -1) {
                            direct.push('<a href="javascript:;" class="btn btn-xs btn-success btn-cms-publish" data-url="' + adminUrl(route + '/publish') + '" data-id="' + id + '" title="立即或定时发布"><i class="fa fa-check"></i> 发布</a>');
                        }
                        if (table.data('operate-reject') && row.status === 'pending') {
                            direct.push('<a href="javascript:;" class="btn btn-xs btn-warning btn-cms-reject" data-url="' + adminUrl(route + '/reject') + '" data-id="' + id + '" title="驳回"><i class="fa fa-reply"></i> 驳回</a>');
                        }
                        if (table.data('operate-offline') && ['published', 'scheduled'].indexOf(row.status) !== -1) {
                            direct.push('<a href="javascript:;" class="btn btn-xs btn-danger btn-cms-action" data-url="' + adminUrl(route + '/offline') + '" data-id="' + id + '" title="' + (row.status === 'scheduled' ? '取消定时' : '下架') + '"><i class="fa fa-arrow-down"></i> ' + (row.status === 'scheduled' ? '取消定时' : '下架') + '</a>');
                        }

                        if (table.data('operate-duplicate')) {
                            more.push('<li><a href="javascript:;" class="btn-cms-action" data-url="' + adminUrl(route + '/duplicate') + '" data-id="' + id + '" title="复制"><i class="fa fa-copy"></i> 复制</a></li>');
                        }
                        if (table.data('operate-del')) {
                            if (more.length) more.push('<li class="divider"></li>');
                            more.push('<li><a href="javascript:;" class="btn-delone text-danger" title="删除"><i class="fa fa-trash"></i> 删除</a></li>');
                        }
                        if (more.length) {
                            direct.push('<div class="btn-group"><button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-ellipsis-h"></i> 更多 <span class="caret"></span></button><ul class="dropdown-menu dropdown-menu-right">' + more.join('') + '</ul></div>');
                        }
                        return direct.join(' ');
                    }});
                    table.bootstrapTable({
                        url: $.fn.bootstrapTable.defaults.extend.index_url,
                        pk: 'id',
                        sortName: 'weigh',
                        sortOrder: 'desc',
                        formatNoMatches: function () { return '暂无内容，点击左上角“新增”开始创建'; },
                        columns: [columns]
                    });
                    Table.api.bindevent(table);

                    $(document).on('click', '.btn-cms-batch', function () {
                        var action = String($(this).data('action') || '');
                        var rule = batchActionRules[action];
                        if (!rule) return false;
                        var selection = batchSelection(table, action);
                        if (!selection) return false;

                        if (action === 'publish') {
                            Layer.open({
                                title: '批量发布设置',
                                area: ['450px', '270px'],
                                content: '<div style="padding:15px 20px"><p>将处理 <strong>' + selection.ids.length + '</strong> 条可发布内容。</p>' + (selection.skipped ? '<p class="text-warning">另有 ' + selection.skipped + ' 条因当前状态不适用将自动跳过。</p>' : '') + '<p class="text-muted">发布时间留空表示立即发布；填写未来时间表示定时发布。</p><label>发布时间</label><input id="cms-batch-publish-time" type="datetime-local" class="form-control" /></div>',
                                btn: ['确认批量发布', '取消'],
                                yes: function (index) {
                                    runBatchAction(table, route, action, selection, {publish_time: $('#cms-batch-publish-time').val()});
                                    Layer.close(index);
                                }
                            });
                            return false;
                        }

                        Layer.confirm(rule.confirm + (selection.skipped ? '<br><span class="text-warning">另有 ' + selection.skipped + ' 条因当前状态不适用将自动跳过。</span>' : ''), {title: rule.label}, function (index) {
                            runBatchAction(table, route, action, selection);
                            Layer.close(index);
                        });
                        return false;
                    });

                    $(document).on('click', '.btn-cms-action', function () {
                        var that = $(this);
                        Layer.confirm('确认执行此操作？', function (index) {
                            Fast.api.ajax({url: that.data('url'), data: {ids: that.data('id')}}, function () {
                                Layer.close(index);
                                table.bootstrapTable('refresh');
                                return false;
                            }, function (data, ret) {
                                Layer.close(index);
                                return showActionError(data, ret);
                            });
                        });
                    });

                    $(document).on('click', '.btn-cms-publish', function () {
                        var that = $(this);
                        Layer.open({
                            title: '发布设置',
                            area: ['430px', '245px'],
                            content: '<div style="padding:15px 20px"><p class="text-muted">发布时间留空表示立即发布；填写未来时间表示定时发布。</p><label>发布时间</label><input id="cms-publish-time" type="datetime-local" class="form-control" /></div>',
                            btn: ['确认发布', '取消'],
                            yes: function (index) {
                                Fast.api.ajax({
                                    url: that.data('url'),
                                    data: {ids: that.data('id'), publish_time: $('#cms-publish-time').val()}
                                }, function () {
                                    Layer.close(index);
                                    table.bootstrapTable('refresh');
                                    return false;
                                }, showActionError);
                            }
                        });
                    });

                    $(document).on('click', '.btn-cms-reject', function () {
                        var that = $(this);
                        Layer.prompt({title: '请输入驳回原因', formType: 2}, function (value, index) {
                            Fast.api.ajax({url: that.data('url'), data: {ids: that.data('id'), reason: value}}, function () {
                                Layer.close(index);
                                table.bootstrapTable('refresh');
                                return false;
                            }, showActionError);
                        });
                    });
                },
                add: function () { var form=$('form[role=form]'); MarkdownEditor.prepare(form); MediaPreview.bindForm(form, Form); MarkdownEditor.bind(form); },
                edit: function () { var form=$('form[role=form]'); MarkdownEditor.prepare(form); MediaPreview.bindForm(form, Form); MarkdownEditor.bind(form); },
                recyclebin: function () {
                    Table.api.init({extend: {recyclebin_url: route + '/recyclebin', restore_url: route + '/restore', destroy_url: route + '/destroy'}});
                    var table = $('#table');
                    table.bootstrapTable({url: $.fn.bootstrapTable.defaults.extend.recyclebin_url, pk: 'id', sortName: 'id', columns: [[
                        {checkbox: true},
                        {field: 'id', title: 'ID'},
                        {field: 'title', title: '标题'},
                        {field: 'deletetime', title: '删除时间', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: '操作', table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]]});
                    Table.api.bindevent(table);
                }
            };
        }
    };
    return Content;
});
