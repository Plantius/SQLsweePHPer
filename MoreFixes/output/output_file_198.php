function PMA_getSqlQueriesForDisplayAndAddUser($username, $hostname, $password)
{
    $create_user_real = 'CREATE USER \''
        . PMA_Util::sqlAddSlashes($username) . '\'@\''
        . PMA_Util::sqlAddSlashes($hostname) . '\'';

    $real_sql_query = 'GRANT ' . join(', ', PMA_extractPrivInfo()) . ' ON *.* TO \''
        . PMA_Util::sqlAddSlashes($username) . '\'@\''
        . PMA_Util::sqlAddSlashes($hostname) . '\'';

    if ($_POST['pred_password'] != 'none' && $_POST['pred_password'] != 'keep') {
        $sql_query = $real_sql_query;
        // Requires SELECT privilege on mysql database
        // for using this with GRANT queries. It can be skipped.
        if ($GLOBALS['is_superuser']) {
            $sql_query .= ' IDENTIFIED BY \'***\'';
            $real_sql_query .= ' IDENTIFIED BY \''
                . PMA_Util::sqlAddSlashes($_POST['pma_pw']) . '\'';
        }
        if (isset($create_user_real)) {
            $create_user_show = $create_user_real . ' IDENTIFIED BY \'***\'';
            $create_user_real .= ' IDENTIFIED BY \''
                . PMA_Util::sqlAddSlashes($_POST['pma_pw']) . '\'';
        }
    } else {
        if ($_POST['pred_password'] == 'keep' && ! empty($password)) {
            $real_sql_query .= ' IDENTIFIED BY PASSWORD \'' . $password . '\'';
            if (isset($create_user_real)) {
                $create_user_real .= ' IDENTIFIED BY PASSWORD \'' . $password . '\'';
            }
        }
        $sql_query = $real_sql_query;
        if (isset($create_user_real)) {
            $create_user_show = $create_user_real;
        }
    }

    // add REQUIRE clause
    $require_clause = PMA_getRequireClause();
    $real_sql_query .= $require_clause;
    $sql_query .= $require_clause;

    if ((isset($_POST['Grant_priv']) && $_POST['Grant_priv'] == 'Y')
        || (isset($_POST['max_questions']) || isset($_POST['max_connections'])
        || isset($_POST['max_updates']) || isset($_POST['max_user_connections']))
    ) {
        $with_clause = PMA_getWithClauseForAddUserAndUpdatePrivs();
        $real_sql_query .= $with_clause;
        $sql_query .= $with_clause;
    }

    if (isset($create_user_real)) {
        $create_user_real .= ';';
        $create_user_show .= ';';
    }
    $real_sql_query .= ';';
    $sql_query .= ';';
    // No Global GRANT_OPTION privilege
    if (!$GLOBALS['is_grantuser']) {
        $real_sql_query = '';
        $sql_query = '';
    }

    return array($create_user_real,
        $create_user_show,
        $real_sql_query,
        $sql_query
    );
}
