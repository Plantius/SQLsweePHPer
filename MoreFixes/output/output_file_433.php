    public function getQuerySelect()
    {
        //Last update date is stored in the changeset (the date of the changeset)
        return "c.submitted_on AS `" . $this->name . "`";
    }
