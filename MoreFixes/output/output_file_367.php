    public function delete($id)
    {
        if ($this->securityController->isWikiHibernated()) {
            throw new \Exception(_t('WIKI_IN_HIBERNATION'));
        }

        // tests of if $formId is int
        if (strval(intval($id)) != strval($id)) {
            return null ;
        }

        $this->clear($id);
        return $this->dbService->query('DELETE FROM ' . $this->dbService->prefixTable('nature') . 'WHERE bn_id_nature=' . $id);
    }
