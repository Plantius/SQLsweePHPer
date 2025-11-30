    public function detach()
    {
        global $zdb, $hist;

        try {
            $update = $zdb->update(self::TABLE);
            $update->set(
                array('parent_group' => new Expression('NULL'))
            )->where(
                self::PK . ' = ' . $this->id
            );

            $edit = $zdb->execute($update);

            //edit == 0 does not mean there were an error, but that there
            //were nothing to change
            if ($edit->count() > 0) {
                $this->parent_group = null;
                $hist->add(
                    _T("Group has been detached from its parent"),
                    $this->group_name
                );
            }

            return true;
        } catch (Throwable $e) {
            Analog::log(
                'Something went wrong detaching group `' . $this->group_name .
                '` (' . $this->id . ') from its parent:\'( | ' .
                $e->getMessage() . "\n" .
                $e->getTraceAsString(),
                Analog::ERROR
            );
            throw $e;
        }
    }
