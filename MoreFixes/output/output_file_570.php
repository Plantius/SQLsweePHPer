    public function updatePreferences(Codendi_Request $request)
    {
        $request->valid(new Valid_String('cancel'));
        if (!$request->exist('cancel')) {
            $job_id = $request->get($this->widget_id . '_job_id');
            $sql = "UPDATE plugin_hudson_widget SET job_id=" . $job_id . " WHERE owner_id = " . $this->owner_id . " AND owner_type = '" . $this->owner_type . "' AND id = " . (int) $request->get('content_id');
            $res = db_query($sql);
        }
