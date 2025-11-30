    public function setupHttpSocket($server = null, $timeout = false, $model = 'Server')
    {
        $params = ['compress' => true];
        if (!empty($server)) {
            if (!empty($server[$model]['cert_file'])) {
                $params['ssl_cafile'] = APP . "files" . DS . "certs" . DS . $server[$model]['id'] . '.pem';
            }
            if (!empty($server[$model]['client_cert_file'])) {
                $params['ssl_local_cert'] = APP . "files" . DS . "certs" . DS . $server[$model]['id'] . '_client.pem';
            }
            if (!empty($server[$model]['self_signed'])) {
                $params['ssl_allow_self_signed'] = true;
                $params['ssl_verify_peer_name'] = false;
                if (!isset($server[$model]['cert_file'])) {
                    $params['ssl_verify_peer'] = false;
                }
            }
            if (!empty($server[$model]['skip_proxy'])) {
                $params['skip_proxy'] = 1;
            }
            if (!empty($timeout)) {
                $params['timeout'] = $timeout;
            }
        }

        return $this->createHttpSocket($params);
    }
