    public function remove($zdb)
    {
        $id = (int)$this->id;
        if ($id === self::MR || $id === self::MRS) {
            throw new \RuntimeException(_T("You cannot delete Mr. or Mrs. titles!"));
        }

        try {
            $delete = $zdb->delete(self::TABLE);
            $delete->where(
                self::PK . ' = ' . $id
            );
            $zdb->execute($delete);
            Analog::log(
                'Title #' . $id . ' (' . $this->short
                . ') deleted successfully.',
                Analog::INFO
            );
            return true;
        } catch (\RuntimeException $re) {
            throw $re;
        } catch (Throwable $e) {
            Analog::log(
                'Unable to delete title ' . $id . ' | ' . $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
