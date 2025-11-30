    public function testResetAction()
    {
        $client = $this->createAuthenticatedClient();
        $newPassword = 'anewpasswordishouldremeber';

        $client->request('GET', '/security/reset', [
            'token' => 'thisisasupersecrettoken',
            'password' => $newPassword,
        ]);
        $response = \json_decode($client->getResponse()->getContent());
        $user = $client->getContainer()->get('doctrine')->getManager()->find(
                'SuluSecurityBundle:User',
                $this->users[2]->getId()
            );

        $this->assertHttpStatusCode(200, $client->getResponse());

        $encoder = $this->getContainer()->get('sulu_security.encoder_factory')->getEncoder($user);
        $this->assertEquals($encoder->encodePassword($newPassword, $user->getSalt()), $user->getPassword());
        $this->assertNull($user->getPasswordResetToken());
        $this->assertNull($user->getPasswordResetTokenExpiresAt());
    }
