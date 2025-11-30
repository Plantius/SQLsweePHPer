    public function testSendEmailAction()
    {
        $client = $this->createAuthenticatedClient();
        $client->enableProfiler();

        $client->request('GET', '/security/reset/email', [
            'user' => $this->users[0]->getEmail(),
        ]);

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        $response = \json_decode($client->getResponse()->getContent());

        // asserting response
        $this->assertHttpStatusCode(200, $client->getResponse());
        $this->assertEquals($this->users[0]->getEmail(), $response->email);

        // asserting user properties
        $user = $client->getContainer()->get('doctrine')->getManager()->find(
            'SuluSecurityBundle:User',
            $this->users[0]->getId()
        );
        $this->assertTrue(\is_string($user->getPasswordResetToken()));
        $this->assertGreaterThan(new \DateTime(), $user->getPasswordResetTokenExpiresAt());

        // asserting sent mail
        $expectedEmailData = $this->getExpectedEmailData($client, $user);

        $this->assertEquals(1, $mailCollector->getMessageCount());
        $message = $mailCollector->getMessages()[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals($expectedEmailData['sender'], \key($message->getFrom()));
        $this->assertEquals($user->getEmail(), \key($message->getTo()));
        $this->assertEquals($expectedEmailData['subject'], $message->getSubject());
        $this->assertEquals($expectedEmailData['body'], $message->getBody());
    }
