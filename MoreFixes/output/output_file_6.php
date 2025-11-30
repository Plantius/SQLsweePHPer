                            $deleted = $deleted + $item->delete();
                        }
                        echo json_encode((count($ids)==$deleted));
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

                    if( !in_array($_REQUEST['sord'], array('', 'desc', 'DESC', 'asc', 'ASC')) ||
                        !in_array($_REQUEST['sidx'], array('id', 'username', 'email', 'profile', 'language', 'blocked') )
                    )
                    {
                        return false;
                    }
                    $orderby = $_REQUEST['sidx'].' '.$_REQUEST['sord'];

					$DB->queryLimit(
					    'id,username,email,profile,language,blocked',
						'nv_users',
                        $where,
						$orderby,
						$offset,
						$max
                    );
									
					$dataset = $DB->result();
					$total = $DB->foundRows();
					
					//echo $DB->get_last_error();
					
					$out = array();				
										
					$profiles = profile::profile_names();
					$languages = language::language_names();
											
					for($i=0; $i < count($dataset); $i++)
					{													
						$out[$i] = array(
							0	=> $dataset[$i]['id'],
							1	=> '<strong>'.core_purify_string($dataset[$i]['username']).'</strong>',
							2	=> core_purify_string($dataset[$i]['email']),
							3 	=> $profiles[$dataset[$i]['profile']],
							4	=> $languages[$dataset[$i]['language']],		
							5	=> (($dataset[$i]['blocked']==1)? '<img src="img/icons/silk/cancel.png" />' : '')
						);
					}
									
					navitable::jqgridJson($out, $page, $offset, $max, $total);					
					break;
			}
			
			session_write_close();
			exit;
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
                    permission::update_permissions(json_decode($_REQUEST['navigate_permissions_changes'], true), 0, $item->id);
                    $layout->navigate_notification(t(53, "Data saved successfully."), false, false, 'fa fa-check');
				}
				catch(Exception $e)
				{
					$layout->navigate_notification($e->getMessage(), true, true);	
				}
			}
		
			$out = users_form($item);
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
					$out = users_list();
				}
				else
				{
					$layout->navigate_notification(t(56, 'Unexpected error.'), false);
					$out = users_form($item);
				}
			}
			break;

					
		case 0: // list / search result
		default:			
			$out = users_list();
			break;
	}
	
	return $out;
}
