    public function api() {
        if (empty($this->params['apikey'])) {
            $_REQUEST['apikey'] = true;  // set this to force an ajax reply
            $ar = new expAjaxReply(550, 'Permission Denied', 'You need an API key in order to access Exponent as a Service', null);
            $ar->send();  //FIXME this doesn't seem to work correctly in this scenario
        } else {
            $key = expUnserialize(base64_decode(urldecode($this->params['apikey'])));
            if (is_object($key) && $key->mod === "eaas") {
                preg_match('/[a-zA-Z0-9_@]*/', $key->src, $matches);
                $key->src = $matches[0];
                $cfg = new expConfig($key);
                $this->config = $cfg->config;
                $cfg = new expConfig($key);
                $this->config = $cfg->config;
            }
            if(empty($cfg->id)) {
                $ar = new expAjaxReply(550, 'Permission Denied', 'Incorrect API key or Exponent as a Service module configuration missing', null);
                $ar->send();
            } else {
                if (!empty($this->params['get'])) {
                    $this->handleRequest();
                } else {
                    $ar = new expAjaxReply(200, 'ok', 'Your API key is working, no data requested', null);
                    $ar->send();
                }
            }
        }
    }
