                                $item->delete();
                            }
                            echo json_encode(true);
                        }
                        else
                        {
                            echo json_encode(false);
                        }
                    }
                    core_terminate();
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
                        !in_array($_REQUEST['sidx'], array('id', 'favicon', 'name', 'homepage', 'permission'))
                    )
                    {
                        return false;
                    }
                    $orderby = $_REQUEST['sidx'].' '.$_REQUEST['sord'];

					$DB->queryLimit(
						'id,name,subdomain,domain,folder,homepage,permission,favicon',
						'nv_websites',
						$where,
						$orderby,
						$offset,
						$max
					);

					$dataset = $DB->result();
					$total = $DB->foundRows();
					//echo $DB->get_last_error();

					$out = array();

					$permissions = array(
						0 => '<img src="img/icons/silk/world.png" align="absmiddle" /> '.t(69, 'Published'),
						1 => '<img src="img/icons/silk/world_dawn.png" align="absmiddle" /> '.t(70, 'Private'),
						2 => '<img src="img/icons/silk/world_night.png" align="absmiddle" /> '.t(81, 'Hidden')
					);

					for($i=0; $i < count($dataset); $i++)
					{
						$homepage = 'http://';
						$homepage_relative_url = $dataset[$i]['homepage'];
						if(is_numeric($homepage_relative_url))
						{
							$homepage_relative_url = path::loadElementPaths('structure', $homepage_relative_url);
							$homepage_relative_url = array_shift($homepage_relative_url);
						}

						if(!empty($dataset[$i]['subdomain']))
                        {
                            $homepage .= $dataset[$i]['subdomain'].'.';
                        }
						$homepage .= $dataset[$i]['domain'].$dataset[$i]['folder'].$homepage_relative_url;

                        $favicon = '';
                        if(!empty($dataset[$i]['favicon']))
                        {
                            $favicon = '<img src="'.NVWEB_OBJECT.'?type=img&id='.$dataset[$i]['favicon'].'&width=24&height=24" align="absmiddle" height="24" />';
                        }

						$out[$i] = array(
							0	=> $dataset[$i]['id'],
							1	=> $favicon,
							2	=> $dataset[$i]['name'],
							3	=> '<a href="'.$homepage.'" target="_blank"><img align="absmiddle" src="'.NAVIGATE_URL.'/img/icons/silk/house_link.png"></a> '.$homepage,
							4	=> $permissions[$dataset[$i]['permission']]
						);
					}

					navitable::jqgridJson($out, $page, $offset, $max, $total);
					break;
			}

			session_write_close();
			exit;
			break;

        case 'cache_clean':
            update::cache_clean(intval($_REQUEST['id']));
            // don't break, show website edit form

        case 'edit':
		case 2: // edit/new form
			if(!empty($_REQUEST['id']))
			{
				$item->load(intval($_REQUEST['id']));
			}

			if(isset($_REQUEST['form-sent']) && $user->permission('websites.edit')=='true')
			{
				$item->load_from_post();

				try
				{
                    naviforms::check_csrf_token();

					$item->save();
					$id = $item->id;
					unset($item);
					$item = new website();
					$item->load($id);

                    $layout->navigate_notification(t(53, "Data saved successfully."), false, false, 'fa fa-check');
				}
				catch(Exception $e)
				{
					$layout->navigate_notification($e->getMessage(), true, true);
				}

				if(!empty($item->id))
                {
                    users_log::action($_REQUEST['fid'], $item->id, 'save', $item->name, json_encode($_REQUEST));
                }
			}
			else
			{
				if(!empty($item->id))
                {
                    users_log::action($_REQUEST['fid'], $item->id, 'load', $item->name);
                }
			}

			$out = websites_form($item);
			break;

		case 'remove':
        case 'delete':
		case 4:
            if($_REQUEST['rtk'] != $_SESSION['request_token'])
            {
                $layout->navigate_notification(t(344, 'Security error'), true, true);
                break;
            }
			else if(!empty($_REQUEST['id']) && ($user->permission('websites.delete')=='true'))
			{
				$item->load(intval($_REQUEST['id']));
				if($item->delete() > 0)
				{
					$layout->navigate_notification(t(55, 'Item removed successfully.'), false);

					if(!empty($item->id))
                    {
                        users_log::action($_REQUEST['fid'], $item->id, 'remove', $item->name, json_encode($_REQUEST));
                    }

                    // if we don't have any websites, tell user a new one will be created
                    $test = $DB->query_single('id', 'nv_websites');

                    if(empty($test) || !$test)
                    {
                        $layout->navigate_notification(t(520, 'No website found; a default one has been created.'), false, true);
                        $nwebsite = new website();
                        $nwebsite->create_default();
                    }

                    $out = websites_list();
				}
				else
				{
					$layout->navigate_notification(t(56, 'Unexpected error.'), false);
					$out = websites_form($item);
				}
			}
			break;

		case 5:	// search an existing path
			$DB->query(
			    'SELECT path as id, path as label, path as value
						  FROM nv_paths
						 WHERE path LIKE :term
						   AND website = :website_id
				      ORDER BY path ASC
					     LIMIT 30',
                'array',
                array(
                    ':website_id' => $_REQUEST['wid'],
                    ':term' => '%'.$_REQUEST['term'].'%'
                ));

			echo json_encode($DB->result());

			core_terminate();
			break;
			
		case 'email_test':
			$website->mail_mailer = $_REQUEST['mail_mailer'];
			$website->mail_server = $_REQUEST['mail_server'];
			$website->mail_port = $_REQUEST['mail_port'];
			$website->mail_address = $_REQUEST['mail_address'];
			$website->mail_user = $_REQUEST['mail_user'];
            $website->mail_security = $_REQUEST['mail_security'];
            $website->mail_ignore_ssl_security = $_REQUEST['mail_ignore_ssl_security'];

			if(!empty($_REQUEST['mail_password']))
            {
                $website->mail_password = $_REQUEST['mail_password'];
            }

			$ok = navigate_send_email(APP_NAME, APP_NAME.'<br /><br />'.NAVIGATE_URL, $_REQUEST['send_to']);
			echo json_encode($ok);
			core_terminate();

			break;

        case 'reset_statistics':
            if(!naviforms::check_csrf_token('header'))
            {
                echo 'false';
            }
            else if($user->permission('websites.edit')=='true')
            {
				$website_id = trim($_REQUEST['website']);
				$website_id = intval($website_id);

                $DB->execute('UPDATE nv_items SET views = 0 WHERE website = '.$website_id);
                $DB->execute('UPDATE nv_paths SET views = 0 WHERE website = '.$website_id);
                $DB->execute('UPDATE nv_structure SET views = 0 WHERE website = '.$website_id);
                echo 'true';

				users_log::action($_REQUEST['fid'], $website_id, 'reset_statistics', "", json_encode($_REQUEST));
            }
            core_terminate();
            break;

		case 'replace_urls':
			$old = trim($_REQUEST['old']);
			$new = trim($_REQUEST['new']);
			$website_id = trim($_REQUEST['website']);

            if(!naviforms::check_csrf_token('header'))
            {
                echo 'false';
            }
			else if(!empty($old) && !empty($new))
			{
				// replace occurrences in nv_webdictionary
				$ok = $DB->execute('
					UPDATE nv_webdictionary
					   SET text = replace(text, :old, :new)
					 WHERE website = :wid',
					array(
						':old' => $old,
						':new' => $new,
						':wid' => $website_id
					)
				);

				// replace occurrences in nv_blocks (triggers & actions)
				$ok = $DB->execute('
					UPDATE nv_blocks
					   SET `trigger` = replace(`trigger`, :old, :new),
					   	   `action` = replace(`action`, :old, :new)
					 WHERE website = :wid',
					array(
						':old' => $old,
						':new' => $new,
						':wid' => $website_id
					)
				);

				echo ($ok? 'true' : 'false');

				if($ok)
                {
                    users_log::action($_REQUEST['fid'], $website_id, 'replace_urls', "", json_encode($_REQUEST));
                }
			}
			else
			{
				echo 'false';
			}
			core_terminate();
			break;

		case 'remove_content':
			$website_id = trim($_REQUEST['website']);
			$website_id = intval($website_id);
			$password = trim($_REQUEST['password']);

			$authenticated = $user->authenticate($user->username, $password);

            if(!naviforms::check_csrf_token('header'))
            {
                echo 'false';
            }
			else if($authenticated)
			{
				// remove all content except Webusers and Files
				@set_time_limit(0);

				$ok = $DB->execute('
					DELETE FROM nv_blocks WHERE website = '.$website_id.';
					DELETE FROM nv_block_groups WHERE website = '.$website_id.';
					DELETE FROM nv_comments WHERE website = '.$website_id.';
					DELETE FROM nv_structure WHERE website = '.$website_id.';
					DELETE FROM nv_feeds WHERE website = '.$website_id.';
					DELETE FROM nv_items WHERE website = '.$website_id.';
					DELETE FROM nv_notes WHERE website = '.$website_id.';
					DELETE FROM nv_paths WHERE website = '.$website_id.';
					DELETE FROM nv_properties WHERE website = '.$website_id.';
					DELETE FROM nv_properties_items WHERE website = '.$website_id.';
					DELETE FROM nv_search_log WHERE website = '.$website_id.';
					DELETE FROM nv_webdictionary WHERE website = '.$website_id.';
					DELETE FROM nv_webdictionary_history WHERE website = '.$website_id.';
				');

				if($ok)
                {
                    users_log::action($_REQUEST['fid'], $website_id, 'remove_content', "", json_encode($_REQUEST));
                }

				echo ($ok? 'true' : $DB->error());
			}
			else
			{
				echo '';
			}
			
			core_terminate();
			break;

		case 0: // list / search result
		default:
			$out = websites_list();
			break;
	}

	return $out;
}
