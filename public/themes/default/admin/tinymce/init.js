$(function() {
    $('textarea.editor').tinymce({
            // Location of TinyMCE script
            script_url : THEMEURL + '/tinymce/tiny_mce.js',

            // General options
            theme : "advanced",
            skin : "cirkuit",

            plugins : "spellchecker,safari,pagebreak,style,layer,table,save,advimage,advlink,advlist,emotions,iespell,inlinepopups,insertdatetime,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

            extended_valid_elements : "iframe[src|width|height|name|align|frameborder|scrolling]",

            // Theme options
            theme_advanced_buttons1 : "formatselect,fontsizeselect,forecolor,|,bold,italic,strikethrough,|,bullist,numlist,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,image,|,spellchecker",
            theme_advanced_buttons2 : "code,paste,pastetext,pasteword,removeformat,|,underline,justifyfull,sup,|,outdent,indent,|,hr,anchor,charmap,|,media,|,search,replace,|,fullscreen,|,undo,redo",
            theme_advanced_buttons3 : "tablecontrols,|,visualaid",

            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : true,

            file_browser_callback : 'newton.filemanager.openFromTiny'

            // Example content CSS (should be your site CSS)
            //content_css : "css/content.css",

            // Replace values for the template plugin
            /*template_replace_values : {
                    username : "Some User",
                    staffid : "991234"
            }*/
    });
});