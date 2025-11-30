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
					$where = " pm.website = ".intval($website->id)." ";

                    $permissions = array(
                        0 => '<img src="img/icons/silk/world.png" align="absmiddle" /> '.t(69, 'Published'),
                        1 => '<img src="img/icons/silk/world_dawn.png" align="absmiddle" /> '.t(70, 'Private'),
                        2 => '<img src="img/icons/silk/world_night.png" align="absmiddle" /> '.t(81, 'Hidden')
                    );

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
                        !in_array($_REQUEST['sidx'], array('id', 'codename', 'title', 'extension', 'permission'))
                    )
                    {
                        return false;
                    }
                    $orderby = $_REQUEST['sidx'].' '.$_REQUEST['sord'];


                    $sql = ' SELECT SQL_CALC_FOUND_ROWS
					                pm.id, pm.codename, pm.extension, pm.image, pm.permission, d.text as title                                    
							   FROM nv_payment_methods pm
						  LEFT JOIN nv_webdictionary d
						  		 	 ON pm.id = d.node_id
								 	AND d.node_type = "payment_method"
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

                    $dataset = grid_notes::summary($dataset, 'payment_method', 'id');

					$out = array();					
											
					for($i=0; $i < count($dataset); $i++)
					{
					    $payment_method_image = $dataset[$i]['image'];
                        if(!empty($payment_method_image))
                            $payment_method_image = '<img src="'.file::file_url($payment_method_image, 'inline').'&height=24&border=true" style="height: 24px; width: auto;" />';
                        else
                            $payment_method_image = '-';

                        $extension_name = "";
                        if(!empty($dataset[$i]['extension']))
                        {
                            $extension = new extension();
                            $extension->load($dataset[$i]['extension']);
                            $extension_name = $extension->title;
                        }

						$out[$i] = array(
							0	=> $dataset[$i]['id'],
                            1	=> core_special_chars($dataset[$i]['codename']),
                            2	=> $payment_method_image,
                            3   => core_special_chars($dataset[$i]['title']),
                            4	=> $extension_name,
                            5   => $permissions[$dataset[$i]['permission']],
                            6 	=> $dataset[$i]['_grid_notes_html']
						);
					}
									
					navitable::jqgridJson($out, $page, $offset, $max, $total);					
					break;
			}
			
			core_terminate();
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

					//$object->save();
					property::save_properties_from_post('payment_method', $object->id);

                    // set block order
                    if(!empty($_REQUEST['payment_methods-order']))
                    {
                        payment_method::reorder($_REQUEST['payment_methods-order']);
                    }

                    $layout->navigate_notification(t(53, "Data saved successfully."), false, false, 'fa fa-check');
				}
				catch(Exception $e)
				{
					$layout->navigate_notification($e->getMessage(), true, true);	
				}
			}

			$out = payment_methods_form($object);
			break;
					
		case 'delete':
            if($_REQUEST['rtk'] != $_SESSION['request_token'])
            {
                $layout->navigate_notification(t(344, 'Security error'), true, true);
                break;
            }
			else if(!empty($_REQUEST['id']))
			{
				$object->load(intval($_REQUEST['id']));	
				if($object->delete() > 0)
				{
					$layout->navigate_notification(t(55, 'Item removed successfully.'), false);
					$out = payment_methods_list();
				}
				else
				{
					$layout->navigate_notification(t(56, 'Unexpected error.'), false);
					$out = payment_methods_form($object);
				}
			}
			break;
					
		case 'list':
		default:			
			$out = payment_methods_list();
			break;
	}
	
	return $out;
}
