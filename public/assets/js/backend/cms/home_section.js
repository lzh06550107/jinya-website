define(['jquery','bootstrap','backend','table','form','backend/cms/media_preview','backend/cms/media_items_editor','backend/cms/metrics_editor','backend/cms/advantages_items_editor','backend/cms/workshop_items_editor','backend/cms/culture_items_editor','backend/cms/icon_picker','backend/cms/markdown_editor'],function($,undefined,Backend,Table,Form,MediaPreview,MediaItemsEditor,MetricsEditor,AdvantagesItemsEditor,WorkshopItemsEditor,CultureItemsEditor,IconPicker,MarkdownEditor){
    function countFormatter(value){return value===null||typeof value==='undefined'?'—':value;}
    function frontStatusFormatter(value){
        var cls='label-default';
        if(value==='双端显示'){cls='label-success';}
        else if(value==='仅 PC 显示'||value==='仅移动端显示'){cls='label-info';}
        else if(value==='双端隐藏'){cls='label-warning';}
        return '<span class="label '+cls+'">'+(value||'—')+'</span>';
    }
    var Controller={
        index:function(){
            Table.api.init({extend:{index_url:'cms/home_section/index',add_url:'cms/home_section/add',edit_url:'cms/home_section/edit',del_url:'cms/home_section/del',multi_url:'cms/home_section/multi'}});
            var table=$('#table');
            table.bootstrapTable({url:$.fn.bootstrapTable.defaults.extend.index_url,pk:'id',sortName:'weigh',sortOrder:'desc',columns:[[
                {field:'section_name',title:'模块名称',operate:'LIKE',align:'left'},
                {field:'section_key',title:'模块标识',operate:'LIKE'},
                {field:'title',title:'前台标题',operate:'LIKE',align:'left'},
                {field:'source_type_text',title:'数据来源',operate:false},
                {field:'pc_reference_count',title:'PC引用',operate:false,formatter:countFormatter},
                {field:'mobile_reference_count',title:'移动引用',operate:false,formatter:countFormatter},
                {field:'front_status_text',title:'前台状态',operate:false,formatter:frontStatusFormatter},
                {field:'operate',title:'操作',table:table,events:Table.api.events.operate,formatter:Table.api.formatter.operate}
            ]]});
            Table.api.bindevent(table);
        },
        add:function(){var form=$('form[role=form]');MarkdownEditor.prepare(form);MediaItemsEditor.bind(form,Form);MetricsEditor.bind(form,Form);AdvantagesItemsEditor.bind(form,Form);WorkshopItemsEditor.bind(form,Form);CultureItemsEditor.bind(form,Form);MediaPreview.bindForm(form,Form);IconPicker.bind(form);MarkdownEditor.bind(form);},
        edit:function(){var form=$('form[role=form]');MarkdownEditor.prepare(form);MediaItemsEditor.bind(form,Form);MetricsEditor.bind(form,Form);AdvantagesItemsEditor.bind(form,Form);WorkshopItemsEditor.bind(form,Form);CultureItemsEditor.bind(form,Form);MediaPreview.bindForm(form,Form);IconPicker.bind(form);MarkdownEditor.bind(form);}
    };
    return Controller;
});
