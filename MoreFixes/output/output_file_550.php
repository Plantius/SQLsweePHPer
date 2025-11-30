    public function testSendEmailActionWithNotExistingUser()
    {
        $client = $this->createAuthenticatedClient();
        $client->enableProfiler();

        $client->request('GET', '/security/reset/email', [
            'user' => 'lord.voldemort@askab.an',
        ]);

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        $response = \json_decode($client->getResponse()->getContent());

        $this->assertHttpStatusCode(400, $client->getResponse());
        $this->assertEquals(0, $response->code);
        $this->assertEquals(0, $mailCollector->getMessageCount());
    }
