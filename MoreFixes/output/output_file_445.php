    public function getSubjectRendered()
    {
        $subject = $this->getSubject();

        if (!$subject && $this->getDocument()) {
            $subject = $this->getDocument()->getSubject();
        }

        if ($subject) {
            return $this->renderParams($subject);
        }

        return '';
    }
