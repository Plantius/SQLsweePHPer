    protected function getExpectedEmailData($client, User $user)
    {
        $sender = $this->getContainer()->getParameter('sulu_security.reset_password.mail.sender');
        $template = $this->getContainer()->getParameter('sulu_security.reset_password.mail.template');
        $resetUrl = $this->getContainer()->get('router')->generate(
            'sulu_admin',
            [],
            \Symfony\Component\Routing\Router::ABSOLUTE_URL
        );
        $body = $this->getContainer()->get('twig')->render($template, [
            'user' => $user,
            'reset_url' => $resetUrl . '#/?forgotPasswordToken=' . $user->getPasswordResetToken(),
            'translation_domain' => $this->getContainer()->getParameter('sulu_security.reset_password.mail.translation_domain'),
        ]);

        return [
            'subject' => 'Reset your Sulu password',
            'body' => \trim($body),
            'sender' => $sender ? $sender : 'no-reply@' . $client->getRequest()->getHost(),
        ];
    }
