    public function get_info($object_id, $table_name = '')
    {
        $table_name = $table_name ? Dba::escape($table_name) : Dba::escape(strtolower(get_class($this)));

        // Make sure we've got a real id
        if ($object_id < 1) {
            return array();
        }

        if (self::is_cached($table_name, $object_id)) {
            return self::get_from_cache($table_name, $object_id);
        }

        $sql        = "SELECT * FROM `$table_name` WHERE `id`='$object_id'";
        $db_results = Dba::read($sql);

        if (!$db_results) {
            return array();
        }

        $row = Dba::fetch_assoc($db_results);

        self::add_to_cache($table_name, $object_id, $row);

        return $row;
    } // get_info
