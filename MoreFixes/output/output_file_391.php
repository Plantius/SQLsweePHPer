    public function getByUuid($uuid)
    {
        $data = $this->db->fetchAssociative('SELECT * FROM ' . self::TABLE_NAME ." where uuid='" . $uuid . "'");
        $model = new Model\Tool\UUID();
        $model->setValues($data);

        return $model;
    }
