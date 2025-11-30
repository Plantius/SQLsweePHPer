    public function invalidate()
    {
        $name = $this->getName();
        if (null !== $name) {
            $params = session_get_cookie_params();

            $cookie_options = array (
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite']
            );

            $this->removeCookie();

            setcookie(
                session_name(),
                '',
                $cookie_options
            );
        }

        if ($this->isSessionStarted()) {
            session_unset();
            session_destroy();
        }

        $this->started = false;

        return $this;
    }
