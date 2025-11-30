    public function getQuerySelect()
    {
        $R = 'R_' . $this->id;
        return "$R.value_id AS `" . $this->name . "`";
    }
