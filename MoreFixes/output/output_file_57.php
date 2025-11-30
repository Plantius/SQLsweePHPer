                            $wtext->delete();
                        }
                        echo json_encode(true);
                    }
                    else
                    {
                        echo json_encode(false);
                    }
					break;
					
				default: // list or search
                    $out = array();
					$page = intval($_REQUEST['page']);
					$max	= intval($_REQUEST['rows']);
					$offset = ($page - 1) * $max;
					$where = ' website = '.$website->id;
															
					if($_REQUEST['_search']=='true' || isset($_REQUEST['quicksearch']))
					{
						if(isset($_REQUEST['quicksearch']))
                        {
                            $where .= $wtext->quicksearch($_REQUEST['quicksearch']);
                        }
						else if(isset($_REQUEST['filters']))
                        {
                            $where .= navitable::jqgridsearch($_REQUEST['filters']);
                        }
						else	// single search
                        {
                            $where .= ' AND '.navitable::jqgridcompare($_REQUEST['searchField'], $_REQUEST['searchOper'], $_REQUEST['searchString']);
                        }
					}

                    if( !in_array($_REQUEST['sord'], array('', 'desc', 'DESC', 'asc', 'ASC')) ||
                        !in_array($_REQUEST['sidx'], array('id', 'node_id', 'source', 'lang', 'text') )
                    )
                    {
                        return false;
                    }
                    $orderby = $_REQUEST['sidx'].' '.$_REQUEST['sord'];

					list($dataset, $total) = webdictionary_search($where, $orderby, $offset, $max);

					for($i=0; $i < count($dataset); $i++)
					{
						$origin = "";
						if(!empty($dataset[$i]['theme']))
                        {
                            $origin = '<i class="fa fa-fw fa-paint-brush ui-text-light" title="'.t(368, "Theme").'"></i> '.$dataset[$i]['theme'];
                        }
						else if(!empty($dataset[$i]['extension']))
                        {
                            $origin = '<i class="fa fa-fw fa-puzzle-piece ui-text-light" title="'.t(617, "Extension").'"></i> '.$dataset[$i]['extension'];
                        }

						if(empty($dataset[$i]))
                        {
                            continue;
                        }

						$string_id = $dataset[$i]['id'];
						if(!empty($dataset[$i]['theme']))
                        {
                            $string_id = $dataset[$i]['theme'].'.'.$string_id;
                        }

						if(!empty($dataset[$i]['extension']))
                        {
                            $string_id = $dataset[$i]['extension'].'.'.$string_id;
                        }

						$out[$i] = array(
							0	=> $string_id,	// this 4th column won't appear, it works as ghost column for setting a unique ID to the row
							1	=> $dataset[$i]['node_id'], // id of the word (Ex. word "3" in English -> test, word "3" in Spanish -> prueba)
							2	=> $origin,
							3 	=> language::name_by_code($dataset[$i]['lang']),
							4	=> core_special_chars($dataset[$i]['text']),
							5   => $dataset[$i]['source']
						);
					}

					navitable::jqgridJson($out, $page, $offset, $max, $total, 0); // 0 is the index of the ghost ID column
					break;
			}
			
			session_write_close();
			exit;
			break;
		
		case 'edit': // edit/new form
			if(!empty($_REQUEST['path']) && !is_numeric($_REQUEST['id']))
            {
                $wtext->load($_REQUEST['path']);
            }
			else if(!empty($_REQUEST['id']))
            {
                $wtext->load(intval($_REQUEST['id']));
            }

			if(isset($_REQUEST['form-sent']))
			{
				$wtext->load_from_post();

				try
				{
                    naviforms::check_csrf_token();
					$wtext->save();
                    $layout->navigate_notification(t(53, "Data saved successfully."), false, false, 'fa fa-check');
				}
				catch(Exception $e)
				{
					$layout->navigate_notification($e->getMessage(), true, true);	
				}
			}
		
			$out = webdictionary_form($wtext);
			break;	
			
		case 'remove':
            if($_REQUEST['rtk'] != $_SESSION['request_token'])
            {
                $layout->navigate_notification(t(344, 'Security error'), true, true);
                break;
            }
			else if(!empty($_REQUEST['id']))
			{
				$wtext->load($_REQUEST['id']);
				if($wtext->delete() > 0)
				{
					$layout->navigate_notification(t(55, 'Item removed successfully.'), false);
					$out = webdictionary_list();
				}
				else
				{
					$layout->navigate_notification(t(56, 'Unexpected error.'), false);
					$out = webdictionary_form($wtext);
				}
			}
			break;

		case 'edit_language':
			if($_REQUEST['form-sent']=='true')
			{
                naviforms::check_csrf_token();
				$status = webdictionary::save_translations_post($_REQUEST['code']);
				if($status=='true')
                {
                    $layout->navigate_notification(t(53, "Data saved successfully."), false, false, 'fa fa-check');
                }
				else
                {
                    $layout->navigate_notification(implode('<br />', $status), true, true);
                }
			}

			$out = webdictionary_edit_language_form($_REQUEST['code']);
			break;
		
		case 0: // list / search result
		default:			
			$out = webdictionary_list();
			break;
	}
	
	return $out;
}
