    public function setGroupBy($groupBy, $qoute = true)
    {
        $this->setData(null);

        if ($groupBy) {
            $this->groupBy = $groupBy;

            if ($qoute && strpos($groupBy, '`') !== 0) {
                $this->groupBy = '`' . $this->groupBy . '`';
            }
        }

        return $this;
    }
