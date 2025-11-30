            $this->keyContents = \file_get_contents($keyPath);
            $this->keyPath = $keyPath;
            if (!$this->isValidKey($this->keyContents, $this->passPhrase ?? '')) {
                throw new LogicException('Unable to read key from file ' . $keyPath);
            }
        } else {
            throw new LogicException('Unable to read key from file ' . $keyPath);
        }

        if ($keyPermissionsCheck === true) {
            // Verify the permissions of the key
            $keyPathPerms = \decoct(\fileperms($this->keyPath) & 0777);
            if (\in_array($keyPathPerms, ['400', '440', '600', '640', '660'], true) === false) {
                \trigger_error(
                    \sprintf(
                        'Key file "%s" permissions are not correct, recommend changing to 600 or 660 instead of %s',
                        $this->keyPath,
                        $keyPathPerms
                    ),
                    E_USER_NOTICE
                );
            }
        }
    }
