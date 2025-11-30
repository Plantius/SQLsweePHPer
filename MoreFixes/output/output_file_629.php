	static function update_report_field($id=false, $field=false, $value=false)
	{
		$id = (int)$id;
		$field = trim($field);
		$value = trim($value);
		$db = Database::instance();
		$sql = "UPDATE scheduled_reports SET ".$field."= ".$db->escape($value)." WHERE id=".$id;
		try {
			$res = $db->query($sql);
		} catch (Kohana_Database_Exception $e) {
			print $e->getMessage();
			return false;
		}
		return true;
	}
