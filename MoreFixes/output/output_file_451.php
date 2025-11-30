    public function hash(string $password)
    {
        if ((defined('PASSWORD_ARGON2I') && $this->config->hashAlgorithm === PASSWORD_ARGON2I)
            || (defined('PASSWORD_ARGON2ID') && $this->config->hashAlgorithm === PASSWORD_ARGON2ID)
        ) {
            $hashOptions = [
                'memory_cost' => $this->config->hashMemoryCost,
                'time_cost'   => $this->config->hashTimeCost,
                'threads'     => $this->config->hashThreads,
            ];
        } else {
            $hashOptions = [
                'cost' => $this->config->hashCost,
            ];
        }

        return password_hash(
            base64_encode(
                hash('sha384', $password, true)
            ),
            $this->config->hashAlgorithm,
            $hashOptions
        );
    }
