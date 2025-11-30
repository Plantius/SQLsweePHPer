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
					$where = " c.website = ".intval($website->id)." ";
										
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
				
                    $sql = ' SELECT SQL_CALC_FOUND_ROWS
					                c.id, c.code, c.date_begin, c.date_end, c.type, d.text as name, d.lang as language                                    
							   FROM nv_coupons c
						  LEFT JOIN nv_webdictionary d
						  		 	 ON c.id = d.node_id
								 	AND d.node_type = "coupon"
									AND d.subtype = "name"
									AND d.lang = "'.$website->languages_list[0].'"
									AND d.website = '.$website->id.'
							  WHERE '.$where.'	
						   GROUP BY c.id, c.code, c.date_begin, c.date_end, c.type, d.text, d.lang						   
						   ORDER BY '.$orderby.' 
							  LIMIT '.$max.'
							 OFFSET '.$offset;

                    if(!$DB->query($sql, 'array'))
                    {
                        throw new Exception($DB->get_last_error());
                    }

                    $dataset = $DB->result();
                    $total = $DB->foundRows();

                    $dataset = grid_notes::summary($dataset, 'coupon', 'id');

                    $types = array(
                        'discount_amount' => t(697, "Discount amount"),
                        'discount_percentage'   =>  t(698, "Discount percentage"),
                        'free_shipping' => t(699, "Free shipping")
                    );

					$out = array();
											
					for($i=0; $i < count($dataset); $i++)
					{
                        $date_begin = core_ts2date($dataset[$i]['date_begin'], false, true);
                        $date_end = core_ts2date($dataset[$i]['date_end'], false, true);

						$out[$i] = array(
							0	=> $dataset[$i]['id'],
							1	=> core_special_chars($dataset[$i]['code']),
							2	=> core_special_chars($dataset[$i]['name']),
							3	=> $types[$dataset[$i]['type']],
							4	=> $date_begin .' - '.$date_end,
                            5 	=> $dataset[$i]['_grid_notes_html']
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
				$object->load(intval($_REQUEST['id']));

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
		
			$out = coupons_form($object);
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
					$out = coupons_list();
				}
				else
				{
					$layout->navigate_notification(t(56, 'Unexpected error.'), false);
					$out = coupons_form($object);
				}
			}
			break;
					
		case 'list':
		default:			
			$out = coupons_list();
			break;
	}
	
	return $out;
}
