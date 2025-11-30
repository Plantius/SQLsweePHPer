function PMA_changePassUrlParamsAndSubmitQuery(
    $password, $sql_query, $hashing_function
) {
    $err_url = 'user_password.php' . PMA_URL_getCommon();
    if (PMA_Util::getServerType() === 'MySQL' && PMA_MYSQL_INT_VERSION >= 50706) {
        $local_query = 'ALTER USER USER() IDENTIFIED BY ' . (($password == '')
            ? '\'\''
            : '\'' . PMA_Util::sqlAddSlashes($password) . '\'');
    } else {
