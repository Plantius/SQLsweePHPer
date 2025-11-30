    protected function load($id, $init = true)
    {
        global $login;

        try {
            $select = $this->zdb->select(self::TABLE);
            $select->limit(1)
                ->where(self::PK . ' = ' . $id);

            $results = $this->zdb->execute($select);

            $count = $results->count();
            if ($count === 0) {
                if ($init === true) {
                    $models = new PdfModels($this->zdb, $this->preferences, $login);
                    $models->installInit();
                    $this->load($id, false);
                } else {
                    throw new \RuntimeException('Model not found!');
                }
            } else {
                $this->loadFromRs($results->current());
            }
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred loading model #' . $id . "Message:\n" .
                $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
