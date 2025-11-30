    private function _getMyUniqueHash()
    {
        $unique = $this->getServer('REMOTE_ADDR');
        $unique .= $this->getServer('HTTP_USER_AGENT');
        $unique .= $this->getServer('HTTP_ACCEPT_LANGUAGE');
        $unique .= C_USER;
        return md5($unique);
    }
