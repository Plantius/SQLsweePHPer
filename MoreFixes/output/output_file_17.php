						$item->delete();
					}
					echo json_encode(true);
					break;
					
				default: // list or search	
					$page = intval($_REQUEST['page']);
					$max	= intval($_REQUEST['rows']);
					$offset = ($page - 1) * $max;
					$where = " i.website = ".$website->id;
										
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
                        !in_array($_REQUEST['sidx'], array('id', 'date_created', 'title', 'size', 'status'))
                    )
                    {
                        return false;
                    }
                    $orderby = $_REQUEST['sidx'].' '.$_REQUEST['sord'];
								
					$sql = ' SELECT SQL_CALC_FOUND_ROWS i.*
							   FROM nv_backups i
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
					
					if(empty($dataset))
                    {
                        $rows = 0;
                    }
					else
                    {
                        $rows = count($dataset);
                    }
	
					for($i=0; $i < $rows; $i++)
					{						
						$out[$i] = array(
							0	=> $dataset[$i]['id'],
							1 	=> core_ts2date($dataset[$i]['date_created'], true),
							2 	=> core_special_chars($dataset[$i]['title']),
							3 	=> core_bytes($dataset[$i]['size']),
							4	=> backup::status($dataset[$i]['status'])
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
							
			if($_REQUEST['form-sent']=='true')
			{						
				$item->load_from_post();
                naviforms::check_csrf_token();

				try
				{
                    // update an existing backup
					$item->save();
                    $layout->navigate_notification(t(53, "Data saved successfully."), false, false, 'fa fa-check');
				}
				catch(Exception $e)
				{
					$layout->navigate_notification($e->getMessage(), true, true);	
				}
			}
		
			$out = backups_form($item);
			break;
			
		case 4:
        case 'delete':
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
					$out = backups_list();
				}
				else
				{
					$layout->navigate_notification(t(56, 'Unexpected error.'), false);
					$out = webdictionary_list();
				}
			}
			break;

        case 'backup':
            if(!empty($_REQUEST['id']))
			{
                // trick to generate a underground process ;)
                @set_time_limit(0);
                @ignore_user_abort(true);
                $foo = str_pad('Navigate CMS ', 2048, 'Navigate CMS  ');

                header("HTTP/1.1 200 OK");
                header("Content-Length: ".strlen($foo));
                echo $foo;
                header('Connection: close');

                if(ob_get_length()!==false)
                {
                    ob_end_flush();
                    ob_flush();
                }
                flush();
                session_write_close();
                // now the process is running in the server, the client thinks the http request has finished
                
				$item->load(intval($_REQUEST['id']));
                $item->backup();
			}
            core_terminate();
            break;

        case 'restore':
            // TO DO: Restore
            break;

        case 'download':
            // download backup
            $item->load(intval($_REQUEST['id']));

			ob_end_flush();

            header('Content-type: application/zip');
			header("Content-Length: ".filesize(NAVIGATE_PRIVATE.$item->file));
			header('Content-Disposition: attachment; filename="'.basename($item->file).'"');

			readfile(NAVIGATE_PRIVATE.$item->file);

            core_terminate();
            break;
			
		case 0: // list / search result
		default:			
			$out = backups_list();
			break;
	}
	
	return $out;
}
