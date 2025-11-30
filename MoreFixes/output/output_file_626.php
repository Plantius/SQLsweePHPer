    static function is_username($username, &$error='') {
        if (strlen($username)<2)
            $error = __('Username must have at least two (2) characters');
        elseif (!preg_match('/^[\p{L}\d._-]+$/u', $username))
            $error = __('Username contains invalid characters');
        return $error == '';
    }
