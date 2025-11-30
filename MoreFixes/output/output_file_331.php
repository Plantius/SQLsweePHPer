    protected function updateLocalRepo()
    {
        $fs = new Filesystem();
        $fs->ensureDirectoryExists($this->checkoutDir);

        if (!is_writable(dirname($this->checkoutDir))) {
            throw new \RuntimeException('Can not clone '.$this->url.' to access package information. The "'.$this->checkoutDir.'" directory is not writable by the current user.');
        }

        // update the repo if it is a valid fossil repository
        if (is_file($this->repoFile) && is_dir($this->checkoutDir) && 0 === $this->process->execute('fossil info', $output, $this->checkoutDir)) {
            if (0 !== $this->process->execute('fossil pull', $output, $this->checkoutDir)) {
                $this->io->writeError('<error>Failed to update '.$this->url.', package information from this repository may be outdated ('.$this->process->getErrorOutput().')</error>');
            }
        } else {
            // clean up directory and do a fresh clone into it
            $fs->removeDirectory($this->checkoutDir);
            $fs->remove($this->repoFile);

            $fs->ensureDirectoryExists($this->checkoutDir);

            if (0 !== $this->process->execute(sprintf('fossil clone %s %s', ProcessExecutor::escape($this->url), ProcessExecutor::escape($this->repoFile)), $output)) {
                $output = $this->process->getErrorOutput();

                throw new \RuntimeException('Failed to clone '.$this->url.' to repository ' . $this->repoFile . "\n\n" .$output);
            }

            if (0 !== $this->process->execute(sprintf('fossil open %s --nested', ProcessExecutor::escape($this->repoFile)), $output, $this->checkoutDir)) {
                $output = $this->process->getErrorOutput();

                throw new \RuntimeException('Failed to open repository '.$this->repoFile.' in ' . $this->checkoutDir . "\n\n" .$output);
            }
        }
    }
