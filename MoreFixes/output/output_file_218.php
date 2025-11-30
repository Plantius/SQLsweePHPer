    function recursive_html( $group_id, $field_id, $parent_tag = '', $parent_weight = 0 ) {

        // Get loop field
        $loop_field = CFS()->api->get_input_fields( [
            'field_id' => $field_id
        ] );

        // Get sub-fields
        $results = CFS()->api->get_input_fields( [
            'group_id' => $group_id,
            'parent_id' => $field_id
        ] );

        // Dynamically build the $values array
        $parent_tag = empty( $parent_tag ) ? "[$field_id]" : $parent_tag;
        eval( "\$values = isset(\$this->values{$parent_tag} ) ? \$this->values{$parent_tag} : false;" );

        // Row options
        $row_display = $this->get_option( $loop_field[ $field_id ], 'row_display', 0 );
        $row_label = $this->get_option( $loop_field[ $field_id ], 'row_label', __( 'Loop Row', 'cfs' ) );
        $button_label = $this->get_option( $loop_field[ $field_id ], 'button_label', __( 'Add Row', 'cfs' ) );
        $css_class = ( 0 < (int) $row_display ) ? ' open' : '';

        // Do the dirty work
        $row_offset = -1;

        if ( $values ) :
            foreach ( $values as $i => $value ) :
                $row_offset = max( $i, $row_offset );
    ?>
        <div class="loop_wrapper">
            <div class="cfs_loop_head<?php echo $css_class; ?>">
                <a class="cfs_delete_field" href="javascript:;"></a>
                <a class="cfs_toggle_field" href="javascript:;"></a>
                <a class="cfs_insert_field" href="javascript:;"></a>
                <span class="label"><?php echo esc_attr( $this->dynamic_label( $row_label, $results, $values[ $i ] ) ); ?>&nbsp;</span>
            </div>
            <div class="cfs_loop_body<?php echo $css_class; ?>">
            <?php foreach ( $results as $field ) : ?>
                <label><?php echo $field->label; ?></label>

                <?php if ( ! empty( $field->notes ) ) : ?>
                <p class="notes"><?php echo $field->notes; ?></p>
                <?php endif; ?>

                <div class="field field-<?php echo $field->name; ?> cfs_<?php echo $field->type; ?>">
                <?php if ( 'loop' == $field->type ) : ?>
                    <?php $this->recursive_html( $group_id, $field->id, "{$parent_tag}[$i][$field->id]", $i ); ?>
                <?php else : ?>
                <?php
                    $args = [
                        'type' => $field->type,
                        'input_name' => "cfs[input]{$parent_tag}[$i][$field->id][value][]",
                        'input_class' => $field->type,
                        'options' => $field->options,
                    ];

                    if ( isset( $values[ $i ][ $field->id ] ) ) {
                        $args['value'] = $values[ $i ][ $field->id ];
                    }
                    elseif ( isset( $field->options['default_value'] ) ) {
                        $args['value'] = $field->options['default_value'];
                    }

                    CFS()->create_field( $args );
                ?>
                <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>

        <?php endforeach; endif; ?>

        <div class="table_footer">
            <input type="button" class="button-primary cfs_add_field" value="<?php echo esc_attr( $button_label ); ?>" data-loop-tag="<?php echo $parent_tag; ?>" data-rows="<?php echo ( $row_offset + 1 ); ?>" />
        </div>
    <?php
    }
