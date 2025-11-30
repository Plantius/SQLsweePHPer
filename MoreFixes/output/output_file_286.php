    private function changePassUrlParamsAndSubmitQuery(
        $username, $hostname, $password, $sql_query, $hashing_function, $orig_auth_plugin
    ) {
        $err_url = 'user_password.php' . Url::getCommon();

        $serverType = Util::getServerType();
        $serverVersion = $GLOBALS['dbi']->getVersion();

        if ($serverType == 'MySQL' && $serverVersion >= 50706) {
            $local_query = 'ALTER USER \'' . $username . '\'@\'' . $hostname . '\''
                . ' IDENTIFIED with ' . $orig_auth_plugin . ' BY '
                . (($password == '')
                ? '\'\''
                : '\'' . $GLOBALS['dbi']->escapeString($password) . '\'');
        } elseif ($serverType == 'MariaDB'
