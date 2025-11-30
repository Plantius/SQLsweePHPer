    public function __construct(
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        TokenGeneratorInterface $tokenGenerator,
        Environment $templating,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $dispatcher,
        \Swift_Mailer $mailer,
        EncoderFactoryInterface $encoderFactory,
        UserRepositoryInterface $userRepository,
        UrlGeneratorInterface $router,
        EntityManagerInterface $entityManager,
        string $suluSecuritySystem,
        string $sender,
        string $subject,
        string $translationDomain,
        string $mailTemplate,
        string $tokenSendLimit,
        string $adminMail
    ) {
        $this->validator = $validator;
        $this->translator = $translator;
        $this->tokenGenerator = $tokenGenerator;
        $this->twig = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
        $this->mailer = $mailer;
        $this->encoderFactory = $encoderFactory;
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->entityManager = $entityManager;

        $this->suluSecuritySystem = $suluSecuritySystem;
        $this->sender = $sender;
        $this->subject = $subject;
        $this->translationDomain = $translationDomain;
        $this->mailTemplate = $mailTemplate;
        $this->tokenSendLimit = $tokenSendLimit;
        $this->adminMail = $adminMail;
    }
