function addPnote(
    $pid,
    $newtext,
    $authorized = '0',
    $activity = '1',
    $title = 'Unassigned',
    $assigned_to = '',
    $datetime = '',
    $message_status = 'New',
    $background_user = ""
) {

    if (empty($datetime)) {
        $datetime = date('Y-m-d H:i:s');
    }

  // make inactive if set as Done
    if ($message_status == 'Done') {
        $activity = 0;
    }
    $user = ($background_user != "" ? $background_user : $_SESSION['authUser']);
    $body = date('Y-m-d H:i') . ' (' . $user;
    if ($assigned_to) {
        $body .= " to $assigned_to";
    }

    $body = $body . ') ' . $newtext;

    return sqlInsert(
        'INSERT INTO pnotes (date, body, pid, user, groupname, ' .
        'authorized, activity, title, assigned_to, message_status, update_by, update_date) VALUES ' .
        '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
        array($datetime, $body, $pid, $user, $_SESSION['authProvider'], $authorized, $activity, $title, $assigned_to, $message_status, $_SESSION['authUserID'])
    );
}
