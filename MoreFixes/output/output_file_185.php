function leave_usergroup($uid, $leavegroup)
{
	global $db, $mybb, $cache;

	$user = get_user($uid);

	if($user['usergroup'] == $leavegroup)
	{
		return false;
	}

	$groupslist = $comma = '';
	$usergroups = $user['additionalgroups'].",";
	$donegroup = array();

	$groups = explode(",", $user['additionalgroups']);

	if(is_array($groups))
	{
		foreach($groups as $gid)
		{
			if(trim($gid) != "" && $leavegroup != $gid && empty($donegroup[$gid]))
			{
				$groupslist .= $comma.$gid;
				$comma = ",";
				$donegroup[$gid] = 1;
			}
		}
	}

	$dispupdate = "";
	if($leavegroup == $user['displaygroup'])
	{
		$dispupdate = ", displaygroup=usergroup";
	}

	$db->write_query("
		UPDATE ".TABLE_PREFIX."users
		SET additionalgroups='$groupslist' $dispupdate
		WHERE uid='".(int)$uid."'
	");
