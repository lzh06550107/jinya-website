define(['jquery','bootstrap','backend','table','form','backend/cms/icon_picker'],function($,undefined,Backend,Table,Form,IconPicker){
    var normalStatusFormatter=function(value){
        var labels=Config.statusList||{};
        var label=labels[value]||value||'-';
        var style=value==='normal'?'success':'default';
        return '<span class="label label-'+style+'">'+$('<div>').text(label).html()+'</span>';
    };
    var compactOperateFormatter=function(table){
        return function(value,row,index){
            var direct=[];
            var more=[];
            if(table.data('operate-edit')){
                direct.push('<a href="javascript:;" class="btn btn-xs btn-success btn-editone" title="编辑"><i class="fa fa-pencil"></i> 编辑</a>');
            }
            if(table.data('operate-del')){
                more.push('<li><a href="javascript:;" class="btn-delone text-danger" title="删除"><i class="fa fa-trash"></i> 删除</a></li>');
            }
            if(more.length){
                direct.push('<div class="btn-group"><button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-ellipsis-h"></i> 更多 <span class="caret"></span></button><ul class="dropdown-menu dropdown-menu-right">'+more.join('')+'</ul></div>');
            }
            return direct.join(' ');
        };
    };
    var Controller={
        index:function(){
            Table.api.init({extend:{index_url:'cms/navigation/index',add_url:'cms/navigation/add',edit_url:'cms/navigation/edit',del_url:'cms/navigation/del',multi_url:'cms/navigation/multi'}});
            var table=$('#table');
            table.bootstrapTable({
                url:$.fn.bootstrapTable.defaults.extend.index_url,
                pk:'id',
                sortName:'weigh',
                sortOrder:'desc',
                formatNoMatches:function(){return '暂无导航，点击左上角“新增”开始创建';},
                columns:[[
                    {field:'id',title:'ID',operate:false},
                    {field:'title',title:'导航名称',operate:'LIKE',align:'left'},
                    {field:'url',title:'链接',operate:'LIKE',align:'left'},
                    {field:'position',title:'位置',searchList:{header:'顶部',footer:'底部'}},
                    {field:'status',title:'状态',searchList:Config.statusList||{},formatter:normalStatusFormatter},
                    {field:'weigh',title:'排序',operate:false},
                    {field:'operate',title:'操作',table:table,events:Table.api.events.operate,formatter:compactOperateFormatter(table)}
                ]]
            });
            Table.api.bindevent(table);
        },
        add:function(){var form=$('form[role=form]');Form.api.bindevent(form);IconPicker.bind(form);},
        edit:function(){var form=$('form[role=form]');Form.api.bindevent(form);IconPicker.bind(form);}
    };
    return Controller;
});
