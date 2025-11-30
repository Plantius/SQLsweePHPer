    public static function supports(IOInterface $io, Config $config, $url, $deep = false)
    {
        $url = self::normalizeUrl($url);
        if (preg_match('#(^svn://|^svn\+ssh://|svn\.)#i', $url)) {
            return true;
        }

        // proceed with deep check for local urls since they are fast to process
        if (!$deep && !Filesystem::isLocalPath($url)) {
            return false;
        }

        $processExecutor = new ProcessExecutor($io);

        $exit = $processExecutor->execute(
            "svn info --non-interactive ".ProcessExecutor::escape($url),
            $ignoredOutput
        );

        if ($exit === 0) {
            // This is definitely a Subversion repository.
            return true;
        }

        // Subversion client 1.7 and older
        if (false !== stripos($processExecutor->getErrorOutput(), 'authorization failed:')) {
            // This is likely a remote Subversion repository that requires
            // authentication. We will handle actual authentication later.
            return true;
        }

        // Subversion client 1.8 and newer
        if (false !== stripos($processExecutor->getErrorOutput(), 'Authentication failed')) {
            // This is likely a remote Subversion or newer repository that requires
            // authentication. We will handle actual authentication later.
            return true;
        }

        return false;
    }
