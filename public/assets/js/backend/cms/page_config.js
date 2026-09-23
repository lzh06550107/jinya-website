define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'cms/page_config/index',
                    edit_url: 'cms/page_config/edit'
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
                    {field: 'page_name', title: '页面名称', operate: 'LIKE'},
                    {field: 'page_key', title: '页面标识', operate: 'LIKE'},
                    {field: 'page_type_text', title: '页面类型', operate: false},
                    {field: 'route_pattern', title: '路由基准', operate: 'LIKE'},
                    {field: 'block_count', title: '功能块数', operate: false},
                    {field: 'content_count', title: '内容记录', operate: false, formatter: function (value, row) { return row.content_mode === 'none' ? '-' : value; }},
                    {field: 'version', title: '配置版本', operate: false},
                    {field: 'updatetime', title: '最后修改', formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange'},
                    {
                        field: 'operate',
                        title: '操作',
                        table: table,
                        events: Table.api.events.operate,
                        formatter: function (value, row) {
                            var edit = '<a href="cms/page_config/edit?ids=' + row.id + '" class="btn btn-xs btn-success btn-dialog" title="编辑页面"><i class="fa fa-pencil"></i></a>';
                            var blocksUrl = row.blocks_url || ('cms/page_block/index?page_key=' + encodeURIComponent(row.page_key));
                            var blocksTitle = row.blocks_title || '配置功能块';
                            var blocks = '<a href="' + blocksUrl + '" class="btn btn-xs btn-info btn-addtabs" title="' + blocksTitle + '"><i class="fa fa-th-large"></i></a>';
                            return edit + ' ' + blocks;
                        }
                    }
                ]]
            });
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($('form[role=form]'));
        }
    };
    return Controller;
});
