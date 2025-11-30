    protected function getCommand($cmd, $url, $path = null)
    {
        $cmd = sprintf(
            '%s %s%s %s',
            $cmd,
            '--non-interactive ',
            $this->getCredentialString(),
            ProcessExecutor::escape($url)
        );

        if ($path) {
            $cmd .= ' ' . ProcessExecutor::escape($path);
        }

        return $cmd;
    }
