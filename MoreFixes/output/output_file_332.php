    protected function updateOriginUrl($path, $url)
    {
        $this->process->execute(sprintf('git remote set-url origin %s', ProcessExecutor::escape($url)), $output, $path);
        $this->setPushUrl($path, $url);
    }
