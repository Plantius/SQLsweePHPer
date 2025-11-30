    protected function getMessage($user)
    {
        $resetUrl = $this->router->generate(static::$resetRouteId, [], UrlGeneratorInterface::ABSOLUTE_URL);
        $template = $this->mailTemplate;
        $translationDomain = $this->translationDomain;

        if (!$this->twig->getLoader()->exists($template)) {
            throw new EmailTemplateException($template);
        }

        return \trim(
            $this->twig->render(
                $template,
                [
                    'user' => $user,
                    'reset_url' => $resetUrl . '#/?forgotPasswordToken=' . $user->getPasswordResetToken(),
                    'translation_domain' => $translationDomain,
                ]
            )
        );
    }
