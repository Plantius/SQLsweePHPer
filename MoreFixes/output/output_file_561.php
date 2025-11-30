    public function testuploadAssets()
    {
        $themeMock = $this->getMockBuilder('\Box\Mod\Theme\Model\Theme')->disableOriginalConstructor()->getMock();
        $themeMock->expects($this->atLeastOnce())
            ->method('getPathAssets');
        $files = array(
            'test2' => array(
                'error' => UPLOAD_ERR_NO_FILE
                ),
            'test1' => array(
                    'error' => UPLOAD_ERR_OK,
                    'tmp_name' => 'tempName',
                ),
        );
        $this->service->uploadAssets($themeMock, $files);
    }
