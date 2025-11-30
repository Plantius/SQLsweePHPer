    private function invertedSection($nodes, $id, $filters, $level)
    {
        $method  = $this->getFindMethod($id);
        $id      = var_export($id, true);
        $filters = $this->getFilters($filters, $level);

        return sprintf($this->prepare(self::INVERTED_SECTION, $level), $id, $method, $id, $filters, $this->walk($nodes, $level));
    }
