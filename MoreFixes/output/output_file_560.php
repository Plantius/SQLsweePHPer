    public function testuploadAssets()
    {
        $files = array(
            'file1' => array(
                    'error' => UPLOAD_ERR_NO_FILE,
                ),
            'file2' => array(
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => 'tmpName',
            ),

        );

        $service = new \Box\Mod\Theme\Service();
        $service->setDi($this->di);

        $themeModel = $service->getTheme('huraga');
        $service->uploadAssets($themeModel, $files);
    }
