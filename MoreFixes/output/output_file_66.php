    final public function modifyLimitQuery($query, $limit, $offset = 0)
    {
        if ($offset < 0) {
            throw new Exception(sprintf(
                'Offset must be a positive integer or zero, %d given',
                $offset
            ));
        }

        if ($offset > 0 && ! $this->supportsLimitOffset()) {
            throw new Exception(sprintf(
                'Platform %s does not support offset values in limit queries.',
                $this->getName()
            ));
        }

        return $this->doModifyLimitQuery($query, $limit, $offset);
    }
