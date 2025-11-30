    public function testSendEmailActionMultipleTimes()
    {
        $client = $this->createAuthenticatedClient();
        $client->enableProfiler();

        $client->request('GET', '/security/reset/email', [
            'user' => $this->users[0]->getUsername(),
        ]);
        $response = \json_decode($client->getResponse()->getContent());
        // asserting response
        $this->assertHttpStatusCode(200, $client->getResponse());
        $this->assertEquals($this->users[0]->getEmail(), $response->email);

        // second request should be blocked
        $client->request('GET', '/security/reset/email', [
            'user' => $this->users[0]->getUsername(),
        ]);
        $response = \json_decode($client->getResponse()->getContent());
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        // asserting response
        $this->assertHttpStatusCode(400, $client->getResponse());
        $this->assertEquals(1003, $response->code);
        $this->assertEquals(0, $mailCollector->getMessageCount());
    }
