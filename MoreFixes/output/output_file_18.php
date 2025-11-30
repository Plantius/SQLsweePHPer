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
                        !in_array($_REQUEST['sidx'], array('id', 'category', 'codename', 'icon', 'lid', 'enabled'))
                    )
                    {
                        return false;
                    }
                    $orderby = $_REQUEST['sidx'].' '.$_REQUEST['sord'];

				
					$DB->queryLimit('id,lid,category,codename,icon,enabled', 
									'nv_functions', 
									$where, 
									$orderby, 
									$offset, 
									$max);
									
					$dataset = $DB->result();
					$total = $DB->foundRows();
					
					//echo $DB->get_last_error();
					
					$out = array();					
											
					for($i=0; $i < count($dataset); $i++)
					{													
						$out[$i] = array(
							0	=> $dataset[$i]['id'],
							1	=> $dataset[$i]['category'],
							2	=> core_special_chars($dataset[$i]['codename']),
							3	=> '<img src="'.NAVIGATE_URL.'/'.value_or_default($dataset[$i]['icon'], 'img/transparent.gif').'" />',
							4 	=> '['.$dataset[$i]['lid'].'] '.core_special_chars(t($dataset[$i]['lid'], $dataset[$i]['lid'])),
							5	=> (($dataset[$i]['enabled']==1)? '<img src="img/icons/silk/accept.png" />' : '<img src="img/icons/silk/cancel.png" />')
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
                    $layout->navigate_notification(t(53, "Data saved successfully."), false, false, 'fa fa-check');
				}
				catch(Exception $e)
				{
					$layout->navigate_notification($e->getMessage(), true, true);	
				}
			}
		
			$out = functions_form($item);
			break;

        case 'delete':
		case 4:
            if($_REQUEST['rtk'] != $_SESSION['request_token'])
            {
                $layout->navigate_notification(t(344, 'Security error'), true, true);
            }
            else if(!empty($_REQUEST['id']))
			{
				$item->load(intval($_REQUEST['id']));	
				if($item->delete() > 0)
				{
					$layout->navigate_notification(t(55, 'Item removed successfully.'), false);
					$out = functions_list();
				}
				else
				{
					$layout->navigate_notification(t(56, 'Unexpected error.'), false);
					$out = functions_form($item);
				}
			}
			break;
					
		case 0: // list / search result
		default:			
			$out = functions_list();
			break;
	}
	
	return $out;
}
