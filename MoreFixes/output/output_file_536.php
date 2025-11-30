    public function testDownloadThrowsRuntimeExceptionIfGitCommandFails()
    {
        $expectedGitCommand = $this->winCompat("git clone --no-checkout 'https://example.com/composer/composer' 'composerPath' && cd 'composerPath' && git remote add composer 'https://example.com/composer/composer' && git fetch composer && git remote set-url origin 'https://example.com/composer/composer' && git remote set-url composer 'https://example.com/composer/composer'");
        $packageMock = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $packageMock->expects($this->any())
            ->method('getSourceReference')
            ->will($this->returnValue('ref'));
        $packageMock->expects($this->any())
            ->method('getSourceUrls')
            ->will($this->returnValue(array('https://example.com/composer/composer')));
        $processExecutor = $this->getMockBuilder('Composer\Util\ProcessExecutor')->getMock();
        $processExecutor->expects($this->at(0))
            ->method('execute')
            ->with($this->equalTo($expectedGitCommand))
            ->will($this->returnValue(1));

        $downloader = $this->getDownloaderMock(null, null, $processExecutor);
        $downloader->download($packageMock, 'composerPath');
    }
