    public function getQueryOrderby()
    {
        $R1 = 'R1_' . $this->field->id;
        $R2 = 'R2_' . $this->field->id;
        return $this->is_rank_alpha ? "$R2.label" : "$R2.rank";
    }
