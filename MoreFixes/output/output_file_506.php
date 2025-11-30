    public function saveInboundEmailSystemSettings($type, $macro)
    {
        global $sugar_config;

        // inbound_email_case_subject_macro
        $var = "inbound_email_" . strtolower($type) . "_subject_macro";
        $sugar_config[$var] = $macro;

        ksort($sugar_config);

        $sugar_config_string = "<?php\n" .
            '// created: ' . date('Y-m-d H:i:s') . "\n" .
            '$sugar_config = ' .
            var_export($sugar_config, true) .
            ";\n?>\n";

        write_array_to_file("sugar_config", $sugar_config, "config.php");
    }

    /**
     * returns the HTML for InboundEmail system settings
     * @return string HTML
     */
    public function getSystemSettingsForm()
    {
        global $sugar_config;
        global $mod_strings;
        global $app_strings;
        global $app_list_strings;

        $c = BeanFactory::newBean('Cases');
        $template = new Sugar_Smarty();
        $template->assign('APP', $app_strings);
        $template->assign('MOD', $mod_strings);
        $template->assign('MACRO', $c->getEmailSubjectMacro());

        return $template->fetch('modules/InboundEmail/tpls/systemSettingsForm.tpl');
    }

    /**
     * For mailboxes of type "Support" parse for '[CASE:%1]'
     *
     * @param string $emailName The subject line of the email
     * @param aCase $aCase A Case object
     *
     * @return string|boolean   Case ID or FALSE if not found
     */
    public function getCaseIdFromCaseNumber($emailName, $aCase)
    {
        //$emailSubjectMacro
        $exMacro = explode('%1', $aCase->getEmailSubjectMacro());
        $open = $exMacro[0];
        $close = $exMacro[1];

        if ($sub = stristr($emailName, $open)) {
            // eliminate everything up to the beginning of the macro and return the rest
            // $sub is [CASE:XX] xxxxxxxxxxxxxxxxxxxxxx
            $sub2 = str_replace($open, '', $sub);
            // $sub2 is XX] xxxxxxxxxxxxxx
            $sub3 = substr($sub2, 0, strpos($sub2, $close));

            // case number is supposed to be numeric
            if (ctype_digit($sub3)) {
                // filter out deleted records in order to create a new case
                // if email is related to deleted one (bug #49840)
                $query = 'SELECT id FROM cases WHERE case_number = '
                    . $this->db->quoted($sub3)
                    . ' and deleted = 0';
                $results = $this->db->query($query, true);
                $row = $this->db->fetchByAssoc($results);
                if (!empty($row['id'])) {
                    return $row['id'];
                }
            }
        }

        return false;
    }

    /**
     * @param $option_name
     * @param null $default_value
     * @param null $stored_options
     * @return mixed
     */
    public function get_stored_options($option_name, $default_value = null, $stored_options = null)
    {
        if (empty($stored_options)) {
            $stored_options = $this->stored_options;
        }

        return self::get_stored_options_static($option_name, $default_value, $stored_options);
    }

    /**
     * Returns the stored options property un-encoded and un serialised.
     * @return array
     */
    public function getStoredOptions()
    {
        return sugar_unserialize(base64_decode($this->stored_options));
    }

    /**
     * @param array $options
     */
    public function setStoredOptions($options)
    {
        $this->stored_options = base64_encode(serialize($options));
    }


    /**
     * @param $option_name
     * @param null $default_value
     * @param null $stored_options
     * @return mixed
     */
    public static function get_stored_options_static($option_name, $default_value = null, $stored_options = null)
    {
        if (!empty($stored_options)) {
            $storedOptions = sugar_unserialize(base64_decode($stored_options));
            if (isset($storedOptions[$option_name])) {
                $default_value = $storedOptions[$option_name];
            }
        }

        return $default_value;
    }


    /**
     * This function returns a contact or user ID if a matching email is found
     * @param    $email        the email address to match
     * @param    $table        which table to query
     */
    public function getRelatedId($email, $module)
    {
        $email = trim(strtoupper($email));
        if (strpos($email, ',') !== false) {
            $emailsArray = explode(',', $email);
            $emailAddressString = "";
            foreach ($emailsArray as $emailAddress) {
                if (!empty($emailAddressString)) {
                    $emailAddressString .= ",";
                }
                $emailAddressString .= $this->db->quoted(trim($emailAddress));
            } // foreach
            $email = $emailAddressString;
        } else {
            $email = $this->db->quoted($email);
        } // else
        $module = $this->db->quoted(ucfirst($module));

        $q = "SELECT bean_id FROM email_addr_bean_rel eabr
                JOIN email_addresses ea ON (eabr.email_address_id = ea.id)
                WHERE bean_module = $module AND ea.email_address_caps in ( {$email} ) AND eabr.deleted=0";

        $r = $this->db->query($q, true);

        $retArr = array();
        while ($a = $this->db->fetchByAssoc($r)) {
            $retArr[] = $a['bean_id'];
        }
        if (count($retArr) > 0) {
            return $retArr;
        }

        return false;
    }

    /**
     * finds emails tagged "//UNSEEN" on mailserver and "SINCE: [date]" if that
     * option is set
     *
     * @return array Array of messageNumbers (mail server's internal keys)
     */
    public function getNewMessageIds()
    {
        $storedOptions = sugar_unserialize(base64_decode($this->stored_options));

        //TODO figure out if the since date is UDT
        if (!is_bool($storedOptions) && $storedOptions['only_since']) {// POP3 does not support Unseen flags
            if (!isset($storedOptions['only_since_last']) && !empty($storedOptions['only_since_last'])) {
                $q = "SELECT last_run FROM schedulers WHERE job = '{$this->job_name}'";
                $r = $this->db->query($q, true);
                $a = $this->db->fetchByAssoc($r);

                $date = date('r', strtotime($a['last_run']));
                LoggerManager::getLogger()->debug("-----> getNewMessageIds() executed query: {$q}");
            } else {
                $date = $storedOptions['only_since_last'];
            }
            $ret = $this->getImap()->search('SINCE "' . $date . '" UNDELETED UNSEEN');
            $check = $this->getImap()->check();
            $storedOptions['only_since_last'] = $check->Date;
            $this->stored_options = base64_encode(serialize($storedOptions));
            $this->save();
        } else {
            if (!$this->getImap()->isValidStream($this->conn)) {
                LoggerManager::getLogger()->fatal('Inbound Email Connection is not valid resource for getting New Message Ids.');

                return false;
            }
            $ret = $this->getImap()->search('UNDELETED UNSEEN');
        }

        $nmessages = is_countable($ret)? count($ret) : 0;
        LoggerManager::getLogger()->debug('-----> getNewMessageIds() got ' . $nmessages . ' new Messages');

        return $ret;
    }

    /**
     * Constructs the resource connection string that IMAP needs
     * @param string $service Service string, will generate if not passed
     * @return string
     */
    public function getConnectString($service = '', $mbox = '', $includeMbox = true)
    {
        $service = empty($service) ? $this->getServiceString() : $service;
        $mbox = empty($mbox) ? $this->mailbox : $mbox;

        $protocol = $this->protocol ?? 'imap';
        $port = $this->port ?? '143';

        $connectString = '{' . $this->server_url . ':' . $port . '/service=' . $protocol . $service . '}';

        if (!empty($this->connection_string)){
            $connectString = '{' . $this->connection_string . '}';
        }

        $connectString .= ($includeMbox) ? $mbox : "";

        return $connectString;
    }

    /**
     *
     */
    public function disconnectMailserver()
    {
        if ($this->getImap()->isValidStream($this->conn)) {
            $this->getImap()->close();
        }
    }

    /**
     * Connects to mailserver.  If an existing IMAP resource is available, it
     * will attempt to reuse the connection, updating the mailbox path.
     *
     * @param bool test Flag to test connection
     * @param bool force Force reconnect
     * @return string "true" on success, "false" or $errorMessage on failure
     */
    public function connectMailserver($test = false, $force = false)
    {
        global $mod_strings;

        $msg = '';

        if (!$this->getImap()->isAvailable()) {
            $GLOBALS['log']->debug('------------------------- IMAP libraries NOT available!!!! die()ing thread.----');

            return $mod_strings['LBL_WARN_NO_IMAP'];
        }


        $this->getImap()->getErrors(); // clearing error stack
        //error_reporting(0); // turn off notices from IMAP

        // tls::ca::ssl::protocol::novalidate-cert::notls

        if (!isset($_REQUEST['ssl'])) {
            LoggerManager::getLogger()->warn('Request ssl value not found.');
            $requestSsl = null;
        } else {
            $requestSsl = $_REQUEST['ssl'];
        }

        $useSsl = ($requestSsl == 'true') ? true : false; // TODO: validate the ssl request variable value (for e.g its posibble to give a numeric 1 as true)
        if ($test) {
            $this->getImap()->setTimeout(1, 5); // 60 secs is the default
            $this->getImap()->setTimeout(2, 5);
            $this->getImap()->setTimeout(3, 5);

            $opts = $this->findOptimumSettings($useSsl);
            if (!empty($opts) && isset($opts['good']) && empty($opts['good'])) {
                $ret = array_pop($opts['err']); // TODO: lost error info?

                return $ret;
            }
            if (!empty($opts) && is_array($opts['service'])) {
                $service = $opts['service'];
            } else {
                $service = null;
            }
            $service = str_replace('foo', '', $service); // foo there to support no-item explodes
        } else {
            $service = $this->getServiceString();
        }

        if (!isset($_REQUEST['folder'])) {
            LoggerManager::getLogger()->warn('Requested folder is not defined');
            $requestFolder = null;
        } else {
            $requestFolder = $_REQUEST['folder'];
        }

        if ($requestFolder === 'sent') {
            $this->mailbox = $this->get_stored_options('sentFolder');
        }

        if ($requestFolder === 'inbound') {
            if (!empty($_REQUEST['folder_name'])) {
                $this->mailbox = $_REQUEST['folder_name'];
            } elseif ($this->mailboxarray && (is_countable($this->mailboxarray) ? count($this->mailboxarray) : 0)) {
                $this->mailbox = $this->mailboxarray[0];
            } else {
                $this->mailbox = 'INBOX';
            }
        }

        $connectString = $this->getConnectString($service, $this->mailbox);

        /*
         * Try to recycle the current connection to reduce response times
         */
        if ($this->getImap()->isValidStream($this->getImap()->getConnection())) {
            if ($force) {
                // force disconnect
                $this->getImap()->close();
            }

            if ($this->getImap()->ping()) {
                // we have a live connection
                $this->getImap()->reopen($connectString, CL_EXPUNGE);
            }
        }

        // final test
        if (!$this->getImap()->isValidStream($this->getImap()->getConnection()) && !$test) {

            $imapUser = $this->email_user;
            [$imapPassword, $imapOAuthConnectionOptions] = $this->getOAuthCredentials($this->email_password, CL_EXPUNGE);

            $this->conn = $this->getImapConnection(
                $connectString,
                $imapUser,
                $imapPassword,
                $imapOAuthConnectionOptions
            );
        }

        if ($test) {
            if ($opts === false && !$this->getImap()->isValidStream($this->getImap()->getConnection())) {

                $imapUser = $this->email_user;
                [$imapPassword, $imapOAuthConnectionOptions] = $this->getOAuthCredentials($this->email_password, CL_EXPUNGE);

                $this->conn = $this->getImapConnection(
                    $connectString,
                    $imapUser,
                    $imapPassword,
                    $imapOAuthConnectionOptions
                );
            }
            $errors = '';
            $alerts = '';
            $successful = false;
            if (($errors = $this->getImap()->getLastError()) || ($alerts = $this->getImap()->getAlerts()) || !$this->conn) {
                if ($errors === 'Mailbox is empty') { // false positive
                    $successful = true;
                } else {
                    if (!isset($msg)) {
                        $msg = $errors;
                    } else {
                        $msg .= $errors;
                    }
                    $msg .= '<p>' . $alerts . '<p>';
                    $msg .= '<p>' . $mod_strings['ERR_TEST_MAILBOX'];
                }
            } else {
                $successful = true;
            }

            if ($successful) {
                if ($this->protocol == 'imap') {
                    $msg .= $mod_strings['LBL_TEST_SUCCESSFUL'];
                } else {
                    $msg .= $mod_strings['LBL_POP3_SUCCESS'];
                }
            }

            $this->getImap()->getErrors(); // collapse error stack

            if ($this->getImap()->isValidStream($this->getImap()->getConnection())) {
                $this->getImap()->close();
            } else {
                LoggerManager::getLogger()->warn('Connection is not a valid resource.');
            }


            return $msg;
        } elseif (!$this->getImap()->isValidStream($this->getImap()->getConnection())) {
            $GLOBALS['log']->fatal('Couldn\'t connect to mail server id: ' . $this->id);

            return "false";
        }
        $GLOBALS['log']->info('Connected to mail server id: ' . $this->id);

        return "true";
    }

    /**
     * @return mixed|string|void
     */
    public function checkImap()
    {
        global $app_strings, $mod_strings;

        if (!$this->getImap()->isAvailable()) {
            $template = new Sugar_Smarty();
            $template->assign('APP', $app_strings);
            $template->assign('MOD', $mod_strings);
            $output = $template->fetch('modules/InboundEmail/tpls/checkImap.tpl');
            echo $output;

            return $output;
        }
    }

    /**
     * Attempt to create an IMAP connection using passed in parameters
     * return either the connection resource or false if unable to connect
     *
     * @param string $mailbox Mailbox to be used to create imap connection
     * @param string $username The user name
     * @param string $password The password associated with the username
     * @param integer $options Bitmask for options parameter to the imap_open function
     *
     * @return resource|boolean  Connection resource on success, FALSE on failure
     */
    protected function getImapConnection($mailbox, $username, $password, $options = 0)
    {
        $connection = null;
        $authenticators = ['', 'GSSAPI', 'NTLM'];

        $isOAuth = $this->isOAuth();
        if ($isOAuth === true) {
            $token = $this->getOAuthToken($this->external_oauth_connection_id ?? '');

            if ($token === null) {
                return false;
            }

            $password = $token;
        }

        while (!$connection && ($authenticator = array_shift($authenticators)) !== null) {
            if ($authenticator) {
                $params = [
                    'DISABLE_AUTHENTICATOR' => $authenticator,
                ];
            } else {
                $params = [];
            }

            $connection = $this->getImap()->open($mailbox, $username, $password, $options, 0, $params);

            if (!$connection){
                break;
            }

        }

        return $connection;
    }

    /**
     * retrieves an array of I-E beans based on the group_id
     * @param string $groupId GUID of the group user or Individual
     * @return    array    $beans        array of beans
     * @return    boolean false if none returned
     */
    public function retrieveByGroupId($groupId)
    {
        $q = '
          SELECT id FROM inbound_email
          WHERE
            group_id = \'' . $groupId . '\' AND
            deleted = 0 AND
            status = \'Active\'';
        $r = $this->db->query($q, true);

        $beans = array();
        while ($a = $this->db->fetchByAssoc($r)) {
            $ie = BeanFactory::newBean('InboundEmail');
            $ie->retrieve($a['id']);
            $beans[$a['id']] = $ie;
        }

        return $beans;
    }

    /**
     * Retrieves the current count of personal accounts for the user specified.
     *
     * @param unknown_type $user
     */
    public function getUserPersonalAccountCount($user = null)
    {
        if ($user == null) {
            $user = $GLOBALS['current_user'];
        }

        $query = "SELECT count(*) as c FROM inbound_email WHERE deleted=0 AND is_personal='1' AND (group_id='{$user->id}' OR created_by='{$user->id}') AND status='Active'";

        $rs = $this->db->query($query);
        $row = $this->db->fetchByAssoc($rs);

        return $row['c'];
    }

    /**
     * retrieves an array of I-E beans based on the group folder id
     * @param string $groupFolderId GUID of the group folder
     * @return    array    $beans        array of beans
     * @return    boolean false if none returned
     */
    public function retrieveByGroupFolderId($groupFolderId)
    {
        $q = 'SELECT id FROM inbound_email WHERE groupfolder_id = \'' . $groupFolderId . '\' AND deleted = 0 ';
        $r = $this->db->query($q, true);

        $beans = array();
        while ($a = $this->db->fetchByAssoc($r)) {
            $ie = BeanFactory::newBean('InboundEmail');
            $ie->retrieve($a['id']);
            $beans[] = $ie;
        }

        return $beans;
    }

    /**
     * Retrieves an array of I-E beans that the user has team access to
     *
     * @param string $id user id
     * @param bool $includePersonal
     * @return array
     */
    public function retrieveAllByGroupId($id, $includePersonal = true)
    {
        $beans = ($includePersonal) ? $this->retrieveByGroupId($id) : array();
        $q = "
          SELECT inbound_email.id FROM inbound_email
          WHERE
            is_personal = 0 AND
            -- (groupfolder_id is null OR groupfolder_id = '') AND
            mailbox_type not like 'bounce' AND
            inbound_email.deleted = 0 AND
            status = 'Active' ";
        $r = $this->db->query($q, true);

        while ($a = $this->db->fetchByAssoc($r)) {
            $found = false;
            foreach ($beans as $bean) {
                if ($bean->id == $a['id']) {
                    $found = true;
                }
            }

            if (!$found) {
                $ie = BeanFactory::newBean('InboundEmail');
                $ie->retrieve($a['id']);
                $beans[$a['id']] = $ie;
            }
        }

        return $beans;
    }

    /**
     * Retrieves an array of I-E beans that the user has team access to including group
     *
     * @param string $id
     * @param bool $includePersonal
     * @return InboundEmail[]
     */
    public function retrieveAllByGroupIdWithGroupAccounts($id, $includePersonal = true)
    {
        $beans = ($includePersonal) ? $this->retrieveByGroupId($id) : array();

        $q = "
          SELECT DISTINCT inbound_email.id
          FROM inbound_email
          WHERE
            is_personal = 0 AND
            mailbox_type not like 'bounce' AND
            status = 'Active' AND
            inbound_email.deleted = 0 ";
        $r = $this->db->query($q, true);

        while ($a = $this->db->fetchByAssoc($r)) {
            $found = false;
            foreach ($beans as $bean) {
                if ($bean->id == $a['id']) {
                    $found = true;
                }
            }

            if (!$found) {
                $ie = BeanFactory::newBean('InboundEmail');
                $ie->retrieve($a['id']);
                $beans[$a['id']] = $ie;
            }
        }

        return $beans;
    }


    /**
     * returns the bean name - overrides SugarBean's
     */
    public function get_summary_text()
    {
        return $this->name;
    }

    /**
     * Override's SugarBean's
     */
    public function create_export_query($order_by, $where, $show_deleted = 0)
    {
        return $this->create_new_list_query($order_by, $where, array(), array(), $show_deleted);
    }

    /**
     * @return array
     */
    public function getUserInboundAccounts(): array {
        global $current_user, $db;

        $where = '';
        if (is_admin($current_user)) {
            $currentUserId = $db->quote($current_user->id);
            $tableName = $db->quote($this->table_name);
            $where = "(($tableName.is_personal IS NULL) OR ($tableName.is_personal = 0 ) OR ($tableName.is_personal = 1 AND $tableName.created_by = '$currentUserId'))";
        }

        return $this->get_list('', $where)['list'] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function create_new_list_query(
        $order_by,
        $where,
        $filter = array(),
        $params = array(),
        $show_deleted = 0,
        $join_type = '',
        $return_array = false,
        $parentbean = null,
        $singleSelect = false,
        $ifListForExport = false
    ) {
        global $current_user, $db;

        $ret_array = parent::create_new_list_query(
            $order_by,
            $where,
            $filter,
            $params ,
            $show_deleted,
            $join_type,
            true,
            $parentbean,
            $singleSelect,
            $ifListForExport
        );

        if(is_admin($current_user)) {
            if ($return_array) {
                return $ret_array;
            }

            return $ret_array['select'] . $ret_array['from'] . $ret_array['where'] . $ret_array['order_by'];
        }

        if (is_array($ret_array) && !empty($ret_array['where'])){
            $tableName = $db->quote($this->table_name);
            $currentUserId = $db->quote($current_user->id);

            $showGroupRecords = "($tableName.is_personal IS NULL) OR ($tableName.is_personal = 0) OR ";

            $hasActionAclsDefined = has_group_action_acls_defined('InboundEmail', 'list');

            if($hasActionAclsDefined === false) {
                $showGroupRecords = '';
            }

            $ret_array['where'] = $ret_array['where'] . " AND ( $showGroupRecords ($tableName.is_personal = 1 AND $tableName.created_by = '$currentUserId') )";
        }

        if ($return_array) {
            return $ret_array;
        }

        return $ret_array['select'] . $ret_array['from'] . $ret_array['where'] . $ret_array['order_by'];
    }

    /**
     * Override's SugarBean's
     */

    /**
     * Override's SugarBean's
     */
    public function get_list_view_data()
    {
        global $mod_strings;
        global $app_list_strings;
        $temp_array = $this->get_list_view_array();

        $temp_array['MAILBOX_TYPE_NAME'] = '';
        if (!empty($this->mailbox_type)) {
            if (!isset($app_list_strings['dom_mailbox_type'][$this->mailbox_type])) {
                LoggerManager::getLogger()->fatal('Language string not found for app_list_string[dom_mailbox_type][' . $this->mailbox_type . ']');
            }
            $temp_array['MAILBOX_TYPE_NAME'] = $app_list_strings['dom_mailbox_type'][$this->mailbox_type] ?? null;
        }

        //cma, fix bug 21670.
        $temp_array['GLOBAL_PERSONAL_STRING'] = ($this->is_personal ? $mod_strings['LBL_IS_PERSONAL'] : $mod_strings['LBL_IS_GROUP']);
        $temp_array['STATUS'] = ($this->status == 'Active') ? $mod_strings['LBL_STATUS_ACTIVE'] : $mod_strings['LBL_STATUS_INACTIVE'];

        return $temp_array;
    }

    /**
     * Override's SugarBean's
     */
    public function fill_in_additional_list_fields()
    {
        $this->fill_in_additional_detail_fields();
    }

    /**
     * Override's SugarBean's
     */
    public function fill_in_additional_detail_fields()
    {
        $this->calculateType();
        $this->calculateDefault();
        $this->calculateSignature();

        $this->expandStoreOptions();

        if (!empty($this->service)) {
            $exServ = explode('::', $this->service);
            $this->tls = $exServ[0];
            if (isset($exServ[1])) {
                $this->ca = $exServ[1];
            }
            if (isset($exServ[2])) {
                $this->ssl = $exServ[2];
            }
            if (isset($exServ[3])) {
                $this->protocol = $exServ[3];
            }
        }
    }

    public function calculateType(): void {

        if (!empty($this->type)){
            return;
        }

        if (isTrue($this->is_personal ?? false)) {
            $this->type = 'personal';
            return;
        }

        $mailboxType = $this->mailbox_type ?? '';
        if ($mailboxType === 'createcase') {
            $this->type = 'group';
            return;
        }

        if ($mailboxType === 'bounce') {
            $this->type = 'bounce';
            return;
        }

        if ($mailboxType === 'pick' ) {
            $this->type = 'group';
        }
    }

    public function calculateDefault(): void {

        global $current_user;

        if ($this->type === 'personal' && $this->getUsersDefaultOutboundServerId($current_user) === $this->id) {
            $this->is_default = 1;
        }
    }

    public function calculateSignature(): void {
        $inboundEmailId = $this->id ?? '';
        $createdBy = $this->created_by ?? '';

        if ($inboundEmailId === '' || $createdBy === '') {
            return;
        }

        /** @var User $owner */
        $owner = BeanFactory::getBean('Users', $createdBy);

        $emailSignatures = $owner->getPreference('account_signatures', 'Emails') ?? '';
        $emailSignatures = sugar_unserialize(base64_decode($emailSignatures));

        $signatureId = $emailSignatures[$inboundEmailId] ?? '';

        if ($signatureId !== '') {
            $this->account_signature_id = $signatureId;
        }
    }

    /**
     * Expand options
     * @return void
     */
    public function expandStoreOptions(): void {

        if (empty($this->stored_options)) {
            return;
        }

        // FROM NAME and Address
        $storedOptions = unserialize(base64_decode($this->stored_options), ['allowed_classes' => false]);

        $this->from_name = ($storedOptions['from_name'] ?? '');
        $this->from_addr = ($storedOptions['from_addr'] ?? '');
        $this->reply_to_name = $storedOptions['reply_to_name'] ?? '';
        $this->reply_to_addr = $storedOptions['reply_to_addr'] ?? '';
        $this->only_since = isTrue($storedOptions['LBL_ONLY_SINCE_NO'] ?? false);
        $this->filter_domain = $storedOptions['filter_domain'] ?? '';
        $this->trashFolder =  $storedOptions['trashFolder'] ?? '';
        $this->sentFolder = $storedOptions['sentFolder'] ?? '';
        $this->mailbox = $storedOptions['mailbox'] ?? '';

        $this->leave_messages_on_mail_server = isTrue($storedOptions['leaveMessagesOnMailServer'] ?? false);
        $this->move_messages_to_trash_after_import = !isTrue($storedOptions['leaveMessagesOnMailServer'] ?? true);

        $this->distrib_method = $storedOptions['distrib_method'] ?? '';
        $this->distribution_user_id = $storedOptions['distribution_user_id'] ?? '';
        $this->distribution_options = $storedOptions['distribution_options'] ?? '';
        $this->create_case_template_id = $storedOptions['create_case_email_template'] ?? '';
        $this->email_num_autoreplies_24_hours = $storedOptions['email_num_autoreplies_24_hours'] ?? $this->defaultEmailNumAutoreplies24Hours;

        $this->is_auto_import = isTrue($storedOptions['isAutoImport'] ?? false);
        $this->is_create_case = ($this->mailbox_type ?? '') === 'createcase';
        $this->allow_outbound_group_usage = isTrue($storedOptions['allow_outbound_group_usage'] ?? false);

        $this->outbound_email_id = $storedOptions['outbound_email'] ?? '';
    }

    /**
     * Checks for $user's autoImport setting and returns the current value
     * @param object $user User in focus, defaults to $current_user
     * @return bool
     */
    public function isAutoImport($user = null)
    {
        if (!empty($this->autoImport)) {
            return $this->autoImport;
        }

        global $current_user;
        if (empty($user)) {
            $user = $current_user;
        }

        $emailSettings = $current_user->getPreference('emailSettings', 'Emails');
        $emailSettings = is_string($emailSettings) ? sugar_unserialize($emailSettings) : $emailSettings;

        $this->autoImport = (isset($emailSettings['autoImport']) && !empty($emailSettings['autoImport'])) ? true : false;

        return $this->autoImport;
    }

    /**
     * Clears out cache files for a user
     */
    public function cleanOutCache()
    {
        $GLOBALS['log']->debug("INBOUNDEMAIL: at cleanOutCache()");
        $this->deleteCache();
    }

    /**
     * moves emails from folder to folder
     * @param string $fromIe I-E id
     * @param string $fromFolder IMAP path to folder in which the email lives
     * @param string $toIe I-E id
     * @param string $toFolder
     * @param string $uids UIDs of emails to move, either Sugar GUIDS or IMAP
     * UIDs
     */
    public function copyEmails($fromIe, $fromFolder, $toIe, $toFolder, $uids)
    {
        $this->moveEmails($fromIe, $fromFolder, $toIe, $toFolder, $uids, true);
    }

    /**
     * moves emails from folder to folder
     * @param string $fromIe I-E id
     * @param string $fromFolder IMAP path to folder in which the email lives
     * @param string $toIe I-E id
     * @param string $toFolder
     * @param string $uids UIDs of emails to move, either Sugar GUIDS or IMAP
     * UIDs
     * @param bool $copy Default false
     * @return bool True on successful execution
     */
    public function moveEmails($fromIe, $fromFolder, $toIe, $toFolder, $uids, $copy = false)
    {
        global $app_strings;
        global $current_user;


        // same I-E server
        if ($fromIe == $toIe) {
            $GLOBALS['log']->debug("********* SUGARFOLDER - moveEmails() moving email from I-E to I-E");
            //$exDestFolder = explode("::", $toFolder);
            //preserve $this->mailbox
            if ($this->mailbox !== null) {
                $oldMailbox = $this->mailbox;
            }


            $this->retrieve($fromIe);
            $this->mailbox = $fromFolder;
            $this->connectMailserver();
            $exUids = explode('::;::', $uids);
            $uids = implode(",", $exUids);
            // imap_mail_move accepts comma-delimited lists of UIDs
            if ($copy) {
                if ($this->getImap()->mailCopy($uids, $toFolder, CP_UID)) {
                    $this->mailbox = $toFolder;
                    $this->connectMailserver();
                    $newOverviews = $this->getImap()->fetchOverview($uids, FT_UID);
                    $this->updateOverviewCacheFile($newOverviews, 'append');
                    if (isset($oldMailbox)) {
                        $this->mailbox = $oldMailbox;
                    }

                    return true;
                }
                $GLOBALS['log']->debug("INBOUNDEMAIL: could not imap_mail_copy() [ {$uids} ] to folder [ {$toFolder} ] from folder [ {$fromFolder} ]");
            } else {
                if ($this->getImap()->mailMove($uids, $toFolder, CP_UID)) {
                    $GLOBALS['log']->info("INBOUNDEMAIL: imap_mail_move() [ {$uids} ] to folder [ {$toFolder} ] from folder [ {$fromFolder} ]");
                    $this->getImap()->expunge(); // hard deletes moved messages

                    // update cache on fromFolder
                    $newOverviews = $this->getOverviewsFromCacheFile($uids, $fromFolder, true);
                    $this->deleteCachedMessages($uids, $fromFolder);

                    // update cache on toFolder
                    $this->checkEmailOneMailbox($toFolder, true, true);
                    if (isset($oldMailbox)) {
                        $this->mailbox = $oldMailbox;
                    }

                    return true;
                }
                $GLOBALS['log']->debug("INBOUNDEMAIL: could not imap_mail_move() [ {$uids} ] to folder [ {$toFolder} ] from folder [ {$fromFolder} ]");
            }
        } elseif ($toIe == 'folder' && $fromFolder == 'sugar::Emails') {
            $GLOBALS['log']->debug("********* SUGARFOLDER - moveEmails() moving email from SugarFolder to SugarFolder");
            // move from sugar folder to sugar folder
            require_once("include/SugarFolders/SugarFolders.php");
            $sugarFolder = new SugarFolder();
            $exUids = explode($app_strings['LBL_EMAIL_DELIMITER'], $uids);
            foreach ($exUids as $id) {
                if ($copy) {
                    $sugarFolder->copyBean($fromIe, $toFolder, $id, "Emails");
                } else {
                    $fromSugarFolder = new SugarFolder();
                    $fromSugarFolder->retrieve($fromIe);
                    $toSugarFolder = new SugarFolder();
                    $toSugarFolder->retrieve($toFolder);

                    $email = BeanFactory::newBean('Emails');
                    $email->retrieve($id);
                    $email->status = 'unread';

                    // when you move from My Emails to Group Folder, Assign To field for the Email should become null
                    if ($fromSugarFolder->is_dynamic && $toSugarFolder->is_group) {
                        // Bug 50972 - assigned_user_id set to empty string not true null
                        // Modifying the field defs in just this one place to allow
                        // a true null since this is what is expected when reading
                        // inbox folders
                        $email->setFieldNullable('assigned_user_id');
                        $email->assigned_user_id = "";
                        $email->save();
                        $email->revertFieldNullable('assigned_user_id');
                        // End fix 50972
                        if (!$toSugarFolder->checkEmailExistForFolder($id)) {
                            $fromSugarFolder->deleteEmailFromAllFolder($id);
                            $toSugarFolder->addBean($email);
                        }
                    } elseif ($fromSugarFolder->is_group && $toSugarFolder->is_dynamic) {
                        $fromSugarFolder->deleteEmailFromAllFolder($id);
                        $email->assigned_user_id = $current_user->id;
                        $email->save();
                    } else {
                        // If you are moving something from personal folder then delete an entry from all folder
                        if (!$fromSugarFolder->is_dynamic && !$fromSugarFolder->is_group) {
                            $fromSugarFolder->deleteEmailFromAllFolder($id);
                        } // if

                        if ($fromSugarFolder->is_dynamic && !$toSugarFolder->is_dynamic && !$toSugarFolder->is_group) {
                            $email->assigned_user_id = "";
                            $toSugarFolder->addBean($email);
                        } // if
                        if (!$toSugarFolder->checkEmailExistForFolder($id)) {
                            if (!$toSugarFolder->is_dynamic) {
                                $fromSugarFolder->deleteEmailFromAllFolder($id);
                                $toSugarFolder->addBean($email);
                            } else {
                                $fromSugarFolder->deleteEmailFromAllFolder($id);
                                $email->assigned_user_id = $current_user->id;
                            }
                        } else {
                            $sugarFolder->move($fromIe, $toFolder, $id);
                        } // else
                        $email->save();
                    } // else
                }
            }

            return true;
        } elseif ($toIe == 'folder') {
            $GLOBALS['log']->debug("********* SUGARFOLDER - moveEmails() moving email from I-E to SugarFolder");
            // move to Sugar folder
            require_once("include/SugarFolders/SugarFolders.php");
            $sugarFolder = new SugarFolder();
            $sugarFolder->retrieve($toFolder);
            //Show the import form if we don't have the required info
            if (!isset($_REQUEST['delete'])) {
                $json = getJSONobj();
                if ($sugarFolder->is_group) {
                    $_REQUEST['showTeam'] = false;
                    $_REQUEST['showAssignTo'] = false;
                }
                $ret = $this->email->et->getImportForm($_REQUEST, $this->email);
                $ret['move'] = true;
                $ret['srcFolder'] = $fromFolder;
                $ret['srcIeId'] = $fromIe;
                $ret['dstFolder'] = $toFolder;
                $ret['dstIeId'] = $toIe;
                $out = trim($json->encode($ret, false));
                echo $out;

                return true;
            }


            // import to Sugar
            $this->retrieve($fromIe);
            $this->mailbox = $fromFolder;
            $this->connectMailserver();
            // If its a group folder the team should be of the folder team
            if ($sugarFolder->is_group) {
                $_REQUEST['team_id'] = $sugarFolder->team_id;
                $_REQUEST['team_set_id'] = $sugarFolder->team_set_id;
            }
            // TODO - set team_id, team_set for new UI
            // else

            $exUids = explode($app_strings['LBL_EMAIL_DELIMITER'], $uids);

            if (!empty($sugarFolder->id)) {
                $count = 1;
                $return = array();
                $json = getJSONobj();
                foreach ($exUids as $k => $uid) {
                    $msgNo = $uid;
                    if ($this->isPop3Protocol()) {
                        $msgNo = $this->getCorrectMessageNoForPop3($uid);
                    } else {
                        $msgNo = $this->getImap()->getMessageNo($uid);
                    }

                    if (!empty($msgNo)) {
                        $importStatus = $this->returnImportedEmail($msgNo, $uid);
                        // add to folder
                        if ($importStatus) {
                            $sugarFolder->addBean($this);
                            if (!$copy && isset($_REQUEST['delete']) && ($_REQUEST['delete'] == "true") && $importStatus) {
                                $GLOBALS['log']->error("********* delete from mailserver [ {explode(", ", $uids)} ]");
                                // delete from mailserver
                                $this->deleteMessageOnMailServer($uid);
                                $this->deleteMessageFromCache($uid);
                            } // if
                        }
                        $return[] = $app_strings['LBL_EMAIL_MESSAGE_NO'] . " " . $count . ", " . $app_strings['LBL_STATUS'] . " " . ($importStatus ? $app_strings['LBL_EMAIL_IMPORT_SUCCESS'] : $app_strings['LBL_EMAIL_IMPORT_FAIL']);
                        $count++;
                    } // if
                } // foreach
                echo $json->encode($return);

                return true;
            }
            $GLOBALS['log']->error("********* SUGARFOLDER - failed to retrieve folder ID [ {$toFolder} ]");
        } else {
            $GLOBALS['log']->debug("********* SUGARFOLDER - moveEmails() called with no passing criteria");
        }

        return false;
    }


    /**
     * Hard deletes an I-E account
     * @param string id GUID
     */
    public function hardDelete($id)
    {
        $q = "DELETE FROM inbound_email WHERE id = '{$id}'";
        $this->db->query($q, true);

        $q = "DELETE FROM folders WHERE id = '{$id}'";
        $this->db->query($q, true);

        $q = "DELETE FROM folders WHERE parent_folder = '{$id}'";
        $this->db->query($q, true);
    }

    /**
     * Generate a unique filename for attachments based on the message id.  There are no maximum
     * specifications for the length of the message id, the only requirement is that it be globally unique.
     *
     * @param bool $nameOnly Whether or not the attachment count should be appended to the filename.
     * @return string The temp file name
     */
    public function getTempFilename($nameOnly = false)
    {
        $str = $this->compoundMessageId;

        if (!$nameOnly) {
            $str = $str . $this->attachmentCount;
            $this->attachmentCount++;
        }

        return $str;
    }

    /**
     * deletes and expunges emails on server
     * @param string $uid UID(s), comma delimited, of email(s) on server
     * @return bool true on success
     */
    public function deleteMessageOnMailServer($uid)
    {
        global $app_strings;
        $this->connectMailserver();

        $uids = [];
        if (strpos($uid, (string) $app_strings['LBL_EMAIL_DELIMITER']) !== false) {
            $uids = explode($app_strings['LBL_EMAIL_DELIMITER'], $uid);
        } else {
            $uids[] = $uid;
        }

        $return = true;
        $msgnos = [];

        if ($this->protocol == 'imap') {
            $trashFolder = $this->get_stored_options("trashFolder");
            if (empty($trashFolder)) {
                $trashFolder = "INBOX.Trash";
            }
            $uidsToMove = implode('::;::', $uids);
            if ($this->moveEmails($this->id, $this->mailbox, $this->id, $trashFolder, $uidsToMove)) {
                $GLOBALS['log']->fatal("INBOUNDEMAIL: MoveEmail to {$trashFolder} successful.");
            } else {
                $GLOBALS['log']->fatal("INBOUNDEMAIL: MoveEmail to {$trashFolder} FAILED - trying hard delete for message: $uid");
                $uidsToDelete = implode(',', $uids);
                $this->getImap()->delete($uidsToDelete, FT_UID);
                $return = true;
            }
        } else {
            foreach ($uids as $uid) {
                $msgnos[] = $this->getCorrectMessageNoForPop3($uid);
            }
            $msgnos = implode(',', $msgnos);
            $this->getImap()->delete($msgnos);
            $return = true;
        }

        if (!$this->getImap()->expunge()) {
            $GLOBALS['log']->debug("NOOP: could not expunge deleted email.");
            $return = false;
        } else {
            LoggerManager::getLogger()->info("INBOUNDEMAIL: hard-deleted mail with MSgno's' [ {$msgnos} ]");
        }

        return $return;
    }

    /**
     * deletes and expunges emails on server
     * @param string $uid UID(s), comma delimited, of email(s) on server
     */
    public function deleteMessageOnMailServerForPop3($uid)
    {
        if (!$this->getImap()->isValidStream($this->conn)) {
            LoggerManager::getLogger()->fatal('Inbound Email connection is not a resource for deleting Message On Mail Server For Pop3');

            return false;
        }
        if ($this->getImap()->delete($uid)) {
            if (!$this->getImap()->expunge()) {
                $GLOBALS['log']->debug("NOOP: could not expunge deleted email.");
                $return = false;
            } else {
                $GLOBALS['log']->info("INBOUNDEMAIL: hard-deleted mail with MSgno's' [ {$uid} ]");
            }
        }
    }

    /**
     * Checks if this is a pop3 type of an account or not
     * @return boolean
     */
    public function isPop3Protocol()
    {
        return ($this->protocol == 'pop3');
    }

    /**
     * Gets the UIDL from database for the corresponding msgno
     * @param int messageNo of a message
     * @return UIDL for the message
     */
    public function getUIDLForMessage($msgNo)
    {
        $query = "SELECT message_id FROM email_cache WHERE ie_id = '{$this->id}' AND msgno = '{$msgNo}'";
        $r = $this->db->query($query);
        $a = $this->db->fetchByAssoc($r);

        // Protect against the query failing.
        if ($a === false) {
            return null;
        } else {
            return $a['message_id'];
        }
    }

    /**
     * Get the users default IE account id
     *
     * @param User $user
     * @return string
     */
    public function getUsersDefaultOutboundServerId($user)
    {
        $id = $user->getPreference($this->keyForUsersDefaultIEAccount, 'Emails', $user);
        //If no preference has been set, grab the default system id.
        if (empty($id)) {
            $oe = new OutboundEmail();
            $system = $oe->getSystemMailerSettings();
            $id = empty($system->id) ? '' : $system->id;
        }

        return $id;
    }


    public function isOnlyPersonalInbound()
    {
        $inboundAccount = $this->getUserPersonalAccountCount();
        if ($inboundAccount == 1) {
            return true;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public function isDefaultPersonalInbound($userId): bool
    {
        $user = BeanFactory::getBean('Users', $userId);
        $isDefault = $user->getPreference($this->keyForUsersDefaultIEAccount, 'Emails');
        if ($isDefault == $userId){
            return true;
        }
        return false;
    }

    /**
     * Get the users default IE account id
     *
     * @param User $user
     */
    public function setUsersDefaultOutboundServerId($user, $oe_id)
    {
        $user->setPreference($this->keyForUsersDefaultIEAccount, $oe_id, '', 'Emails');
    }

    /**
     * Gets the UIDL from database for the corresponding msgno
     * @param int messageNo of a message
     * @return UIDL for the message
     */
    public function getMsgnoForMessageID($messageid)
    {
        $query = "SELECT msgno FROM email_cache WHERE ie_id = '{$this->id}' AND message_id = '{$messageid}'";
        $r = $this->db->query($query);
        $a = $this->db->fetchByAssoc($r);

        if (!isset($a['message_id'])) {
            LoggerManager::getLogger()->warn('unable to get msgno for message id');

            return null;
        }

        return $a['message_id'];
    }

    /**
     * fills InboundEmail->email with an email's details
     * @param int uid Unique ID of email
     * @param bool isMsgNo flag that passed ID is msgNo, default false
     * @param bool setRead Sets the 'seen' flag in cache
     * @param bool forceRefresh Skips cache file
     * @return string
     */
    public function setEmailForDisplay($uid, $isMsgNo = false, $setRead = false, $forceRefresh = false)
    {

        if (empty($uid)) {
            $GLOBALS['log']->debug("*** ERROR: INBOUNDEMAIL trying to setEmailForDisplay() with no UID");

            return 'NOOP';
        }

        global $sugar_config;
        global $app_strings;

        $cacheFile = [];
        // if its a pop3 then get the UIDL and see if this file name exist or not
        if ($this->isPop3Protocol()) {
            // get the UIDL from database;
            $cachedUIDL = md5($uid);
            $cache = "{$this->EmailCachePath}/{$this->id}/messages/{$this->mailbox}{$cachedUIDL}.php";
        } else {
            $cache = "{$this->EmailCachePath}/{$this->id}/messages/{$this->mailbox}{$uid}.php";
        }

        if (isset($cache) && strpos($cache, "..") !== false) {
            die("Directory navigation attack denied.");
        }

        if (file_exists($cache) && !$forceRefresh) {
            $GLOBALS['log']->info("INBOUNDEMAIL: Using cache file for setEmailForDisplay()");

            include($cache); // profides $cacheFile
            /** @var $cacheFile array */

            $metaOut = unserialize($cacheFile['out']);
            $meta = $metaOut['meta']['email'];
            $email = BeanFactory::newBean('Emails');

            foreach ($meta as $k => $v) {
                $email->$k = $v;
            }

            $email->to_addrs = $meta['toaddrs'];
            $email->date_sent_received = $meta['date_start'];

            $this->email = $email;
            $this->email->email2init();
            $ret = 'cache';
        } else {
            $GLOBALS['log']->info("INBOUNDEMAIL: opening new connection for setEmailForDisplay()");
            if ($this->isPop3Protocol()) {
                $msgNo = $this->getCorrectMessageNoForPop3($uid);
            } else {
                if (empty($this->conn)) {
                    $this->connectMailserver();
                }
                $msgNo = ($isMsgNo) ? $uid : $this->getImap()->getMessageNo($uid);
            }
            if (empty($this->conn)) {
                $status = $this->connectMailserver();
                if ($status == "false") {
                    $this->email = BeanFactory::newBean('Emails');
                    $this->email->name = $app_strings['LBL_EMAIL_ERROR_MAILSERVERCONNECTION'];
                    $ret = 'error';

                    return $ret;
                }
            }

            $this->returnImportedEmail($msgNo, $uid, true);
            $this->email->id = '';
            $this->email->new_with_id = false;
            $ret = 'import';
        }

        if ($setRead) {
            $this->setStatuses($uid, 'seen', 1);
        }

        return $ret;
    }


    /**
     * Sets status for a particular attribute on the mailserver and the local cache file
     */
    public function setStatuses($uid, $field, $value)
    {
        global $sugar_config;
        /** available status fields
         * [subject] => aaa
         * [from] => Some Name
         * [to] => Some Name
         * [date] => Mon, 22 Jan 2007 17:32:57 -0800
         * [message_id] =>
         * [size] => 718
         * [uid] => 191
         * [msgno] => 141
         * [recent] => 0
         * [flagged] => 0
         * [answered] => 0
         * [deleted] => 0
         * [seen] => 1
         * [draft] => 0
         */
        // local cache
        $file = "{$this->mailbox}.imapFetchOverview.php";
        $overviews = $this->getCacheValueForUIDs($this->mailbox, array($uid));

        if (!empty($overviews)) {
            $updates = array();

            foreach ($overviews['retArr'] as $k => $obj) {
                if ($obj->imap_uid == $uid) {
                    $obj->$field = $value;
                    $updates[] = $obj;
                }
            }

            if (!empty($updates)) {
                $this->setCacheValue($this->mailbox, array(), $updates);
            }
        }
    }

    /**
     * Removes an email from the cache file, deletes the message from the cache too
     * @param string String of uids, comma delimited
     */
    public function deleteMessageFromCache($uids)
    {
        global $app_strings;

        // delete message cache file and email_cache file
        $exUids = explode($app_strings['LBL_EMAIL_DELIMITER'], $uids);

        foreach ($exUids as $uid) {
            // local cache
            $queryUID = $this->db->quote($uid);
            if ($this->isPop3Protocol()) {
                $q = "DELETE FROM email_cache WHERE message_id = '{$queryUID}' AND ie_id = '{$this->id}'";
            } else {
                $q = "DELETE FROM email_cache WHERE imap_uid = '{$queryUID}' AND ie_id = '{$this->id}'";
            }
            $r = $this->db->query($q);
            if ($this->isPop3Protocol()) {
                $uid = md5($uid);
            } // if
            $msgCacheFile = "{$this->EmailCachePath}/{$this->id}/messages/{$this->mailbox}{$uid}.php";
            if (file_exists($msgCacheFile)) {
                if (!unlink($msgCacheFile)) {
                    $GLOBALS['log']->error("***ERROR: InboundEmail could not delete the cache file [ {$msgCacheFile} ]");
                }
            }
        }
    }


    /**
     * Shows one email.
     * @param int uid UID of email to display
     * @param string mbox Mailbox to look in for the message
     * @param bool isMsgNo Flag to assume $uid is a MessageNo, not UniqueID, default false
     */
    public function displayOneEmail($uid, $mbox, $isMsgNo = false)
    {
        require_once("include/JSON.php");

        global $timedate;
        global $app_strings;
        global $app_list_strings;
        global $sugar_smarty;
        global $theme;
        global $current_user;
        global $sugar_config;

        $fetchedAttributes = array(
            'name',
            'from_name',
            'from_addr',
            'date_start',
            'time_start',
            'message_id',
        );

        $souEmail = array();
        foreach ($fetchedAttributes as $k) {
            if ($k == 'date_start') {
                $this->email->$k . " " . $this->email->time_start;
                $souEmail[$k] = $this->email->$k . " " . $this->email->time_start;
            } elseif ($k == 'time_start') {
                $souEmail[$k] = "";
            } else {
                $souEmail[$k] = trim($this->email->$k);
            }
        }

        // if a MsgNo is passed in, convert to UID
        if ($isMsgNo) {
            $uid = $this->getImap()->getUid($uid);
        }

        // meta object to allow quick retrieval for replies
        $meta = array();
        $meta['type'] = $this->email->type;
        $meta['uid'] = $uid;
        $meta['ieId'] = $this->id;
        $meta['email'] = $souEmail;
        $meta['mbox'] = $this->mailbox;
        $ccs = '';
        // imap vs pop3

        // self mapping
        $exMbox = explode("::", $mbox);

        // CC section
        $cc = '';
        if (!empty($this->email->cc_addrs)) {
            //$ccs = $this->collapseLongMailingList($this->email->cc_addrs);
            $ccs = to_html($this->email->cc_addrs_names);
            $cc = <<<eoq
                <tr>
                    <td NOWRAP valign="top" class="displayEmailLabel">
                        {$app_strings['LBL_EMAIL_CC']}:
                    </td>
                    <td class="displayEmailValue">
                        {$ccs}
                    </td>
                </tr>
eoq;
        }
        $meta['cc'] = $cc;
        $meta['email']['cc_addrs'] = $ccs;
        // attachments
        $attachments = '';
        if ($mbox == "sugar::Emails") {
            $q = "SELECT id, filename, file_mime_type FROM notes WHERE parent_id = '{$uid}' AND deleted = 0";
            $r = $this->db->query($q);
            $i = 0;
            while ($a = $this->db->fetchByAssoc($r)) {
                $url = "index.php?entryPoint=download&type=notes&id={$a['id']}";
                $lbl = ($i == 0) ? $app_strings['LBL_EMAIL_ATTACHMENTS'] . ":" : '';
                $i++;
                $attachments .= <<<EOQ
                <tr>
                            <td NOWRAP valign="top" class="displayEmailLabel">
                                {$lbl}
                            </td>
                            <td NOWRAP valign="top" colspan="2" class="displayEmailValue">
                                <a href="{$url}">{$a['filename']}</a>
                            </td>
                        </tr>
EOQ;
                $this->email->cid2Link($a['id'], $a['file_mime_type']);
            } // while
        } else {
            if ($this->attachmentCount > 0) {
                $theCount = $this->attachmentCount;

                for ($i = 0; $i < $theCount; $i++) {
                    $lbl = ($i == 0) ? $app_strings['LBL_EMAIL_ATTACHMENTS'] . ":" : '';
                    $name = $this->getTempFilename(true) . $i;
                    $tempName = urlencode($this->tempAttachment[$name]);

                    $url = "index.php?entryPoint=download&type=temp&isTempFile=true&ieId={$this->id}&tempName={$tempName}&id={$name}";

                    $attachments .= <<<eoq
                        <tr>
                            <td NOWRAP valign="top" class="displayEmailLabel">
                                {$lbl}
                            </td>
                            <td NOWRAP valign="top" colspan="2" class="displayEmailValue">
                                <a href="{$url}">{$this->tempAttachment[$name]}</a>
                            </td>
                        </tr>
eoq;
                } // for
            } // if
        } // else
        $meta['email']['attachments'] = $attachments;

        // toasddrs
        $meta['email']['toaddrs'] = $this->collapseLongMailingList($this->email->to_addrs);
        $meta['email']['cc_addrs'] = $ccs;

        // body
        $description = (empty($this->email->description_html)) ? nl2br($this->email->description) : $this->email->description_html;
        $meta['email']['description'] = $description;

        // meta-metadata
        $meta['is_sugarEmail'] = ($exMbox[0] == 'sugar') ? true : false;

        if (!$meta['is_sugarEmail']) {
            if ($this->isAutoImport) {
                $meta['is_sugarEmail'] = true;
            }
        } else {
            if ($this->email->status != 'sent') {
                // mark SugarEmail read
                $q = "UPDATE emails SET status = 'read' WHERE id = '{$uid}'";
                $r = $this->db->query($q);
            }
        }

        $return = array();
        $meta['email']['name'] = to_html($this->email->name);
        $meta['email']['from_addr'] = (!empty($this->email->from_addr_name)) ? to_html($this->email->from_addr_name) : to_html($this->email->from_addr);
        isValidEmailAddress($meta['email']['from_addr']);
        $meta['email']['toaddrs'] = (!empty($this->email->to_addrs_names)) ? to_html($this->email->to_addrs_names) : to_html($this->email->to_addrs);
        $meta['email']['cc_addrs'] = to_html($this->email->cc_addrs_names);
        $meta['email']['reply_to_addr'] = to_html($this->email->reply_to_addr);
        $return['meta'] = $meta;

        return $return;
    }

    /**
     * Takes a long list of email addresses from a To or CC field and shows the first 3, the rest hidden
     * @param string emails
     * @return string
     */
    public function collapseLongMailingList($emails)
    {
        global $app_strings;

        $ex = explode(",", $emails);
        $i = 0;
        $j = 0;

        if (count($ex) > 3) {
            $emails = "";
            $emailsHidden = "";

            foreach ($ex as $email) {
                if ($i < 2) {
                    if (!empty($emails)) {
                        $emails .= ", ";
                    }
                    $emails .= trim($email);
                } else {
                    if (!empty($emailsHidden)) {
                        $emailsHidden .= ", ";
                    }
                    $emailsHidden .= trim($email);
                    $j++;
                }
                $i++;
            }

            if (!empty($emailsHidden)) {
                $email2 = $emails;
                $emails = "<span onclick='javascript:SUGAR.email2.detailView.showFullEmailList(this);' style='cursor:pointer;'>{$emails} [...{$j} {$app_strings['LBL_MORE']}]</span>";
                $emailsHidden = "<span onclick='javascript:SUGAR.email2.detailView.showCroppedEmailList(this)' style='cursor:pointer; display:none;'>{$email2}, {$emailsHidden} [ {$app_strings['LBL_LESS']} ]</span>";
            }

            $emails .= $emailsHidden;
        }

        return $emails;
    }


    /**
     * Sorts IMAP's imap_fetch_overview() results
     * @param array $arr Array of standard objects
     * @param string $sort Column to sort by
     * @param string direction Direction to sort by (asc/desc)
     * @return array Sorted array of obj.
     */
    public function sortFetchedOverview($arr, $sort = 4, $direction = 'DESC', $forceSeen = false)
    {
        global $current_user;

        $currentNode = [];

        $sortPrefs = $current_user->getPreference('folderSortOrder', 'Emails');
        if (!empty($sortPrefs)) {
            $listPrefs = $sortPrefs;
        } else {
            $listPrefs = array();
        }

        if (isset($listPrefs[$this->id][$this->mailbox])) {
            $currentNode = $listPrefs[$this->id][$this->mailbox];
        }

        if (isset($currentNode['current']) && !empty($currentNode['current'])) {
            $sort = $currentNode['current']['sort'];
            $direction = $currentNode['current']['direction'];
        }

        // sort defaults
        if (empty($sort)) {
            $sort = $this->defaultSort;//4;
            $direction = $this->defaultDirection; //'DESC';
        } elseif (!is_numeric($sort)) {
            // handle bad sort index
            $sort = $this->defaultSort;
        } else {
            // translate numeric index to human readable
            $sort = $this->hrSort[$sort];
        }
        if (empty($direction)) {
            $direction = 'DESC';
        }


        $retArr = array();
        $sorts = array();

        foreach ($arr as $k => $overview) {
            $sorts['flagged'][$k] = $overview->flagged;
            $sorts['status'][$k] = $overview->answered;
            $sorts['from'][$k] = str_replace('"', "", $this->handleMimeHeaderDecode($overview->from));
            $sorts['subj'][$k] = $this->handleMimeHeaderDecode(quoted_printable_decode($overview->subject));
            $sorts['date'][$k] = $overview->date;
        }

        // sort by column
        natcasesort($sorts[$sort]);

        // direction
        if (strtolower($direction) == 'desc') {
            $revSorts = array();
            $keys = array_reverse(array_keys($sorts[$sort]));
            $keysCount = count($keys);

            for ($i = 0; $i < $keysCount; $i++) {
                $v = $keys[$i];
                $revSorts[$v] = $sorts[$sort][$v];
            }

            $sorts[$sort] = $revSorts;
        }
        $timedate = TimeDate::getInstance();
        foreach ($sorts[$sort] as $k2 => $overview2) {
            $arr[$k2]->date = $timedate->fromString($arr[$k2]->date)->asDb();
            $retArr[] = $arr[$k2];
        }

        $finalReturn = array();
        $finalReturn['retArr'] = $retArr;
        $finalReturn['sortBy'] = $sort;
        $finalReturn['direction'] = $direction;

        return $finalReturn;
    }


    public function setReadFlagOnFolderCache($mbox, $uid)
    {
        global $sugar_config;

        $this->mailbox = $mbox;

        // cache
        if ($this->validCacheExists($this->mailbox)) {
            $ret = $this->getCacheValue($this->mailbox);

            $updates = array();

            foreach ($ret as $k => $v) {
                if ($v->imap_uid == $uid) {
                    $v->seen = 1;
                    $updates[] = $v;
                    break;
                }
            }

            $this->setCacheValue($this->mailbox, array(), $updates);
        }
    }

    /**
     * Returns a list of emails in a mailbox.
     * @param string mbox Name of mailbox using dot notation paths to display
     * @param string $forceRefresh Flag to use cache or not
     * @param integer page number
     */
    public function displayFolderContents($mbox, $forceRefresh = 'false', $page = 1)
    {
        global $current_user;

        $delimiter = $this->get_stored_options('folderDelimiter');
        if ($delimiter) {
            $mbox = str_replace('.', $delimiter, (string) $mbox);
        }

        $this->mailbox = $mbox;

        // jchi #9424, get sort and direction from user preference
        $sort = 'date';
        $direction = 'desc';
        $sortSerial = $current_user->getPreference('folderSortOrder', 'Emails');
        if (!empty($sortSerial) && !empty($_REQUEST['ieId']) && !empty($_REQUEST['mbox'])) {
            $sortArray = sugar_unserialize($sortSerial);
            $sort = $sortArray[$_REQUEST['ieId']][$_REQUEST['mbox']]['current']['sort'];
            $direction = $sortArray[$_REQUEST['ieId']][$_REQUEST['mbox']]['current']['direction'];
        }
        //end

        // save sort order
        if (!empty($_REQUEST['sort']) && !empty($_REQUEST['dir'])) {
            $this->email->et->saveListViewSortOrder(
                $_REQUEST['ieId'],
                $_REQUEST['mbox'],
                $_REQUEST['sort'],
                $_REQUEST['dir']
            );
            $sort = $_REQUEST['sort'];
            $direction = $_REQUEST['dir'];
        } else {
            $_REQUEST['sort'] = '';
            $_REQUEST['dir'] = '';
        }

        // cache
        $ret = array();
        $cacheUsed = false;
        if ($forceRefresh == 'false' && $this->validCacheExists($this->mailbox)) {
            $emailSettings = $current_user->getPreference('emailSettings', 'Emails');

            // cn: default to a low number until user specifies otherwise
            if (empty($emailSettings['showNumInList'])) {
                $emailSettings['showNumInList'] = 20;
            }

            $ret = $this->getCacheValue($this->mailbox, $emailSettings['showNumInList'], $page, $sort, $direction);
            $cacheUsed = true;
        }

        $out = $this->displayFetchedSortedListXML($ret, $mbox);

        $metadata = array();
        $metadata['mbox'] = $mbox;
        $metadata['ieId'] = $this->id;
        $metadata['name'] = $this->name;
        $metadata['fromCache'] = $cacheUsed ? 1 : 0;
        $metadata['out'] = $out;

        return $metadata;
    }

    /**
     * For a group email account, create subscriptions for all users associated with the
     * team assigned to the account.
     *
     */
    public function createUserSubscriptionsForGroupAccount()
    {
        $team = new Team();
        $team->retrieve($this->team_id);
        $usersList = $team->get_team_members(true);
        foreach ($usersList as $userObject) {
            $previousSubscriptions = sugar_unserialize(
                base64_decode(
                    $userObject->getPreference(
                        'showFolders',
                        'Emails',
                        $userObject
                    )
                )
            );
            if ($previousSubscriptions === false) {
                $previousSubscriptions = array();
            }

            $previousSubscriptions[] = $this->id;

            $encodedSubs = base64_encode(serialize($previousSubscriptions));
            $userObject->setPreference('showFolders', $encodedSubs, '', 'Emails');
            $userObject->savePreferencesToDB();
        }
    }

    /**
     * Create a sugar folder for this inbound email account
     * if the Enable Auto Import option is selected
     *
     * @return String Id of the sugar folder created.
     */
    public function createAutoImportSugarFolder()
    {
        global $current_user;
        $guid = create_guid();
        $GLOBALS['log']->debug("Creating Sugar Folder for IE with id $guid");
        $folder = new SugarFolder();
        $folder->id = $guid;
        $folder->new_with_id = true;
        $folder->name = $this->name;
        $folder->has_child = 0;
        $folder->is_group = 1;
        $folder->assign_to_id = $current_user->id;
        $folder->parent_folder = "";


        //If this inbound email is marked as inactive, don't add subscriptions.
        $addSubscriptions = ($this->status == 'Inactive' || $this->mailbox_type == 'bounce') ? false : true;
        $folder->save($addSubscriptions);

        return $guid;
    }

    public function validCacheExists($mbox)
    {
        $q = "SELECT count(*) c FROM email_cache WHERE ie_id = '{$this->id}'";
        $r = $this->db->query($q);
        $a = $this->db->fetchByAssoc($r);
        $count = $a['c'];

        if ($count > 0) {
            return true;
        }

        return false;
    }


    public function displayFetchedSortedListXML($ret, $mbox)
    {
        global $timedate;
        global $current_user;
        global $sugar_config;

        if (empty($ret['retArr'])) {
            return array();
        }

        $tPref = $current_user->getUserDateTimePreferences();

        $return = array();

        foreach ($ret['retArr'] as $msg) {
            $flagged = ($msg->flagged == 0) ? "" : $this->iconFlagged;
            $status = ($msg->deleted) ? $this->iconDeleted : "";
            $status = ($msg->draft == 0) ? $status : $this->iconDraft;
            $status = ($msg->answered == 0) ? $status : $this->iconAnswered;
            $from = $this->handleMimeHeaderDecode($msg->from);
            $subject = $this->handleMimeHeaderDecode($msg->subject);
            //$date		= date($tPref['date']." ".$tPref['time'], $msg->date);
            $date = $timedate->to_display_date_time($this->db->fromConvert($msg->date, 'datetime'));
            //$date		= date($tPref['date'], $this->getUnixHeaderDate($msg->date));

            $temp = array();
            $temp['flagged'] = $flagged;
            $temp['status'] = $status;
            $temp['from'] = to_html($from);
            $temp['subject'] = $subject;
            $temp['date'] = $date;
            $temp['uid'] = $msg->uid; // either from an imap_search() or massaged cache value
            $temp['mbox'] = $this->mailbox;
            $temp['ieId'] = $this->id;
            $temp['site_url'] = $sugar_config['site_url'];
            $temp['seen'] = $msg->seen;
            $temp['type'] = (isset($msg->type)) ? $msg->type : 'remote';
            $temp['to_addrs'] = to_html($msg->to);
            $temp['hasAttach'] = '0';

            $return[] = $temp;
        }

        return $return;
    }


    /**
     * retrieves the mailboxes for a given account in the following format
     * Array(
     * [INBOX] => Array
     * (
     * [Bugs] => Bugs
     * [Builder] => Builder
     * [DEBUG] => Array
     * (
     * [out] => out
     * [test] => test
     * )
     * )
     * @param bool $justRaw Default false
     * @return array
     */
    public function getMailboxes($justRaw = false)
    {
        if ($justRaw == true) {
            return $this->mailboxarray;
        } // if

        return $this->generateMultiDimArrayFromFlatArray($this->mailboxarray, $this->retrieveDelimiter());
    }

    public function getMailBoxesForGroupAccount()
    {
        $mailboxes = $this->generateMultiDimArrayFromFlatArray(
            explode(",", $this->mailbox),
            $this->retrieveDelimiter()
        );
        $mailboxesArray = $this->generateFlatArrayFromMultiDimArray($mailboxes, $this->retrieveDelimiter());
        $mailboxesArray = $this->filterMailBoxFromRaw(explode(",", $this->mailbox), $mailboxesArray);
        $this->saveMailBoxFolders($mailboxesArray);

        return $mailboxes;
    } // fn

    public function saveMailBoxFolders($value)
    {
        if (is_array($value)) {
            $value = implode(",", $value);
        }
        $this->mailboxarray = explode(",", $value);
        $value = $this->db->quoted($value);
        $query = "update inbound_email set mailbox = $value where id ='{$this->id}'";
        $this->db->query($query);
    }

    public function insertMailBoxFolders($value)
    {
        $query = "select value from config where category='InboundEmail' and name='{$this->id}'";
        $r = $this->db->query($query);
        $a = $this->db->fetchByAssoc($r);
        if (empty($a['value'])) {
            if (is_array($value)) {
                $value = implode(",", $value);
            }
            $this->mailboxarray = explode(",", $value);
            $value = $this->db->quoted($value);

            $query = "INSERT INTO config VALUES('InboundEmail', '{$this->id}', $value)";
            $this->db->query($query);
        } // if
    }

    public function saveMailBoxValueOfInboundEmail()
    {
        $emailUserQuoted = $this->db->quote($this->email_user);
        $query = "update Inbound_email set mailbox = '$emailUserQuoted'";
        $this->db->query($query);
    }

    public function retrieveMailBoxFolders()
    {
        $this->mailboxarray = explode(",", $this->mailbox);
    } // fn


    public function retrieveDelimiter()
    {
        $delimiter = $this->get_stored_options('folderDelimiter');
        if (!$delimiter) {
            $delimiter = '.';
        }

        return $delimiter;
    } // fn

    public function generateFlatArrayFromMultiDimArray($arraymbox, $delimiter)
    {
        $ret = array();
        foreach ($arraymbox as $key => $value) {
            $this->generateArrayData($key, $value, $ret, $delimiter);
        } // foreach

        return $ret;
    } // fn

    public function generateMultiDimArrayFromFlatArray($raw, $delimiter)
    {
        // generate a multi-dimensional array to iterate through
        $ret = array();
        foreach ($raw as $mbox) {
            $ret = $this->sortMailboxes($mbox, $ret, $delimiter);
        }

        return $ret;
    } // fn

    public function generateArrayData($key, $arraymbox, &$ret, $delimiter)
    {
        $ret [] = $key;
        if (is_array($arraymbox)) {
            foreach ($arraymbox as $mboxKey => $value) {
                $newKey = $key . $delimiter . $mboxKey;
                $this->generateArrayData($newKey, $value, $ret, $delimiter);
            } // foreach
        } // if
    }

    /**
     * sorts the folders in a mailbox in a multi-dimensional array
     * @param string $MBOX
     * @param array $ret
     * @return array
     */
    public function sortMailboxes($mbox, $ret, $delimeter = ".")
    {
        if (strpos((string) $mbox, (string) $delimeter)) {
            $node = substr((string) $mbox, 0, strpos((string) $mbox, (string) $delimeter));
            $nodeAfter = substr((string) $mbox, strpos((string) $mbox, (string) $node) + strlen($node) + 1, strlen((string) $mbox));

            if (!isset($ret[$node])) {
                $ret[$node] = array();
            } elseif (isset($ret[$node]) && !is_array($ret[$node])) {
                $ret[$node] = array();
            }
            $ret[$node] = $this->sortMailboxes($nodeAfter, $ret[$node], $delimeter);
        } else {
            $ret[$mbox] = $mbox;
        }

        return $ret;
    }

    /**
     * parses Sugar's storage method for imap server service strings
     * @return string
     */
    public function getServiceString()
    {
        $service = '';
        $exServ = explode('::', $this->service);

        foreach ($exServ as $v) {
            if (!empty($v) && ($v != 'imap' && $v != 'pop3')) {
                $service .= '/' . $v;
            }
        }

        return $service;
    }


    /**
     * Get Email messages IDs from server which aren't in database
     * @return array Ids of messages, which aren't still in database
     */
    public function getNewEmailsForSyncedMailbox()
    {
        // ids's count limit for batch processing
        $limit = 20;

        if (!$this->getImap()->isValidStream($this->conn)) {
            LoggerManager::getLogger()->fatal('Inbound Email connection is not a resource for getting New Emails For Synced Mailbox');

            return false;
        }

        $msgIds = $this->getImap()->search('ALL UNDELETED');
        $result = array();
        try {
            if ((is_countable($msgIds) ? count($msgIds) : 0) > 0) {
                /*
                 * @var collect results of queries and message headers
                 */
                $tmpMsgs = array();
                $repeats = 0;
                $counter = 0;

                // sort IDs to get lastest on top
                arsort($msgIds);
                $GLOBALS['log']->debug('-----> getNewEmailsForSyncedMailbox() got ' . (is_countable($msgIds) ? count($msgIds) : 0) . ' Messages');
                foreach ($msgIds as $k => &$msgNo) {
                    $uid = $this->getImap()->getUid($msgNo);
                    $header = $this->getImap()->headerInfo($msgNo);
                    $fullHeader = $this->getImap()->fetchHeader($msgNo);
                    $message_id = isset($header->message_id) ? $header->message_id : '';
                    $deliveredTo = $this->id;
                    $matches = array();
                    preg_match('/(delivered-to:|x-real-to:){1}\s*(\S+)\s*\n{1}/im', (string) $fullHeader, $matches);
                    if (count($matches)) {
                        $deliveredTo = $matches[2];
                    }
                    if (empty($message_id) || !isset($message_id)) {
                        $GLOBALS['log']->debug('*********** NO MESSAGE_ID.');
                        $message_id = $this->getMessageId($header);
                    }

                    // generate compound messageId
                    $this->compoundMessageId = trim($message_id) . trim($deliveredTo);
                    // if the length > 255 then md5 it so that the data will be of smaller length
                    if (strlen($this->compoundMessageId) > 255) {
                        $this->compoundMessageId = md5($this->compoundMessageId);
                    } // if

                    if (empty($this->compoundMessageId)) {
                        break;
                    } // if
                    $counter++;
                    $potentials = clean_xss($this->compoundMessageId, false);

                    if (is_array($potentials) && !empty($potentials)) {
                        foreach ($potentials as $bad) {
                            $this->compoundMessageId = str_replace($bad, "", $this->compoundMessageId);
                        }
                    }
                    array_push($tmpMsgs, array('msgNo' => $msgNo, 'msgId' => $this->compoundMessageId, 'exists' => 0));
                    if ($counter == $limit) {
                        $counter = 0;
                        $query = array();
                        foreach (array_slice($tmpMsgs, -$limit, $limit) as $k1 => $v1) {
                            $query[] = $v1['msgId'];
                        }
                        $query = 'SELECT count(emails.message_id) as cnt, emails.message_id AS mid FROM emails WHERE emails.message_id IN ("' . implode(
                                '","',
                                $query
                            ) . '") and emails.deleted = 0 group by emails.message_id';
                        $r = $this->db->query($query);
                        $tmp = array();
                        while ($a = $this->db->fetchByAssoc($r)) {
                            $tmp[html_entity_decode((string) $a['mid'])] = $a['cnt'];
                        }
                        foreach ($tmpMsgs as $k1 => $v1) {
                            if (isset($tmp[$v1['msgId']]) && $tmp[$v1['msgId']] > 0) {
                                $tmpMsgs[$k1]['exists'] = 1;
                            }
                        }
                        foreach ($tmpMsgs as $k1 => $v1) {
                            if ($v1['exists'] == 0) {
                                $repeats = 0;
                                array_push($result, $v1['msgNo']);
                            } else {
                                $repeats++;
                            }
                        }
                        if ($repeats > 0) {
                            if ($repeats >= $limit) {
                                break;
                            }
                            $tmpMsgs = array_splice($tmpMsgs, -$repeats, $repeats);
                        } else {
                            $tmpMsgs = array();
                        }
                    }
                }
                unset($msgNo);
            }
        } catch (Exception $ex) {
            $GLOBALS['log']->fatal($ex->getMessage());
        }
        $GLOBALS['log']->debug('-----> getNewEmailsForSyncedMailbox() got ' . count($result) . ' unsynced messages');

        return $result;
    }

    /**
     * Import new messages from given account.
     */
    public function importMessages()
    {
        $protocol = $this->isPop3Protocol() ? 'pop3' : 'imap';
        switch ($protocol) {
            case 'pop3':
                $this->importMailboxMessages($protocol);
                break;
            case 'imap':
                $mailboxes = $this->getMailboxes(true);
                foreach ($mailboxes as $mailbox) {
                    $this->importMailboxMessages($protocol, $mailbox);
                }
                $this->getImap()->expunge();
                $this->getImap()->close();
                break;
        }
    }

    /**
     * Import messages from specified mailbox
     *
     * @param string $protocol Mailing protocol
     * @param string|null $mailbox Mailbox (if applied to protocol)
     */
    protected function importMailboxMessages($protocol, $mailbox = null)
    {
        switch ($protocol) {
            case 'pop3':
                $msgNumbers = $this->getPop3NewMessagesToDownload();
                break;
            case 'imap':
                $this->mailbox = $mailbox;
                $this->connectMailserver();
                $msgNumbers = $this->getNewMessageIds();
                if (!$msgNumbers) {
                    $msgNumbers = array();
                }
                break;
            default:
                $msgNumbers = array();
                break;
        }

        foreach ($msgNumbers as $msgNumber) {
            $uid = $this->getMessageUID($msgNumber, $protocol);
            $GLOBALS['log']->info('Importing message no: ' . $msgNumber);
            $this->returnImportedEmail($msgNumber, $uid, false, false);
        }
    }

    /**
     * Retrieves message UID by it's number
     *
     * @param int $msgNumber Number of the message in current sequence
     * @param string $protocol Mailing protocol
     * @return string
     */
    protected function getMessageUID($msgNumber, $protocol)
    {
        switch ($protocol) {
            case 'pop3':
                $uid = $this->getUIDLForMessage($msgNumber);
                break;
            case 'imap':
                $uid = $this->getImap()->getUid($msgNumber);
                break;
            default:
                $uid = null;
                break;
        }

        return $uid;
    }

    public function bean_implements($interface)
    {
        if ($interface === 'ACL') {
            return true;
        }

        return false;
    }

    /**
     * Check if its admin only action
     * @param string $view
     * @return bool
     */
    protected function isAdminOnlyAction(string $view): bool
    {
        $adminOnlyAction = ['edit', 'delete', 'editview', 'save'];
        return in_array(strtolower($view), $adminOnlyAction);
    }

    /**
     * Check if its a security based action
     * @param string $view
     * @return bool
     */
    protected function isSecurityGroupBasedAction(string $view): bool
    {
        $securityBasedActions = ['detail', 'detailview', 'view'];
        return in_array(strtolower($view), $securityBasedActions);
    }

    /**
     * Get not allowed action
     * @param string $view
     * @return bool
     */
    protected function isNotAllowedAction(string $view): bool
    {
        $notAllowed = ['export', 'import', 'massupdate', 'duplicate'];
        return in_array(strtolower($view), $notAllowed);
    }


    /**
     * @param array $order
     * @return array
     */
    protected function getSortCriteria(array $order): array
    {
        // handle sorting
        // Default: to sort the date in descending order
        $sortCriteria = SORTARRIVAL;
        $sortCRM = 'udate';
        $sortOrder = 1;

        return [$sortCriteria, $sortCRM, $sortOrder];
    }

    /**
     * @param array $filter
     * @return string|null
     */
    protected function getFilterCriteria(array $filter): ?string
    {
        // handle filtering
        $filterCriteria = null;
        $emailFilteringOption = 'multi';

        if ($this->email_body_filtering) {
            $emailFilteringOption = $this->email_body_filtering;
        }

        if (!empty($filter)) {
            foreach ($filter as $filterField => $filterFieldValue) {

                if (empty($filterFieldValue)) {
                    continue;
                }

                // Convert to a blank string as NULL will break the IMAP request
                if ($filterCriteria == null) {
                    $filterCriteria = '';
                }

                if ($filterField === 'BODY' && $emailFilteringOption !== 'multi') {
                    $word = strtok($filterFieldValue, ' ') ?? '';
                    if (!empty($word)){
                        $filterCriteria .= ' ' . $filterField . ' "' . $word . '" ';
                    }
                } else {
                    $filterCriteria .= ' ' . $filterField . ' "' . $filterFieldValue . '" ';
                }
            }
        }

        return $filterCriteria;
    }

    /**
     * @param array $returnService
     * @param array $serviceArr
     * @param $tmpMailbox
     * @return void
     */
    protected function overrideConnectionConfigs(array &$returnService, array &$serviceArr, $tmpMailbox): void
    {
        $connectionString = str_replace('//', '', $this->connection_string ?? '');

        $parts = explode('/', $connectionString) ?? [];
        array_shift($parts);
        $servicesString = implode('/', $parts);
        $serviceKey = implode('-', $parts);

        $returnService[$serviceKey] = 'foo' . $servicesString;
        $serviceArr[$serviceKey] = '{' . $this->connection_string . '}' . $tmpMailbox;
    }

    /**
     * @param $emailHeaders
     * @param $sortCRM
     * @param $sortOrder
     * @return mixed
     */
    protected function sortMessageList($emailHeaders, $sortCRM, $sortOrder)
    {
        // TODO: parameter 1 could be a bool but it should be an array!
        usort(
            $emailHeaders,
            function ($a, $b) use ($sortCRM) {  // defaults to DESC order
                if ($a[$sortCRM] === $b[$sortCRM]) {
                    return 0;
                } elseif ($a[$sortCRM] < $b[$sortCRM]) {
                    return 1;
                }

                return -1;
            }
        );

        // Make it ASC order
        if (!$sortOrder) {
            array_reverse($emailHeaders);
        };

        return $emailHeaders;
    }

    /**
     * @param $password
     * @param int $imapConnectionOptions
     * @return array
     */
    protected function getOAuthCredentials($password, int $imapConnectionOptions): array
    {
        if ($this->isOAuth()) {
            /** @var ExternalOAuthConnection $oAuthConnection */
            $oAuthConnection = BeanFactory::getBean('ExternalOAuthConnection', $this->external_oauth_connection_id);
            $password = $oAuthConnection->access_token;
            $imapConnectionOptions = OP_XOAUTH2;
        }

        return [$password, $imapConnectionOptions];
    }

    /**
     * Get Imap handler type
     * @return string
     */
    protected function getImapHandlerType(): string
    {
        return 'imap2';
    }

    /**
     * Get refersh token error messages
     * @param $reLogin
     * @param ExternalOAuthConnection $oauthConnection
     * @param string $oAuthConnectionId
     * @return string
     */
    protected function getOAuthRefreshTokenErrorMessage(
        $reLogin,
        ExternalOAuthConnection $oauthConnection,
        string $oAuthConnectionId
    ): string {
        $message = translate('ERR_IMAP_OAUTH_CONNECTION_ERROR', 'InboundEmail');
        $linkAction = 'DetailView';

        if ($reLogin === true) {
            $linkAction = 'EditView';
            $message = translate('WARN_OAUTH_TOKEN_SESSION_EXPIRED', 'InboundEmail');
        }

        $oauthConnectionName = $oauthConnection->name;

        $hasAccess = $oauthConnection->ACLAccess('edit') ?? false;
        if ($hasAccess === true) {
            $message .= " <a href=\"index.php?module=ExternalOAuthConnection&action=$linkAction&record=$oAuthConnectionId\">$oauthConnectionName</a>.";
        } else {
            $message .= $oauthConnectionName . '.';
        }

        return $message;
    }

    /**
     * Get OAuthToken. Refresh if needed
     * @param string $oAuthConnectionId
     * @return string|null
     */
    protected function getOAuthToken(string $oAuthConnectionId): ?string
    {
        require_once __DIR__ . '/../ExternalOAuthConnection/services/OAuthAuthorizationService.php';
        $oAuth = new OAuthAuthorizationService();

        /** @var ExternalOAuthConnection $oauthConnection */
        $oauthConnection = BeanFactory::getBean('ExternalOAuthConnection', $oAuthConnectionId);
        $password = $oauthConnection->access_token;

        $hasExpiredFeedback = $oAuth->hasConnectionTokenExpired($oauthConnection);
        $refreshToken = $hasExpiredFeedback['refreshToken'] ?? false;
        if ($refreshToken === true) {
            $refreshTokenFeedback = $oAuth->refreshConnectionToken($oauthConnection);

            if ($refreshTokenFeedback['success'] === false) {
                $message = $this->getOAuthRefreshTokenErrorMessage(
                    $refreshTokenFeedback['reLogin'],
                    $oauthConnection,
                    $oAuthConnectionId
                );
                displayAdminError($message);
                return null;
            }

            return $oauthConnection->access_token;
        }

        return $password;
    }

    /**
     * Check if is using oauth authentication
     * @return bool
     */
    protected function isOAuth(): bool
    {
        $authType = $this->auth_type ?? '';
        $oAuthConnectionId = $this->external_oauth_connection_id ?? '';

        return $authType === 'oauth' && $oAuthConnectionId !== '';
    }


} // end class definition

