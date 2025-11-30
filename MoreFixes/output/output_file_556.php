    public function testUpdate()
    {
        $fs = new Filesystem;
        $fs->ensureDirectoryExists($this->workingDir.'/.hg');
        $packageMock = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $packageMock->expects($this->any())
            ->method('getSourceReference')
            ->will($this->returnValue('ref'));
        $packageMock->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('1.0.0.0'));
        $packageMock->expects($this->any())
            ->method('getSourceUrls')
            ->will($this->returnValue(array('https://github.com/l3l0/composer')));
        $processExecutor = $this->getMockBuilder('Composer\Util\ProcessExecutor')->getMock();

        $expectedHgCommand = $this->getCmd("hg st");
        $processExecutor->expects($this->at(0))
            ->method('execute')
            ->with($this->equalTo($expectedHgCommand))
            ->will($this->returnValue(0));
        $expectedHgCommand = $this->getCmd("hg pull 'https://github.com/l3l0/composer' && hg up 'ref'");
        $processExecutor->expects($this->at(1))
            ->method('execute')
            ->with($this->equalTo($expectedHgCommand))
            ->will($this->returnValue(0));

        $downloader = $this->getDownloaderMock(null, null, $processExecutor);
        $downloader->update($packageMock, $packageMock, $this->workingDir);
    }
