    public function testDownload()
    {
        $packageMock = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $packageMock->expects($this->any())
            ->method('getSourceReference')
            ->will($this->returnValue('trunk'));
        $packageMock->expects($this->once())
            ->method('getSourceUrls')
            ->will($this->returnValue(array('http://fossil.kd2.org/kd2fw/')));
        $processExecutor = $this->getMockBuilder('Composer\Util\ProcessExecutor')->getMock();

        $expectedFossilCommand = $this->getCmd('fossil clone \'http://fossil.kd2.org/kd2fw/\' \'repo.fossil\'');
        $processExecutor->expects($this->at(0))
            ->method('execute')
            ->with($this->equalTo($expectedFossilCommand))
            ->will($this->returnValue(0));

        $expectedFossilCommand = $this->getCmd('fossil open \'repo.fossil\' --nested');
        $processExecutor->expects($this->at(1))
            ->method('execute')
            ->with($this->equalTo($expectedFossilCommand))
            ->will($this->returnValue(0));

        $expectedFossilCommand = $this->getCmd('fossil update \'trunk\'');
        $processExecutor->expects($this->at(2))
            ->method('execute')
            ->with($this->equalTo($expectedFossilCommand))
            ->will($this->returnValue(0));

        $downloader = $this->getDownloaderMock(null, null, $processExecutor);
        $downloader->download($packageMock, 'repo');
    }
