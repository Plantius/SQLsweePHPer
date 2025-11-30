	public function testAfterExceptionFail() {
		// BoardService $boardService, PermissionService $permissionService, $userId
		$boardController = new BoardController('deck', $this->createMock(IRequest::class), $this->createMock(BoardService::class), $this->createMock(PermissionService::class), 'admin');
		$result = $this->exceptionMiddleware->afterException($boardController, 'bar', new \Exception('failed hard'));
		$this->assertEquals('failed hard', $result->getData()['message']);
		$this->assertEquals(500, $result->getData()['status']);
	}
