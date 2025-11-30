	public static function hierarchy($id_parent=0, $ws_id=null)
	{
		global $website;
        global $theme;

		if(empty($ws_id))
        {
            $ws_id = $website->id;
        }

		$ws = new website();
		$ws->load($ws_id);

		$flang = $ws->languages_list[0];
		if(empty($flang))
        {
            return array();
        }
		
		$tree = array();
		
		if($id_parent==-1)
		{
            // create the virtual root structure entry (the website)
			$obj = new structure();
			$obj->id = 0;
			$obj->label = $ws->name;
            $obj->_multilanguage_label = $ws->name;
			$obj->parent = -1;
			$obj->children = structure::hierarchy(0, $ws_id);
			$obj->paths = $ws->homepage();

			$tree[] = $obj;
		}
		else
		{
			$tree = structure::loadTree($id_parent, $ws_id);

            $templates = template::elements('structure');
            if(empty($templates))
            {
                $templates = array();
            }

			for($i=0; $i < count($tree); $i++)
            {
				$tree[$i]->dictionary = webdictionary::load_element_strings('structure', $tree[$i]->id);
                $tree[$i]->paths	  = path::loadElementPaths('structure', $tree[$i]->id, $ws_id);
                $tree[$i]->label = $tree[$i]->dictionary[$ws->languages_list[0]]['title'];

                $tree[$i]->template_title = $tree[$i]->template;

                foreach($templates as $template_def)
                {
                    if($template_def->type == $tree[$i]->template)
                    {
                        $tree[$i]->template_title = $template_def->title;
                        break;
                    }
                }

                if(method_exists($theme, "t"))
                {
                    $tree[$i]->template_title = $theme->t($tree[$i]->template_title);
                }

                for($wl=0; $wl < count($ws->languages_list); $wl++)
                {
                    $lang = $ws->languages_list[$wl];

                    if(empty($tree[$i]->dictionary[$lang]['title']))
                    {
                        $tree[$i]->dictionary[$lang]['title'] = '[ ? ]';
                    }
                    else
                    {
                        core_special_chars($tree[$i]->dictionary[$lang]['title']);
                    }

                    // the following could be removed? seems like is not used
                        $style = '';
                        if($lang != $flang)
                        {
                            $style = 'display: none';
                        }

                        $label[] = '<span class="structure-label" lang="'.$lang.'" style="'.$style.'">'
                                  .core_special_chars($tree[$i]->dictionary[$lang]['title'])
                                  .'</span>';

                        $bc[$tree[$i]->id][$lang] = $tree[$i]->dictionary[$lang]['title'];
                }

                $children = structure::hierarchy($tree[$i]->id, $ws_id);
                $tree[$i]->children = $children;
            }
		}
		
		return $tree;
	}
