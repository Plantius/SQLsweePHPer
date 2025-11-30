    public function update(Request $request, string $attachmentId)
    {
        $attachment = $this->attachment->newQuery()->findOrFail($attachmentId);

        try {
            $this->validate($request, [
                'attachment_edit_name' => 'required|string|min:1|max:255',
                'attachment_edit_url' =>  'string|min:1|max:255'
            ]);
        } catch (ValidationException $exception) {
            return response()->view('attachments.manager-edit-form', array_merge($request->only(['attachment_edit_name', 'attachment_edit_url']), [
                'attachment' => $attachment,
                'errors' => new MessageBag($exception->errors()),
            ]), 422);
        }

        $this->checkOwnablePermission('view', $attachment->page);
        $this->checkOwnablePermission('page-update', $attachment->page);
        $this->checkOwnablePermission('attachment-create', $attachment);

        $attachment = $this->attachmentService->updateFile($attachment, [
            'name' => $request->get('attachment_edit_name'),
            'link' => $request->get('attachment_edit_url'),
        ]);

        return view('attachments.manager-edit-form', [
            'attachment' => $attachment,
        ]);
    }
