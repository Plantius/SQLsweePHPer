                            $object->delete();
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
					$where = " website = ".intval($website->id)." ";
										
					if($_REQUEST['_search']=='true' || isset($_REQUEST['quicksearch']))
					{
						if(isset($_REQUEST['quicksearch']))
                        {
                            $where .= $object->quicksearch($_REQUEST['quicksearch']);
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
                        !in_array($_REQUEST['sidx'], array('id', 'name'))
                    )
                    {
                        return false;
                    }
                    $orderby = $_REQUEST['sidx'].' '.$_REQUEST['sord'];
				
					$DB->queryLimit(
					    'id,name,image',
                        'nv_brands',
                        $where,
                        $orderby,
                        $offset,
                        $max
                    );
									
					$dataset = $DB->result();
					$total = $DB->foundRows();

                    $dataset = grid_notes::summary($dataset, 'brand', 'id');

					$out = array();					
											
					for($i=0; $i < count($dataset); $i++)
					{
					    $brand_image = $dataset[$i]['image'];
                        if(!empty($brand_image))
                        {
                            $brand_image = '<img src="'.file::file_url($brand_image, 'inline').'&width=64&height=48&border=true" />';
                        }
                        else
                        {
                            $brand_image = '-';
                        }

						$out[$i] = array(
							0	=> $dataset[$i]['id'],
							1	=> $brand_image,
							2	=> core_special_chars($dataset[$i]['name']),
                            3 	=> $dataset[$i]['_grid_notes_html']
						);
					}
									
					navitable::jqgridJson($out, $page, $offset, $max, $total);					
					break;
			}
			
			session_write_close();
			exit;
			break;

        case 'create':
		case 'edit':
			if(!empty($_REQUEST['id']))
            {
                $object->load(intval($_REQUEST['id']));
            }

			if(isset($_REQUEST['form-sent']))
			{
				$object->load_from_post();
				try
				{
                    naviforms::check_csrf_token();
					$object->save();
                    $layout->navigate_notification(t(53, "Data saved successfully."), false, false, 'fa fa-check');
				}
				catch(Exception $e)
				{
					$layout->navigate_notification($e->getMessage(), true, true);	
				}
			}
		
			$out = brands_form($object);
			break;
					
		case 'delete':
            if($_REQUEST['rtk'] != $_SESSION['request_token'])
            {
                $layout->navigate_notification(t(344, 'Security error'), true, true);
            }
            else if(!empty($_REQUEST['id']))
			{
				$object->load(intval($_REQUEST['id']));	
				if($object->delete() > 0)
				{
					$layout->navigate_notification(t(55, 'Item removed successfully.'), false);
					$out = brands_list();
				}
				else
				{
					$layout->navigate_notification(t(56, 'Unexpected error.'), false);
					$out = brands_form($object);
				}
			}
			break;
					
		case 'list':
		default:			
			$out = brands_list();
			break;
	}
	
	return $out;
}
