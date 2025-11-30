    public function getQueryOrderby()
    {
        $uh = UserHelper::instance();
        $R1 = 'R1_' . $this->field->id;
        $R2 = 'R2_' . $this->field->id;
        return $R2 . "." . str_replace('user.', '', $uh->getDisplayNameSQLOrder());
    }
