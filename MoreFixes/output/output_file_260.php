                @mkdir($cachePath, 0777, true);

                return 0;
            }));
        $processExecutor->expects($this->at(1))
            ->method('execute')
            ->with($this->equalTo('git rev-parse --git-dir'), $this->anything(), $this->equalTo($this->winCompat($cachePath)))
            ->will($this->returnCallback(function ($command, &$output = null) {
                $output = '.';

                return 0;
            }));
        $processExecutor->expects($this->at(2))
            ->method('execute')
            ->with($this->equalTo($this->winCompat('git rev-parse --quiet --verify \'1234567890123456789012345678901234567890^{commit}\'')), $this->equalTo(null), $this->equalTo($this->winCompat($cachePath)))
            ->will($this->returnValue(0));

        $expectedGitCommand = $this->winCompat(sprintf("git clone --no-checkout '%1\$s' 'composerPath' --dissociate --reference '%1\$s' && cd 'composerPath' && git remote set-url origin 'https://example.com/composer/composer' && git remote add composer 'https://example.com/composer/composer'", $cachePath));
        $processExecutor->expects($this->at(3))
            ->method('execute')
            ->with($this->equalTo($expectedGitCommand))
            ->will($this->returnValue(0));

        $processExecutor->expects($this->at(4))
            ->method('execute')
            ->with($this->equalTo($this->winCompat("git branch -r")), $this->equalTo(null), $this->equalTo($this->winCompat('composerPath')))
            ->will($this->returnValue(0));

        $processExecutor->expects($this->at(5))
            ->method('execute')
            ->with($this->equalTo($this->winCompat("git checkout 'master' --")), $this->equalTo(null), $this->equalTo($this->winCompat('composerPath')))
            ->will($this->returnValue(0));

        $processExecutor->expects($this->at(6))
            ->method('execute')
            ->with($this->equalTo($this->winCompat("git reset --hard '1234567890123456789012345678901234567890' --")), $this->equalTo(null), $this->equalTo($this->winCompat('composerPath')))
            ->will($this->returnValue(0));

        $downloader = $this->getDownloaderMock(null, $config, $processExecutor);
        $downloader->download($packageMock, 'composerPath');
        @rmdir($cachePath);
    }
