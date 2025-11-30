    public function update()
    {
        try {
            $_recipients = array();
            if ($this->recipients != null) {
                foreach ($this->recipients as $_r) {
                    $_recipients[$_r->id] = $_r->sname . ' <' . $_r->email . '>';
                }
            }

            $sender = ($this->sender === 0) ?
                new Expression('NULL') : $this->sender;
            $sender_name = ($this->sender_name === null) ?
                new Expression('NULL') : $this->sender_name;
            $sender_address = ($this->sender_address === null) ?
                new Expression('NULL') : $this->sender_address;

            $values = array(
                'mailing_sender'            => $sender,
                'mailing_sender_name'       => $sender_name,
                'mailing_sender_address'    => $sender_address,
                'mailing_subject'           => $this->subject,
                'mailing_body'              => $this->message,
                'mailing_date'              => $this->date,
                'mailing_recipients'        => serialize($_recipients),
                'mailing_sent'              => ($this->sent) ?
                    true :
                    ($this->zdb->isPostgres() ? 'false' : 0)
            );

            $update = $this->zdb->update(self::TABLE);
            $update->set($values);
            $update->where(self::PK . ' = ' . $this->mailing->history_id);
            $this->zdb->execute($update);
            return true;
        } catch (Throwable $e) {
            Analog::log(
                'An error occurend updating Mailing | ' . $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
