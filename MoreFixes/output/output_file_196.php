function PMA_getChangeLoginInformationHtmlForm($username, $hostname)
{
    $choices = array(
        '4' => __('… keep the old one.'),
        '1' => __('… delete the old one from the user tables.'),
        '2' => __(
            '… revoke all active privileges from '
            . 'the old one and delete it afterwards.'
        ),
        '3' => __(
            '… delete the old one from the user tables '
            . 'and reload the privileges afterwards.'
        )
    );

    $html_output = '<form action="server_privileges.php" '
        . 'onsubmit="return checkAddUser(this);" '
        . 'method="post" class="copyUserForm submenu-item">' . "\n"
        . PMA_URL_getHiddenInputs('', '')
        . '<input type="hidden" name="old_username" '
        . 'value="' . htmlspecialchars($username) . '" />' . "\n"
        . '<input type="hidden" name="old_hostname" '
        . 'value="' . htmlspecialchars($hostname) . '" />' . "\n"
        . '<fieldset id="fieldset_change_copy_user">' . "\n"
        . '<legend data-submenu-label="' . __('Login Information') . '">' . "\n"
        . __('Change login information / Copy user account')
        . '</legend>' . "\n"
        . PMA_getHtmlForLoginInformationFields('change');

    $html_output .= '<fieldset id="fieldset_mode">' . "\n"
        . ' <legend>'
        . __('Create a new user account with the same privileges and …')
        . '</legend>' . "\n";
    $html_output .= PMA_Util::getRadioFields(
        'mode', $choices, '4', true
    );
    $html_output .= '</fieldset>' . "\n"
       . '</fieldset>' . "\n";

    $html_output .= '<fieldset id="fieldset_change_copy_user_footer" '
        . 'class="tblFooters">' . "\n"
        . '<input type="hidden" name="change_copy" value="1" />' . "\n"
        . '<input type="submit" value="' . __('Go') . '" />' . "\n"
        . '</fieldset>' . "\n"
        . '</form>' . "\n";

    return $html_output;
}
