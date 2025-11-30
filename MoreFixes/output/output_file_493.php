    public function remove()
    {
        $id = (int)$this->id;
        try {
            $delete = $this->zdb->delete(self::TABLE);
            $delete->where(
                self::PK . ' = ' . $id
            );
            $this->zdb->execute($delete);
            Analog::log(
                'Saved search #' . $id . ' (' . $this->name
                . ') deleted successfully.',
                Analog::INFO
            );
            return true;
        } catch (\RuntimeException $re) {
            throw $re;
        } catch (Throwable $e) {
            Analog::log(
                'Unable to delete saved search ' . $id . ' | ' . $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
