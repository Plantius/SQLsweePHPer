	public function authenticate($website, $username, $password)
	{
		global $DB;
        global $events;
		
		$username = trim($username);
		$username = mb_strtolower($username);
				
		$A1 = md5($username.':'.APP_REALM.':'.$password);

        $website_check = '';
		if($website > 0)
        {
            $website_check = 'AND website  = '.intval($website);
        }

		$ok = $DB->query(
		    'SELECT * 
                     FROM nv_webusers 
                    WHERE ( access = 0 OR
                            (access = 2 AND 
                                (access_begin = 0 OR access_begin < '.time().') AND 
                                (access_end = 0 OR access_end > '.time().') 
                            )
                           )
                      '.$website_check.'
                      AND LOWER(username) = :username',
        'object',
            array(
                ':username' => $username
            )
        );

		if($ok)
		{		
			$data = $DB->result();

			if(!empty($data))
			{
				if($data[0]->password==$A1)
				{
					$this->load_from_resultset($data);

	                // maybe this function is called without initializing $events
	                if(method_exists($events, 'trigger'))
	                {
	                    $events->trigger(
	                        'webuser',
	                        'sign_in',
	                        array(
	                            'webuser' => $this,
	                            'by' => 'authenticate'
	                        )
	                    );
	                }

					return true;
				}
			}
		}
		
		return false;		
	}
