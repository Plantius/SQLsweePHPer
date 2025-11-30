            return sprintf('git clone --mirror %s %s', ProcessExecutor::escape($url), ProcessExecutor::escape($dir));
        };
