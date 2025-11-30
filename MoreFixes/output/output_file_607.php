    public static function stripTags($dirtyHtml, $isEncoded = true)
    {
        if ($isEncoded) {
            $dirtyHtml = from_html($dirtyHtml);
        }
        $dirtyHtml = filter_var($dirtyHtml, FILTER_SANITIZE_STRIPPED, FILTER_FLAG_NO_ENCODE_QUOTES);
        return $isEncoded ? to_html($dirtyHtml) : $dirtyHtml;
    }
