    public function pingAction()
    {
        $ip = $this->getServer('REMOTE_ADDR');
        $hash = $this->_getMyUniqueHash();
        $user = $this->getRequest('username', 'No Username');
        if ($user == 'currentol') {
            $onlines = $this->getModel()->getOnline(false);
            $this->setHeader(array('Content-Type' => 'application/json'));
            return json_encode($onlines);
        }

        if (IS_PORTAL) {
            $userid = IS_PORTAL;
        } else {
            $userid = IS_DASHBOARD;
        }

        $this->getModel()->updateOnline($hash, $ip, $user, $userid);
        $this->getModel()->clearOffline();

        $onlines = $this->getModel()->getOnline();

        $this->setHeader(array('Content-Type' => 'application/json'));
        return json_encode($onlines);
    }
