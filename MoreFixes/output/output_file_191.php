function PMA_addUser(
    $dbname, $username, $hostname,
    $password, $is_menuwork
) {
    $_add_user_error = false;
    $message = null;
    $queries = null;
    $queries_for_display = null;
    $sql_query = null;

    if (isset($_REQUEST['adduser_submit']) || isset($_REQUEST['change_copy'])) {
        $sql_query = '';
        if ($_POST['pred_username'] == 'any') {
            $username = '';
        }
        switch ($_POST['pred_hostname']) {
        case 'any':
            $hostname = '%';
            break;
        case 'localhost':
            $hostname = 'localhost';
            break;
        case 'hosttable':
            $hostname = '';
            break;
        case 'thishost':
            $_user_name = $GLOBALS['dbi']->fetchValue('SELECT USER()');
            $hostname = /*overload*/mb_substr(
                $_user_name,
                (/*overload*/mb_strrpos($_user_name, '@') + 1)
            );
            unset($_user_name);
            break;
        }
        $sql = "SELECT '1' FROM `mysql`.`user`"
            . " WHERE `User` = '" . PMA_Util::sqlAddSlashes($username) . "'"
            . " AND `Host` = '" . PMA_Util::sqlAddSlashes($hostname) . "';";
        if ($GLOBALS['dbi']->fetchValue($sql) == 1) {
            $message = PMA_Message::error(__('The user %s already exists!'));
            $message->addParam(
                '[em]\'' . $username . '\'@\'' . $hostname . '\'[/em]'
            );
            $_REQUEST['adduser'] = true;
            $_add_user_error = true;
        } else {
            list($create_user_real, $create_user_show, $real_sql_query, $sql_query)
                = PMA_getSqlQueriesForDisplayAndAddUser(
                    $username, $hostname, (isset ($password) ? $password : '')
                );

            if (empty($_REQUEST['change_copy'])) {
                $_error = false;

                if (isset($create_user_real)) {
                    if (! $GLOBALS['dbi']->tryQuery($create_user_real)) {
                        $_error = true;
                    }
                    $sql_query = $create_user_show . $sql_query;
                }
                list($sql_query, $message) = PMA_addUserAndCreateDatabase(
                    $_error, $real_sql_query, $sql_query, $username, $hostname,
                    isset($dbname) ? $dbname : null
                );
                if (! empty($_REQUEST['userGroup']) && $is_menuwork) {
                    PMA_setUserGroup($GLOBALS['username'], $_REQUEST['userGroup']);
                }

            } else {
                if (isset($create_user_real)) {
                    $queries[] = $create_user_real;
                }
                $queries[] = $real_sql_query;
                // we put the query containing the hidden password in
                // $queries_for_display, at the same position occupied
                // by the real query in $queries
                $tmp_count = count($queries);
                if (isset($create_user_real)) {
                    $queries_for_display[$tmp_count - 2] = $create_user_show;
                }
                $queries_for_display[$tmp_count - 1] = $sql_query;
            }
            unset($real_sql_query);
        }
    }

    return array(
        $message, $queries, $queries_for_display, $sql_query, $_add_user_error
    );
}
