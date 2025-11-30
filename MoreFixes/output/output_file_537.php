    public function testDownloadUsesVariousProtocolsAndSetsPushUrlForGithub()
    {
        $packageMock = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $packageMock->expects($this->any())
            ->method('getSourceReference')
            ->will($this->returnValue('ref'));
        $packageMock->expects($this->any())
            ->method('getSourceUrls')
            ->will($this->returnValue(array('https://github.com/mirrors/composer', 'https://github.com/composer/composer')));
        $packageMock->expects($this->any())
            ->method('getSourceUrl')
            ->will($this->returnValue('https://github.com/composer/composer'));
        $packageMock->expects($this->any())
            ->method('getPrettyVersion')
            ->will($this->returnValue('1.0.0'));
        $processExecutor = $this->getMockBuilder('Composer\Util\ProcessExecutor')->getMock();

        $expectedGitCommand = $this->winCompat("git clone --no-checkout 'https://github.com/mirrors/composer' 'composerPath' && cd 'composerPath' && git remote add composer 'https://github.com/mirrors/composer' && git fetch composer && git remote set-url origin 'https://github.com/mirrors/composer' && git remote set-url composer 'https://github.com/mirrors/composer'");
        $processExecutor->expects($this->at(0))
            ->method('execute')
            ->with($this->equalTo($expectedGitCommand))
            ->will($this->returnValue(1));

        $processExecutor->expects($this->at(1))
            ->method('getErrorOutput')
            ->with()
            ->will($this->returnValue('Error1'));

        $expectedGitCommand = $this->winCompat("git clone --no-checkout 'git@github.com:mirrors/composer' 'composerPath' && cd 'composerPath' && git remote add composer 'git@github.com:mirrors/composer' && git fetch composer && git remote set-url origin 'git@github.com:mirrors/composer' && git remote set-url composer 'git@github.com:mirrors/composer'");
        $processExecutor->expects($this->at(2))
            ->method('execute')
            ->with($this->equalTo($expectedGitCommand))
            ->will($this->returnValue(0));

        $expectedGitCommand = $this->winCompat("git remote set-url origin 'https://github.com/composer/composer'");
        $processExecutor->expects($this->at(3))
            ->method('execute')
            ->with($this->equalTo($expectedGitCommand), $this->equalTo(null), $this->equalTo($this->winCompat('composerPath')))
            ->will($this->returnValue(0));

        $expectedGitCommand = $this->winCompat("git remote set-url --push origin 'git@github.com:composer/composer.git'");
        $processExecutor->expects($this->at(4))
            ->method('execute')
            ->with($this->equalTo($expectedGitCommand), $this->equalTo(null), $this->equalTo($this->winCompat('composerPath')))
            ->will($this->returnValue(0));

        $processExecutor->expects($this->at(5))
            ->method('execute')
            ->with($this->equalTo('git branch -r'))
            ->will($this->returnValue(0));

        $processExecutor->expects($this->at(6))
            ->method('execute')
            ->with($this->equalTo($this->winCompat("git checkout 'ref' -- && git reset --hard 'ref' --")), $this->equalTo(null), $this->equalTo($this->winCompat('composerPath')))
            ->will($this->returnValue(0));

        $downloader = $this->getDownloaderMock(null, new Config(), $processExecutor);
        $downloader->download($packageMock, 'composerPath');
    }
