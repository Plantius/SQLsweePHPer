    public function testDownloadAndSetPushUrlUseCustomVariousProtocolsForGithub($protocols, $url, $pushUrl)
    {
        $packageMock = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $packageMock->expects($this->any())
            ->method('getSourceReference')
            ->will($this->returnValue('ref'));
        $packageMock->expects($this->any())
            ->method('getSourceUrls')
            ->will($this->returnValue(array('https://github.com/composer/composer')));
        $packageMock->expects($this->any())
            ->method('getSourceUrl')
            ->will($this->returnValue('https://github.com/composer/composer'));
        $packageMock->expects($this->any())
            ->method('getPrettyVersion')
            ->will($this->returnValue('1.0.0'));
        $processExecutor = $this->getMockBuilder('Composer\Util\ProcessExecutor')->getMock();

        $expectedGitCommand = $this->winCompat("git clone --no-checkout '{$url}' 'composerPath' && cd 'composerPath' && git remote add composer '{$url}' && git fetch composer && git remote set-url origin '{$url}' && git remote set-url composer '{$url}'");
        $processExecutor->expects($this->at(0))
            ->method('execute')
            ->with($this->equalTo($expectedGitCommand))
            ->will($this->returnValue(0));

        $expectedGitCommand = $this->winCompat("git remote set-url --push origin '{$pushUrl}'");
        $processExecutor->expects($this->at(1))
            ->method('execute')
            ->with($this->equalTo($expectedGitCommand), $this->equalTo(null), $this->equalTo($this->winCompat('composerPath')))
            ->will($this->returnValue(0));

        $processExecutor->expects($this->exactly(4))
            ->method('execute')
            ->will($this->returnValue(0));

        $config = new Config();
        $config->merge(array('config' => array('github-protocols' => $protocols)));

        $downloader = $this->getDownloaderMock(null, $config, $processExecutor);
        $downloader->download($packageMock, 'composerPath');
    }
