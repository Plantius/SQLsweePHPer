    protected function setPushUrl($path, $url)
    {
        // set push url for github projects
        if (preg_match('{^(?:https?|git)://'.GitUtil::getGitHubDomainsRegex($this->config).'/([^/]+)/([^/]+?)(?:\.git)?$}', $url, $match)) {
            $protocols = $this->config->get('github-protocols');
            $pushUrl = 'git@'.$match[1].':'.$match[2].'/'.$match[3].'.git';
            if (!in_array('ssh', $protocols, true)) {
                $pushUrl = 'https://' . $match[1] . '/'.$match[2].'/'.$match[3].'.git';
            }
            $cmd = sprintf('git remote set-url --push origin %s', ProcessExecutor::escape($pushUrl));
            $this->process->execute($cmd, $ignoredOutput, $path);
        }
    }
