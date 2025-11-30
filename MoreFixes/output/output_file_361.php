    public function create($data)
    {
        if ($this->securityController->isWikiHibernated()) {
            throw new \Exception(_t('WIKI_IN_HIBERNATION'));
        }
        // If ID is not set or if it is already used, find a new ID
        if (!$data['bn_id_nature'] || $this->getOne($data['bn_id_nature'])) {
            $data['bn_id_nature'] = $this->findNewId();
        }

        return $this->dbService->query('INSERT INTO ' . $this->dbService->prefixTable('nature')
            . '(`bn_id_nature` ,`bn_ce_i18n` ,`bn_label_nature` ,`bn_template` ,`bn_description` ,`bn_sem_context` ,`bn_sem_type` ,`bn_sem_use_template` ,`bn_condition`)'
            . ' VALUES (' . $data['bn_id_nature'] . ', "fr-FR", "'
            . addslashes(_convert($data['bn_label_nature'], YW_CHARSET, true)) . '","'
            . addslashes(_convert($data['bn_template'], YW_CHARSET, true)) . '", "'
            . addslashes(_convert($data['bn_description'], YW_CHARSET, true)) . '", "'
            . addslashes(_convert($data['bn_sem_context'], YW_CHARSET, true)) . '", "'
            . addslashes(_convert($data['bn_sem_type'], YW_CHARSET, true)) . '", '
            . (isset($data['bn_sem_use_template']) ? '1' : '0') . ', "'
            . addslashes(_convert($data['bn_condition'], YW_CHARSET, true)) . '")');
