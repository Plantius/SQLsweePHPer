    public function clear($id)
    {
        if ($this->securityController->isWikiHibernated()) {
            throw new \Exception(_t('WIKI_IN_HIBERNATION'));
        }
        $this->dbService->query(
            'DELETE FROM' . $this->dbService->prefixTable('acls') .
            'WHERE page_tag IN (SELECT tag FROM ' . $this->dbService->prefixTable('pages') .
            'WHERE tag IN (SELECT resource FROM ' . $this->dbService->prefixTable('triples') .
            'WHERE property="http://outils-reseaux.org/_vocabulary/type" AND value="fiche_bazar") AND body LIKE \'%"id_typeannonce":"' . $id . '"%\' );'
        );

        // TODO use PageManager
        $this->dbService->query(
            'DELETE FROM' . $this->dbService->prefixTable('pages') .
            'WHERE tag IN (SELECT resource FROM ' . $this->dbService->prefixTable('triples') .
            'WHERE property="http://outils-reseaux.org/_vocabulary/type" AND value="fiche_bazar") AND body LIKE \'%"id_typeannonce":"' . $id . '"%\';'
        );

        // TODO use TripleStore
        $this->dbService->query(
            'DELETE FROM' . $this->dbService->prefixTable('triples') .
            'WHERE resource NOT IN (SELECT tag FROM ' . $this->dbService->prefixTable('pages') .
            'WHERE 1) AND property="http://outils-reseaux.org/_vocabulary/type" AND value="fiche_bazar";'
        );
    }
