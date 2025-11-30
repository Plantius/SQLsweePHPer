                $result->post_title = ( 'private' == $result->post_status ) ? '(Private) ' . $result->post_title : $result->post_title;
                $selected_posts[ $result->ID ] = $result;
            }
        }
    ?>
        <div class="filter_posts">
            <input type="text" class="cfs_filter_input" autocomplete="off" placeholder="<?php _e( 'Search posts', 'cfs' ); ?>" />
        </div>

        <div class="available_posts post_list">
        <?php foreach ( $available_posts as $post ) : ?>
            <?php $class = ( isset( $selected_posts[ $post->ID ] ) ) ? ' class="used"' : ''; ?>
            <div rel="<?php echo $post->ID; ?>"<?php echo $class; ?> title="<?php echo $post->post_type; ?>"><?php echo apply_filters( 'cfs_relationship_display', $post->post_title, $post->ID, $field ); ?></div>
        <?php endforeach; ?>
        </div>

        <div class="selected_posts post_list">
        <?php foreach ( $selected_posts as $post ) : ?>
            <div rel="<?php echo $post->ID; ?>"><span class="remove"></span><?php echo apply_filters( 'cfs_relationship_display', $post->post_title, $post->ID, $field ); ?></div>
        <?php endforeach; ?>
        </div>
        <div class="clear"></div>
        <input type="hidden" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
    <?php
    }
