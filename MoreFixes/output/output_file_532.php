    public function testDownload()
    {
        $packageMock = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $packageMock->expects($this->any())
            ->method('getSourceReference')
            ->will($this->returnValue('1234567890123456789012345678901234567890'));
        $packageMock->expects($this->any())
            ->method('getSourceUrls')
            ->will($this->returnValue(array('https://example.com/composer/composer')));
        $packageMock->expects($this->any())
            ->method('getSourceUrl')
            ->will($this->returnValue('https://example.com/composer/composer'));
        $packageMock->expects($this->any())
            ->method('getPrettyVersion')
            ->will($this->returnValue('dev-master'));
        $processExecutor = $this->getMockBuilder('Composer\Util\ProcessExecutor')->getMock();

        $expectedGitCommand = $this->winCompat("git clone --no-checkout 'https://example.com/composer/composer' 'composerPath' && cd 'composerPath' && git remote add composer 'https://example.com/composer/composer' && git fetch composer && git remote set-url origin 'https://example.com/composer/composer' && git remote set-url composer 'https://example.com/composer/composer'");
        $processExecutor->expects($this->at(0))
            ->method('execute')
            ->with($this->equalTo($expectedGitCommand))
            ->will($this->returnValue(0));

        $processExecutor->expects($this->at(1))
            ->method('execute')
            ->with($this->equalTo($this->winCompat("git branch -r")), $this->equalTo(null), $this->equalTo($this->winCompat('composerPath')))
            ->will($this->returnValue(0));

        $processExecutor->expects($this->at(2))
            ->method('execute')
            ->with($this->equalTo($this->winCompat("git checkout 'master' --")), $this->equalTo(null), $this->equalTo($this->winCompat('composerPath')))
            ->will($this->returnValue(0));

        $processExecutor->expects($this->at(3))
            ->method('execute')
            ->with($this->equalTo($this->winCompat("git reset --hard '1234567890123456789012345678901234567890' --")), $this->equalTo(null), $this->equalTo($this->winCompat('composerPath')))
            ->will($this->returnValue(0));

        $downloader = $this->getDownloaderMock(null, null, $processExecutor);
        $downloader->download($packageMock, 'composerPath');
    }
