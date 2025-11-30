    function html( $field ) {
        $field->value = ( 0 < (int) $field->value ) ? 1 : 0;
    ?>
		<label>
			<input type="checkbox" <?php echo $field->value ? ' checked' : ''; ?>>
			<span><?php echo $field->options['message']; ?></span>
			<input type="hidden" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
		</label>
    <?php
    }
