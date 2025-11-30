    function html( $field ) {
    ?>
        <div class="wp-editor-wrap">
            <div class="wp-media-buttons">
                <?php do_action( 'media_buttons' ); ?>
            </div>
            <div class="wp-editor-container">
                <textarea name="<?php echo $field->input_name; ?>" class="wp-editor-area <?php echo $field->input_class; ?>" style="height:300px"><?php echo $field->value; ?></textarea>
            </div>
        </div>
    <?php
    }
