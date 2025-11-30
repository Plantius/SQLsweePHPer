                            $item->delete();
                        }
                        echo json_encode(true);
                    }
                    else
                    {
                        echo json_encode(false);
                    }
					break;
					
				default: // list or search	
					$page = intval($_REQUEST['page']);
					$max	= intval($_REQUEST['rows']);
					$offset = ($page - 1) * $max;
					$where = " f.website = ".$website->id;
										
					if($_REQUEST['_search']=='true' || isset($_REQUEST['quicksearch']))
					{
						if(isset($_REQUEST['quicksearch']))
                        {
                            $where .= $item->quicksearch($_REQUEST['quicksearch']);
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

                    // filter orderby vars
                    if( !in_array($_REQUEST['sord'], array('', 'desc', 'DESC', 'asc', 'ASC')) ||
                        !in_array($_REQUEST['sidx'], array('id', 'title', 'categories', 'format', 'views', 'permission', 'enabled'))
                    )
                    {
                        return false;
                    }
                    $orderby = $_REQUEST['sidx'].' '.$_REQUEST['sord'];
								
					$sql = ' SELECT SQL_CALC_FOUND_ROWS f.*, d.text as title
							   FROM nv_feeds f
						  LEFT JOIN nv_webdictionary d
						  		 	 ON f.id = d.node_id
								 	AND d.node_type = "feed"
									AND d.subtype = "title"
									AND d.lang = "'.$website->languages_list[0].'"
									AND d.website = '.$website->id.'
							  WHERE '.$where.'	
						   ORDER BY '.$orderby.' 
							  LIMIT '.$max.'
							 OFFSET '.$offset;	
				
					if(!$DB->query($sql, 'array'))
					{
						throw new Exception($DB->get_last_error());	
					}
					
					$dataset = $DB->result();	
					$total = $DB->foundRows();					
					
					$out = array();		
					$permissions = array(	
							0 => '<img src="img/icons/silk/world.png" align="absmiddle" /> '.t(69, 'Published'),
							1 => '<img src="img/icons/silk/world_dawn.png" align="absmiddle" /> '.t(70, 'Private'),
							2 => '<img src="img/icons/silk/world_night.png" align="absmiddle" /> '.t(81, 'Hidden')
						);		
					
					if(empty($dataset)) $rows = 0;
					else				$rows = count($dataset);
	
					for($i=0; $i < $rows; $i++)
					{						
						$out[$i] = array(
							0	=> $dataset[$i]['id'],
							1 	=> $dataset[$i]['title'],
							2 	=> count(explode(',', $dataset[$i]['categories'])),
							3 	=> $dataset[$i]['format'],
							4 	=> $dataset[$i]['views'],
							5	=> $permissions[$dataset[$i]['permission']],
							6	=> (($dataset[$i]['enabled']==1)? '<img src="img/icons/silk/accept.png" />' : '<img src="img/icons/silk/cancel.png" />')
						);
					}
									
					navitable::jqgridJson($out, $page, $offset, $max, $total);					
					break;
			}
			
			core_terminate();
			break;
		
		case 2: // edit/new form
        case 'edit':
			if(!empty($_REQUEST['id']))
			{
				$item->load(intval($_REQUEST['id']));	
			}
							
			if(isset($_REQUEST['form-sent']))
			{						
				$item->load_from_post();
								
				try
				{
                    naviforms::check_csrf_token();

					$item->save();
					$id = $item->id;
					unset($item);
					$item = new feed();				
					$item->load($id);

                    $layout->navigate_notification(t(53, "Data saved successfully."), false, false, 'fa fa-check');
				}
				catch(Exception $e)
				{
					$layout->navigate_notification($e->getMessage(), true, true);	
				}
			}
		
			$out = feeds_form($item);
			break;

        case 'delete':
		case 4: // remove
            if($_REQUEST['rtk'] != $_SESSION['request_token'])
            {
                $layout->navigate_notification(t(344, 'Security error'), true, true);
                break;
            }
			else if(!empty($_REQUEST['id']))
			{
				$item->load(intval($_REQUEST['id']));	
				if($item->delete() > 0)
				{
					$layout->navigate_notification(t(55, 'Item removed successfully.'), false);
					$out = feeds_list();
				}
				else
				{
					$layout->navigate_notification(t(56, 'Unexpected error.'), false);
					$out = feeds_list();
				}
			}
			break;
			
		case "path_check": // check if a requested path is not used
			$DB->query(
			    'SELECT type, object_id, lang
                      FROM nv_paths
                     WHERE path = :path
                       AND website = :wid',
                'object',
                array(
                    ':wid' => $this->website,
                    'path' => $_REQUEST['path']
                )
            );
						 
			$rs = $DB->result();
			
			echo json_encode($rs);
			core_terminate();		
			break;						

		case 0: // list / search result
		default:			
			$out = feeds_list();
			break;
	}
	
	return $out;
}
