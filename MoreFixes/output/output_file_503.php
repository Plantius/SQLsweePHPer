    public function save($check_notify = false)
    {
        global $current_user;

        $id = false;

        if ($this->isDuplicate) {
            $GLOBALS['log']->debug("EMAIL - tried to save a duplicate Email record");
        } else {
            if (empty($this->id)) {
                $this->id = create_guid();
                $this->new_with_id = true;
            }

            if ($this->cleanEmails($this->from_addr_name) === '') {
                $this->from_addr_name = $this->cleanEmails($this->from_name);
            } else {
                $this->from_addr_name = $this->cleanEmails($this->from_addr_name);
            }
            $this->to_addrs_names = $this->cleanEmails($this->to_addrs_names);
            $this->cc_addrs_names = $this->cleanEmails($this->cc_addrs_names);
            $this->bcc_addrs_names = $this->cleanEmails($this->bcc_addrs_names);
            $this->reply_to_addr = $this->cleanEmails($this->reply_to_addr);
            $this->description = SugarCleaner::cleanHtml($this->description);
            if (empty($this->description_html)) {
                $this->description_html = $this->description;
                $this->description_html = nl2br($this->description_html);
            }
            $this->description_html = SugarCleaner::cleanHtml($this->description_html, true);
            $this->raw_source = SugarCleaner::cleanHtml($this->raw_source, true);
            $this->saveEmailText();
            $this->saveEmailAddresses();


            if (empty($this->assigned_user_id)) {
                $this->assigned_user_id = $current_user->id;
            }

            $GLOBALS['log']->debug('-------------------------------> Email called save()');

            if (empty($this->date_sent_received)) {
                global $timedate;

                $date_sent_received_obj = $timedate->fromUser(
                    $timedate->merge_date_time($this->date_start, $this->time_start),
                    $current_user
                );

                if ($date_sent_received_obj !== null && ($date_sent_received_obj instanceof SugarDateTime)) {
                    $this->date_sent_received = $date_sent_received_obj->asDb();
                }
            }


            $validator = new EmailFromValidator();
            if (!defined('SUGARCRM_IS_INSTALLING') && !$validator->isValid($this)) {
                $errors = $validator->getErrorsAsText();
                $details = "Details:\n{$errors['messages']}\ncodes:{$errors['codes']}";
                LoggerManager::getLogger()->error("Saving Email with invalid From name and/or Address. $details");
            }


            if ((!isset($this->date_sent_received) || !$this->date_sent_received) && in_array($this->status, ['sent', 'replied'])) {
                $this->date_sent_received = TimeDate::getInstance()->nowDb();
            }

            $id = parent::save($check_notify);

            if (!empty($this->parent_type) && !empty($this->parent_id)) {
                if (!empty($this->fetched_row) && !empty($this->fetched_row['parent_id']) && !empty($this->fetched_row['parent_type'])) {
                    if ($this->fetched_row['parent_id'] != $this->parent_id || $this->fetched_row['parent_type'] != $this->parent_type) {
                        $mod = strtolower($this->fetched_row['parent_type']);
                        $rel = array_key_exists(
                            $mod,
                            $this->field_defs
                        ) ? $mod : $mod . "_activities_emails"; //Custom modules rel name
                        if ($this->load_relationship($rel)) {
                            $this->$rel->delete($this->id, $this->fetched_row['parent_id']);
                        }
                    }
                }
                $mod = strtolower($this->parent_type);
                $rel = array_key_exists(
                    $mod,
                    $this->field_defs
                ) ? $mod : $mod . "_activities_emails"; //Custom modules rel name
                if ($this->load_relationship($rel)) {
                    $this->$rel->add($this->parent_id);
                }
            }
        }
        $GLOBALS['log']->debug('-------------------------------> Email save() done');

        return $id;
    }
