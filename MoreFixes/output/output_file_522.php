    public function store($zdb)
    {
        $data = array(
            'short_label'   => $this->short,
            'long_label'    => $this->long
        );
        try {
            if ($this->id !== null && $this->id > 0) {
                $update = $zdb->update(self::TABLE);
                $update->set($data)->where(
                    self::PK . '=' . $this->id
                );
                $zdb->execute($update);
            } else {
                $insert = $zdb->insert(self::TABLE);
                $insert->values($data);
                $add = $zdb->execute($insert);
                if (!$add->count() > 0) {
                    Analog::log('Not stored!', Analog::ERROR);
                    return false;
                }

                $this->id = $zdb->getLastGeneratedValue($this);
            }
            return true;
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred storing title: ' . $e->getMessage() .
                "\n" . print_r($data, true),
                Analog::ERROR
            );
            throw $e;
        }
    }
