    public static function block_group_block($block_group, $block_code)
    {
        global $theme;

        $block = null;

        if(is_array($theme->block_groups))
        {
            foreach($theme->block_groups as $key => $bg)
            {
                // block_group matches?
                // if we don't have a block_group, find the first block_group block with the code requested
                if($bg->id == $block_group || empty($block_group))
                {
                    for($i=0; $i < count($bg->blocks); $i++)
                    {
                        if($bg->blocks[$i]->id == $block_code)
                        {
                            $block = $bg->blocks[$i];
                            $block->_block_group_id = $bg->id;
                            break;
                        }
                    }
                }
                if(!empty($block))
                    break;
            }
        }

        return $block;
    }
