    function html( $field ) {
        $file_url = $field->value;

        if ( ctype_digit( $field->value ) ) {
            if ( wp_attachment_is_image( $field->value ) ) {
                $file_url = wp_get_attachment_image_src( $field->value );
                $file_url = '<img src="' . $file_url[0] . '" />';
            }
            else
            {
                $file_url = wp_get_attachment_url( $field->value );
                $filename = substr( $file_url, strrpos( $file_url, '/' ) + 1 );
                $file_url = '<a href="'. $file_url .'" target="_blank">'. $filename .'</a>';
            }
        }

        // CSS logic for "Add" / "Remove" buttons
        $css = empty( $field->value ) ? [ '', ' hidden' ] : [ ' hidden', '' ];
    ?>
        <span class="file_url"><?php echo $file_url; ?></span>
        <input type="button" class="media button add<?php echo $css[0]; ?>" value="<?php _e( 'Add File', 'cfs' ); ?>" />
        <input type="button" class="media button remove<?php echo $css[1]; ?>" value="<?php _e( 'Remove', 'cfs' ); ?>" />
        <input type="hidden" name="<?php echo $field->input_name; ?>" class="file_value" value="<?php echo $field->value; ?>" />
    <?php
    }
