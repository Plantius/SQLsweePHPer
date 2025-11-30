	public function __construct($appName, IRequest $request, IRootFolder $rootFolder, IManager $shareManager, IDirectEditingManager $directEditingManager, IURLGenerator $urlGenerator,	WorkspaceService $workspaceService, IEventDispatcher $eventDispatcher, $userId) {
		parent::__construct($appName, $request);
		$this->rootFolder = $rootFolder;
		$this->shareManager = $shareManager;
		$this->workspaceService = $workspaceService;
		$this->userId = $userId;
		$this->directEditingManager = $directEditingManager;
		$this->urlGenerator = $urlGenerator;
		$this->eventDispatcher = $eventDispatcher;
	}
