    public static function cleanHtml($dirtyHtml, $removeHtml = false)
    {
        // $encode_html previously effected the decoding process.
        // we should decode regardless, just in case, the calling method passing encoded html
        //Prevent that the email address in Outlook format are removed
        $pattern = '/(.*)(&lt;([a-zA-Z0-9.!#$%&\'*+\=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*)&gt;)(.*)/';
        $replacement = '${1}<<a href="mailto:${3}">${3}</a>> ${4}';
        $dirtyHtml =  preg_replace($pattern, $replacement, $dirtyHtml);
        $dirty_html_decoded = html_entity_decode($dirtyHtml);

        // Re-encode html
        if ($removeHtml === true) {
            // remove all HTML tags
            $sugarCleaner = self::getInstance();
            $purifier = $sugarCleaner->purifier;
            $clean_html = $purifier->purify($dirty_html_decoded);
        } else {
            // encode all HTML tags
            $clean_html = $dirty_html_decoded;
        }

        return $clean_html;
    }
