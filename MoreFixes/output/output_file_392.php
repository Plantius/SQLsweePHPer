    public function getCookiePathsDataProvider()
    {
        return [
            ['', '/'],
            ['/', '/'],
            ['/foo', '/'],
            ['/foo/bar', '/foo'],
            ['/foo/bar/', '/foo/bar'],
            ['foo', '/'],
            ['foo/bar', '/'],
            ['foo/bar/', '/'],
        ];
    }
