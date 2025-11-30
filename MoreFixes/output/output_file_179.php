    function input_head( $field = null ) {

        // make sure the user has WYSIWYG enabled
        if ( 'true' == get_user_meta( get_current_user_id(), 'rich_editing', true ) ) {
            if ( ! is_admin() ) {
    ?>
        <div class="hidden"><?php wp_editor( '', 'cfswysi' ); ?></div>
    <?php
            }
    ?>
        <script>
        (function($) {

            var wpautop;
            var resize;
            var wysiwyg_count = 0;

            $(function() {
                $(document).on('cfs/ready', '.cfs_add_field', function() {
                    $('.cfs_wysiwyg:not(.ready)').init_wysiwyg();
                });
                $('.cfs_wysiwyg').init_wysiwyg();

                // set the active editor
                $(document).on('click', 'a.add_media', function() {
                    var editor_id = $(this).closest('.wp-editor-wrap').find('.wp-editor-area').attr('id');
                    wpActiveEditor = editor_id;
                });
            });

            $.fn.init_wysiwyg = function() {
                this.each(function() {
                    $(this).addClass('ready');

                    // generate css id
                    wysiwyg_count = wysiwyg_count + 1;
                    var input_id = 'cfs_wysiwyg_' + wysiwyg_count;

                    // set the wysiwyg css id
                    $(this).find('.wysiwyg').attr('id', input_id);
                    $(this).find('a.add_media').attr('data-editor', input_id);
                    
                    // if all editors on page are in 'text' tab, tinyMCE.settings will not be set
                    if ('undefined' === typeof tinyMCE.settings || Object.keys(tinyMCE.settings).length === 0) {
                        
                        // let's pull from tinyMCEPreInit for main content area (if it's set)
                        if ('undefined' !== typeof tinyMCEPreInit && 'undefined' !== typeof tinyMCEPreInit.mceInit.content) {
                            tinyMCE.settings = tinyMCEPreInit.mceInit.content;
                        }
                        // otherwise, setup basic settings object
                        else {
                            tinymce.settings = {
                                wpautop : true,
                                resize : 'vertical',
                                toolbar2 : 'code'
                            };  
                        }
                    }
                    
                    // add the "code" button
                    if ('undefined' !== typeof tinyMCE.settings.toolbar2) {
                        if (tinyMCE.settings.toolbar2.indexOf('code') < 0) {
                            tinyMCE.settings.toolbar2 += ',code';
                        }
                    }

                    // create wysiwyg
                    wpautop = tinyMCE.settings.wpautop;
                    resize = tinyMCE.settings.resize;
                    
                    if (tinyMCE.settings.plugins){
                        if ( tinyMCE.settings.plugins.indexOf('code,link') === -1 ){
                            tinyMCE.settings.plugins = tinyMCE.settings.plugins + ',code,link';
                        }
                    } else {
                        tinyMCE.settings.plugins = 'code,link';
                    }

                    tinyMCE.settings.wpautop = false;
                    tinyMCE.settings.resize = 'vertical';
                    tinyMCE.execCommand('mceAddEditor', false, input_id);
                    tinyMCE.settings.wpautop = wpautop;
                    tinyMCE.settings.resize = resize;
                });
            };

            $('.meta-box-sortables, .cfs_loop').on('sortstart', function(event, ui) {
                tinyMCE.settings.wpautop = false;
                tinyMCE.settings.resize = 'vertical';
                $(this).find('.wysiwyg').each(function() {
                    tinyMCE.execCommand('mceRemoveEditor', false, $(this).attr('id'));
                });
            });

            $('.meta-box-sortables, .cfs_loop').on('sortstop', function(event, ui) {
                $(this).find('.wysiwyg').each(function() {
                    tinyMCE.execCommand('mceAddEditor', false, $(this).attr('id'));
                });
                tinyMCE.settings.wpautop = wpautop;
                tinyMCE.settings.resize = resize;
            });
        })(jQuery);
        </script>
    <?php
        }
    }
