define(['jquery','backend/cms/media_preview'],function($,MediaPreview){
    var EDITOR='[data-boxes-block-editor]',ITEM='[data-boxes-item]';

    var allItems=function(editor){
        return $(editor).find('[data-boxes-items-list]').children(ITEM);
    };

    var reindex=function(editor){
        editor=$(editor);
        var prefix=editor.attr('data-input-prefix')||'row[boxes_items]';
        var items=allItems(editor);
        items.each(function(i){
            var item=$(this);
            item.find('[data-boxes-item-label]').text('项目 #'+(i+1));
            item.find('[data-boxes-field]').each(function(){
                var input=$(this),field=input.attr('data-boxes-field');
                if(field) input.attr('name',prefix+'['+i+']['+field+']');
            });
            var siblings=item.parent().children(ITEM);
            var localIndex=siblings.index(item);
            item.find('[data-boxes-item-up]').prop('disabled',localIndex===0);
            item.find('[data-boxes-item-down]').prop('disabled',localIndex===siblings.length-1);
        });
    };

    var bind=function(form,Form){
        form=$(form);
        form.off('.cmsBoxesBlock');
        var editor=form.find(EDITOR);

        var toggle=function(){
            var type=String(form.find('[name="row[block_type]"]').val()||''),isBoxes=type==='boxes_section';
            editor.toggle(isBoxes);
            editor.find('input,textarea,select').prop('disabled',!isBoxes);
            form.find('[data-generic-block-editor]').toggle(type!=='bags_section'&&type!=='label_section'&&type!=='about_section'&&type!=='boxes_section'&&type!=='about_section');
        };

        form.on('change.cmsBoxesBlock','[name="row[block_type]"]',toggle);

        form.on('click.cmsBoxesBlock','[data-boxes-item-add]',function(){
            var button=$(this);
            var e=button.closest(EDITOR);
            var markup=e.find('[data-boxes-item-template]').html()||'';
            var item=$('<div>').html(markup).children(ITEM).first();
            var target=String(button.attr('data-boxes-item-target')||'');
            var list=target
                ? e.find('[data-boxes-items-list="'+target+'"]').first()
                : e.find('[data-boxes-items-list]').first();
            if(!list.length) return;

            var defaultGroup=String(button.attr('data-boxes-item-default-group')||'');
            if(defaultGroup){
                item.find('[data-boxes-field="group"]').val(defaultGroup);
            }
            list.append(item);
            reindex(e);
            MediaPreview.refresh(form,Form);
        });

        form.on('click.cmsBoxesBlock','[data-boxes-item-remove]',function(){
            var e=$(this).closest(EDITOR);
            $(this).closest(ITEM).remove();
            reindex(e);
        });
        form.on('click.cmsBoxesBlock','[data-boxes-item-up]',function(){
            var e=$(this).closest(EDITOR),item=$(this).closest(ITEM),prev=item.prev(ITEM);
            if(prev.length)item.insertBefore(prev);
            reindex(e);
        });
        form.on('click.cmsBoxesBlock','[data-boxes-item-down]',function(){
            var e=$(this).closest(EDITOR),item=$(this).closest(ITEM),next=item.next(ITEM);
            if(next.length)item.insertAfter(next);
            reindex(e);
        });

        editor.each(function(){reindex(this);});
        toggle();
        return form;
    };

    return{bind:bind,reindex:reindex};
});
