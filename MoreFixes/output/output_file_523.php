    public function store($zdb)
    {
        try {
            $values = array(
                self::PK        => $this->id,
                'model_fields'  => serialize($this->fields)
            );

            if (!isset($this->id) || $this->id == '') {
                //we're inserting a new model
                unset($values[self::PK]);
                $this->creation_date = date("Y-m-d H:i:s");
                $values['model_creation_date'] = $this->creation_date;

                $insert = $zdb->insert(self::TABLE);
                $insert->values($values);
                $results = $zdb->execute($insert);

                if ($results->count() > 0) {
                    return true;
                } else {
                    throw new \Exception(
                        'An error occurred inserting new import model!'
                    );
                }
            } else {
                //we're editing an existing model
                $update = $zdb->update(self::TABLE);
                $update->set($values);
                $update->where(self::PK . '=' . $this->id);
                $zdb->execute($update);
                return true;
            }
        } catch (Throwable $e) {
            Analog::log(
                'Something went wrong storing import model :\'( | ' .
                $e->getMessage() . "\n" . $e->getTraceAsString(),
                Analog::ERROR
            );
            throw $e;
        }
    }
