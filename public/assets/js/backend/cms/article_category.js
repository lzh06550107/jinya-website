define(['jquery','bootstrap','backend','table','form'],function($,undefined,Backend,Table,Form){
    function parentFormatter(value,row){
        if(row.parent_missing){return '<span class="text-warning"><i class="fa fa-warning"></i> '+(value||'上级分类已不存在')+'</span>';}
        return value||'—';
    }
    var Controller={
        index:function(){
            Table.api.init({extend:{index_url:'cms/article_category/index',add_url:'cms/article_category/add',edit_url:'cms/article_category/edit',del_url:'cms/article_category/del',multi_url:'cms/article_category/multi'}});
            var table=$('#table');
            table.bootstrapTable({url:$.fn.bootstrapTable.defaults.extend.index_url,pk:'id',sortName:'weigh',sortOrder:'desc',columns:[[
                {field:'name',title:'分类名称',operate:'LIKE',align:'left'},
                {field:'parent_name',title:'上级分类',operate:false,align:'left',formatter:parentFormatter},
                {field:'content_count',title:'内容数量',operate:false},
                {field:'child_count',title:'子分类数量',operate:false},
                {field:'slug',title:'URL标识',operate:'LIKE'},
                {field:'status',title:'状态',searchList:Config.statusList||{},formatter:Table.api.formatter.status},
                {field:'weigh',title:'排序'},
                {field:'operate',title:'操作',table:table,events:Table.api.events.operate,formatter:Table.api.formatter.operate}
            ]]});
            Table.api.bindevent(table);
        },
        add:function(){Form.api.bindevent($('form[role=form]'));},
        edit:function(){Form.api.bindevent($('form[role=form]'));}
    };
    return Controller;
});
