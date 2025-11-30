    private function sendTokenEmail(UserInterface $user, $from, $to)
    {
        if (null === $user->getPasswordResetToken()) {
            throw new NoTokenFoundException($user);
        }

        $maxNumberEmails = $this->tokenSendLimit;

        if ($user->getPasswordResetTokenEmailsSent() === \intval($maxNumberEmails)) {
            throw new TokenEmailsLimitReachedException($maxNumberEmails, $user);
        }
        $mailer = $this->mailer;
        $message = $mailer->createMessage()
            ->setSubject($this->getSubject())
            ->setFrom($from)
            ->setTo($to)
            ->setBody($this->getMessage($user));

        $mailer->send($message);
        $user->setPasswordResetTokenEmailsSent($user->getPasswordResetTokenEmailsSent() + 1);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
