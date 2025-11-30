    private function generateTokenForUser(UserInterface $user)
    {
        // if a token was already requested within the request interval time frame
        if (null !== $user->getPasswordResetToken()
            && $this->dateIsInRequestFrame($user->getPasswordResetTokenExpiresAt())) {
            throw new TokenAlreadyRequestedException(self::getRequestInterval());
        }

        $user->setPasswordResetToken($this->getToken());
        $expireDateTime = (new \DateTime())->add(self::getResetInterval());
        $user->setPasswordResetTokenExpiresAt($expireDateTime);
        $user->setPasswordResetTokenEmailsSent(0);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
