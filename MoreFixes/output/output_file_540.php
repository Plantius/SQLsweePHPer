    public function testInteractiveString()
    {
        $url = 'http://svn.example.org';

        $svn = new Svn($url, new NullIO(), new Config());
        $reflMethod = new \ReflectionMethod('Composer\\Util\\Svn', 'getCommand');
        $reflMethod->setAccessible(true);

        $this->assertEquals(
            $this->getCmd("svn ls --non-interactive  'http://svn.example.org'"),
            $reflMethod->invokeArgs($svn, array('svn ls', $url))
        );
    }
