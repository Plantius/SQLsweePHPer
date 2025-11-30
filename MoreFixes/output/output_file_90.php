function authenticate($WSUser, $WSKey)
{
    $tUser = Database::get_main_table(TABLE_MAIN_USER);
    $tApi = Database::get_main_table(TABLE_MAIN_USER_API_KEY);
    $login = Database::escape_string($WSUser);
    $sql = "SELECT u.user_id, u.status FROM $tUser u, $tApi a 
            WHERE 
                u.username='".$login."' AND  
                u.user_id = a.user_id AND 
                a.api_service = 'dokeos' AND 
                a.api_key='".$WSKey."'";
    $result = Database::query($sql);

    if (Database::num_rows($result) > 0) {
        $row = Database::fetch_row($result);
        if ($row[1] == '4') { //UserManager::is_admin($row[0])) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
