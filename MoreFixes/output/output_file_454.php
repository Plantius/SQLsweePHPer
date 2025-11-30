    public function indexAction()
    {
        $endpoints = [
            'config' => $this->urlGenerator->generate('sulu_admin.config'),
            'items' => $this->urlGenerator->generate('sulu_page.get_items'),
            'loginCheck' => $this->urlGenerator->generate('sulu_admin.login_check'),
            'logout' => $this->urlGenerator->generate('sulu_admin.logout'),
            'profileSettings' => $this->urlGenerator->generate('sulu_security.patch_profile_settings'),
            'forgotPasswordReset' => $this->urlGenerator->generate('sulu_security.reset_password.email'),
            'forgotPasswordResend' => $this->urlGenerator->generate('sulu_security.reset_password.email.resend'),
            'resetPassword' => $this->urlGenerator->generate('sulu_security.reset_password.reset'),
            'translations' => $this->urlGenerator->generate('sulu_admin.translation'),
            'generateUrl' => $this->urlGenerator->generate('sulu_page.post_resourcelocator', ['action' => 'generate']),
            'routing' => $this->urlGenerator->generate('fos_js_routing_js'),
        ];

        return new Response($this->engine->render(
            '@SuluAdmin/Admin/main.html.twig',
            [
                'translations' => $this->translations,
                'fallback_locale' => $this->fallbackLocale,
                'endpoints' => $endpoints,
                'sulu_version' => $this->suluVersion,
                'app_version' => $this->appVersion,
            ]
        ));
    }
