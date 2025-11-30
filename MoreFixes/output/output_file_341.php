    public function attachLink(Request $request)
    {
        $pageId = $request->get('attachment_link_uploaded_to');

        try {
            $this->validate($request, [
                'attachment_link_uploaded_to' => 'required|integer|exists:pages,id',
                'attachment_link_name' => 'required|string|min:1|max:255',
                'attachment_link_url' =>  'required|string|min:1|max:255'
            ]);
        } catch (ValidationException $exception) {
            return response()->view('attachments.manager-link-form', array_merge($request->only(['attachment_link_name', 'attachment_link_url']), [
                'pageId' => $pageId,
                'errors' => new MessageBag($exception->errors()),
            ]), 422);
        }

        $page = $this->pageRepo->getById($pageId);

        $this->checkPermission('attachment-create-all');
        $this->checkOwnablePermission('page-update', $page);

        $attachmentName = $request->get('attachment_link_name');
        $link = $request->get('attachment_link_url');
        $attachment = $this->attachmentService->saveNewFromLink($attachmentName, $link, $pageId);

        return view('attachments.manager-link-form', [
            'pageId' => $pageId,
        ]);
    }
