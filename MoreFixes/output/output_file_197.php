function PMA_getHtmlForLoginInformationFields($mode = 'new')
{
    list($username_length, $hostname_length) = PMA_getUsernameAndHostnameLength();

    if (isset($GLOBALS['username'])
        && /*overload*/mb_strlen($GLOBALS['username']) === 0
    ) {
        $GLOBALS['pred_username'] = 'any';
    }
    $html_output = '<fieldset id="fieldset_add_user_login">' . "\n"
        . '<legend>' . __('Login Information') . '</legend>' . "\n"
        . '<div class="item">' . "\n"
        . '<label for="select_pred_username">' . "\n"
        . '    ' . __('User name:') . "\n"
        . '</label>' . "\n"
        . '<span class="options">' . "\n";

    $html_output .= '<select name="pred_username" id="select_pred_username" '
        . 'title="' . __('User name') . '"' . "\n";

    $html_output .= '        onchange="'
        . 'if (this.value == \'any\') {'
        . '    username.value = \'\'; '
        . '    user_exists_warning.style.display = \'none\'; '
        . '    username.required = false; '
        . '} else if (this.value == \'userdefined\') {'
        . '    username.focus(); username.select(); '
        . '    username.required = true; '
        . '}">' . "\n";

    $html_output .= '<option value="any"'
        . ((isset($GLOBALS['pred_username']) && $GLOBALS['pred_username'] == 'any')
            ? ' selected="selected"'
            : '') . '>'
