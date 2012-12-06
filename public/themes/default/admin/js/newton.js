newton = {
 
};

newton.modal = {
    confirmDelete : function(url) {
        bootbox.confirm("Are you sure you wish to delete this?", "No, don't delete", "Yes, delete this item", function(result) {
            if(result) {
                location.href = url;
            }
        });
    }
};


newton.tabs = {

    init: function() {

    },

    setupFromFieldsets: function()
    {
        addTab = function(title, target) {
            $('#content-tabs').append(
                $('<li>').append(
                    $('<a>', {
                        href:           '#' + target,
                        id:             'taber-' + target,
                        text:           title
                    }).click(function(){
                        $(this).tab('show');
                    })
                )
            );
        };

        // Hide all fieldsets and create the nav
        $('.tab-content fieldset').each(function() {
            $(this).addClass('tab-pane');

            // Append the tabs + events
            addTab($(this).data('title'), $(this).attr('id'));
        });

        $('#content-tab li a').click(function(e) {
            e.preventDefault();
            $('.top-level-tab').removeClass('active');
            $('#tab-content').addClass('active');
        });

        // Select first tab, only if not in url
        var hash = document.location.hash;
        var prefix = "tab:";
        if (hash && hash != '#no-tab') {
            $('.nav-tabs a[href='+hash.replace(prefix,"")+']').tab('show');
        } else {
            $('#tab-content').addClass('active');
            $('#content-tabs a:first').tab('show');
        }
    }

};




newton.filemanager = {

    init: function() {
        _ = this;
        // Select image
        $('.btn-select-file').click(function() {
            $id = $(this).data('target-element');
            _.openCustom($id);
        });

        // Clear image
        $('.btn-clear-file').click(function() {
            $id = $(this).data('target-element');
            $('#' + $id).val('');
        });
    },

    openCustom: function(id) {
        $.colorbox({
            href: FILEMANAGERURL + "?editor=custom&element=" + id,
            width: '960px',
            height: '650px',
            transition: 'none',
            scrolling: false,
            iframe: true
        });
    },

    openFromTiny: function(field_name, url, type, win) {
        var elfinder_url = FILEMANAGERURL + '?editor=tiny';

        tinyMCE.activeEditor.windowManager.open({
            file: elfinder_url,
            title: 'Newton Filemanager',
            width: 900,
            height: 580,
            resizable: 'yes',
            inline: 'yes',
            popup_css: false,
            close_previous: 'no'
        }, {
            window: win,
            input: field_name
        });

        return false;
    },


    selectFile: function(url, elementId) {
        $('#' + elementId).val(url);
    }

};




$(function() {
    newton.filemanager.init();
    newton.tabs.init();
});


/**
 * Newton Menu
 *
 *
 */
(function($, window, newton) {
    newton.menu = {
        
        init: function() {
            $('#topMenu>ul>li').hover(function() {
                $('ul', this).show();
            }, function() {
                $('ul', this).hide();
            });
        }
    };
    // Add to dom ready
    $(function() { newton.menu.init(); });
})(jQuery, window, newton);