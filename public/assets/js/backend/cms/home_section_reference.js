define(['jquery','bootstrap','backend','table','form','backend/cms/media_preview'],function($,undefined,Backend,Table,Form,MediaPreview){
    var route='cms/home_section_reference';
    var parentQuery=encodeURIComponent(Config.parentField)+'='+encodeURIComponent(Config.parentValue||'');
    function titleFormatter(value,row){
        if(row.content_missing){return '<span class="text-danger"><i class="fa fa-warning"></i> '+(value||'内容已不存在')+'</span>';}
        return value||'—';
    }
    var Controller={
        index:function(){
            Table.api.init({extend:{index_url:route+'/index?'+parentQuery,add_url:route+'/add?'+parentQuery,edit_url:route+'/edit?'+parentQuery,del_url:route+'/del',multi_url:route+'/multi'}});
            var table=$('#table');
            var isProductReference=Config.parentValue==='products';
            var productColumns=[
                {field:'content_type',title:'内容类型',searchList:Config.contentTypeList||{}},
                {field:'content_title',title:'内容标题',operate:false,align:'left',formatter:titleFormatter},
                {field:'content_id',title:'内容ID',operate:false},
                {field:'pc_image',title:'PC展示图',operate:false,formatter:Table.api.formatter.image},
                {field:'mobile_image',title:'移动展示图',operate:false,formatter:Table.api.formatter.image}
            ];
            if(!isProductReference){
                productColumns.push({field:'terminal',title:'终端',searchList:Config.terminalList||{}});
            }
            productColumns.push(
                {field:'weigh',title:'排序'},
                {field:'status',title:'状态',searchList:{normal:'启用',hidden:'停用'},formatter:Table.api.formatter.status},
                {field:'operate',title:'操作',table:table,events:Table.api.events.operate,formatter:Table.api.formatter.operate}
            );
            table.bootstrapTable({url:$.fn.bootstrapTable.defaults.extend.index_url,pk:'id',sortName:'weigh',sortOrder:'desc',columns:[productColumns]});
            Table.api.bindevent(table);
        },
        add:function(){MediaPreview.bindForm($('form[role=form]'),Form);},
        edit:function(){MediaPreview.bindForm($('form[role=form]'),Form);}
    };
    return Controller;
});
