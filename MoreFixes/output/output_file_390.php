    public function getBodyTextRendered()
    {
        $text = $this->getTextBody();

        //if the content was manually set with $obj->text(); this content will be used
        if ($text) {
            $content = $this->renderParams($text);
        } else {
            //creating text version from html email
            try {
                $htmlContent = $this->getBodyHtmlRendered();
                $html = new DomCrawler($htmlContent);

                $body = $html->filter('body')->eq(0);
                if ($body->count()) {
                    $style = $body->filter('style')->eq(0);
                    if ($style->count()) {
                        $style->clear();
                    }
                    $htmlContent = $body->html();
                }

                $html->clear();
                unset($html);

                $content = $this->html2Text($htmlContent);
            } catch (\Exception $e) {
                Logger::err((string) $e);
                $content = '';
            }
        }

        return $content;
    }
