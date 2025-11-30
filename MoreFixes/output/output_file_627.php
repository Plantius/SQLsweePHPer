    static function lookup($var) {
        if (is_array($var))
            return parent::lookup($var);
        elseif (is_numeric($var))
            return parent::lookup(array('staff_id'=>$var));
        elseif (Validator::is_email($var))
            return parent::lookup(array('email'=>$var));
        elseif (is_string($var))
            return parent::lookup(array('username'=>$var));
        else
            return null;
    }
