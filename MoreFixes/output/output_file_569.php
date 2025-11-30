    public function updateFile(Attachment $attachment, $requestData)
    {
        $attachment->name = $requestData['name'];
        if (isset($requestData['link']) && trim($requestData['link']) !== '') {
            $attachment->path = $requestData['link'];
            if (!$attachment->external) {
                $this->deleteFileInStorage($attachment);
                $attachment->external = true;
            }
        }
        $attachment->save();
        return $attachment;
    }
