	function checkPassword($user_login, $user_password) {
		global $fmdb, $__FM_CONFIG, $fm_name;
		
		if (empty($user_login) || empty($user_password)) return false;
		
		/** Built-in authentication */
		$fm_db_version = getOption('fm_db_version');
		$auth_method = ($fm_db_version >= 18) ? getOption('auth_method') : true;
		if ($auth_method) {
			/** Use Builtin Auth when Default Auth Method is LDAP but user is defined with 'facileManager/Builtin' */
			$result = $fmdb->query("SELECT * FROM `fm_users` WHERE `user_login` = '$user_login' and `user_auth_type`=1 and `user_status`='active'");
			if (is_array($fmdb->last_result) && $fmdb->last_result[0]->user_login == $user_login) {
				$auth_method = 1;
			}

			/** Builtin Authentication */
			if ($auth_method == 1) {
				if ($fm_db_version >= 18) {
					$result = $fmdb->get_results("SELECT * FROM `fm_users` WHERE `user_status`='active' AND `user_auth_type`=1 AND `user_template_only`='no' AND `user_login`='$user_login'");
				} else {
					/** Old auth */
					$result = $fmdb->get_results("SELECT * FROM `fm_users` WHERE `user_status`='active' AND `user_login`='$user_login' AND `user_password`='$user_password'");
				}
				if (!$fmdb->num_rows) {
					return false;
				} else {
					$user = $fmdb->last_result[0];
					
					/** Check password */
					if ($user->user_password[0] == '*') {
						/** Old MySQL hashing that needs to change */
						if ($user->user_password != '*' . strtoupper(sha1(sha1($user_password, true)))) {
							return false;
						}
						resetPassword($user_login, $user_password);
					} else {
						/** PHP hashing */
						if (!password_verify($user_password, $user->user_password)) {
							return false;
						}
					}
					
					/** Enforce password change? */
					if ($fm_db_version >= 15) {
						if ($user->user_force_pwd_change == 'yes') {
							$pwd_reset_query = "SELECT * FROM `fm_pwd_resets` WHERE `pwd_login`={$user->user_id} ORDER BY `pwd_timestamp` LIMIT 1";
							$fmdb->get_results($pwd_reset_query);
							if ($fmdb->num_rows) {
								$reset = $fmdb->last_result[0];
								return array($reset->pwd_id, $user_login);
							}
						}
					}
			
					$this->setSession($user);

					return true;
				}
			/** LDAP Authentication */
			} else {
				return $this->doLDAPAuth($user_login, $_POST['password']);
			}
		}
		
		return false;
	}
