    function html( $field ) {
        $multiple = '';
        $field->input_class = empty( $field->input_class ) ? '' : $field->input_class;

        // Multi-select
        if ( isset( $field->options['multiple'] ) && '1' == $field->options['multiple'] ) {
            $multiple = ' multiple';
            $field->input_class .= ' multiple';
        }

        // Select2
        if ( isset( $field->options['select2'] ) && '1' == $field->options['select2'] ) {
            $field->input_class .= ' select2';

            add_action( 'admin_footer', [ $this, 'select2_code' ] );
        }

        // Select boxes should return arrays (unless "force_single" is true)
        if ( '[]' != substr( $field->input_name, -2 ) && empty( $field->options['force_single'] ) ) {
            $field->input_name .= '[]';
        }
    ?>
        <select name="<?php echo $field->input_name; ?>" class="<?php echo trim( $field->input_class ); ?>"<?php echo $multiple; ?>>
        <?php foreach ( $field->options['choices'] as $val => $label ) : ?>
            <?php $val = ( '{empty}' == $val ) ? '' : $val; ?>
            <?php $selected = in_array( $val, (array) $field->value ) ? ' selected' : ''; ?>
            <option value="<?php echo esc_attr( $val ); ?>"<?php echo $selected; ?>><?php echo esc_attr( $label ); ?></option>
        <?php endforeach; ?>
        </select>
    <?php
    }
