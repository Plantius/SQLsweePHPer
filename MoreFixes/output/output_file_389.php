    public function getBodyHtmlRendered()
    {
        $html = $this->getHtmlBody();

        // if the content was manually set with $obj->setBody(); this content will be used
        // and not the content of the Document!
        if (!$html) {
            // render document
            if ($this->getDocument() instanceof Model\Document) {
                $attributes = $this->getParams();
                $attributes[ElementListener::FORCE_ALLOW_PROCESSING_UNPUBLISHED_ELEMENTS] = true;

                $html = Model\Document\Service::render($this->getDocument(), $attributes);
            }
        }

        $content = null;
        if ($html) {
            $content = $this->renderParams($html);

            // modifying the content e.g set absolute urls...
            $content = MailHelper::embedAndModifyCss($content, $this->getDocument());
            $content = MailHelper::setAbsolutePaths($content, $this->getDocument(), $this->getHostUrl());
        }

        return $content;
    }
