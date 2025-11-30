	static public function edit_report($id=false, $rep_type=false, $saved_report_id=false, $period=false, $recipients=false, $filename='', $description='', $local_persistent_filepath = '', $attach_description = 0, $report_time=false, $report_on=false, $report_period=false)
	{

		$local_persistent_filepath = trim($local_persistent_filepath);
		if($local_persistent_filepath && !is_writable(rtrim($local_persistent_filepath, '/').'/')) {
			return _("File path '$local_persistent_filepath' is not writable");
		}
		$db = Database::instance();
		$id = (int)$id;
		$rep_type = (int)$rep_type;
		$saved_report_id = (int)$saved_report_id;
		$period	= (int)$period;
		$report_time = trim($report_time);
		$report_on = trim($report_on);
		$report_period = trim($report_period);
		$recipients = trim($recipients);
		$filename = trim($filename);
		$description = trim($description);
		$attach_description = (int) $attach_description;
		$user = Auth::instance()->get_user()->get_username();

		if (!$rep_type || !$saved_report_id || !$period || empty($recipients)) return _('Missing data');

		// some users might use ';' to separate email adresses
		// just replace it with ',' and continue
		$recipients = str_replace(';', ',', $recipients);
		$rec_arr = explode(',', $recipients);
		if (!empty($rec_arr)) {
			foreach ($rec_arr as $recipient) {
				if (trim($recipient)!='') {
					$checked_recipients[] = trim($recipient);
				}
			}
			$recipients = implode(', ', $checked_recipients);
		}

		if ($id) {
			// UPDATE
			$sql = "UPDATE scheduled_reports SET ".self::USERFIELD."=".$db->escape($user).", report_type_id=".$rep_type.", report_id=".$saved_report_id.", recipients=".$db->escape($recipients).", period_id=".$period.", filename=".$db->escape($filename).", description=".$db->escape($description).", local_persistent_filepath = ".$db->escape($local_persistent_filepath).", attach_description = ".$db->escape($attach_description)." WHERE id=".$id;
		} else {
			$sql = "INSERT INTO scheduled_reports (".self::USERFIELD.", report_type_id, report_id, recipients, period_id, filename, description, local_persistent_filepath, attach_description, report_time, report_on, report_period)VALUES(".$db->escape($user).", ".$rep_type.", ".$saved_report_id.", ".$db->escape($recipients).", ".$period.", ".$db->escape($filename).", ".$db->escape($description).", ".$db->escape($local_persistent_filepath).", ".$db->escape($attach_description).", '".$report_time."', '".$report_on."', '".$report_period."' )";

		}

		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			return _('DATABASE ERROR').": {$e->getMessage()}; $sql";
		}

		if (!$id) {
			$id = $res->insert_id();
		}
		return $id;
	}
