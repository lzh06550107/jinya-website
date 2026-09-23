define(['jquery','bootstrap','backend','table','form'],function($,undefined,Backend,Table,Form){
    var route='cms/product_parameter';
    function visibleFormatter(value){
        return parseInt(value,10)===1?'<span class="label label-success">显示</span>':'<span class="label label-default">隐藏</span>';
    }
    var Controller={
        index:function(){
            var canAdd=Config.parentExists!==false&&Config.parentExists!==0;
            Table.api.init({extend:{index_url:route+'/index?product_id='+Config.productId,add_url:canAdd?route+'/add?product_id='+Config.productId:null,edit_url:route+'/edit?product_id='+Config.productId,del_url:route+'/del',multi_url:route+'/multi'}});
            var table=$('#table');
            table.bootstrapTable({url:$.fn.bootstrapTable.defaults.extend.index_url,pk:'id',sortName:'weigh',sortOrder:'desc',columns:[[
                {field:'parameter_group',title:'分组',operate:'LIKE',align:'left'},
                {field:'parameter_name',title:'参数名称',operate:'LIKE',align:'left'},
                {field:'parameter_value',title:'参数值',operate:'LIKE',align:'left'},
                {field:'unit',title:'单位'},
                {field:'is_visible',title:'前台显示',searchList:{0:'隐藏',1:'显示'},formatter:visibleFormatter},
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
