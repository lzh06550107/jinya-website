define(['jquery','bootstrap','backend','table','form','backend/cms/media_preview','backend/cms/markdown_editor'],function($,undefined,Backend,Table,Form,MediaPreview,MarkdownEditor){
    var route='cms/product_section';
    var parentQuery=encodeURIComponent(Config.parentField)+'='+encodeURIComponent(Config.parentValue||'');
    function terminalFormatter(value,row){
        var pc=parseInt(row.pc_visible,10)===1;
        var mobile=parseInt(row.mobile_visible,10)===1;
        if(row.status!=='normal'||(!pc&&!mobile)){return '<span class="label label-default">前台隐藏</span>';}
        if(pc&&mobile){return '<span class="label label-success">双端显示</span>';}
        if(pc){return '<span class="label label-info">仅 PC</span>';}
        return '<span class="label label-info">仅移动端</span>';
    }
    var Controller={
        index:function(){
            var canAdd=Config.parentExists!==false&&Config.parentExists!==0;
            Table.api.init({extend:{index_url:route+'/index?'+parentQuery,add_url:canAdd?route+'/add?'+parentQuery:null,edit_url:route+'/edit?'+parentQuery,del_url:route+'/del',multi_url:route+'/multi'}});
            var table=$('#table');
            table.bootstrapTable({url:$.fn.bootstrapTable.defaults.extend.index_url,pk:'id',sortName:'weigh',sortOrder:'desc',columns:[[
                {field:'section_type',title:'区块类型',searchList:Config.sectionTypeList||{}},
                {field:'title',title:'标题',operate:'LIKE',align:'left'},
                {field:'terminal_summary',title:'终端生效',operate:false,formatter:terminalFormatter},
                {field:'weigh',title:'排序'},
                {field:'operate',title:'操作',table:table,events:Table.api.events.operate,formatter:Table.api.formatter.operate}
            ]]});
            Table.api.bindevent(table);
        },
        add:function(){var form=$('form[role=form]');MarkdownEditor.prepare(form);MediaPreview.bindForm(form,Form);MarkdownEditor.bind(form);},
        edit:function(){var form=$('form[role=form]');MarkdownEditor.prepare(form);MediaPreview.bindForm(form,Form);MarkdownEditor.bind(form);}
    };
    return Controller;
});
