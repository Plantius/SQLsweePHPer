    public function save($check_notify = false)
    {
        if (isset($_POST['email_recipients']) && is_array($_POST['email_recipients'])) {
            $this->email_recipients = base64_encode(serialize($_POST['email_recipients']));
        }

        return parent::save($check_notify);
    }
