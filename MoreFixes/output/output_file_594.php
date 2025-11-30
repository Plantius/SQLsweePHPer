    public static function get_user($input_count, $input_type, $user, $full = 0)
    {
        $type = self::validate_type($input_type);

        // If full then don't limit on date
        $date = ($full > 0) ? '0' : time() - (86400 * (int)AmpConfig::get('stats_threshold', 7));

        // Select Objects based on user
        // FIXME:: Requires table scan, look at improving
        $sql        = "SELECT `object_id`, COUNT(`id`) AS `count` FROM `object_count` WHERE `object_type` = ? AND `date` >= ? AND `user` = ? GROUP BY `object_id` ORDER BY `count` DESC LIMIT $input_count";
        $db_results = Dba::read($sql, array($type, $date, $user));

        $results = array();

        while ($row = Dba::fetch_assoc($db_results)) {
            $results[] = $row;
        }

        return $results;
    } // get_user
