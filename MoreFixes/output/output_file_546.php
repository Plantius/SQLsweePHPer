    public function testResetActionWithoutToken()
    {
        $client = $this->createAuthenticatedClient();
        $passwordBefore = $this->users[2]->getPassword();

        $client->request('GET', '/security/reset', [
            'password' => 'thispasswordshouldnotbeapplied',
        ]);
        $response = \json_decode($client->getResponse()->getContent());
        $user = $this->em->find('SuluSecurityBundle:User', $this->users[2]->getId());

        $this->assertHttpStatusCode(400, $client->getResponse());
        $this->assertEquals(1005, $response->code);
        $this->assertEquals($passwordBefore, $user->getPassword());
    }
