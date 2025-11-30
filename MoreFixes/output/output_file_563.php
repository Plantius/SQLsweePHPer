    public function testUrlEncodingForKeyWillReturnValidArray()
    {
        $this->request = new ServerRequest(
            'GET',
            'http://localhost',
            array('Cookie' => 'react%3Bphp=is%20great')
        );

        $cookies = $this->request->getCookieParams();
        $this->assertEquals(array('react;php' => 'is great'), $cookies);
    }
