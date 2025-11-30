    public function getFlashCookieObject($name)
    {
        if (isset($_COOKIE[$name])) {
            $object = json_decode($_COOKIE[$name], false);
            setcookie($name, '', time() - 3600, '/');
            return $object;
        }

        return null;
    }
