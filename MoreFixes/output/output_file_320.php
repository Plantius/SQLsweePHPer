    protected function getCommitLogs($fromReference, $toReference, $path)
    {
        if (preg_match('{.*@(\d+)$}', $fromReference) && preg_match('{.*@(\d+)$}', $toReference)) {
            // retrieve the svn base url from the checkout folder
            $command = sprintf('svn info --non-interactive --xml %s', ProcessExecutor::escape($path));
            if (0 !== $this->process->execute($command, $output, $path)) {
                throw new \RuntimeException(
                    'Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput()
                );
            }

            $urlPattern = '#<url>(.*)</url>#';
            if (preg_match($urlPattern, $output, $matches)) {
                $baseUrl = $matches[1];
            } else {
                throw new \RuntimeException(
                    'Unable to determine svn url for path '. $path
                );
            }

            // strip paths from references and only keep the actual revision
            $fromRevision = preg_replace('{.*@(\d+)$}', '$1', $fromReference);
            $toRevision = preg_replace('{.*@(\d+)$}', '$1', $toReference);

            $command = sprintf('svn log -r%s:%s --incremental', ProcessExecutor::escape($fromRevision), ProcessExecutor::escape($toRevision));

            $util = new SvnUtil($baseUrl, $this->io, $this->config);
            $util->setCacheCredentials($this->cacheCredentials);
            try {
                return $util->executeLocal($command, $path, null, $this->io->isVerbose());
            } catch (\RuntimeException $e) {
                throw new \RuntimeException(
                    'Failed to execute ' . $command . "\n\n".$e->getMessage()
                );
            }
        }

        return "Could not retrieve changes between $fromReference and $toReference due to missing revision information";
    }
