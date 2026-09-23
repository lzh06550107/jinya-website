define(['jquery','bootstrap','backend','table','form','backend/cms/media_preview'],function($,undefined,Backend,Table,Form,MediaPreview){
    var route='cms/product_image';
    function coverFormatter(value){
        return parseInt(value,10)===1?'<span class="label label-success">是</span>':'<span class="label label-default">否</span>';
    }
    var Controller={
        index:function(){
            var canAdd=Config.parentExists!==false&&Config.parentExists!==0;
            Table.api.init({extend:{index_url:route+'/index?product_id='+Config.productId,add_url:canAdd?route+'/add?product_id='+Config.productId:null,edit_url:route+'/edit?product_id='+Config.productId,del_url:route+'/del',multi_url:route+'/multi'}});
            var table=$('#table');
            table.bootstrapTable({url:$.fn.bootstrapTable.defaults.extend.index_url,pk:'id',sortName:'weigh',sortOrder:'desc',columns:[[
                {field:'image',title:'图片',width:88,formatter:Table.api.formatter.image,operate:false},
                {field:'image_type',title:'类型',searchList:Config.imageTypeList||{}},
                {field:'is_cover',title:'主图',searchList:{0:'否',1:'是'},formatter:coverFormatter},
                {field:'alt',title:'Alt文本',operate:'LIKE',align:'left'},
                {field:'weigh',title:'排序'},
                {field:'operate',title:'操作',table:table,events:Table.api.events.operate,formatter:Table.api.formatter.operate}
            ]]});
            Table.api.bindevent(table);
        },
        add:function(){MediaPreview.bindForm($('form[role=form]'),Form);},
        edit:function(){MediaPreview.bindForm($('form[role=form]'),Form);}
    };
    return Controller;
});
