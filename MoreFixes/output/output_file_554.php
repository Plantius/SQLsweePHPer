    public function testUnsupportedKeyType()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unable to read key');

        try {
            // Create the keypair
            $res = \openssl_pkey_new([
                'digest_alg' => 'sha512',
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_DSA,
            ]);
            // Get private key
            \openssl_pkey_export($res, $keyContent, 'mystrongpassword');
            $path = self::generateKeyPath($keyContent);

            new CryptKey($keyContent, 'mystrongpassword');
        } finally {
            if (isset($path)) {
                @\unlink($path);
            }
        }
    }
