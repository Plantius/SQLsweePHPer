    public function saveNewFromLink($name, $link, $page_id)
    {
        $largestExistingOrder = Attachment::where('uploaded_to', '=', $page_id)->max('order');
        return Attachment::forceCreate([
            'name' => $name,
            'path' => $link,
            'external' => true,
            'extension' => '',
            'uploaded_to' => $page_id,
            'created_by' => user()->id,
            'updated_by' => user()->id,
            'order' => $largestExistingOrder + 1
        ]);
    }
