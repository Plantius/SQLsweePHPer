	public function update_custom_field_definition ($field) {
		if (!in_array($field['fieldType'], $this->valid_custom_field_types())) {
			$this->Result->show("danger", _("Error: ")._("Invalid custom field type"));
			return false;
		}

	    # set type definition and size of needed
	    if($field['fieldType']=="bool" || $field['fieldType']=="text" || $field['fieldType']=="date" || $field['fieldType']=="datetime")	{ $field['ftype'] = $field['fieldType']; }
	    else																																{ $field['ftype'] = $field['fieldType']."(".$field['fieldSize'].")"; }

	    # default value null
	    $field['fieldDefault'] = is_blank($field['fieldDefault']) ? NULL : $field['fieldDefault'];

	    # character set if needed
	    if($field['fieldType']=="varchar" || $field['fieldType']=="text" || $field['fieldType']=="set" || $field['fieldType']=="enum")	{ $charset = "CHARACTER SET utf8mb4"; }
	    else																															{ $charset = ""; }

	    # escape fields
	    $field['table'] 		= $this->Database->escape($field['table']);
	    $field['name'] 			= $this->Database->escape($field['name']);
	    $field['oldname'] 		= $this->Database->escape($field['oldname']);
	    # strip values
	    $field['action'] 		= $this->strip_input_tags($field['action']);
	    $field['Comment'] 		= $this->strip_input_tags($field['Comment']);

	    # add name prefix to distinguish custom fields
	    if($field['action']=="edit" || $field['action']=="add") {
		    if(strpos($field['name'], "custom_")!==0) { $field['name'] = "custom_".$field['name']; }
		}

	    # set update query
	    if($field['action']=="delete") 								{ $query  = "ALTER TABLE `$field[table]` DROP `$field[oldname]`;"; }
	    else if ($field['action']=="edit"&&@$field['NULL']=="NO") 	{ $query  = "ALTER TABLE `$field[table]` CHANGE COLUMN `$field[oldname]` `$field[name]` $field[ftype] $charset DEFAULT :default NOT NULL COMMENT :comment;"; }
	    else if ($field['action']=="edit") 							{ $query  = "ALTER TABLE `$field[table]` CHANGE COLUMN `$field[oldname]` `$field[name]` $field[ftype] $charset DEFAULT :default COMMENT :comment;"; }
	    else if ($field['action']=="add"&&@$field['NULL']=="NO") 	{ $query  = "ALTER TABLE `$field[table]` ADD COLUMN 	`$field[name]` 					$field[ftype] $charset DEFAULT :default NOT NULL COMMENT :comment;"; }
	    else if ($field['action']=="add")							{ $query  = "ALTER TABLE `$field[table]` ADD COLUMN 	`$field[name]` 					$field[ftype] $charset DEFAULT :default NULL COMMENT :comment;"; }
	    else {
		    return false;
	    }

	    # set parametized values
	    $params = array();
	    if (strpos($query, ":default")>0)	$params['default'] = $field['fieldDefault'];
	    if (strpos($query, ":comment")>0)	$params['comment'] = $field['Comment'];

		# execute
		try { $res = $this->Database->runQuery($query, $params); }
		catch (Exception $e) {
			$this->Result->show("danger", _("Error: ").$e->getMessage(), false);
	        $this->Log->write( _("Custom field")." ".$field["action"], _("Custom field")." ".$field["action"]." "._("failed")." (".$field["name"].").<hr>".$this->array_to_log($field), 2);
			return false;
		}
		# field updated
        $this->Log->write( _("Custom field")." ".$field["action"], _("Custom field")." ".$field["action"]." "._("success")." (".$field["name"].").<hr>".$this->array_to_log($field), 0);
	    return true;
	}
