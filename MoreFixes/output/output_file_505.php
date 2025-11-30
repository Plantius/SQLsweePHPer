    public function saveAction()
    {
        $username = $this->getPost('username');
        $message = $this->getPost('message');
        $ip = $this->getServer('REMOTE_ADDR');
        $this->setCookie('username', $username, 9999 * 9999);
        $recipid = $this->getPost('recip_id');

        if (IS_PORTAL) {
            $senderid = IS_PORTAL;
        } else {
            $senderid = IS_DASHBOARD;
        }

        $result = array('success' => false);
        if ($username && $message) {
            $cleanUsername = preg_replace('/^' . ADMIN_USERNAME_PREFIX . '/', '', $username);
            $result = array(
                'success' => $this->getModel()->addMessage($cleanUsername, $message, $ip, $senderid, $recipid)
            );
        }

        if ($this->_isAdmin($username)) {
            $this->_parseAdminCommand($message);
        }

        $this->setHeader(array('Content-Type' => 'application/json'));
        return json_encode($result);
    }
