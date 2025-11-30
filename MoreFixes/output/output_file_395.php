    public function getFileContent($file, $identifier)
    {
        $command = sprintf('fossil cat -r %s %s', ProcessExecutor::escape($identifier), ProcessExecutor::escape($file));
        $this->process->execute($command, $content, $this->checkoutDir);

        if (!trim($content)) {
            return null;
        }

        return $content;
    }
