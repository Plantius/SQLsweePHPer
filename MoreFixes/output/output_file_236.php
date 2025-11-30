	function updateUser($post) {
		global $fmdb, $fm_name, $fm_login;
		
		/** Template user? */
		if (isset($post['user_template_only']) && $post['user_template_only'] == 'yes') {
			$post['user_template_only'] = 'yes';
			$post['user_auth_type'] = 0;
			$post['user_status'] = 'disabled';
		} else {
			$post['user_template_only'] = 'no';
			$post['user_auth_type'] = getNameFromID($post['user_id'], 'fm_users', 'user_', 'user_id', 'user_auth_type');
			if (!$post['user_auth_type']) $post['user_auth_type'] = 1;
		}

		$post['user_id'] = (!isset($post['user_id']) || !$post['user_id']) ? $_SESSION['user']['id'] : intval($post['user_id']);

		/** Authorized to update users? */
		if ((!currentUserCan('manage_users') && $_SESSION['user']['id'] != $post['user_id']) || isset($post['user_login'])) {
			return _('You do not have permission to make these changes.');
		}

		if (!empty($post['user_password'])) {
			if (empty($post['cpassword']) || $post['user_password'] != $post['cpassword']) return _('Passwords do not match.');
			$post['user_password'] = sanitize($post['user_password']);
			if (password_verify($post['user_password'], getNameFromID($post['user_id'], 'fm_users', 'user_', 'user_id', 'user_password'))) return _('Password is not changed.');
			$sql_pwd = "`user_password`='" . password_hash($post['user_password'], PASSWORD_DEFAULT) . "',";
		} else $sql_pwd = null;
		
		$sql_edit = null;
		
		$exclude = array('submit', 'action', 'user_id', 'cpassword', 'user_password', 'user_caps', 'is_ajax', 'process_user_caps', 'type');

		foreach ($post as $key => $data) {
			if (!in_array($key, $exclude)) {
				$sql_edit .= $key . "='" . sanitize($data) . "', ";
			}
		}
		$sql = rtrim($sql_edit . $sql_pwd, ', ');
		
		/** Process user permissions */
		if (isset($post['process_user_caps']) && (!isset($post['user_caps']) || $post['user_group'])) $post['user_caps'] = array();
		
		if (isset($post['user_caps'][$fm_name])) {
			if (array_key_exists('do_everything', $post['user_caps'][$fm_name])) {
				$post['user_caps'] = array($fm_name => array('do_everything' => 1));
			}
		}
		if (isset($post['user_caps'])) {
			$sql .= ",user_caps='" . serialize($post['user_caps']) . "'";
		}
		
		/** Update the user */
		$query = "UPDATE `fm_users` SET $sql WHERE `user_id`={$post['user_id']} AND `account_id`='{$_SESSION['user']['account_id']}'";
		$result = $fmdb->query($query);
		
		if ($fmdb->sql_errors) {
			return formatError(_('Could not update the user because a database error occurred.'), 'sql');
		}

		$user_login = getNameFromID($post['user_id'], 'fm_users', 'user_', 'user_id', 'user_login');
		
		/** Process forced password change */
		if (isset($post['user_force_pwd_change']) && $post['user_force_pwd_change'] == 'yes') $fm_login->processUserPwdResetForm($user_login);
		
		addLogEntry(sprintf(_("Updated user '%s'."), $user_login), $fm_name);
		
		return true;
	}
