    public function verifyChallenge(string $code)
    {
        try {
            $session = $this->kirby->session();

            // first check if we have an active challenge at all
            $email     = $session->get('kirby.challenge.email');
            $challenge = $session->get('kirby.challenge.type');
            if (is_string($email) !== true || is_string($challenge) !== true) {
                throw new InvalidArgumentException('No authentication challenge is active');
            }

            $user = $this->kirby->users()->find($email);
            if ($user === null) {
                throw new NotFoundException([
                    'key'  => 'user.notFound',
                    'data' => [
                        'name' => $email
                    ]
                ]);
            }

            // rate-limiting
            $this->checkRateLimit($email);

            // time-limiting
            $timeout = $session->get('kirby.challenge.timeout');
            if ($timeout !== null && time() > $timeout) {
                throw new PermissionException('Authentication challenge timeout');
            }

            if (
                isset(static::$challenges[$challenge]) === true &&
                class_exists(static::$challenges[$challenge]) === true &&
                is_subclass_of(static::$challenges[$challenge], 'Kirby\Cms\Auth\Challenge') === true
            ) {
                $class = static::$challenges[$challenge];
                if ($class::verify($user, $code) === true) {
                    $this->logout();
                    $user->loginPasswordless();

                    // clear the status cache
                    $this->status = null;

                    return $user;
                } else {
                    throw new PermissionException(['key' => 'access.code']);
                }
            }

            throw new LogicException('Invalid authentication challenge: ' . $challenge);
        } catch (Throwable $e) {
            if (
                empty($email) === false &&
                ($e->getDetails()['reason'] ?? null) !== 'rate-limited'
            ) {
                $this->track($email);
            }

            // sleep for a random amount of milliseconds
            // to make automated attacks harder and to
            // avoid leaking whether the user exists
            usleep(random_int(10000, 2000000));

            $fallback = new PermissionException(['key' => 'access.code']);

            // keep throwing the original error in debug mode,
            // otherwise hide it to avoid leaking security-relevant information
            $this->fail($e, $fallback);
        }
    }
