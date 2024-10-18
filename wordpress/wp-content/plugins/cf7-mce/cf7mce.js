function cf7mce_insert_tag(content) {
    jQuery('textarea#hiddeneditor').each(function () {        
        if (tinymce.activeEditor && tinyMCE.activeEditor.isHidden() === false) {
            tinymce.execCommand('mceInsertContent', false, content);
        }
        else{
            this.focus();
            if (document.selection) { // IE
                var selection = document.selection.createRange();
                selection.text = content;
            } else if (this.selectionEnd || 0 === this.selectionEnd) {
                var val = jQuery(this).val();
                var end = this.selectionEnd;
                jQuery(this).val(val.substring(0, end) + content + val.substring(end, val.length));
                this.selectionStart = end + content.length;
                this.selectionEnd = end + content.length;
            } else {
                jQuery(this).val(jQuery(this).val() + content);
            }
        }
        this.focus();
    });
}

jQuery(document).ready(function () {
    var form_dest = jQuery('#wpcf7-form').parent();
    jQuery('#wpcf7-form').remove();
    form_dest.append(jQuery('#tiny-mce-hidden-4-cf7'));
    jQuery('#hiddeneditor').attr('name', 'wpcf7-form');

    jQuery('input.insert-tag').unbind('click').click(function (event) {
        var form = jQuery(this).closest('form.tag-generator-panel');
        var tag = form.find('input.tag').val();
        cf7mce_insert_tag(tag);
        tb_remove(); // close thickbox
        return false;
    });
    
    if ( typeof( tinymce ) == "object" && typeof( tinymce.execCommand ) == "function" ) {
            jQuery("#wpcf7-mail-body, #wpcf7-mail-2-body").addClass("wp-editor-area");
            var cf7tinymce_params = {
                wpautop:true,
                menubar : false,
                plugins:"image,wordpress,wpeditimage,wplink",
                toolbar: "undo redo | styleselect | bold underline italic |  alignleft aligncenter alignright | bullist numlist | indent outdent | link unlink image code source"

            };
            var ed = new tinymce.Editor('wpcf7-mail-body', cf7tinymce_params, tinymce.EditorManager);
            var ed2 = new tinymce.Editor('wpcf7-mail-2-body', cf7tinymce_params, tinymce.EditorManager);

            ed.render();
            ed2.render();
     }
});


