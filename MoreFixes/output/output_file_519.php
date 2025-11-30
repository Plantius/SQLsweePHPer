    public function store()
    {
        $data = array(
            'type_name' => $this->name
        );
        try {
            if ($this->id !== null && $this->id > 0) {
                if ($this->old_name !== null) {
                    $this->deleteTranslation($this->old_name);
                    $this->addTranslation($this->name);
                }

                $update = $this->zdb->update(self::TABLE);
                $update->set($data)->where(
                    self::PK . '=' . $this->id
                );
                $this->zdb->execute($update);
            } else {
                $insert = $this->zdb->insert(self::TABLE);
                $insert->values($data);
                $add = $this->zdb->execute($insert);
                if (!$add->count() > 0) {
                    Analog::log('Not stored!', Analog::ERROR);
                    return false;
                }

                $this->id = $this->zdb->getLastGeneratedValue($this);

                $this->addTranslation($this->name);
            }
            return true;
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred storing payment type: ' . $e->getMessage() .
                "\n" . print_r($data, true),
                Analog::ERROR
            );
            throw $e;
        }
    }
