    public function invalidateAllSessionsByUserId(SessionBackendInterface $backend, int $userId, AbstractUserAuthentication $userAuthentication = null)
    {
        $sessionToRenew = '';
        // Prevent destroying the session of the current user session, but renew session id
        if ($userAuthentication !== null && (int)$userAuthentication->user['uid'] === $userId) {
            $sessionToRenew = $userAuthentication->getSessionId();
        }

        foreach ($backend->getAll() as $session) {
            if ($userAuthentication !== null && $session['ses_id'] === $sessionToRenew) {
                $userAuthentication->enforceNewSessionId();
                continue;
            }
            if ((int)$session['ses_userid'] === $userId) {
                $backend->remove($session['ses_id']);
            }
        }
    }
