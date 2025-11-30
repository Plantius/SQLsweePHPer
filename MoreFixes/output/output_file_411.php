    public function getQueryGroupby()
    {
        $R = 'R_' . $this->id;
        return "$R.value_id";
    }
