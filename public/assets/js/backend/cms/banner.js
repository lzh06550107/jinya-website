define(['jquery','bootstrap','backend','table','form','backend/cms/media_preview','backend/cms/icon_picker','backend/cms/banner_highlight_editor'],function($,undefined,Backend,Table,Form,MediaPreview,IconPicker,BannerHighlightEditor){
    var normalStatusFormatter=function(value){
        var labels=Config.statusList||{};
        var label=labels[value]||value||'-';
        var style=value==='normal'?'success':'default';
        return '<span class="label label-'+style+'">'+$('<div>').text(label).html()+'</span>';
    };
    var escapeHtml=function(value){
        return $('<div>').text(value===null||value===undefined?'':String(value)).html();
    };
    var fullUrl=function(value){
        value=$.trim(value||'');
        if(!value){return '';}
        try{return Fast.api.cdnurl(value);}catch(e){return value;}
    };
    var previewFormatter=function(value,row){
        var type=String(row.preview_type||'image')==='video'?'video':'image';
        var terminal=escapeHtml(row.preview_terminal_text||'PC');
        var rawUrl=$.trim(row.preview_url||'');
        var rawPoster=$.trim(row.preview_poster||'');
        if(!rawUrl){
            return '<span class="label label-danger">'+(type==='video'?'缺视频':'缺图片')+'</span>'
                +'<div><small class="text-muted">'+terminal+'</small></div>';
        }
        var url=fullUrl(rawUrl);
        var link=$('<a target="_blank" rel="noopener"></a>').attr('href',url).css({display:'inline-block',position:'relative',textDecoration:'none'});
        if(type==='image'){
            link.append($('<img alt="Banner 预览">').attr('src',url).css({width:'72px',height:'42px',objectFit:'cover',borderRadius:'3px',background:'#f5f5f5'}));
        }else if(rawPoster){
            link.append($('<img alt="Banner 视频封面">').attr('src',fullUrl(rawPoster)).css({width:'72px',height:'42px',objectFit:'cover',borderRadius:'3px',background:'#222'}));
            link.append($('<span title="打开视频"><i class="fa fa-play"></i></span>').css({position:'absolute',left:'50%',top:'50%',transform:'translate(-50%,-50%)',width:'24px',height:'24px',lineHeight:'24px',textAlign:'center',borderRadius:'50%',background:'rgba(0,0,0,.65)',color:'#fff'}));
        }else{
            link.append($('<span><i class="fa fa-play-circle"></i> 视频</span>').css({display:'inline-block',width:'72px',height:'42px',lineHeight:'42px',textAlign:'center',borderRadius:'3px',background:'#333',color:'#fff'}));
        }
        var wrap=$('<div></div>').append(link).append($('<div><small class="text-muted"></small></div>').find('small').text(terminal).end());
        return wrap.html();
    };
    var configCheckFormatter=function(value,row){
        var warnings=$.isArray(row.media_warnings)?row.media_warnings:[];
        var count=Math.max(0,parseInt(row.media_warning_count,10)||warnings.length||0);
        if(!count){
            return '<span class="label label-success"><i class="fa fa-check"></i> 正常</span>';
        }
        var html=['<span class="label label-warning"><i class="fa fa-exclamation-triangle"></i> '+count+' 项</span>'];
        $.each(warnings,function(_,warning){
            html.push('<div><small class="text-danger">'+escapeHtml(warning)+'</small></div>');
        });
        return html.join('');
    };
    var nameFormatter=function(value,row){
        var title=escapeHtml(value||'未命名 Banner');
        var page=escapeHtml(row.page_key||'-');
        var position=escapeHtml(row.position||'-');
        return '<div>'+title+'</div><small class="text-muted">'+page+' / '+position+'</small>';
    };
    var mediaFormatter=function(value,row){
        return '<div><span class="text-muted">PC</span> '+escapeHtml(row.pc_media_text||'-')+'</div>'
            +'<div><span class="text-muted">Mobile</span> '+escapeHtml(row.mobile_media_text||'-')+'</div>';
    };
    var terminalFormatter=function(value,row){
        var pc=Number(row.pc_visible)===1;
        var mobile=Number(row.mobile_visible)===1;
        return '<span class="label label-'+(pc?'success':'default')+'" style="margin-right:4px">PC</span>'
            +'<span class="label label-'+(mobile?'success':'default')+'">移动端</span>'
            +'<div><small class="text-muted">'+escapeHtml(row.terminal_text||'-')+'</small></div>';
    };
    var highlightFormatter=function(value,row){
        var count=Math.max(0,parseInt(row.highlight_count,10)||0);
        return count>0
            ? '<span class="label label-info">'+count+' 条</span>'
            : '<span class="text-muted">无</span>';
    };
    var deliveryFormatter=function(value,row){
        var styles={active:'success',scheduled:'info',expired:'danger',disabled:'warning',hidden:'default'};
        var state=String(row.delivery_state||'');
        var style=styles[state]||'default';
        return '<span class="label label-'+style+'">'+escapeHtml(row.delivery_state_text||'-')+'</span>'
            +'<div><small class="text-muted">'+escapeHtml(row.delivery_time_text||'长期')+'</small></div>';
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
    var bindEditor=function(){
        var form=$('form[role=form]');
        var toggle=function(){
            var pcVideo=String(form.find('#c-media_type').val()||'image')==='video';
            var mobileType=String(form.find('#c-mobile_media_type').val()||'');
            form.find('.cms-banner-pc-image-settings').css('opacity',pcVideo?0.72:1);
            form.find('.cms-banner-pc-video-settings').css('opacity',pcVideo?1:0.72);
            form.find('.cms-banner-mobile-image-settings').css('opacity',mobileType === 'video'?0.72:1);
            form.find('.cms-banner-mobile-video-settings').css('opacity',mobileType === 'image'?0.72:1);
        };
        form.on('change.cmsBannerMediaType','#c-media_type,#c-mobile_media_type',toggle);
        BannerHighlightEditor.bind(form,Form);
        MediaPreview.bindForm(form,Form);
        IconPicker.bind(form);
        toggle();
    };
    var Controller={
        index:function(){
            Table.api.init({extend:{index_url:'cms/banner/index',add_url:'cms/banner/add',edit_url:'cms/banner/edit',del_url:'cms/banner/del',multi_url:'cms/banner/multi'}});
            var table=$('#table');
            table.bootstrapTable({
                url:$.fn.bootstrapTable.defaults.extend.index_url,
                pk:'id',
                sortName:'weigh',
                sortOrder:'desc',
                formatNoMatches:function(){return '暂无 Banner，点击左上角“新增”开始创建';},
                columns:[[
                    {field:'id',title:'ID',operate:false},
                    {field:'preview_url',title:'预览',width:88,operate:false,sortable:false,formatter:previewFormatter},
                    {field:'title',title:'名称',operate:'LIKE',align:'left',formatter:nameFormatter},
                    {field:'media_type',title:'媒体',searchList:{image:'图片',video:'视频'},formatter:mediaFormatter},
                    {field:'media_warning_count',title:'配置检查',operate:false,sortable:false,formatter:configCheckFormatter},
                    {field:'terminal_text',title:'终端',operate:false,sortable:false,formatter:terminalFormatter},
                    {field:'highlight_count',title:'卖点',operate:false,sortable:false,formatter:highlightFormatter},
                    {field:'status',title:'投放状态',searchList:Config.statusList||{},formatter:deliveryFormatter},
                    {field:'weigh',title:'排序',operate:false},
                    {field:'operate',title:'操作',table:table,events:Table.api.events.operate,formatter:compactOperateFormatter(table)}
                ]]
            });
            Table.api.bindevent(table);
        },
        add:bindEditor,
        edit:bindEditor
    };
    return Controller;
});
