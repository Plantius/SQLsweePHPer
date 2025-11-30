    public function testDownload()
    {
        $packageMock = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $packageMock->expects($this->any())
            ->method('getSourceReference')
            ->will($this->returnValue('ref'));
        $packageMock->expects($this->once())
            ->method('getSourceUrls')
            ->will($this->returnValue(array('https://mercurial.dev/l3l0/composer')));
        $processExecutor = $this->getMockBuilder('Composer\Util\ProcessExecutor')->getMock();

        $expectedGitCommand = $this->getCmd('hg clone \'https://mercurial.dev/l3l0/composer\' \'composerPath\'');
        $processExecutor->expects($this->at(0))
            ->method('execute')
            ->with($this->equalTo($expectedGitCommand))
            ->will($this->returnValue(0));

        $expectedGitCommand = $this->getCmd('hg up \'ref\'');
        $processExecutor->expects($this->at(1))
            ->method('execute')
            ->with($this->equalTo($expectedGitCommand))
            ->will($this->returnValue(0));

        $downloader = $this->getDownloaderMock(null, null, $processExecutor);
        $downloader->download($packageMock, 'composerPath');
    }
