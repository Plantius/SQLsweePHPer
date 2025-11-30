    public function testuploadAssets_Exception()
    {
        $themeMock = $this->getMockBuilder('\Box\Mod\Theme\Model\Theme')->disableOriginalConstructor()->getMock();
        $themeMock->expects($this->atLeastOnce())
            ->method('getPathAssets');
        $files = array(
            'test0' => array(
                'error' => UPLOAD_ERR_CANT_WRITE
            ),
        );
        $this->expectException(\Box_Exception::class);
        $this->expectExceptionMessage(sprintf("Error uploading file %s Error code: %d", 'test0', UPLOAD_ERR_CANT_WRITE));
        $this->service->uploadAssets($themeMock, $files);
    }
