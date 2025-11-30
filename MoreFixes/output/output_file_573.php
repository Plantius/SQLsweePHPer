    public function userFieldIsNotSetForAnonymousSessions()
    {
        $uniqueSessionId = StringUtility::getUniqueId('test');
        $_COOKIE['fe_typo_user'] = $uniqueSessionId;

        // This setup fakes the "getAuthInfoArray() db call
        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $connectionPoolProphecy = $this->prophesize(ConnectionPool::class);
        $connectionPoolProphecy->getQueryBuilderForTable('fe_users')->willReturn($queryBuilderProphecy->reveal());
        GeneralUtility::addInstance(ConnectionPool::class, $connectionPoolProphecy->reveal());
        $expressionBuilderProphecy = $this->prophesize(ExpressionBuilder::class);
        $queryBuilderProphecy->expr()->willReturn($expressionBuilderProphecy->reveal());
        $compositeExpressionProphecy = $this->prophesize(CompositeExpression::class);
        $expressionBuilderProphecy->andX(Argument::cetera())->willReturn($compositeExpressionProphecy->reveal());
        $expressionBuilderProphecy->in(Argument::cetera())->willReturn('');

        // Main session backend setup
        $sessionBackendProphecy = $this->prophesize(SessionBackendInterface::class);
        $sessionRecord = [
            'ses_id' => $uniqueSessionId,
            'ses_data' => serialize(['foo' => 'bar']),
            'ses_anonymous' => true,
            'ses_iplock' => '[DISABLED]',
        ];
        $sessionBackendProphecy->get($uniqueSessionId)->shouldBeCalled()->willReturn($sessionRecord);
        $sessionManagerProphecy = $this->prophesize(SessionManager::class);
        GeneralUtility::setSingletonInstance(SessionManager::class, $sessionManagerProphecy->reveal());
        $sessionManagerProphecy->getSessionBackend('FE')->willReturn($sessionBackendProphecy->reveal());

        $subject = new FrontendUserAuthentication();
        $subject->setLogger(new NullLogger());
        $subject->gc_probability = -1;
        $subject->start();

        self::assertArrayNotHasKey('uid', $subject->user);
        self::assertEquals('bar', $subject->getSessionData('foo'));
        self::assertEquals($uniqueSessionId, $subject->id);
    }
