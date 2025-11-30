    public function setFlashCookieObject($name, $object, $time = 60)
    {
        setcookie($name, json_encode($object), time() + $time, '/');

        return $this;
    }
