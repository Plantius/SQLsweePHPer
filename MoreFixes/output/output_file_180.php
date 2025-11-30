    function input_head( $field = null ) {
        wp_enqueue_media();
    ?>
        <style>
        .cfs_frame .media-frame-menu {
            display: none;
        }
        
        .cfs_frame .media-frame-title,
        .cfs_frame .media-frame-router,
        .cfs_frame .media-frame-content,
        .cfs_frame .media-frame-toolbar {
            left: 0;
        }
        </style>

        <script>
        (function($) {
            $(function() {

                var cfs_frame;

                $(document).on('click', '.cfs_input .media.button.add', function(e) {
                    $this = $(this);

                    if (cfs_frame) {
                        cfs_frame.open();
                        return;
                    }

                    cfs_frame = wp.media.frames.cfs_frame = wp.media({
                        className: 'media-frame cfs_frame',
                        frame: 'post',
                        multiple: false,
                        library: {
                            type: 'image'
                        }
                    });

                    cfs_frame.on('insert', function() {
                        var attachment = cfs_frame.state().get('selection').first().toJSON();
                        if ('image' == attachment.type && 'undefined' != typeof attachment.sizes) {
                            file_url = attachment.sizes.full.url;
                            if ('undefined' != typeof attachment.sizes.thumbnail) {
                                file_url = attachment.sizes.thumbnail.url;
                            }
                            file_url = '<img src="' + file_url + '" />';
                        }
                        else {
                            file_url = '<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>';
                        }
                        $this.hide();
                        $this.siblings('.media.button.remove').show();
                        $this.siblings('.file_value').val(attachment.id);
                        $this.siblings('.file_url').html(file_url);
                    });

                    cfs_frame.open();
                    cfs_frame.content.mode('upload');
                });

                $(document).on('click', '.cfs_input .media.button.remove', function() {
                    $(this).siblings('.file_url').html('');
                    $(this).siblings('.file_value').val('');
                    $(this).siblings('.media.button.add').show();
                    $(this).hide();
                });
            });
        })(jQuery);
        </script>
    <?php
    }
