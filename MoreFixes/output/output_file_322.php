    protected function getJobIdFromWidgetConfiguration()
    {
        $sql = "SELECT *
                    FROM plugin_hudson_widget
                    WHERE widget_name = '" . db_es($this->widget_id) . "'
                      AND owner_id = " . db_ei($this->owner_id) . "
                      AND owner_type = '" . db_es($this->owner_type) . "'
                      AND id = " . db_ei($this->content_id);

        $res = db_query($sql);
        if ($res && db_numrows($res)) {
            $data   = db_fetch_array($res);
            return $data['job_id'];
        }

        return null;
    }
