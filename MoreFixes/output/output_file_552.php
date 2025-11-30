    public function testSendEmailActionWithUserWithoutEmail()
    {
        $client = $this->createAuthenticatedClient();
        $client->enableProfiler();

        $client->request('GET', '/security/reset/email', [
            'user' => $this->users[1]->getUsername(),
        ]);

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        $response = \json_decode($client->getResponse()->getContent());

        // asserting response
        $this->assertHttpStatusCode(200, $client->getResponse());
        $this->assertEquals('installation.email@sulu.test', $response->email);

        // asserting user properties
        $user = $client->getContainer()->get('doctrine')->getManager()->find(
                'SuluSecurityBundle:User',
                $this->users[1]->getId()
            );
        $this->assertTrue(\is_string($user->getPasswordResetToken()));
        $this->assertGreaterThan(new \DateTime(), $user->getPasswordResetTokenExpiresAt());
        $this->assertEquals(1, $user->getPasswordResetTokenEmailsSent());

        // asserting sent mail
        $expectedEmailData = $this->getExpectedEmailData($client, $user);

        $this->assertEquals(1, $mailCollector->getMessageCount());
        $message = $mailCollector->getMessages()[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals($expectedEmailData['sender'], \key($message->getFrom()));
        $this->assertEquals('installation.email@sulu.test', \key($message->getTo()));
        $this->assertEquals($expectedEmailData['subject'], $message->getSubject());
        $this->assertEquals($expectedEmailData['body'], $message->getBody());
    }
