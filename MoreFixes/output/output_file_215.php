	function query($Query_String, $line = '', $file = '', $offset=0, $num_rows=-1, $inputarr=false, $fetchmode=self::FETCH_BOTH, $reconnect=true)
	{
		if ($Query_String == '')
		{
			return 0;
		}
		if (!$this->Link_ID && !$this->connect())
		{
			return False;
		}

		if ($this->Link_ID->fetchMode != $fetchmode)
		{
			$this->Link_ID->SetFetchMode($fetchmode);
		}
		if (!$num_rows)
		{
			$num_rows = $GLOBALS['egw_info']['user']['preferences']['common']['maxmatchs'];
		}
		if (($this->readonly || $this->log_updates === true) && !preg_match('/^\(?(SELECT|SET|SHOW)/i', $Query_String))
		{
			if ($this->log_updates === true)
			{
				$msg = $Query_String."\n".implode("\n", array_map(static function($level)
					{
						$args = substr(json_encode($level['args'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), 1, -1);
						if (strlen($args) > 120) $args = substr($args, 0, 120).'...';
						return str_replace(EGW_SERVER_ROOT.'/', '', $level['file']).'('.$level['line'].'): '.
							(empty($level['class']) ? '' : str_replace('EGroupware\\', '', $level['class']).$level['type']).$level['function'].'('.$args.')';
					}, debug_backtrace()));

				if (!empty($this->log_updates_to))
				{
					$msg = date('Y-m-d H:i:s: ').$_SERVER['REQUEST_METHOD'].' '.Framework::getUrl($_SERVER['REQUEST_URI'])."\n".$msg."\n".
						'User: '.$GLOBALS['egw_info']['user']['account_lid'].', User-agent: '.$_SERVER['HTTP_USER_AGENT']."\n\n";
				}
				error_log($msg, empty($this->log_updates_to) ? 0 : 3, $this->log_updates_to);
			}
			if ($this->readonly)
			{
				$this->Error = 'Database is readonly';
				$this->Errno = -2;
				return 0;
			}
		}
