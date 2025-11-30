    public function clearAllSessionsByUserIdDestroyAllSessionsForFrontend()
    {
        $frontendSessionBackend = $this->subject->getSessionBackend('FE');
        $allActiveSessions = $frontendSessionBackend->getAll();
        self::assertCount(3, $allActiveSessions);
        $this->subject->invalidateAllSessionsByUserId($frontendSessionBackend, 1);
        $allActiveSessions = $frontendSessionBackend->getAll();
        self::assertCount(1, $allActiveSessions);
        self::assertSame('randomSessionId3', $allActiveSessions[0]['ses_id']);
        self::assertSame(2, (int)$allActiveSessions[0]['ses_userid']);
    }
