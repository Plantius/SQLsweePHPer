    public function getMoreEmployeeSharesByEmployeeNumber($limit, $fromId, $employeeNumber) {
        try {
            if (!$employeeNumber) {
                $queryBlock = 'employee_number is NULL';
            } else {
                $queryBlock = 'employee_number=' . $employeeNumber;
            }
            $q = Doctrine_Query::create()
                ->select('*')
                ->from('Share')
                ->andWhere('id < ?', $fromId)
                ->andWhere($queryBlock)
                ->limit($limit)
                ->orderBy('share_time DESC');
            return $q->execute();

            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
