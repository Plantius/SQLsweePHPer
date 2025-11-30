    public function validateMessageCategory()
    {
        if ($this->enableI18N && empty($this->messageCategory)) {
            $this->addError('messageCategory', "Message Category cannot be blank.");
        }
    }
