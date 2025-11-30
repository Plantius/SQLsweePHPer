    public function create(Codendi_Request $request)
    {
        $content_id = false;
        $vId = new Valid_UInt($this->widget_id . '_job_id');
        $vId->setErrorMessage("Can't add empty job id");
        $vId->required();
        if ($request->valid($vId)) {
            $job_id = $request->get($this->widget_id . '_job_id');
            $sql = 'INSERT INTO plugin_hudson_widget (widget_name, owner_id, owner_type, job_id) VALUES ("' . $this->id . '", ' . $this->owner_id . ", '" . $this->owner_type . "', " . db_escape_int($job_id) . " )";
            $res = db_query($sql);
            $content_id = db_insertid($res);
        }
        return $content_id;
    }
