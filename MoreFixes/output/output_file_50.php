                        $subgroup->remove(true);
                    }
                }

                Analog::log(
                    'Cascading remove ' . $this->group_name .
                    '. Members and managers will be detached.',
                    Analog::INFO
                );

                //delete members
                $delete = $zdb->delete(self::GROUPSUSERS_TABLE);
                $delete->where(
                    self::PK . ' = ' . $this->id
                );
                $zdb->execute($delete);

                //delete managers
                $delete = $zdb->delete(self::GROUPSMANAGERS_TABLE);
                $delete->where(
                    self::PK . ' = ' . $this->id
                );
                $zdb->execute($delete);
            }

            //delete group itself
            $delete = $zdb->delete(self::TABLE);
            $delete->where(
                self::PK . ' = ' . $this->id
            );
            $zdb->execute($delete);

            //commit all changes
            if ($transaction) {
                $zdb->connection->commit();
            }

            return true;
        } catch (Throwable $e) {
            if ($transaction) {
                $zdb->connection->rollBack();
            }
            if ($e->getCode() == 23000) {
                Analog::log(
                    str_replace(
                        '%group',
                        $this->group_name,
                        'Group "%group" still have members!'
                    ),
                    Analog::WARNING
                );
                $this->isempty = false;
            } else {
                Analog::log(
                    'Unable to delete group ' . $this->group_name .
                    ' (' . $this->id . ') |' . $e->getMessage(),
                    Analog::ERROR
                );
                throw $e;
            }
            return false;
        }
    }
