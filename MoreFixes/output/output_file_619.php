            return sprintf('hg pull %s && hg up %s', ProcessExecutor::escape($url), ProcessExecutor::escape($ref));
        };
