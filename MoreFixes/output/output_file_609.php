    public static function supports(IOInterface $io, Config $config, $url, $deep = false)
    {
        if (preg_match('#(^git://|\.git/?$|git(?:olite)?@|//git\.|//github.com/)#i', $url)) {
            return true;
        }

        // local filesystem
        if (Filesystem::isLocalPath($url)) {
            $url = Filesystem::getPlatformPath($url);
            if (!is_dir($url)) {
                return false;
            }

            $process = new ProcessExecutor($io);
            // check whether there is a git repo in that path
            if ($process->execute('git tag', $output, $url) === 0) {
                return true;
            }
        }

        if (!$deep) {
            return false;
        }

        $gitUtil = new GitUtil($io, $config, new ProcessExecutor($io), new Filesystem());
        GitUtil::cleanEnv();

        try {
            $gitUtil->runCommand(function ($url) {
                return 'git ls-remote --heads ' . ProcessExecutor::escape($url);
            }, $url, sys_get_temp_dir());
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }
