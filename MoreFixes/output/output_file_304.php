    private function section($nodes, $id, $filters, $start, $end, $otag, $ctag, $level)
    {
        $source   = var_export(substr($this->source, $start, $end - $start), true);
        $callable = $this->getCallable();

        if ($otag !== '{{' || $ctag !== '}}') {
            $delimTag = var_export(sprintf('{{= %s %s =}}', $otag, $ctag), true);
            $helper = sprintf('$this->lambdaHelper->withDelimiters(%s)', $delimTag);
            $delims = ', ' . $delimTag;
        } else {
            $helper = '$this->lambdaHelper';
            $delims = '';
        }

        $key = ucfirst(md5($delims . "\n" . $source));

        if (!isset($this->sections[$key])) {
            $this->sections[$key] = sprintf($this->prepare(self::SECTION), $key, $callable, $source, $helper, $delims, $this->walk($nodes, 2));
        }

        $method  = $this->getFindMethod($id);
        $id      = var_export($id, true);
        $filters = $this->getFilters($filters, $level);

        return sprintf($this->prepare(self::SECTION_CALL, $level), $id, $method, $id, $filters, $key);
    }
