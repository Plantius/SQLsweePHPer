                $commandCallable = function ($url) {
                    $sanitizedUrl = preg_replace('{://([^@]+?):(.+?)@}', '://', $url);

                    return sprintf('git remote set-url origin %s && git remote update --prune origin && git remote set-url origin %s', ProcessExecutor::escape($url), ProcessExecutor::escape($sanitizedUrl));
                };
