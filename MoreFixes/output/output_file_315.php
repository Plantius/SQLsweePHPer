    protected function extract($file, $path)
    {
        $targetFilepath = $path . DIRECTORY_SEPARATOR . basename(substr($file, 0, -3));

        // Try to use gunzip on *nix
        if (!Platform::isWindows()) {
            $command = 'gzip -cd ' . ProcessExecutor::escape($file) . ' > ' . ProcessExecutor::escape($targetFilepath);

            if (0 === $this->process->execute($command, $ignoredOutput)) {
                return;
            }

            if (extension_loaded('zlib')) {
                // Fallback to using the PHP extension.
                $this->extractUsingExt($file, $targetFilepath);

                return;
            }

            $processError = 'Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput();
            throw new \RuntimeException($processError);
        }

        // Windows version of PHP has built-in support of gzip functions
        $this->extractUsingExt($file, $targetFilepath);
    }
