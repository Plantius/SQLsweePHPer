    private function buildWhereClause(Select $select)
    {
        global $zdb, $login;

        try {
            if ($this->filters->email_filter == self::FILTER_W_EMAIL) {
                $select->where('email_adh != \'\'');
            }
            if ($this->filters->email_filter == self::FILTER_WO_EMAIL) {
                $select->where('(email_adh = \'\' OR email_adh IS NULL)');
            }

            if ($this->filters->filter_str != '') {
                $token = $zdb->platform->quoteValue(
                    '%' . strtolower($this->filters->filter_str) . '%'
                );
                switch ($this->filters->field_filter) {
                    case self::FILTER_NAME:
                        if (TYPE_DB === 'pgsql') {
                            $sep = " || ' ' || ";
                            $pre = '';
                            $post = '';
                        } else {
                            $sep = ', " ", ';
                            $pre = 'CONCAT(';
                            $post = ')';
                        }

                        $select->where(
                            '(' .
                            $pre . 'LOWER(nom_adh)' . $sep .
                            'LOWER(prenom_adh)' . $sep .
                            'LOWER(pseudo_adh)' . $post . ' LIKE ' .
                            $token
                            . ' OR ' .
                            $pre . 'LOWER(prenom_adh)' . $sep .
                            'LOWER(nom_adh)' . $sep .
                            'LOWER(pseudo_adh)' . $post . ' LIKE ' .
                            $token
                            . ')'
                        );
                        break;
                    case self::FILTER_COMPANY_NAME:
                        $select->where(
                            'LOWER(societe_adh) LIKE ' .
                            $token
                        );
                        break;
                    case self::FILTER_ADDRESS:
                        $select->where(
                            '(' .
                            'LOWER(adresse_adh) LIKE ' . $token
                            . ' OR ' .
                            'LOWER(adresse2_adh) LIKE ' . $token
                            . ' OR ' .
                            'cp_adh LIKE ' . $token
                            . ' OR ' .
                            'LOWER(ville_adh) LIKE ' . $token
                            . ' OR ' .
                            'LOWER(pays_adh) LIKE ' . $token
                            . ')'
                        );
                        break;
                    case self::FILTER_MAIL:
                        $select->where(
                            '(' .
                            'LOWER(email_adh) LIKE ' . $token
                            . ' OR ' .
                            'LOWER(so.url) LIKE ' . $token
                            . ')'
                        );
                        break;
                    case self::FILTER_JOB:
                        $select->where(
                            'LOWER(prof_adh) LIKE ' . $token
                        );
                        break;
                    case self::FILTER_INFOS:
                        $more = '';
                        if ($login->isAdmin() || $login->isStaff()) {
                            $more = ' OR LOWER(info_adh) LIKE ' . $token;
                        }
                        $select->where(
                            '(LOWER(info_public_adh) LIKE ' .
                            $token . $more . ')'
                        );
                        break;
                    case self::FILTER_NUMBER:
                        $select->where->equalTo('a.num_adh', $this->filters->filter_str);
                        break;
                    case self::FILTER_ID:
                        $select->where->equalTo('a.id_adh', $this->filters->filter_str);
                        break;
                }
            }

            if ($this->filters->membership_filter) {
                switch ($this->filters->membership_filter) {
                    case self::MEMBERSHIP_NEARLY:
                        $now = new \DateTime();
                        $duedate = new \DateTime();
                        $duedate->modify('+1 month');
                        $select->where->greaterThan(
                            'date_echeance',
                            $now->format('Y-m-d')
                        )->lessThanOrEqualTo(
                            'date_echeance',
                            $duedate->format('Y-m-d')
                        );
                        break;
                    case self::MEMBERSHIP_LATE:
                        $select->where
                            ->lessThan(
                                'date_echeance',
                                date('Y-m-d', time())
                            )->equalTo('bool_exempt_adh', new Expression('false'));
                        break;
                    case self::MEMBERSHIP_UP2DATE:
                        $select->where(
                            '(' . 'date_echeance >= \'' . date('Y-m-d', time())
                            . '\' OR bool_exempt_adh=true)'
                        );
                        break;
                    case self::MEMBERSHIP_NEVER:
                        $select->where('date_echeance IS NULL')
                            ->where('bool_exempt_adh = false');
                        break;
                    case self::MEMBERSHIP_STAFF:
                        $select->where->lessThan(
                            'status.priorite_statut',
                            self::NON_STAFF_MEMBERS
                        );
                        break;
                    case self::MEMBERSHIP_ADMIN:
                        $select->where->equalTo('bool_admin_adh', true);
                        break;
                    case self::MEMBERSHIP_NONE:
                        $select->where->equalTo('a.id_statut', Status::DEFAULT_STATUS);
                        break;
                }
            }

            if ($this->filters->filter_account) {
                switch ($this->filters->filter_account) {
                    case self::ACTIVE_ACCOUNT:
                        $select->where('activite_adh=true');
                        break;
                    case self::INACTIVE_ACCOUNT:
                        $select->where('activite_adh=false');
                        break;
                }
            }

            if ($this->filters->group_filter) {
                $select->join(
                    array('g' => PREFIX_DB . Group::GROUPSUSERS_TABLE),
                    'a.' . Adherent::PK . '=g.' . Adherent::PK,
                    array(),
                    $select::JOIN_LEFT
                )->join(
                    array('gs' => PREFIX_DB . Group::TABLE),
                    'gs.' . Group::PK . '=g.' . Group::PK,
                    array(),
                    $select::JOIN_LEFT
                )->where(
                    '(g.' . Group::PK . ' = ' . $this->filters->group_filter .
                    ' OR gs.parent_group = NULL OR gs.parent_group = ' .
                    $this->filters->group_filter . ')'
                );
            }

            if ($this->filters instanceof AdvancedMembersList) {
                $this->buildAdvancedWhereClause($select);
            }

            return $select;
        } catch (Throwable $e) {
            Analog::log(
                __METHOD__ . ' | ' . $e->getMessage(),
                Analog::WARNING
            );
            throw $e;
        }
    }
