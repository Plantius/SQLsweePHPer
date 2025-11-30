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
					$where = " 1=1 ";
										
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
                        !in_array($_REQUEST['sidx'], array('id', 'name'))
                    )
                    {
                        return false;
                    }
                    $orderby = $_REQUEST['sidx'].' '.$_REQUEST['sord'];
				
					$DB->queryLimit(
					    'id,name',
                        'nv_profiles',
                        $where,
						$orderby,
						$offset,
						$max
                    );
									
					$dataset = $DB->result();
					$total = $DB->foundRows();

					$out = array();					
											
					for($i=0; $i < count($dataset); $i++)
					{													
						$out[$i] = array(
							0	=> $dataset[$i]['id'],
							1	=> core_special_chars($dataset[$i]['name'])
						);
					}
									
					navitable::jqgridJson($out, $page, $offset, $max, $total);					
					break;
			}
			
			session_write_close();
			exit;
			break;

        case 'edit':
		case 2: // edit/new form		
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
                    permission::update_permissions(
                        json_decode($_REQUEST['navigate_permissions_changes'], true),
                        $item->id,
                        0
                    );
                    $layout->navigate_notification(t(53, "Data saved successfully."), false, false, 'fa fa-check');
				}
				catch(Exception $e)
				{
					$layout->navigate_notification($e->getMessage(), true, true);	
				}
			}
		
			$out = profiles_form($item);
			break;

        case 'delete':
		case 4:
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
					$out = profiles_list();
				}
				else
				{
					$layout->navigate_notification(t(56, 'Unexpected error.'), false);
					$out = profiles_form($item);
				}
			}
			break;

        case 'list':
		case 0: // list / search result
		default:			
			$out = profiles_list();
			break;
	}
	
	return $out;
}
