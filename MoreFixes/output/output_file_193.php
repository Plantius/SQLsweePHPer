function PMA_changePassUrlParamsAndSubmitQuery(
    $username, $hostname, $password, $sql_query, $hashing_function, $auth_plugin
) {
    $err_url = 'user_password.php' . PMA_URL_getCommon();
    if (PMA_Util::getServerType() === 'MySQL' && PMA_MYSQL_INT_VERSION >= 50706) {
        $local_query = 'ALTER USER \'' . $username . '\'@\'' . $hostname . '\''
            . ' IDENTIFIED with ' . $auth_plugin . ' BY '
            . (($password == '')
            ? '\'\''
            : '\'' . PMA_Util::sqlAddSlashes($password) . '\'');
    } else {
