    public function checkConfig($file)
    {
        $config_hash_after = md5(serialize($GLOBALS['sugar_config']));
        if ($config_hash_after != $this->config_hash) {
            $this->issues['file'][$file] = array(translate('ML_CONFIG_OVERRIDE'));
            return $this->issues;
        }
        return false;
    }
