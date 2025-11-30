    public static function supports(IOInterface $io, Config $config, $url, $deep = false)
    {
        if (preg_match('#(^(?:https?|ssh)://(?:[^@]+@)?bitbucket.org|https://(?:.*?)\.kilnhg.com)#i', $url)) {
            return true;
        }

        // local filesystem
        if (Filesystem::isLocalPath($url)) {
            $url = Filesystem::getPlatformPath($url);
            if (!is_dir($url)) {
                return false;
            }

            $process = new ProcessExecutor($io);
            // check whether there is a hg repo in that path
            if ($process->execute('hg summary', $output, $url) === 0) {
                return true;
            }
        }

        if (!$deep) {
            return false;
        }

        $processExecutor = new ProcessExecutor($io);
        $exit = $processExecutor->execute(sprintf('hg identify %s', ProcessExecutor::escape($url)), $ignored);

        return $exit === 0;
    }
