define(['jquery','backend/cms/media_preview'],function($,MediaPreview){
    var EDITOR='[data-label-block-editor]', ITEM='[data-label-item]';

    var blockKey=function(editor){
        return String($(editor).closest('form').find('[name="row[block_key]"]').first().val()||'');
    };

    var lists=function(editor){
        return $(editor).find('[data-label-items-list]');
    };

    var listByName=function(editor,name){
        var list=$(editor).find('[data-label-items-list="'+name+'"]').first();
        if(!list.length&&name==='main') list=$(editor).find('[data-label-items-list]').first();
        return list;
    };

    var splitMaterials=function(editor){
        editor=$(editor);
        var isMaterials=blockKey(editor)==='label_materials';
        var printConfig=editor.find('[data-label-materials-print-config]').first();
        if(!isMaterials){
            printConfig.hide();
            return;
        }
        var main=listByName(editor,'main'), print=listByName(editor,'print');
        if(!main.length||!print.length) return;
        printConfig.show();
        lists(editor).children(ITEM).each(function(){
            var item=$(this);
            var group=String(item.find('[data-label-field="group"]').filter('input,textarea,select').first().val()||'');
            if(group==='print-image'){
                if(item.parent().get(0)!==print.get(0)) print.append(item);
            }else{
                if(item.parent().get(0)!==main.get(0)) main.append(item);
            }
        });
    };

    var labelMaterialsItems=function(editor){
        editor=$(editor);
        var main=listByName(editor,'main').children(ITEM);
        var print=listByName(editor,'print').children(ITEM);
        main.each(function(i){
            $(this).find('[data-label-item-label]').text('工艺 #'+(i+1));
        });
        print.each(function(i){
            $(this).find('[data-label-item-label]').text('轮播图 #'+(i+1)+' · 不干胶印刷');
        });
        editor.find('[data-label-item-add][data-label-item-target="print"]').html('<i class="fa fa-plus"></i> 添加轮播图');
    };

    var reindex=function(editor){
        editor=$(editor);
        splitMaterials(editor);
        var prefix=editor.attr('data-input-prefix')||'row[label_items]';
        var all=lists(editor).children(ITEM);
        all.each(function(i){
            var item=$(this);
            if(blockKey(editor)!=='label_materials'){
                item.find('[data-label-item-label]').text('项目 #'+(i+1));
            }
            item.find('[data-label-field]').each(function(){
                var input=$(this),field=input.attr('data-label-field');
                if(field) input.attr('name',prefix+'['+i+']['+field+']');
            });
            var siblings=item.parent().children(ITEM);
            var localIndex=siblings.index(item);
            item.find('[data-label-item-up]').prop('disabled',localIndex===0);
            item.find('[data-label-item-down]').prop('disabled',localIndex===siblings.length-1);
        });
        if(blockKey(editor)==='label_materials') labelMaterialsItems(editor);
    };

    var bind=function(form,Form){
        form=$(form);
        form.off('.cmsLabelBlock');
        var editor=form.find(EDITOR);
        var toggle=function(){
            var isLabel=String(form.find('[name="row[block_type]"]').val()||'')==='label_section';
            editor.toggle(isLabel);
            editor.find('input,textarea,select').prop('disabled',!isLabel);
            form.find('[data-generic-block-editor]').toggle(!isLabel);
        };

        form.on('change.cmsLabelBlock','[name="row[block_type]"]',toggle);

        form.on('click.cmsLabelBlock','[data-label-item-add]',function(){
            var button=$(this),e=button.closest(EDITOR),markup=e.find('[data-label-item-template]').html()||'';
            var item=$('<div>').html(markup).children(ITEM).first();
            var target=String(button.attr('data-label-item-target')||'main');
            var targetList=listByName(e,target);
            if(!targetList.length) targetList=listByName(e,'main');
            targetList.append(item);
            var defaultGroup=String(button.attr('data-label-item-default-group')||'');
            if(defaultGroup) item.find('[data-label-field="group"]').filter('input,textarea,select').first().val(defaultGroup);
            reindex(e);
            MediaPreview.refresh(form,Form);
            window.setTimeout(function(){reindex(e);},0);
        });

        form.on('click.cmsLabelBlock','[data-label-item-remove]',function(){
            var e=$(this).closest(EDITOR);
            $(this).closest(ITEM).remove();
            reindex(e);
            window.setTimeout(function(){reindex(e);},0);
        });

        form.on('click.cmsLabelBlock','[data-label-item-up]',function(){
            var item=$(this).closest(ITEM),prev=item.prev(ITEM),e=item.closest(EDITOR);
            if(prev.length)item.insertBefore(prev);
            reindex(e);
            window.setTimeout(function(){reindex(e);},0);
        });

        form.on('click.cmsLabelBlock','[data-label-item-down]',function(){
            var item=$(this).closest(ITEM),next=item.next(ITEM),e=item.closest(EDITOR);
            if(next.length)item.insertAfter(next);
            reindex(e);
            window.setTimeout(function(){reindex(e);},0);
        });

        form.on('change.cmsLabelBlock','[data-label-field="group"]',function(){
            var e=$(this).closest(EDITOR);
            window.setTimeout(function(){reindex(e);},0);
        });

        editor.each(function(){
            reindex(this);
            var current=this;
            window.setTimeout(function(){reindex(current);},0);
        });
        toggle();
        return form;
    };

    return {bind:bind,reindex:reindex};
});
