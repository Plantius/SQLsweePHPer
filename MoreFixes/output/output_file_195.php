function PMA_changePassword($password, $message, $change_password_message)
{
    global $auth_plugin;

    $hashing_function = PMA_changePassHashingFunction();
    if (PMA_Util::getServerType() === 'MySQL' && PMA_MYSQL_INT_VERSION >= 50706) {
        $sql_query = 'ALTER USER USER() IDENTIFIED BY '
            . (($password == '') ? '\'\'' : '\'***\'');
    } else {
