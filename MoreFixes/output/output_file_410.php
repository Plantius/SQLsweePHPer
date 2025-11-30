    public function getQueryGroupby()
    {
        $R1 = 'R1_' . $this->id;
        $R2 = 'R2_' . $this->id;
        return "$R2.value";
    }
