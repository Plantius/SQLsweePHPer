    public function testUpdateThrowsRuntimeExceptionIfGitCommandFails()
    {
        $expectedGitUpdateCommand = $this->winCompat("(git remote set-url composer 'https://github.com/composer/composer' && git rev-parse --quiet --verify 'ref^{commit}' || (git fetch composer && git fetch --tags composer)) && git remote set-url composer 'https://github.com/composer/composer'");

        $packageMock = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $packageMock->expects($this->any())
            ->method('getSourceReference')
            ->will($this->returnValue('ref'));
        $packageMock->expects($this->any())
            ->method('getSourceUrls')
            ->will($this->returnValue(array('https://github.com/composer/composer')));
        $packageMock->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('1.0.0.0'));
        $processExecutor = $this->getMockBuilder('Composer\Util\ProcessExecutor')->getMock();
        $processExecutor->expects($this->at(0))
            ->method('execute')
            ->with($this->equalTo($this->winCompat("git show-ref --head -d")))
            ->will($this->returnValue(0));
        $processExecutor->expects($this->at(1))
            ->method('execute')
            ->with($this->equalTo($this->winCompat("git status --porcelain --untracked-files=no")))
            ->will($this->returnValue(0));
        $processExecutor->expects($this->at(2))
            ->method('execute')
            ->with($this->equalTo($this->winCompat("git remote -v")))
            ->will($this->returnValue(0));
        $processExecutor->expects($this->at(3))
            ->method('execute')
            ->with($this->equalTo($this->winCompat("git remote -v")))
            ->will($this->returnValue(0));
        $processExecutor->expects($this->at(4))
            ->method('execute')
            ->with($this->equalTo($expectedGitUpdateCommand))
            ->will($this->returnValue(1));

        $this->fs->ensureDirectoryExists($this->workingDir.'/.git');
        $downloader = $this->getDownloaderMock(null, new Config(), $processExecutor);
        $downloader->update($packageMock, $packageMock, $this->workingDir);
    }
