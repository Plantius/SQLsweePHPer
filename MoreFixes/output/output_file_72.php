        foreach ($this->testSessionRecords as $testSessionRecord) {
            $frontendSessionBackend->set($testSessionRecord['ses_id'], $testSessionRecord);
        }
