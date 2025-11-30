    public function __construct(UserInterface $user)
    {
        parent::__construct(\sprintf('The user "%s" has no token!', $user->getUsername()), 1006);
        $this->user = $user;
    }
