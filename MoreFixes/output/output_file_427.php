    public function getQuerySelect()
    {
        $R1 = 'R1_' . $this->field->id;
        $R2 = 'R2_' . $this->field->id;
        $R3 = 'R3_' . $this->field->id;
        return "$R2.id AS `" . $this->field->name . "`";
    }
