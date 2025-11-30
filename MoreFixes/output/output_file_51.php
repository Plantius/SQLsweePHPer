                    $this->errors[] = _T("Password misrepeated: ");
                } else {
                    $pinfos = password_get_info($value);
                    //check if value is already a hash
                    if ($pinfos['algo'] == 0) {
                        $this->$prop = password_hash(
                            $value,
                            PASSWORD_BCRYPT
                        );

                        $pwcheck = new \Galette\Util\Password($preferences);
                        $pwcheck->setAdherent($this);
                        if (!$pwcheck->isValid($value)) {
                            $this->errors = array_merge(
                                $this->errors,
                                $pwcheck->getErrors()
                            );
                        }
                    }
                }
                break;
            case 'id_statut':
                try {
                    $this->$prop = (int)$value;
                    //check if status exists
                    $select = $this->zdb->select(Status::TABLE);
                    $select->where(Status::PK . '= ' . $value);

                    $results = $this->zdb->execute($select);
                    $result = $results->current();
                    if (!$result) {
                        $this->errors[] = str_replace(
                            '%id',
                            $value,
                            _T("Status #%id does not exists in database.")
                        );
                        break;
                    }
                } catch (Throwable $e) {
                    Analog::log(
                        'An error occurred checking status existence: ' . $e->getMessage(),
                        Analog::ERROR
                    );
                    $this->errors[] = _T("An error has occurred while looking if status does exists.");
                }
                break;
            case 'sexe_adh':
                if (in_array($value, [self::NC, self::MAN, self::WOMAN])) {
                    $this->$prop = (int)$value;
                } else {
                    $this->errors[] = _T("Gender %gender does not exists!");
                }
                break;
            case 'parent_id':
                $this->$prop = ($value instanceof Adherent) ? (int)$value->id : (int)$value;
                $this->loadParent();
                break;
        }
