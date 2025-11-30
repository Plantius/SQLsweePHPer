                    return sprintf('hg clone --noupdate %s %s', ProcessExecutor::escape($url), ProcessExecutor::escape($repoDir));
                };

                $hgUtils->runCommand($command, $this->url, null);
            }
        }
