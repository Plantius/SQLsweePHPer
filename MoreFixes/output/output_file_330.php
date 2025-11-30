    protected function switchUser($switchUser)
    {
        $targetUser = BackendUtility::getRecord('be_users', $switchUser);
        if (is_array($targetUser) && $this->getBackendUserAuthentication()->isAdmin()) {
            // Set backend user listing module as starting module for switchback
            $this->getBackendUserAuthentication()->uc['startModuleOnFirstLogin'] = 'system_BeuserTxBeuser';
            $this->getBackendUserAuthentication()->uc['recentSwitchedToUsers'] = $this->generateListOfMostRecentSwitchedUsers($targetUser['uid']);
            $this->getBackendUserAuthentication()->writeUC();

            // User switch   written to log
            $this->getBackendUserAuthentication()->writelog(
                255,
                2,
                0,
                1,
                'User %s switched to user %s (be_users:%s)',
                [
                    $this->getBackendUserAuthentication()->user['username'],
                    $targetUser['username'],
                    $targetUser['uid'],
                ]
            );

            $sessionBackend = $this->getSessionBackend();
            $sessionBackend->update(
                $this->getBackendUserAuthentication()->getSessionId(),
                [
                    'ses_userid' => (int)$targetUser['uid'],
                    'ses_backuserid' => (int)$this->getBackendUserAuthentication()->user['uid']
                ]
            );

            $event = new SwitchUserEvent(
                $this->getBackendUserAuthentication()->getSessionId(),
                $targetUser,
                (array)$this->getBackendUserAuthentication()->user
            );
            $this->eventDispatcher->dispatch($event);

            $redirectUrl = 'index.php' . ($GLOBALS['TYPO3_CONF_VARS']['BE']['interfaces'] ? '' : '?commandLI=1');
            HttpUtility::redirect($redirectUrl);
        }
