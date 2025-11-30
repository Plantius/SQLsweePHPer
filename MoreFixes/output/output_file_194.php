function PMA_changePassword($password, $message, $change_password_message)
{
    global $auth_plugin;

    $hashing_function = PMA_changePassHashingFunction();

    $orig_auth_plugin = null;

    $row = $GLOBALS['dbi']->fetchSingleRow('SELECT CURRENT_USER() as user');
    $curr_user = $row['user'];
    list($username, $hostname) = explode('@', $curr_user);

    $serverType = PMA_Util::getServerType();

    if (isset($_REQUEST['authentication_plugin'])
        && ! empty($_REQUEST['authentication_plugin'])
    ) {
        $orig_auth_plugin = $_REQUEST['authentication_plugin'];
    } else {
        $orig_auth_plugin = PMA_getCurrentAuthenticationPlugin(
            'change', $username, $hostname
        );
    }

    if ($serverType === 'MySQL'
        && PMA_MYSQL_INT_VERSION >= 50706
    ) {
        $sql_query = 'ALTER USER \'' . $username . '\'@\'' . $hostname
            . '\' IDENTIFIED WITH ' . $orig_auth_plugin . ' BY '
            . (($password == '') ? '\'\'' : '\'***\'');
    } else if (($serverType == 'MySQL'
