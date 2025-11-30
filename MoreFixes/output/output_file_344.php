    public function canLoadExistingAuthenticatedSession()
    {
        $uniqueSessionId = StringUtility::getUniqueId('test');
        $_COOKIE['fe_typo_user'] = $uniqueSessionId;
        $currentTime = $GLOBALS['EXEC_TIME'];

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
        $sessionManagerProphecy = $this->prophesize(SessionManager::class);
        GeneralUtility::setSingletonInstance(SessionManager::class, $sessionManagerProphecy->reveal());
        $sessionManagerProphecy->getSessionBackend('FE')->willReturn($sessionBackendProphecy->reveal());

        // a valid session is returned
        $sessionBackendProphecy->get($uniqueSessionId)->shouldBeCalled()->willReturn(
            [
                'ses_id' => $uniqueSessionId,
                'ses_userid' => 1,
                'ses_iplock' => '[DISABLED]',
                'ses_tstamp' => $currentTime,
                'ses_data' => serialize(['foo' => 'bar']),
                'ses_permanent' => 0,
                'ses_anonymous' => 0 // sic!
            ]
        );

        // Mock call to fe_users table and let it return a valid user row
        $connectionPoolFeUserProphecy = $this->prophesize(ConnectionPool::class);
        GeneralUtility::addInstance(ConnectionPool::class, $connectionPoolFeUserProphecy->reveal());
        $queryBuilderFeUserProphecy = $this->prophesize(QueryBuilder::class);
        $queryBuilderFeUserProphecyRevelation = $queryBuilderFeUserProphecy->reveal();
        $connectionPoolFeUserProphecy->getQueryBuilderForTable('fe_users')->willReturn($queryBuilderFeUserProphecyRevelation);
        $queryBuilderFeUserProphecy->select('*')->willReturn($queryBuilderFeUserProphecyRevelation);
        $queryBuilderFeUserProphecy->setRestrictions(Argument::cetera())->shouldBeCalled();
        $queryBuilderFeUserProphecy->from('fe_users')->shouldBeCalled()->willReturn($queryBuilderFeUserProphecyRevelation);
        $expressionBuilderFeUserProphecy = $this->prophesize(ExpressionBuilder::class);
        $queryBuilderFeUserProphecy->expr()->willReturn($expressionBuilderFeUserProphecy->reveal());
        $queryBuilderFeUserProphecy->createNamedParameter(Argument::cetera())->willReturnArgument(0);
        $expressionBuilderFeUserProphecy->eq(Argument::cetera())->willReturn('1=1');
        $queryBuilderFeUserProphecy->where(Argument::cetera())->shouldBeCalled()->willReturn($queryBuilderFeUserProphecyRevelation);
        $statementFeUserProphecy = $this->prophesize(Statement::class);
        $queryBuilderFeUserProphecy->execute()->shouldBeCalled()->willReturn($statementFeUserProphecy->reveal());
        $statementFeUserProphecy->fetch()->willReturn(
            [
                'uid' => 1,
                'username' => 'existingUserName',
                'password' => 'abc',
                'deleted' => 0,
                'disabled' => 0
            ]
        );

        $subject = new FrontendUserAuthentication();
        $subject->setLogger(new NullLogger());
        $subject->gc_probability = -1;
        $subject->start();

        self::assertNotNull($subject->user);
        self::assertEquals('existingUserName', $subject->user['username']);
    }
