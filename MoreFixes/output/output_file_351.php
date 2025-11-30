    public function clearAllSessionsByUserIdDestroyAllSessionsForBackend()
    {
        $backendSessionBackend = $this->subject->getSessionBackend('BE');
        $allActiveSessions = $backendSessionBackend->getAll();
        self::assertCount(3, $allActiveSessions);
        $this->subject->invalidateAllSessionsByUserId($backendSessionBackend, 1);
        $allActiveSessions = $backendSessionBackend->getAll();
        self::assertCount(1, $allActiveSessions);
        self::assertSame('randomSessionId3', $allActiveSessions[0]['ses_id']);
        self::assertSame(2, (int)$allActiveSessions[0]['ses_userid']);
    }
