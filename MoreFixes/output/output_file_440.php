    public function getQuerySelect()
    {
        // SubmittedOn is stored in the artifact
        return "a.submitted_by AS `" . $this->name . "`";
    }
