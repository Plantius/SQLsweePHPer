	public function insert()
	{
		global $DB;	
		global $website;
        global $events;

        $groups = '';
        if(is_array($this->groups))
        {
            $this->groups = array_unique($this->groups); // remove duplicates
            $this->groups = array_filter($this->groups); // remove empty
            if(!empty($this->groups))
            {
                $groups = 'g'.implode(',g', $this->groups);
            }
        }

        if($groups == 'g')
        {
            $groups = '';
        }

		$ok = $DB->execute(' 
		    INSERT INTO nv_webusers
                (	id, website, username, password, email, `groups`, fullname, gender, avatar, birthdate,
                    language, country, region, timezone, company, nin, 
                    address, zipcode, location, phone, social_website,
                    joindate, lastseen, newsletter, private_comment, activation_key, cookie_hash, 
                    access, access_begin, access_end, email_verification_date
                )
                VALUES 
                (
                    :id, :website, :username, :password, :email, :groups, :fullname, :gender, :avatar, :birthdate,
                    :language, :country, :region, :timezone, :company, :nin, 
                    :address, :zipcode, :location, :phone, :social_website,
                    :joindate, :lastseen, :newsletter, :private_comment, :activation_key, :cookie_hash, 
                    :access, :access_begin, :access_end, :email_verification_date
                )',
            array(
                ":id" => 0,
                ":website" => $website->id,
                ":username" => is_null($this->username)? '' : $this->username,
                ":password" => is_null($this->password)? '' : $this->password,
                ":email" => is_null($this->email)? '' : strtolower($this->email),
                ":groups" => $groups,
                ":fullname" => is_null($this->fullname)? '' : $this->fullname,
                ":gender" => is_null($this->gender)? '' : $this->gender,
                ":avatar" => is_null($this->avatar)? '' : $this->avatar,
                ":birthdate" => value_or_default($this->birthdate, 0),
                ":language" => is_null($this->language)? '' : $this->language,
                ":country" => is_null($this->country)? '' : $this->country,
                ":region" => value_or_default($this->region, 0),
                ":timezone" => is_null($this->timezone)? '' : $this->timezone,
                ":company" => is_null($this->company)? '' : $this->company,
                ":nin" => is_null($this->nin)? '' : $this->nin,
                ":address" => is_null($this->address)? '' : $this->address,
                ":zipcode" => is_null($this->zipcode)? '' : $this->zipcode,
                ":location" => is_null($this->location)? '' : $this->location,
                ":phone" => is_null($this->phone)? '' : $this->phone,
                ":social_website" => is_null($this->social_website)? '' : $this->social_website,
                ":joindate" => core_time(),
                ":lastseen" => 0,
                ":newsletter" => is_null($this->newsletter)? '0' : $this->newsletter,
                ":private_comment" => is_null($this->private_comment)? '' : $this->private_comment,
                ":activation_key" => is_null($this->activation_key)? '' : $this->activation_key,
                ":cookie_hash" => is_null($this->cookie_hash)? '' : $this->cookie_hash,
				":access" => value_or_default($this->access, 0),
                ":access_begin" => value_or_default($this->access_begin, 0),
                ":access_end" => value_or_default($this->access_end, 0),
	            ":email_verification_date" => value_or_default($this->email_verification_date, 0)
            )
        );							
				
		if(!$ok)
        {
            throw new Exception($DB->get_last_error());
        }
		
		$this->id = $DB->get_last_id();

        $events->trigger(
            'webuser',
            'save',
            array(
                'webuser' => $this
            )
        );

        $this->new_webuser_notification();
		
		return true;
	}	
