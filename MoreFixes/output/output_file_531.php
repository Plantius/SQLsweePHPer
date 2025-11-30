    public function testCookiePathWithEmptySetCookiePath($uriPath, $cookiePath)
    {
        $response = (new Response(200))
            ->withAddedHeader(
                'Set-Cookie',
                "foo=bar; expires={$this->futureExpirationDate()}; domain=www.example.com; path=;"
            )
            ->withAddedHeader(
                'Set-Cookie',
                "bar=foo; expires={$this->futureExpirationDate()}; domain=www.example.com; path=foobar;"
            )
        ;
        $request = (new Request('GET', $uriPath))->withHeader('Host', 'www.example.com');
        $this->jar->extractCookies($request, $response);

        self::assertSame($cookiePath, $this->jar->toArray()[0]['Path']);
        self::assertSame($cookiePath, $this->jar->toArray()[1]['Path']);
    }
