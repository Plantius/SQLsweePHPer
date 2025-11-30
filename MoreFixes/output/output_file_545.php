    public function testResetActionNoRole()
    {
        $user = $this->createUser(4);
        $this->em->persist($user);
        $this->em->flush();

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/security/reset/email', [
            'user' => $user->getUsername(),
        ]);
        $this->assertHttpStatusCode(400, $client->getResponse());

        $response = \json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(1009, $response['code']);
    }
