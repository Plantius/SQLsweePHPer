    public function delete($id)
    {
        $ret = $this->get($id);
        if (!$ret) {
            /* get() already logged */
            return self::ID_NOT_EXITS;
        }

        if ($this->isUsed($id)) {
            $this->errors[] = _T("Cannot delete this label: it's still used");
            return false;
        }

        try {
            $this->zdb->connection->beginTransaction();
            $delete = $this->zdb->delete($this->table);
            $delete->where($this->fpk . ' = ' . $id);

            $this->zdb->execute($delete);
            $this->deleteTranslation($ret->{$this->flabel});

            Analog::log(
                $this->getType() . ' ' . $id . ' deleted successfully.',
                Analog::INFO
            );

            $this->zdb->connection->commit();
            return true;
        } catch (Throwable $e) {
            $this->zdb->connection->rollBack();
            Analog::log(
                'Unable to delete ' . $this->getType() . ' ' . $id .
                ' | ' . $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
