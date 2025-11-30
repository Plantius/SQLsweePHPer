    public function getQuerySelect()
    {
        $R2 = 'R2_' . $this->id;

        return "$R2.value AS `" . $this->name . "`";
    }
