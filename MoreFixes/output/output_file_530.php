    public function test_attachment_updating()
    {
        $page = Page::first();
        $this->asAdmin();

        $this->call('POST', 'attachments/link', [
            'attachment_link_url' => 'https://example.com',
            'attachment_link_name' => 'Example Attachment Link',
            'attachment_link_uploaded_to' => $page->id,
        ]);

        $attachmentId = Attachment::first()->id;

        $update = $this->call('PUT', 'attachments/' . $attachmentId, [
            'attachment_edit_name' => 'My new attachment name',
            'attachment_edit_url' => 'https://test.example.com'
        ]);

        $expectedData = [
            'id' => $attachmentId,
            'path' => 'https://test.example.com',
            'name' => 'My new attachment name',
            'uploaded_to' => $page->id
        ];

        $update->assertStatus(200);
        $this->assertDatabaseHas('attachments', $expectedData);

        $this->deleteUploads();
    }
