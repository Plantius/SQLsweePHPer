    public function testResendEmailActionTooMuch()
    {
        $client = $this->createAuthenticatedClient();
        $client->enableProfiler();

        // these request should all work (starting counter at 1 - because user3 already has one sent email)
        $counter = 1;
        $maxNumberEmails = $this->getContainer()->getParameter('sulu_security.reset_password.mail.token_send_limit');
        for (; $counter < $maxNumberEmails; ++$counter) {
            $client->request('GET', '/security/reset/email/resend', [
                'user' => $this->users[2]->getEmail(),
            ]);

            $mailCollector = $client->getProfile()->getCollector('swiftmailer');
            $response = \json_decode($client->getResponse()->getContent());

            $this->assertHttpStatusCode(200, $client->getResponse());
            $this->assertEquals($this->users[2]->getEmail(), $response->email);
            $this->assertEquals(1, $mailCollector->getMessageCount());
        }

        // now this request should fail
        $client->request('GET', '/security/reset/email/resend', [
            'user' => $this->users[2]->getEmail(),
        ]);

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $response = \json_decode($client->getResponse()->getContent());
        $user = $client->getContainer()->get('doctrine')->getManager()->find(
                'SuluSecurityBundle:User',
                $this->users[2]->getId()
            );

        $this->assertHttpStatusCode(400, $client->getResponse());
        $this->assertEquals(1007, $response->code);
        $this->assertEquals(0, $mailCollector->getMessageCount());
        $this->assertEquals($counter, $user->getPasswordResetTokenEmailsSent());
    }
