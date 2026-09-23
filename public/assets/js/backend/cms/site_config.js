define(['jquery', 'bootstrap', 'backend', 'form', 'backend/cms/media_preview'], function ($, undefined, Backend, Form, MediaPreview) {
    var Controller = {
        index: function () {
            MediaPreview.bindForm($('form[role=form]'), Form);
        }
    };
    return Controller;
});
