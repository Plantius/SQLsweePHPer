    public function update($id, $label, $extra)
    {
        $ret = $this->get($id);
        if (!$ret) {
            /* get() already logged and set $this->error. */
            return self::ID_NOT_EXITS;
        }

        $class = get_class($this);

        try {
            $oldlabel = $ret->{$this->flabel};
            $this->zdb->connection->beginTransaction();
            $values = array(
                $this->flabel  => $label,
                $this->fthird  => $extra
            );

            $update = $this->zdb->update($this->table);
            $update->set($values);
            $update->where($this->fpk . ' = ' . $id);

            $ret = $this->zdb->execute($update);

            if ($oldlabel != $label) {
                $this->deleteTranslation($oldlabel);
                $this->addTranslation($label);
            }

            Analog::log(
                $this->getType() . ' #' . $id . ' updated successfully.',
                Analog::INFO
            );
            $this->zdb->connection->commit();
            return true;
        } catch (Throwable $e) {
            $this->zdb->connection->rollBack();
            Analog::log(
                'Unable to update ' . $this->getType() . ' #' . $id . ' | ' .
                $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
