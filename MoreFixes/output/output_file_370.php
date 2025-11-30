    public function destroy($id)
    {
        $sql = 'DELETE FROM plugin_hudson_widget WHERE id = ' . $id . ' AND owner_id = ' . $this->owner_id . " AND owner_type = '" . $this->owner_type . "'";
        db_query($sql);
    }
