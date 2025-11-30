    public function update($data)
    {
        if ($this->securityController->isWikiHibernated()) {
            throw new \Exception(_t('WIKI_IN_HIBERNATION'));
        }
        return $this->dbService->query('UPDATE' . $this->dbService->prefixTable('nature') . 'SET '
            . '`bn_label_nature`="' . addslashes(_convert($data['bn_label_nature'], YW_CHARSET, true)) . '" ,'
            . '`bn_template`="' . addslashes(_convert($data['bn_template'], YW_CHARSET, true)) . '" ,'
            . '`bn_description`="' . addslashes(_convert($data['bn_description'], YW_CHARSET, true)) . '" ,'
            . '`bn_sem_context`="' . addslashes(_convert($data['bn_sem_context'], YW_CHARSET, true)) . '" ,'
            . '`bn_sem_type`="' . addslashes(_convert($data['bn_sem_type'], YW_CHARSET, true)) . '" ,'
            . '`bn_sem_use_template`=' . (isset($data['bn_sem_use_template']) ? '1' : '0') . ' ,'
            . '`bn_condition`="' . addslashes(_convert($data['bn_condition'], YW_CHARSET, true)) . '"'
            . ' WHERE `bn_id_nature`=' . $data['bn_id_nature']);
