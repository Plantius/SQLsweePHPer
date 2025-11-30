            return sprintf('hg clone %s %s', ProcessExecutor::escape($url), ProcessExecutor::escape($path));
        };
