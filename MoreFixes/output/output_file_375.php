    public function doDownload(PackageInterface $package, $path, $url)
    {
        // Ensure we are allowed to use this URL by config
        $this->config->prohibitUrlByConfig($url, $this->io);

        $url = ProcessExecutor::escape($url);
        $ref = ProcessExecutor::escape($package->getSourceReference());
        $repoFile = $path . '.fossil';
        $this->io->writeError("Cloning ".$package->getSourceReference());
        $command = sprintf('fossil clone %s %s', $url, ProcessExecutor::escape($repoFile));
        if (0 !== $this->process->execute($command, $ignoredOutput)) {
            throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
        }
        $command = sprintf('fossil open %s --nested', ProcessExecutor::escape($repoFile));
        if (0 !== $this->process->execute($command, $ignoredOutput, realpath($path))) {
            throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
        }
        $command = sprintf('fossil update %s', $ref);
        if (0 !== $this->process->execute($command, $ignoredOutput, realpath($path))) {
            throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
        }
    }
