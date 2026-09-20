define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            Table.api.init({extend: {index_url: 'cms/inquiry/index', del_url: 'cms/inquiry/del'}});
            var table = $('#table');
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                columns: [[
                    {checkbox: true},
                    {field: 'id', title: 'ID'},
                    {field: 'name', title: '姓名', operate: 'LIKE'},
                    {field: 'mobile', title: '手机号', operate: 'LIKE'},
                    {field: 'company', title: '公司', operate: 'LIKE'},
                    {field: 'content', title: '咨询内容', operate: 'LIKE', align: 'left'},
                    {field: 'status', title: '状态', searchList: Config.statusList || {}, formatter: Table.api.formatter.status},
                    {field: 'assigned_admin.nickname', title: '负责人', operate: false, formatter: function (value) { return value || '<span class="text-muted">未分配</span>'; }},
                    {field: 'next_follow_time', title: '下次跟进', formatter: Table.api.formatter.datetime},
                    {field: 'createtime', title: '提交时间', formatter: Table.api.formatter.datetime},
                    {field: 'operate', title: '操作', formatter: function (value, row) {
                        var html = '';
                        if (table.data('operate-detail')) html += '<a href="cms/inquiry/detail?ids=' + row.id + '" class="btn btn-xs btn-info btn-dialog" title="客户详情"><i class="fa fa-eye"></i></a> ';
                        if (table.data('operate-follow')) html += '<a href="javascript:;" class="btn btn-xs btn-success btn-inquiry-dialog" data-url="cms/inquiry/follow?ids=' + row.id + '" data-title="新增跟进"><i class="fa fa-phone"></i></a> ';
                        if (table.data('operate-assign')) html += '<a href="javascript:;" class="btn btn-xs btn-primary btn-inquiry-dialog" data-url="cms/inquiry/allocate?ids=' + row.id + '" data-title="分配负责人"><i class="fa fa-user-plus"></i></a> ';
                        if (table.data('operate-status')) html += '<a href="javascript:;" class="btn btn-xs btn-warning btn-inquiry-dialog" data-url="cms/inquiry/change_status?ids=' + row.id + '" data-title="修改状态"><i class="fa fa-exchange"></i></a>';
                        return html;
                    }}
                ]]
            });
            Table.api.bindevent(table);
            $(document).on('click', '.btn-inquiry-dialog', function () {
                Fast.api.open($(this).data('url'), $(this).data('title'), {
                    callback: function () { table.bootstrapTable('refresh'); }
                });
            });
        },
        allocate: function () { Form.api.bindevent($('form[role=form]')); },
        follow: function () { Form.api.bindevent($('form[role=form]')); },
        changeStatus: function () { Form.api.bindevent($('form[role=form]')); }
    };
    return Controller;
});
