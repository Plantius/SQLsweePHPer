    public function getQuerySelect()
    {
        // SubmittedOn is stored in the artifact
        return "a.submitted_on AS `" . $this->name . "`";
    }
