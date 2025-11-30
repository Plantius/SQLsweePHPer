									list($day, $month, $year) = explode('-', $date);
									if($day > 0)
									{
										$time += 60*60*24*$day;
									}

									if($month > 0)
									{
										$time += 60*60*24*30*$month;
									}

									if($year > 0)
									{
										$time += 60*60*24*365*$year;
									}

									if($time == $action['length'])
									{
										$bantime = $date;
										break;
									}
								}
							}

							$new_ban = array(
								"uid" => $user['uid'],
								"gid" => $action['usergroup'],
								"oldgroup" => $user['usergroup'],
								"oldadditionalgroups" => $user['additionalgroups'],
								"olddisplaygroup" => $user['displaygroup'],
								"admin" => $mybb->user['uid'],
								"dateline" => TIME_NOW,
								"bantime" => $db->escape_string($bantime),
								"lifted" => $expiration,
								"reason" => $db->escape_string($warning['title'])
							);
							// Delete old ban for this user, taking details
							if($existing_ban['uid'])
							{
								$db->delete_query("banned", "uid='{$user['uid']}' AND gid='{$action['usergroup']}'");
								// Override new ban details with old group info
								$new_ban['oldgroup'] = $existing_ban['oldgroup'];
								$new_ban['oldadditionalgroups'] = $existing_ban['oldadditionalgroups'];
								$new_ban['olddisplaygroup'] = $existing_ban['olddisplaygroup'];
							}

							$period = $lang->expiration_never;
							$ban_length = fetch_friendly_expiration($action['length']);

							if($ban_length['time'])
							{
								$lang_str = "expiration_".$ban_length['period'];
								$period = $lang->sprintf($lang->result_period, $ban_length['time'], $lang->$lang_str);
							}

							$group_name = $groupscache[$action['usergroup']]['title'];
							$this->friendly_action = $lang->sprintf($lang->redirect_warned_banned, $group_name, $period);

							$db->insert_query("banned", $new_ban);
							$this->updated_user['usergroup'] = $action['usergroup'];
							$this->updated_user['additionalgroups'] = '';
							$this->updated_user['displaygroup'] = 0;
						}
						break;
					// Suspend posting privileges
					case 2:
						// Only perform if the expiration time is greater than the users current suspension period
						if($expiration == 0 || $expiration > $user['suspensiontime'])
						{
							if(($user['suspensiontime'] != 0 && $user['suspendposting']) || !$user['suspendposting'])
							{
								$period = $lang->expiration_never;
								$ban_length = fetch_friendly_expiration($action['length']);

								if($ban_length['time'])
								{
									$lang_str = "expiration_".$ban_length['period'];
									$period = $lang->sprintf($lang->result_period, $ban_length['time'], $lang->$lang_str);
								}

								$this->friendly_action = $lang->sprintf($lang->redirect_warned_suspended, $period);

								$this->updated_user['suspensiontime'] = $expiration;
								$this->updated_user['suspendposting'] = 1;
							}
						}
						break;
					// Moderate new posts
					case 3:
						// Only perform if the expiration time is greater than the users current suspension period
						if($expiration == 0 || $expiration > $user['moderationtime'])
						{
							if(($user['moderationtime'] != 0 && $user['moderateposts']) || !$user['suspendposting'])
							{
								$period = $lang->expiration_never;
								$ban_length = fetch_friendly_expiration($action['length']);

								if($ban_length['time'])
								{
									$lang_str = "expiration_".$ban_length['period'];
									$period = $lang->sprintf($lang->result_period, $ban_length['time'], $lang->$lang_str);
								}

								$this->friendly_action = $lang->sprintf($lang->redirect_warned_moderate, $period);

								$this->updated_user['moderationtime'] = $expiration;
								$this->updated_user['moderateposts'] = 1;
							}
						}
						break;
				}
			}
		}
		else
		{
			// Warning is still active, lower users point count
			if($warning['expired'] != 1)
			{
				$new_warning_points = $user['warningpoints']-$warning['points'];
				if($new_warning_points < 0)
				{
					$new_warning_points = 0;
				}

				$this->updated_user = array(
					"warningpoints" => $new_warning_points
				);


				// check if we need to revoke any consequences with this warning
				$current_level = round($user['warningpoints']/$mybb->settings['maxwarningpoints']*100);
				$this->new_warning_level = round($new_warning_points/$mybb->settings['maxwarningpoints']*100);
				$query = $db->simple_select("warninglevels", "action", "percentage>{$this->new_warning_level} AND percentage<=$current_level");
				if($db->num_rows($query))
				{
					// we have some warning levels we need to revoke
					$max_expiration_times = $check_levels = array();
					find_warnlevels_to_check($query, $max_expiration_times, $check_levels);

					// now check warning levels already applied to this user to see if we need to lower any expiration times
					$query = $db->simple_select("warninglevels", "action", "percentage<={$this->new_warning_level}");
					$lower_expiration_times = $lower_levels = array();
					find_warnlevels_to_check($query, $lower_expiration_times, $lower_levels);

					// now that we've got all the info, do necessary stuff
					for($i = 1; $i <= 3; ++$i)
					{
						if($check_levels[$i])
						{
							switch($i)
							{
								case 1: // Ban
									// we'll have to resort to letting the admin/mod remove the ban manually, since there's an issue if stacked bans are in force...
									continue 2;
								case 2: // Revoke posting
									$current_expiry_field = 'suspensiontime';
									$current_inforce_field = 'suspendposting';
									break;
								case 3:
									$current_expiry_field = 'moderationtime';
									$current_inforce_field = 'moderateposts';
									break;
							}

							// if the thing isn't in force, don't bother with trying to update anything
							if(!$user[$current_inforce_field])
							{
								continue;
							}

							if($lower_levels[$i])
							{
								// lessen the expiration time if necessary

								if(!$lower_expiration_times[$i])
								{
									// doesn't expire - enforce this
									$this->updated_user[$current_expiry_field] = 0;
									continue;
								}

								if($max_expiration_times[$i])
								{
									// if the old level did have an expiry time...
									if($max_expiration_times[$i] <= $lower_expiration_times[$i])
									{
										// if the lower expiration time is actually higher than the upper expiration time -> skip
										continue;
									}
									// both new and old max expiry times aren't infinite, so we can take a difference
									$expire_offset = ($lower_expiration_times[$i] - $max_expiration_times[$i]);
								}
								else
								{
									// the old level never expired, not much we can do but try to estimate a new expiry time... which will just happen to be starting from today...
									$expire_offset = TIME_NOW + $lower_expiration_times[$i];
									// if the user's expiry time is already less than what we're going to set it to, skip
									if($user[$current_expiry_field] <= $expire_offset)
									{
										continue;
									}
								}

								$this->updated_user[$current_expiry_field] = $user[$current_expiry_field] + $expire_offset;
								// double-check if it's expired already
								if($this->updated_user[$current_expiry_field] < TIME_NOW)
								{
									$this->updated_user[$current_expiry_field] = 0;
									$this->updated_user[$current_inforce_field] = 0;
								}
							}
							else
							{
								// there's no lower level for this type - remove the consequence entirely
								$this->updated_user[$current_expiry_field] = 0;
								$this->updated_user[$current_inforce_field] = 0;
							}
						}
					}
				}
			}
		}

		// Save updated details
		$db->update_query("users", $this->updated_user, "uid='{$user['uid']}'");

		$mybb->cache->update_moderators();

		return $this->updated_user;
	}
