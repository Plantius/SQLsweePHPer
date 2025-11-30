function clean_xss($str, $cleanImg = true)
{
    global $sugar_config;

    if (empty($sugar_config['email_xss'])) {
        $sugar_config['email_xss'] = getDefaultXssTags();
    }

    $xsstags = unserialize(base64_decode($sugar_config['email_xss']));

    // cn: bug 13079 - "on\w" matched too many non-events (cONTact, strONG, etc.)
    $jsEvents = 'onblur|onfocus|oncontextmenu|onresize|onscroll|onunload|ondblclick|onclick|';
    $jsEvents .= 'onmouseup|onmouseover|onmousedown|onmouseenter|onmouseleave|onmousemove|onload|onchange|';
    $jsEvents .= 'onreset|onselect|onsubmit|onkeydown|onkeypress|onkeyup|onabort|onerror|ondragdrop';

    $attribute_regex = "#\b({$jsEvents})\s*=\s*(?|(?!['\"])\S+|['\"].+?['\"])#sim";
    $javascript_regex = '@<[^/>][^>]+(expression\(|j\W*a\W*v\W*a|v\W*b\W*s\W*c\W*r|&#|/\*|\*/)[^>]*>@sim';
    $imgsrc_regex = '#<[^>]+src[^=]*=([^>]*?http(s)?://[^>]*)>#sim';
    $css_url = '#url\(.*\.\w+\)#';

    $tagsrex = '#<\/?(\w+)((?:\s+(?:\w|\w[\w-]*\w)(?:\s*=\s*(?:\".*?\"|\'.*?\'|[^\'\">\s]+))?)+\s*|\s*)\/?>#im';

    $tagmatches = array();
    $matches = array();
    preg_match_all($tagsrex, (string) $str, $tagmatches, PREG_PATTERN_ORDER);
    foreach ($tagmatches[1] as $no => $tag) {
        if (in_array($tag, $xsstags)) {
            // dangerous tag - take out whole
            $matches[] = $tagmatches[0][$no];
            continue;
        }
        $attrmatch = array();
        preg_match_all($attribute_regex, $tagmatches[2][$no], $attrmatch, PREG_PATTERN_ORDER);
        if (!empty($attrmatch[0])) {
            $matches = array_merge($matches, $attrmatch[0]);
        }
    }

    $matches = array_merge($matches, xss_check_pattern($javascript_regex, $str));

    if ($cleanImg) {
        $matches = array_merge(
            $matches,
            xss_check_pattern($imgsrc_regex, $str)
        );
    }

    // cn: bug 13498 - custom white-list of allowed domains that vet remote images
    preg_match_all($css_url, (string) $str, $cssUrlMatches, PREG_PATTERN_ORDER);

    if (isset($sugar_config['security_trusted_domains']) && !empty($sugar_config['security_trusted_domains']) && is_array($sugar_config['security_trusted_domains'])) {
        if (is_array($cssUrlMatches) && count($cssUrlMatches) > 0) {
            // normalize whitelist
            foreach ($sugar_config['security_trusted_domains'] as $k => $v) {
                $sugar_config['security_trusted_domains'][$k] = strtolower($v);
            }

            foreach ($cssUrlMatches[0] as $match) {
                $domain = strtolower(substr(strstr($match, '://'), 3));
                $baseUrl = substr($domain, 0, strpos($domain, '/'));

                if (!in_array($baseUrl, $sugar_config['security_trusted_domains'])) {
                    $matches[] = $match;
                }
            }
        }
    } else {
        $matches = array_merge($matches, $cssUrlMatches[0]);
    }

    return $matches;
}

/**
 * Helper function used by clean_xss() to parse for known-bad vectors.
 *
 * @param string pattern Regex pattern to use
 * @param string str String to parse for badness
 *
 * @return array
 */
function xss_check_pattern($pattern, $str)
{
    preg_match_all($pattern, (string) $str, $matches, PREG_PATTERN_ORDER);

    return $matches[1];
}

/**
 * Designed to take a string passed in the URL as a parameter and clean all "bad" data from it.
 *
 * @param string $str
 * @param string $filter       which corresponds to a regular expression to use; choices are:
 *                             "STANDARD" ( default )
 *                             "STANDARDSPACE"
 *                             "FILE"
 *                             "NUMBER"
 *                             "SQL_COLUMN_LIST"
 *                             "PATH_NO_URL"
 *                             "SAFED_GET"
 *                             "UNIFIED_SEARCH"
 *                             "AUTO_INCREMENT"
 *                             "ALPHANUM"
 * @param bool   $dieOnBadData true (default) if you want to die if bad data if found, false if not
 */
function clean_string($str, $filter = 'STANDARD', $dieOnBadData = true)
{
    global $sugar_config;

    $filters = array(
        'STANDARD' => '#[^A-Z0-9\-_\.\@]#i',
        'STANDARDSPACE' => '#[^A-Z0-9\-_\.\@\ ]#i',
        'FILE' => '#[^A-Z0-9\-_\.]#i',
        'NUMBER' => '#[^0-9\-]#i',
        'SQL_COLUMN_LIST' => '#[^A-Z0-9\(\),_\.]#i',
        'PATH_NO_URL' => '#://#i',
        'SAFED_GET' => '#[^A-Z0-9\@\=\&\?\.\/\-_~+]#i', /* range of allowed characters in a GET string */
        'UNIFIED_SEARCH' => '#[\\x00]#', /* cn: bug 3356 & 9236 - MBCS search strings */
        'AUTO_INCREMENT' => '#[^0-9\-,\ ]#i',
        'ALPHANUM' => '#[^A-Z0-9\-]#i',
    );

    if (preg_match($filters[$filter], $str)) {
        if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
            $GLOBALS['log']->fatal("SECURITY[$filter]: bad data passed in; string: {$str}");
        }
        if ($dieOnBadData) {
            die("Bad data passed in; <a href=\"{$sugar_config['site_url']}\">Return to Home</a>");
        }

        return false;
    }
    return $str;
}

function clean_file_output($string, $mine_type)
{
    $patterns = [];

    if ($mine_type === 'image/svg+xml') {
        $patterns[] = "/onload=\"window.location='(.*?)'\"/";
    }

    $string = preg_replace($patterns, '', (string) $string);

    return $string;
}


function clean_special_arguments()
{
    if (isset($_SERVER['PHP_SELF'])) {
        if (!empty($_SERVER['PHP_SELF'])) {
            clean_string($_SERVER['PHP_SELF'], 'SAFED_GET');
        }
    }
    if (!empty($_REQUEST) && !empty($_REQUEST['login_theme'])) {
        clean_string($_REQUEST['login_theme'], 'STANDARD');
    }
    if (!empty($_REQUEST) && !empty($_REQUEST['login_module'])) {
        clean_string($_REQUEST['login_module'], 'STANDARD');
    }
    if (!empty($_REQUEST) && !empty($_REQUEST['login_action'])) {
        clean_string($_REQUEST['login_action'], 'STANDARD');
    }
    if (!empty($_REQUEST) && !empty($_REQUEST['ck_login_theme_20'])) {
        clean_string($_REQUEST['ck_login_theme_20'], 'STANDARD');
    }
    if (!empty($_SESSION) && !empty($_SESSION['authenticated_user_theme'])) {
        clean_string($_SESSION['authenticated_user_theme'], 'STANDARD');
    }
    if (!empty($_REQUEST) && !empty($_REQUEST['module_name'])) {
        clean_string($_REQUEST['module_name'], 'STANDARD');
    }
    if (!empty($_REQUEST) && !empty($_REQUEST['module'])) {
        clean_string($_REQUEST['module'], 'STANDARD');
    }
    if (!empty($_POST) && !empty($_POST['parent_type'])) {
        clean_string($_POST['parent_type'], 'STANDARD');
    }
    if (!empty($_REQUEST) && !empty($_REQUEST['mod_lang'])) {
        clean_string($_REQUEST['mod_lang'], 'STANDARD');
    }
    if (!empty($_SESSION) && !empty($_SESSION['authenticated_user_language'])) {
        clean_string($_SESSION['authenticated_user_language'], 'STANDARD');
    }
    if (!empty($_SESSION) && !empty($_SESSION['dyn_layout_file'])) {
        clean_string($_SESSION['dyn_layout_file'], 'PATH_NO_URL');
    }
    if (!empty($_GET) && !empty($_GET['from'])) {
        clean_string($_GET['from']);
    }
    if (!empty($_GET) && !empty($_GET['gmto'])) {
        clean_string($_GET['gmto'], 'NUMBER');
    }
    if (!empty($_GET) && !empty($_GET['case_number'])) {
        clean_string($_GET['case_number'], 'AUTO_INCREMENT');
    }
    if (!empty($_GET) && !empty($_GET['bug_number'])) {
        clean_string($_GET['bug_number'], 'AUTO_INCREMENT');
    }
    if (!empty($_GET) && !empty($_GET['quote_num'])) {
        clean_string($_GET['quote_num'], 'AUTO_INCREMENT');
    }
    clean_superglobals('stamp', 'ALPHANUM'); // for vcr controls
    clean_superglobals('offset', 'ALPHANUM');
    clean_superglobals('return_action');
    clean_superglobals('return_module');

    return true;
}

/**
 * cleans the given key in superglobals $_GET, $_POST, $_REQUEST.
 */
function clean_superglobals($key, $filter = 'STANDARD')
{
    if (isset($_GET[$key])) {
        clean_string($_GET[$key], $filter);
    }
    if (isset($_POST[$key])) {
        clean_string($_POST[$key], $filter);
    }
    if (isset($_REQUEST[$key])) {
        clean_string($_REQUEST[$key], $filter);
    }
}

function set_superglobals($key, $val)
{
    $_GET[$key] = $val;
    $_POST[$key] = $val;
    $_REQUEST[$key] = $val;
}

// Works in conjunction with clean_string() to defeat SQL injection, file inclusion attacks, and XSS
function clean_incoming_data()
{
    global $sugar_config;
    global $RAW_REQUEST;

    $RAW_REQUEST = $_REQUEST;

    $req = array_map('securexss', $_REQUEST);
    $post = array_map('securexss', $_POST);
    $get = array_map('securexss', $_GET);

    // PHP cannot stomp out superglobals reliably
    foreach ($post as $k => $v) {
        $_POST[$k] = $v;
    }
    foreach ($get as $k => $v) {
        $_GET[$k] = $v;
    }
    foreach ($req as $k => $v) {
        $_REQUEST[$k] = $v;

        //ensure the keys are safe as well.  If mbstring encoding translation is on, the post keys don't
        //get translated, so scrub the data but don't die
        if (ini_get('mbstring.encoding_translation') === '1') {
            securexsskey($k, false);
        } else {
            securexsskey($k, true);
        }
    }
    // Any additional variables that need to be cleaned should be added here
    if (isset($_REQUEST['login_theme'])) {
        clean_string($_REQUEST['login_theme']);
    }
    if (isset($_REQUEST['login_module'])) {
        clean_string($_REQUEST['login_module']);
    }
    if (isset($_REQUEST['login_action'])) {
        clean_string($_REQUEST['login_action']);
    }
    if (isset($_REQUEST['login_language'])) {
        clean_string($_REQUEST['login_language']);
    }
    if (isset($_REQUEST['action'])) {
        clean_string($_REQUEST['action']);
    }
    if (isset($_REQUEST['module'])) {
        clean_string($_REQUEST['module']);
    }
    if (isset($_REQUEST['record'])) {
        clean_string($_REQUEST['record'], 'STANDARDSPACE');
    }
    if (isset($_SESSION['authenticated_user_theme'])) {
        clean_string($_SESSION['authenticated_user_theme']);
    }
    if (isset($_SESSION['authenticated_user_language'])) {
        clean_string($_SESSION['authenticated_user_language']);
    }
    if (isset($_REQUEST['language'])) {
        clean_string($_REQUEST['language']);
    }
    if (isset($sugar_config['default_theme'])) {
        clean_string($sugar_config['default_theme']);
    }
    if (isset($_REQUEST['offset'])) {
        clean_string($_REQUEST['offset']);
    }
    if (isset($_REQUEST['stamp'])) {
        clean_string($_REQUEST['stamp']);
    }

    if (isset($_REQUEST['lvso'])) {
        set_superglobals('lvso', (strtolower($_REQUEST['lvso']) === 'desc') ? 'desc' : 'asc');
    }
    // Clean "offset" and "order_by" parameters in URL
    foreach ($_REQUEST as $key => $val) {
        if (str_end($key, '_offset')) {
            clean_string($_REQUEST[$key], 'ALPHANUM'); // keep this ALPHANUM for disable_count_query
            set_superglobals($key, $_REQUEST[$key]);
        } elseif (str_end($key, '_ORDER_BY')) {
            clean_string($_REQUEST[$key], 'SQL_COLUMN_LIST');
            set_superglobals($key, $_REQUEST[$key]);
        }
    }

    return 0;
}

// Returns TRUE if $str begins with $begin
function str_begin($str, $begin)
{
    return substr((string) $str, 0, strlen((string) $begin)) == $begin;
}

// Returns TRUE if $str ends with $end
function str_end($str, $end)
{
    return substr((string) $str, strlen((string) $str) - strlen((string) $end)) == $end;
}

/**
 * @param $uncleanString
 * @return array
 */
function securexss($uncleanString)
{
    if (is_array($uncleanString)) {
        $new = [];
        foreach ($uncleanString as $key => $val) {
            $new[$key] = securexss($val);
        }

        return $new;
    }

    static $xss_cleanup = [
        '&quot;' => '&#38;',
        '"' => '&quot;',
        "'" => '&#039;',
        '<' => '&lt;',
        '>' => '&gt;',
        '`' => '&#96;'
    ];

    $uncleanString = preg_replace(array('/javascript:/i', '/\0/', '/javascript:/i'),
        array('java script:', '', 'java script:'), (string) $uncleanString);

    $partialString = str_replace(array_keys($xss_cleanup), $xss_cleanup, $uncleanString);

    $antiXss = new AntiXSS();
    $antiXss->removeEvilAttributes(['style']);

    return $antiXss->xss_clean($partialString);
}

function securexsskey($value, $die = true)
{
    global $sugar_config;
    $matches = array();
    preg_match('/[\'"<>]/', (string) $value, $matches);
    if (!empty($matches)) {
        if ($die) {
            die("Bad data passed in; <a href=\"{$sugar_config['site_url']}\">Return to Home</a>");
        }
        unset($_REQUEST[$value]);
        unset($_POST[$value]);
        unset($_GET[$value]);
    }
}

/**
 * @param string|null $value
 * @return string
 */
function purify_html(?string $value): string {

    if (($value ?? '') === '') {
        return '';
    }

    $cleanedValue = htmlentities((string) SugarCleaner::cleanHtml($value, true));
    $decoded = html_entity_decode($cleanedValue);
    $doubleDecoded = html_entity_decode($decoded);

    if (stripos($decoded, '<script>') !== false || stripos($doubleDecoded, '<script>') !== false){
        $cleanedValue = '';
    }

    $doubleCleanedValue = htmlentities((string) SugarCleaner::cleanHtml($doubleDecoded, true));

    return $doubleCleanedValue;
}

function preprocess_param($value)
{
    if (is_string($value)) {
        $value = securexss($value);
    } elseif (is_array($value)) {
        foreach ($value as $key => $element) {
            $value[$key] = preprocess_param($element);
        }
    }

    return $value;
}

function cleanup_slashes($value)
{
    if (is_string($value)) {
        return stripslashes($value);
    }

    return $value;
}

function set_register_value($category, $name, $value)
{
    return sugar_cache_put("{$category}:{$name}", $value);
}

function get_register_value($category, $name)
{
    return sugar_cache_retrieve("{$category}:{$name}");
}

function clear_register_value($category, $name)
{
    return sugar_cache_clear("{$category}:{$name}");
}

// this function cleans id's when being imported
function convert_id($string)
{
    $errorLevelStored = error_reporting();
    error_reporting(0);

    $function = function ($matches) {
        return ord($matches[0]);
    };

    if ($function === false) {
        LoggerManager::getLogger()->warn('Function not created');
    }

    error_reporting($errorLevelStored);

    return preg_replace_callback('|[^A-Za-z0-9\-]|', $function, (string) $string);
}

/**
 * @deprecated use SugarTheme::getImage()
 */
function get_image($image, $other_attributes, $width = '', $height = '', $ext = '.gif', $alt = '')
{
    return SugarThemeRegistry::current()->getImage(basename((string) $image), $other_attributes, empty($width) ? null : $width, empty($height) ? null : $height, $ext, $alt);
}

/**
 * @deprecated use SugarTheme::getImageURL()
 */
function getImagePath($image_name)
{
    return SugarThemeRegistry::current()->getImageURL($image_name);
}

function getWebPath($relative_path)
{
    $current_theme = SugarThemeRegistry::current();
    $theme_directory = $current_theme->dirName;
    if (strpos((string) $relative_path, "themes" . DIRECTORY_SEPARATOR . $theme_directory) === false) {
        $test_path = SUGAR_PATH . DIRECTORY_SEPARATOR . "themes" . DIRECTORY_SEPARATOR . $theme_directory . DIRECTORY_SEPARATOR . $relative_path;
        if (file_exists($test_path)) {
            $resource_name = "themes" . DIRECTORY_SEPARATOR . $theme_directory . DIRECTORY_SEPARATOR . $relative_path;
        }
    }
    //if it has  a :// then it isn't a relative path
    if (substr_count((string) $relative_path, '://') > 0) {
        return $relative_path;
    }
    if (defined('TEMPLATE_URL')) {
        $relative_path = SugarTemplateUtilities::getWebPath($relative_path);
    }

    return $relative_path;
}

function getVersionedPath($path, $additional_attrs = '')
{
    if (empty($GLOBALS['sugar_config']['js_custom_version'])) {
        $GLOBALS['sugar_config']['js_custom_version'] = 1;
    }
    $js_version_key = isset($GLOBALS['js_version_key']) ? $GLOBALS['js_version_key'] : '';
    if (inDeveloperMode()) {
        static $rand;
        if (empty($rand)) {
            $rand = mt_rand();
        }
        $dev = $rand;
    } else {
        $dev = '';
    }
    if (is_array($additional_attrs)) {
        $additional_attrs = implode('|', $additional_attrs);
    }
    // cutting 2 last chars here because since md5 is 32 chars, it's always ==
    $str = substr(base64_encode(md5("$js_version_key|{$GLOBALS['sugar_config']['js_custom_version']}|$dev|$additional_attrs", true)), 0, -2);
    // remove / - it confuses some parsers
    $str = strtr($str, '/+', '-_');
    if (empty($path)) {
        return $str;
    }

    return $path . "?v=$str";
}

function getVersionedScript($path, $additional_attrs = '')
{
    return '<script type="text/javascript" src="' . getVersionedPath($path, $additional_attrs) . '"></script>';
}

function getJSPath($relative_path, $additional_attrs = '')
{
    if (defined('TEMPLATE_URL')) {
        $relative_path = SugarTemplateUtilities::getWebPath($relative_path);
    }

    return getVersionedPath($relative_path) . (!empty($additional_attrs) ? "&$additional_attrs" : '');
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function getSWFPath($relative_path, $additional_params = '')
{
    $path = $relative_path;
    if (!empty($additional_params)) {
        $path .= '?' . $additional_params;
    }
    if (defined('TEMPLATE_URL')) {
        $path = TEMPLATE_URL . '/' . $path;
    }

    return $path;
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function getSQLDate($date_str)
{
    if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', (string) $date_str, $match)) {
        if (strlen($match[2]) == 1) {
            $match[2] = '0' . $match[2];
        }
        if (strlen($match[1]) == 1) {
            $match[1] = '0' . $match[1];
        }

        return "{$match[3]}-{$match[1]}-{$match[2]}";
    } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', (string) $date_str, $match)) {
        if (strlen($match[2]) == 1) {
            $match[2] = '0' . $match[2];
        }
        if (strlen($match[1]) == 1) {
            $match[1] = '0' . $match[1];
        }

        return "{$match[3]}-{$match[1]}-{$match[2]}";
    }
    return '';
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function clone_history(&$db, $from_id, $to_id, $to_type)
{
    global $timedate;
    $old_note_id = null;
    $old_filename = null;
    require_once 'include/upload_file.php';
    $tables = array('calls' => 'Call', 'meetings' => 'Meeting', 'notes' => 'Note', 'tasks' => 'Task');

    $location = array('Email' => 'modules/Emails/Email.php',
        'Call' => 'modules/Calls/Call.php',
        'Meeting' => 'modules/Meetings/Meeting.php',
        'Note' => 'modules/Notes/Note.php',
        'Tasks' => 'modules/Tasks/Task.php',
    );

    foreach ($tables as $table => $bean_class) {
        if (!class_exists($bean_class)) {
            require_once $location[$bean_class];
        }

        $bProcessingNotes = false;
        if ($table == 'notes') {
            $bProcessingNotes = true;
        }
        $query = "SELECT id FROM $table WHERE parent_id='$from_id'";
        $results = $db->query($query);
        while ($row = $db->fetchByAssoc($results)) {
            //retrieve existing record.
            $bean = new $bean_class();
            $bean->retrieve($row['id']);
            //process for new instance.
            if ($bProcessingNotes) {
                $old_note_id = $row['id'];
                $old_filename = $bean->filename;
            }
            $bean->id = null;
            $bean->parent_id = $to_id;
            $bean->parent_type = $to_type;
            if ($to_type == 'Contacts' && in_array('contact_id', $bean->column_fields)) {
                $bean->contact_id = $to_id;
            }
            $bean->update_date_modified = false;
            $bean->update_modified_by = false;
            if (isset($bean->date_modified)) {
                $bean->date_modified = $timedate->to_db($bean->date_modified);
            }
            if (isset($bean->date_entered)) {
                $bean->date_entered = $timedate->to_db($bean->date_entered);
            }
            //save
            $new_id = $bean->save();

            //duplicate the file now. for notes.
            if ($bProcessingNotes && !empty($old_filename)) {
                UploadFile::duplicate_file($old_note_id, $new_id, $old_filename);
            }
            //reset the values needed for attachment duplication.
            $old_note_id = null;
            $old_filename = null;
        }
    }
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function values_to_keys($array)
{
    $new_array = array();
    if (!is_array($array)) {
        return $new_array;
    }
    foreach ($array as $arr) {
        $new_array[$arr] = $arr;
    }

    return $new_array;
}

/**
 * @param $db
 * @param array $tables
 * @param $from_column
 * @param $from_id
 * @param $to_id
 */
function clone_relationship(&$db, $tables, $from_column = null, $from_id = null, $to_id = null)
{
    foreach ((array) $tables as $table) {
        if ($table == 'emails_beans') {
            $query = "SELECT * FROM $table WHERE $from_column='$from_id' and bean_module='Leads'";
        } else {
            $query = "SELECT * FROM $table WHERE $from_column='$from_id'";
        }
        $results = $db->query($query);
        while ($row = $db->fetchByAssoc($results)) {
            $query = "INSERT INTO $table ";
            $names = '';
            $values = '';
            $row[$from_column] = $to_id;
            $row['id'] = create_guid();
            if ($table == 'emails_beans') {
                $row['bean_module'] == 'Contacts';
            }

            foreach ($row as $name => $value) {
                if (empty($names)) {
                    $names .= $name;
                    $values .= "'$value'";
                } else {
                    $names .= ', ' . $name;
                    $values .= ", '$value'";
                }
            }
            $query .= "($names)	VALUES ($values)";
            $db->query($query);
        }
    }
}

function get_unlinked_email_query($type, $bean)
{
    global $current_user;

    $return_array = [];
    $return_array['select'] = 'SELECT emails.id ';
    $return_array['from'] = 'FROM emails ';
    $return_array['where'] = '';
    $return_array['join'] = " JOIN (select DISTINCT email_id from emails_email_addr_rel eear

	join email_addr_bean_rel eabr on eabr.bean_id ='$bean->id' and eabr.bean_module = '$bean->module_dir' and
	eabr.email_address_id = eear.email_address_id and eabr.deleted=0
	where eear.deleted=0 and eear.email_id not in
	(select eb.email_id from emails_beans eb where eb.bean_module ='$bean->module_dir' and eb.bean_id = '$bean->id')
	) derivedemails on derivedemails.email_id = emails.id";
    $return_array['join_tables'][0] = '';

    if (isset($type) && ! empty($type['return_as_array'])) {
        return $return_array;
    }

    return $return_array['select'] . $return_array['from'] . $return_array['where'] . $return_array['join'];
}

// fn

function get_emails_by_assign_or_link($params)
{
    $relation = $params['link'];
    $bean = $GLOBALS['app']->controller->bean;
    if (empty($bean->$relation)) {
        $bean->load_relationship($relation);
    }
    if (empty($bean->$relation)) {
        $GLOBALS['log']->error("Bad relation '$relation' for bean '{$bean->object_name}' id '{$bean->id}'");

        return array();
    }
    $rel_module = $bean->$relation->getRelatedModuleName();
    $rel_join = $bean->$relation->getJoin(array(
        'join_table_alias' => 'link_bean',
        'join_table_link_alias' => 'linkt',
    ));
    $rel_join = str_replace("{$bean->table_name}.id", "'{$bean->id}'", (string) $rel_join);
    $return_array = [];
    $return_array['select'] = 'SELECT DISTINCT emails.id ';
    $return_array['from'] = 'FROM emails ';

    $return_array['join'] = array();

    // directly assigned emails
    $return_array['join'][] = "
        SELECT
            eb.email_id,
            'direct' source
        FROM
            emails_beans eb
        WHERE
            eb.bean_module = '{$bean->module_dir}'
            AND eb.bean_id = '{$bean->id}'
            AND eb.deleted=0
    ";

    // Related by directly by email
    $return_array['join'][] = "
        SELECT DISTINCT
            eear.email_id,
            'relate' source
        FROM
            emails_email_addr_rel eear
        INNER JOIN
            email_addr_bean_rel eabr
        ON
            eabr.bean_id ='{$bean->id}'
            AND eabr.bean_module = '{$bean->module_dir}'
            AND eabr.email_address_id = eear.email_address_id
            AND eabr.deleted=0
        WHERE
            eear.deleted=0
    ";

    $showEmailsOfRelatedContacts = empty($bean->field_defs[$relation]['hide_history_contacts_emails']);
    if (!empty($GLOBALS['sugar_config']['hide_history_contacts_emails']) && isset($GLOBALS['sugar_config']['hide_history_contacts_emails'][$bean->module_name])) {
        $showEmailsOfRelatedContacts = empty($GLOBALS['sugar_config']['hide_history_contacts_emails'][$bean->module_name]);
    }
    if ($showEmailsOfRelatedContacts) {
        // Assigned to contacts
        $return_array['join'][] = "
            SELECT DISTINCT
                eb.email_id,
                'contact' source
            FROM
                emails_beans eb
            $rel_join AND link_bean.id = eb.bean_id
            WHERE
                eb.bean_module = '$rel_module'
                AND eb.deleted=0
        ";
        // Related by email to linked contact
        $return_array['join'][] = "
            SELECT DISTINCT
                eear.email_id,
                'relate_contact' source
            FROM
                emails_email_addr_rel eear
            INNER JOIN
                email_addr_bean_rel eabr
            ON
                eabr.email_address_id=eear.email_address_id
                AND eabr.bean_module = '$rel_module'
                AND eabr.deleted=0
            $rel_join AND link_bean.id = eabr.bean_id
            WHERE
                eear.deleted=0
        ";
    }

    $return_array['join'] = ' INNER JOIN (' . implode(' UNION ', $return_array['join']) . ') email_ids ON emails.id=email_ids.email_id ';

    $return_array['where'] = ' WHERE emails.deleted=0 ';

    //$return_array['join'] = '';
    $return_array['join_tables'][0] = '';

    if ($bean->object_name == 'Case' && !empty($bean->case_number)) {
        $where = str_replace('%1', $bean->case_number, (string) $bean->getEmailSubjectMacro());
        $return_array['where'] .= "\n AND (email_ids.source = 'direct' OR emails.name LIKE '%$where%')";
    }

    return $return_array;
}

/**
 * Check to see if the number is empty or non-zero.
 *
 * @param $value
 *
 * @return bool
 * */
function number_empty($value)
{
    return empty($value) && $value != '0';
}

/**
 * @param bool $add_blank
 * @param $bean_name
 * @param $display_columns
 * @param string $where
 * @param string $order_by
 * @param bool $blank_is_none
 * @return array
 */
function get_bean_select_array(
    $add_blank,
    $bean_name = null,
    $display_columns = null,
    $where = '',
    $order_by = '',
    $blank_is_none = false
) {
    global $beanFiles;

    // set $add_blank = true by default
    if (!is_bool($add_blank)) {
        $add_blank = true;
    }

    require_once $beanFiles[$bean_name];
    $focus = new $bean_name();
    $user_array = array();

    $key = ($bean_name == 'EmailTemplate') ? $bean_name : $bean_name . $display_columns . $where . $order_by;
    $user_array = get_register_value('select_array', $key);
    if (!$user_array) {
        $db = DBManagerFactory::getInstance();

        $temp_result = array();
        $query = "SELECT {$focus->table_name}.id, {$display_columns} as display from {$focus->table_name} ";
        $query .= 'where ';
        if ($where != '') {
            $query .= $where . ' AND ';
        }

        $query .= " {$focus->table_name}.deleted=0";

        $accessWhere = $focus->buildAccessWhere('list');
        if (!empty($accessWhere)) {
            $query .= ' AND ' . $accessWhere;
        }

        if ($order_by != '') {
            $query .= " order by {$focus->table_name}.{$order_by}";
        }

        $GLOBALS['log']->debug("get_user_array query: $query");
        $result = $db->query($query, true, 'Error filling in user array: ');

        if ($add_blank == true) {
            // Add in a blank row
            if ($blank_is_none == true) { // set 'blank row' to "--None--"
                global $app_strings;
                $temp_result[''] = $app_strings['LBL_NONE'];
            } else {
                $temp_result[''] = '';
            }
        }

        // Get the id and the name.
        while ($row = $db->fetchByAssoc($result)) {
            $temp_result[$row['id']] = $row['display'];
        }

        $user_array = $temp_result;
        set_register_value('select_array', $key, $temp_result);
    }

    return $user_array;
}

/**
 * @param unknown_type $listArray
 */
// function parse_list_modules
// searches a list for items in a user's allowed tabs and returns an array that removes unallowed tabs from list
function parse_list_modules(&$listArray)
{
    global $modListHeader;
    $returnArray = array();

    foreach ($listArray as $optionName => $optionVal) {
        if (array_key_exists($optionName, $modListHeader)) {
            $returnArray[$optionName] = $optionVal;
        }

        // special case for projects
        if (array_key_exists('Project', $modListHeader)) {
            $returnArray['ProjectTask'] = $listArray['ProjectTask'];
        }
    }
    $acldenied = ACLController::disabledModuleList($listArray, false);
    foreach ($acldenied as $denied) {
        unset($returnArray[$denied]);
    }
    asort($returnArray);

    return $returnArray;
}

function display_notice($msg = false)
{
    global $error_notice;
    //no error notice - lets just display the error to the user
    if (!isset($error_notice)) {
        echo '<br>' . $msg . '<br>';
    } else {
        $error_notice .= $msg . '<br>';
    }
}

/**
 * Checks if it is a number that at least has the plus at the beginning.
 *
 * @deprecated No longer used, will be removed without replacement in SuiteCRM 7.12.
 */
function skype_formatted($number)
{
    //kbrill - BUG #15375
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'Popup') {
        return false;
    }
    return substr((string) $number, 0, 1) == '+' || substr((string) $number, 0, 2) == '00' || substr((string) $number, 0, 3) == '011';

    //	return substr($number, 0, 1) == '+' || substr($number, 0, 2) == '00' || substr($number, 0, 2) == '011';
}

/**
 * @deprecated No longer used, will be removed without replacement in SuiteCRM 7.12.
 */
function format_skype($number)
{
    return preg_replace('/[^\+0-9]/', '', (string) $number);
}

function insert_charset_header()
{
    header('Content-Type: text/html; charset=UTF-8');
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function getCurrentURL()
{
    $href = 'http:';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $href = 'https:';
    }

    $href .= '//' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];

    return $href;
}

function javascript_escape($str)
{
    $new_str = '';

    for ($i = 0; $i < strlen((string) $str); ++$i) {
        if (ord(substr((string) $str, $i, 1)) == 10) {
            $new_str .= '\n';
        } elseif (ord(substr((string) $str, $i, 1)) == 13) {
            $new_str .= '\r';
        } else {
            $new_str .= $str[$i];
        }
    }

    $new_str = str_replace("'", "\\'", $new_str);

    return $new_str;
}

function js_escape($str, $keep = true)
{
    $str = html_entity_decode(str_replace('\\', '', (string) $str), ENT_QUOTES);

    if ($keep) {
        $str = javascript_escape($str);
    } else {
        $str = str_replace("'", ' ', $str);
        $str = str_replace('"', ' ', $str);
    }

    return $str;

    //end function js_escape
}

function br2nl($str)
{
    $regex = '#<[^>]+br.+?>#i';
    preg_match_all($regex, (string) $str, $matches);

    foreach ($matches[0] as $match) {
        $str = str_replace($match, '<br>', (string) $str);
    }

    $brs = array('<br>', '<br/>', '<br />');
    $str = str_replace("\r\n", "\n", (string) $str); // make from windows-returns, *nix-returns
    $str = str_replace("\n\r", "\n", $str); // make from windows-returns, *nix-returns
    $str = str_replace("\r", "\n", $str); // make from windows-returns, *nix-returns
    $str = str_ireplace($brs, "\n", $str); // to retrieve it

    return $str;
}

/**
 * Private helper function for displaying the contents of a given variable.
 * This function is only intended to be used for SugarCRM internal development.
 * The ppd stands for Pre Print Die.
 * @deprecated This function is unused and will be removed in a future release.
 */
function _ppd($mixed)
{
}

/**
 * Private helper function for displaying the contents of a given variable in
 * the Logger. This function is only intended to be used for SugarCRM internal
 * development. The pp stands for Pre Print.
 *
 * @param $mixed var to print_r()
 * @param $die boolean end script flow
 * @param $displayStackTrace also show stack trace
 * @deprecated This function is unused and will be removed in a future release.
 */
function _ppl($mixed, $die = false, $displayStackTrace = false, $loglevel = 'fatal')
{
    if (!isset($GLOBALS['log']) || empty($GLOBALS['log'])) {
        $GLOBALS['log'] = LoggerManager:: getLogger();
    }

    $mix = print_r($mixed, true); // send print_r() output to $mix
    $stack = debug_backtrace();

    $GLOBALS['log']->$loglevel('------------------------------ _ppLogger() output start -----------------------------');
    $GLOBALS['log']->$loglevel($mix);
    if ($displayStackTrace) {
        foreach ($stack as $position) {
            $GLOBALS['log']->$loglevel($position['file'] . "({$position['line']})");
        }
    }

    $GLOBALS['log']->$loglevel('------------------------------ _ppLogger() output end -----------------------------');
    $GLOBALS['log']->$loglevel('------------------------------ _ppLogger() file: ' . $stack[0]['file'] . ' line#: ' . $stack[0]['line'] . '-----------------------------');

    if ($die) {
        die();
    }
}

/**
 * private helper function to quickly show the major, direct, field attributes of a given bean.
 * The ppf stands for Pre[formatted] Print Focus [object].
 *
 * @param object bean The focus bean
 * @deprecated This function is unused and will be removed in a future release.
 */
function _ppf($bean, $die = false)
{
}

/**
 * Private helper function for displaying the contents of a given variable.
 * This function is only intended to be used for SugarCRM internal development.
 * The pp stands for Pre Print.
 * @deprecated This function is unused and will be removed in a future release.
 */
function _pp($mixed)
{
}

/**
 * Private helper function for displaying the contents of a given variable.
 * This function is only intended to be used for SugarCRM internal development.
 * The pp stands for Pre Print.
 * @deprecated This function is unused and will be removed in a future release.
 */
function _pstack_trace($mixed = null)
{
}

/**
 * Private helper function for displaying the contents of a given variable.
 * This function is only intended to be used for SugarCRM internal development.
 * The pp stands for Pre Print Trace.
 * @deprecated This function is unused and will be removed in a future release.
 */
function _ppt($mixed, $textOnly = false)
{
}

/**
 * Private helper function for displaying the contents of a given variable.
 * This function is only intended to be used for SugarCRM internal development.
 * The pp stands for Pre Print Trace Die.
 * @deprecated This function is unused and will be removed in a future release.
 */
function _pptd($mixed)
{
}

/**
 * Private helper function for decoding javascript UTF8
 * This function is only intended to be used for SugarCRM internal development.
 * @deprecated This function is unused and will be removed in a future release.
 */
function decodeJavascriptUTF8($str)
{
}

/**
 * Will check if a given PHP version string is accepted or not.
 * Do not pass in any pararameter to default to a check against the
 * current environment's PHP version.
 *
 * @param string $sys_php_version Version to check against, defaults to the current environment's.
 * @param string $min_php_version Minimum version to check against. Defaults to the SUITECRM_PHP_MIN_VERSION constant.
 * @param string $rec_php_version Recommended version. Defaults to the SUITECRM_PHP_REC_VERSION constant
 *
 * @return integer 1 if version is greater than the recommended PHP version,
 *   0 if version is between minimun and recomended PHP versions,
 *   -1 otherwise (less than minimum or buggy version)
 */
function check_php_version($sys_php_version = '', $min_php_version = '', $rec_php_version = '')
{
    if ($sys_php_version === '') {
        $sys_php_version = constant('PHP_VERSION');
    }
    if ($min_php_version === '') {
        $min_php_version = constant('SUITECRM_PHP_MIN_VERSION');
    }
    if ($rec_php_version === '') {
        $rec_php_version = constant('SUITECRM_PHP_REC_VERSION');
    }

    // versions below MIN_PHP_VERSION are not accepted, so return early.
    if (version_compare($sys_php_version, $min_php_version, '<') === true) {
        return -1;
    }

    // If the checked version is between the minimum and recommended versions, return 0.
    if (version_compare($sys_php_version, $rec_php_version, '<') === true) {
        return 0;
    }

    // Everything else is fair game
    return 1;
}

/**
 * Will check if a given IIS version string is supported (tested on this ver),
 * unsupported (results unknown), or invalid (something will break on this
 * ver).
 *
 * @return 1 implies supported, 0 implies unsupported, -1 implies invalid
 */
function check_iis_version($sys_iis_version = '')
{
    $server_software = $_SERVER['SERVER_SOFTWARE'];
    $iis_version = '';
    if (strpos((string) $server_software, 'Microsoft-IIS') !== false && preg_match_all("/^.*\/(\d+\.?\d*)$/", (string) $server_software, $out)) {
        $iis_version = $out[1][0];
    }

    $sys_iis_version = empty($sys_iis_version) ? $iis_version : $sys_iis_version;

    // versions below $min_considered_iis_version considered invalid by default,
    // versions equal to or above this ver will be considered depending
    // on the rules that follow
    $min_considered_iis_version = '6.0';

    // only the supported versions,
    // should be mutually exclusive with $invalid_iis_versions
    $supported_iis_versions = array('6.0', '7.0');
    $unsupported_iis_versions = array();
    $invalid_iis_versions = array('5.0');

    // default unsupported
    $retval = 0;

    // versions below $min_considered_iis_version are invalid
    if (1 == version_compare($sys_iis_version, $min_considered_iis_version, '<')) {
        $retval = -1;
    }

    // supported version check overrides default unsupported
    foreach ($supported_iis_versions as $ver) {
        if (1 == version_compare($sys_iis_version, $ver, 'eq') || strpos((string) $sys_iis_version, $ver) !== false) {
            $retval = 1;
            break;
        }
    }

    // unsupported version check overrides default unsupported
    foreach ($unsupported_iis_versions as $ver) {
        if (1 == version_compare($sys_iis_version, $ver, 'eq') && strpos((string) $sys_iis_version, (string) $ver) !== false) {
            $retval = 0;
            break;
        }
    }

    // invalid version check overrides default unsupported
    foreach ($invalid_iis_versions as $ver) {
        if (1 == version_compare($sys_iis_version, $ver, 'eq') && strpos((string) $sys_iis_version, $ver) !== false) {
            $retval = -1;
            break;
        }
    }

    return $retval;
}

function pre_login_check()
{
    global $action, $login_error;
    if (!empty($action) && $action == 'Login') {
        if (!empty($login_error)) {
            $login_error = htmlentities((string) $login_error);
            $login_error = str_replace(array('&lt;pre&gt;', '&lt;/pre&gt;', "\r\n", "\n"), '<br>', $login_error);
            $_SESSION['login_error'] = $login_error;
            echo '<script>
						function set_focus() {}
						if(document.getElementById("post_error")) {
							document.getElementById("post_error").innerHTML="' . $login_error . '";
							document.getElementById("cant_login").value=1;
							document.getElementById("login_button").disabled = true;
							document.getElementById("user_name").disabled = true;
						}
						</script>';
        }
    }
}

/**
 * Like exit() but will throw an exception if called during tests.
 *
 * This is to avoid exit() stopping the test suite without us noticing.
 *
 * @param int|string $status
 * @throws Exception
 */
function suite_exit($status = 0)
{
    if (defined('SUITE_PHPUNIT_RUNNER'))
        throw new Exception("exit() called during tests with status: $status");
    else
        exit($status);
}

function sugar_cleanup($exit = false)
{
    static $called = false;
    if ($called) {
        return;
    }
    $called = true;
    set_include_path(realpath(__DIR__ . '/..') . PATH_SEPARATOR . get_include_path());
    chdir(realpath(__DIR__ . '/..'));
    global $sugar_config;
    require_once 'include/utils/LogicHook.php';
    LogicHook::initialize();
    $GLOBALS['logic_hook']->call_custom_logic('', 'server_round_trip');

    //added this check to avoid errors during install.
    if (empty($sugar_config['dbconfig'])) {
        if ($exit) {
            suite_exit();
        }
        return;
    }

    if (!class_exists('Tracker', true)) {
        require_once 'modules/Trackers/Tracker.php';
    }
    Tracker::logPage();
    // Now write the cached tracker_queries
    if (!empty($GLOBALS['savePreferencesToDB']) && $GLOBALS['savePreferencesToDB']) {
        if (isset($GLOBALS['current_user']) && $GLOBALS['current_user'] instanceof User) {
            $GLOBALS['current_user']->savePreferencesToDB();
        }
    }

    //check to see if this is not an `ajax call AND the user preference error flag is set
    if (
            (isset($_SESSION['USER_PREFRENCE_ERRORS']) && $_SESSION['USER_PREFRENCE_ERRORS']) && ($_REQUEST['action'] != 'modulelistmenu' && $_REQUEST['action'] != 'DynamicAction') && ($_REQUEST['action'] != 'favorites' && $_REQUEST['action'] != 'DynamicAction') && (empty($_REQUEST['to_pdf']) || !$_REQUEST['to_pdf']) && (empty($_REQUEST['sugar_body_only']) || !$_REQUEST['sugar_body_only'])
    ) {
        global $app_strings;
        //this is not an ajax call and the user preference error flag is set, so reset the flag and print js to flash message
        $err_mess = $app_strings['ERROR_USER_PREFS'];
        $_SESSION['USER_PREFRENCE_ERRORS'] = false;
        echo "
		<script>
			ajaxStatus.flashStatus('$err_mess',7000);
		</script>";
    }

    pre_login_check();
    if (class_exists('DBManagerFactory')) {
        $db = DBManagerFactory::getInstance();
        $db->disconnect();
        if ($exit) {
            suite_exit();
        }
    }
}

register_shutdown_function('sugar_cleanup');

/*
  check_logic_hook - checks to see if your custom logic is in the logic file
  if not, it will add it. If the file isn't built yet, it will create the file

 */

function check_logic_hook_file($module_name, $event, $action_array)
{
    require_once 'include/utils/logic_utils.php';
    $add_logic = false;

    if (file_exists("custom/modules/$module_name/logic_hooks.php")) {
        $hook_array = get_hook_array($module_name);

        if (check_existing_element($hook_array, $event, $action_array) == true) {
            //the hook at hand is present, so do nothing
        } else {
            $add_logic = true;

            $logic_count = 0;
            if (!empty($hook_array[$event])) {
                $logic_count = is_countable($hook_array[$event]) ? count($hook_array[$event]) : 0;
            }

            if ($action_array[0] == '') {
                $action_array[0] = $logic_count + 1;
            }
            $hook_array[$event][] = $action_array;
        }
        //end if the file exists already
    } else {
        $add_logic = true;
        if ($action_array[0] == '') {
            $action_array[0] = 1;
        }
        $hook_array = array();
        $hook_array[$event][] = $action_array;
        //end if else file exists already
    }
    if ($add_logic == true) {

        //reorder array by element[0]
        //$hook_array = reorder_array($hook_array, $event);
        //!!!Finish this above TODO

        $new_contents = replace_or_add_logic_type($hook_array);
        write_logic_file($module_name, $new_contents);

        //end if add_element is true
    }

    //end function check_logic_hook_file
}

function remove_logic_hook($module_name, $event, $action_array)
{
    require_once 'include/utils/logic_utils.php';
    $add_logic = false;

    if (file_exists('custom/modules/' . $module_name . '/logic_hooks.php')) {
        // The file exists, let's make sure the hook is there
        $hook_array = get_hook_array($module_name);

        if (check_existing_element($hook_array, $event, $action_array) == true) {
            // The hook is there, time to take it out.

            foreach ($hook_array[$event] as $i => $hook) {
                // We don't do a full comparison below just in case the filename changes
                if ($hook[0] == $action_array[0] && $hook[1] == $action_array[1] && $hook[3] == $action_array[3] && $hook[4] == $action_array[4]
                ) {
                    unset($hook_array[$event][$i]);
                }
            }

            $new_contents = replace_or_add_logic_type($hook_array);
            write_logic_file($module_name, $new_contents);
        }
    }
}

function display_stack_trace($textOnly = false)
{
    $stack = debug_backtrace();

    echo "\n\n display_stack_trace caller, file: " . $stack[0]['file'] . ' line#: ' . $stack[0]['line'];

    if (!$textOnly) {
        echo '<br>';
    }

    $first = true;
    $out = '';

    foreach ($stack as $item) {
        $file = '';
        $class = '';
        $line = '';
        $function = '';

        if (isset($item['file'])) {
            $file = $item['file'];
        }
        if (isset($item['class'])) {
            $class = $item['class'];
        }
        if (isset($item['line'])) {
            $line = $item['line'];
        }
        if (isset($item['function'])) {
            $function = $item['function'];
        }

        if (!$first) {
            if (!$textOnly) {
                $out .= '<font color="black"><b>';
            }

            $out .= $file;

            if (!$textOnly) {
                $out .= '</b></font><font color="blue">';
            }

            $out .= "[L:{$line}]";

            if (!$textOnly) {
                $out .= '</font><font color="red">';
            }

            $out .= "({$class}:{$function})";

            if (!$textOnly) {
                $out .= '</font><br>';
            } else {
                $out .= "\n";
            }
        } else {
            $first = false;
        }
    }

    echo $out;
    return $out;
}

function StackTraceErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
    $error_msg = " $errstr occurred in <b>$errfile</b> on line $errline [" . date('Y-m-d H:i:s') . ']';

    switch ($errno) {
//        case 2048:
//            return; //depricated we have lots of these ignore them
        case E_USER_NOTICE:
            $type = 'User notice';
            // no break
        case E_NOTICE:
            $type = 'Notice';
            $halt_script = false;
            break;


        case E_USER_WARNING:
            $type = 'User warning';
            // no break
        case E_COMPILE_WARNING:
            $type = 'Compile warning';
            // no break
        case E_CORE_WARNING:
            $type = 'Core warning';
            // no break
        case E_WARNING:
            $type = 'Warning';
            $halt_script = false;
            break;

        case E_USER_ERROR:
            $type = 'User error';
            // no break
        case E_COMPILE_ERROR:
            $type = 'Compile error';
            // no break
        case E_CORE_ERROR:
            $type = 'Core error';
            // no break
        case E_ERROR:
            $type = 'Error';
            $halt_script = true;
            break;

        case E_PARSE:
            $type = 'Parse Error';
            $halt_script = true;
            break;

        default:
            //don't know what it is might not be so bad
            $type = "Unknown Error ($errno)";
            $halt_script = false;
            break;
    }
    $error_msg = '<b>[' . $type . ']</b> ' . $error_msg;
    echo $error_msg;
    $trace = display_stack_trace();
    \SuiteCRM\ErrorMessage::log("Catch an error: $error_msg \nTrace info:\n" . $trace);
    if ($halt_script) {
        exit(1);
    }
}

if (isset($sugar_config['stack_trace_errors']) && $sugar_config['stack_trace_errors']) {
    set_error_handler('StackTraceErrorHandler');
}

function get_sub_cookies($name)
{
    $cookies = array();
    if (isset($_COOKIE[$name])) {
        $subs = explode('#', $_COOKIE[$name]);
        foreach ($subs as $cookie) {
            if (!empty($cookie)) {
                $cookie = explode('=', $cookie);

                $cookies[$cookie[0]] = $cookie[1];
            }
        }
    }

    return $cookies;
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function mark_delete_components($sub_object_array, $run_second_level = false, $sub_sub_array = '')
{
    if (!empty($sub_object_array)) {
        foreach ($sub_object_array as $sub_object) {

            //run_second level is set to true if you need to remove sub-sub components
            if ($run_second_level == true) {
                mark_delete_components($sub_object->get_linked_beans($sub_sub_array['rel_field'], $sub_sub_array['rel_module']));

                //end if run_second_level is true
            }
            $sub_object->mark_deleted($sub_object->id);
            //end foreach sub component
        }
        //end if this is not empty
    }

    //end function mark_delete_components
}

/**
 * Translates php.ini memory values into bytes.
 * For example, an input value of '8M' will return 8388608.
 * 8M is 8 mebibytes, 1 mebibyte is 1,048,576 bytes or 2^20 bytes.
 *
 * @param string $val A string like '8M'.
 * @return integer The number of bytes represented by that string.
 */
function return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $val = preg_replace("/[^0-9,.]/", "", $val);

    switch ($last) {
        case 'g':
            $val *= 1024;
            // no break
        case 'm':
            $val *= 1024;
            // no break
        case 'k':
            $val *= 1024;
    }

    return $val;
}

/**
 * Adds the href HTML tags around any URL in the $string.
 */
function url2html($string)
{
    $return_string = preg_replace('/(\w+:\/\/)(\S+)/', ' <a href="\\1\\2" target="_new"  style="font-weight: normal;">\\1\\2</a>', (string) $string);

    return $return_string;
}

/**
 * tries to determine whether the Host machine is a Windows machine.
 */
function is_windows()
{
    static $is_windows = null;
    if (!isset($is_windows)) {
        $is_windows = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
    }

    return $is_windows;
}

/**
 * equivalent for windows filesystem for PHP's is_writable().
 *
 * @param string file Full path to the file/dir
 *
 * @return bool true if writable
 */
function is_writable_windows($file)
{
    if ($file[strlen((string) $file) - 1] == '/') {
        return is_writable_windows($file . uniqid(mt_rand()) . '.tmp');
    }

    // the assumption here is that Windows has an inherited permissions scheme
    // any file that is a descendant of an unwritable directory will inherit
    // that property and will trigger a failure below.
    if (is_dir($file)) {
        return true;
    }

    $file = str_replace('/', '\\', (string) $file);

    if (file_exists($file)) {
        if (!($f = @sugar_fopen($file, 'r+'))) {
            return false;
        }
        fclose($f);

        return true;
    }

    if (!($f = @sugar_fopen($file, 'w'))) {
        return false;
    }
    fclose($f);
    unlink($file);

    return true;
}

/**
 * best guesses Timezone based on webserver's TZ settings.
 */
function lookupTimezone($userOffset = 0)
{
    return TimeDate::guessTimezone($userOffset);
}

function convert_module_to_singular($module_array)
{
    global $beanList;

    foreach ($module_array as $key => $value) {
        if (!empty($beanList[$value])) {
            $module_array[$key] = $beanList[$value];
        }

        if ($value == 'Cases') {
            $module_array[$key] = 'Case';
        }
        if ($key == 'projecttask') {
            $module_array['ProjectTask'] = 'Project Task';
            unset($module_array[$key]);
        }
    }

    return $module_array;

    //end function convert_module_to_singular
}

/*
 * Given the bean_name which may be plural or singular return the singular
 * bean_name. This is important when you need to include files.
 */

function get_singular_bean_name($bean_name)
{
    global $beanFiles, $beanList;
    if (array_key_exists($bean_name, $beanList)) {
        return $beanList[$bean_name];
    }
    return $bean_name;
}

/*
 * Given the potential module name (singular name, renamed module name)
 * Return the real internal module name.
 */

function get_module_from_singular($singular)
{

    // find the internal module name for a singular name
    if (isset($GLOBALS['app_list_strings']['moduleListSingular'])) {
        $singular_modules = $GLOBALS['app_list_strings']['moduleListSingular'];

        foreach ($singular_modules as $mod_name => $sin_name) {
            if ($singular == $sin_name && $mod_name != $sin_name) {
                return $mod_name;
            }
        }
    }

    // find the internal module name for a renamed module
    if (isset($GLOBALS['app_list_strings']['moduleList'])) {
        $moduleList = $GLOBALS['app_list_strings']['moduleList'];

        foreach ($moduleList as $mod_name => $name) {
            if ($singular == $name && $mod_name != $name) {
                return $mod_name;
            }
        }
    }

    // if it's not a singular name, nor a renamed name, return the original value
    return $singular;
}

function get_label($label_tag, $temp_module_strings)
{
    global $app_strings;
    if (!empty($temp_module_strings[$label_tag])) {
        $label_name = $temp_module_strings[$label_tag];
    } else {
        if (!empty($app_strings[$label_tag])) {
            $label_name = $app_strings[$label_tag];
        } else {
            $label_name = $label_tag;
        }
    }

    return $label_name;

    //end function get_label
}

function search_filter_rel_info(&$focus, $tar_rel_module, $relationship_name)
{
    $rel_list = array();

    foreach ($focus->relationship_fields as $rel_key => $rel_value) {
        if ($rel_value == $relationship_name) {
            $temp_bean = BeanFactory::getBean($tar_rel_module, $focus->$rel_key);
            if ($temp_bean) {
                $rel_list[] = $temp_bean;

                return $rel_list;
            }
        }
    }

    foreach ($focus->field_defs as $field_name => $field_def) {
        //Check if the relationship_name matches a "relate" field
        if (!empty($field_def['type']) && $field_def['type'] == 'relate' && !empty($field_def['id_name']) && !empty($focus->field_defs[$field_def['id_name']]) && !empty($focus->field_defs[$field_def['id_name']]['relationship']) && $focus->field_defs[$field_def['id_name']]['relationship'] == $relationship_name
        ) {
            $temp_bean = BeanFactory::getBean($tar_rel_module, $field_def['id_name']);
            if ($temp_bean) {
                $rel_list[] = $temp_bean;

                return $rel_list;
            }
            //Check if the relationship_name matches a "link" in a relate field
        } elseif (!empty($rel_value['link']) && !empty($rel_value['id_name']) && $rel_value['link'] == $relationship_name) {
            $temp_bean = BeanFactory::getBean($tar_rel_module, $rel_value['id_name']);
            if ($temp_bean) {
                $rel_list[] = $temp_bean;

                return $rel_list;
            }
        }
    }

    // special case for unlisted parent-type relationships
    if (!empty($focus->parent_type) && $focus->parent_type == $tar_rel_module && !empty($focus->parent_id)) {
        $temp_bean = BeanFactory::getBean($tar_rel_module, $focus->parent_id);
        if ($temp_bean) {
            $rel_list[] = $temp_bean;

            return $rel_list;
        }
    }

    return $rel_list;

    //end function search_filter_rel_info
}

/**
 * @param $module_name
 * @return mixed
 */
function get_module_info($module_name)
{
    return BeanFactory::getBean($module_name);
}

/**
 * In order to have one place to obtain the proper object name. aCase for example causes issues throughout the application.
 *
 * @param string $moduleName
 */
function get_valid_bean_name($module_name)
{
    global $beanList;

    $vardef_name = $beanList[$module_name];
    if ($vardef_name == 'aCase') {
        $bean_name = 'Case';
    } else {
        $bean_name = $vardef_name;
    }

    return $bean_name;
}

function checkAuthUserStatus()
{

    //authUserStatus();
}

/**
 * This function returns an array of phpinfo() results that can be parsed and
 * used to figure out what version we run, what modules are compiled in, etc.
 *
 * @param   $level int        info level constant (1,2,4,8...64);
 *
 * @return $returnInfo array    array of info about the PHP environment
 *
 * @author    original by "code at adspeed dot com" Fron php.net
 * @author    customized for Sugar by Chris N.
 */
function getPhpInfo($level = -1)
{
    /* 	Name (constant)		Value	Description
      INFO_GENERAL		1		The configuration line, php.ini location, build date, Web Server, System and more.
      INFO_CREDITS		2		PHP Credits. See also phpcredits().
      INFO_CONFIGURATION	4		Current Local and Master values for PHP directives. See also ini_get().
      INFO_MODULES		8		Loaded modules and their respective settings. See also get_loaded_extensions().
      INFO_ENVIRONMENT	16		Environment Variable information that's also available in $_ENV.
      INFO_VARIABLES		32		Shows all predefined variables from EGPCS (Environment, GET, POST, Cookie, Server).
      INFO_LICENSE		64		PHP License information. See also the license FAQ.
      INFO_ALL			-1		Shows all of the above. This is the default value.
     */
    ob_start();
    phpinfo($level);
    $phpinfo = ob_get_contents();
    ob_end_clean();

    $phpinfo = strip_tags($phpinfo, '<h1><h2><th><td>');
    $phpinfo = preg_replace('/<th[^>]*>([^<]+)<\/th>/', '<info>\\1</info>', $phpinfo);
    $phpinfo = preg_replace('/<td[^>]*>([^<]+)<\/td>/', '<info>\\1</info>', $phpinfo);
    $parsedInfo = preg_split('/(<h.?>[^<]+<\/h.>)/', $phpinfo, -1, PREG_SPLIT_DELIM_CAPTURE);
    $match = '';
    $version = '';
    $returnInfo = array();

    if (preg_match('/<h1 class\=\"p\">PHP Version ([^<]+)<\/h1>/', $phpinfo, $version)) {
        $returnInfo['PHP Version'] = $version[1];
    }
    $parsedInfoCount = count($parsedInfo);

    for ($i = 1; $i < $parsedInfoCount; ++$i) {
        if (preg_match('/<h.>([^<]+)<\/h.>/', $parsedInfo[$i], $match)) {
            $vName = trim($match[1]);
            $parsedInfo2 = explode("\n", $parsedInfo[$i + 1]);

            foreach ($parsedInfo2 as $vOne) {
                $vPat = '<info>([^<]+)<\/info>';
                $vPat3 = "/$vPat\s*$vPat\s*$vPat/";
                $vPat2 = "/$vPat\s*$vPat/";

                if (preg_match($vPat3, $vOne, $match)) { // 3cols
                    $returnInfo[$vName][trim($match[1])] = array(trim($match[2]), trim($match[3]));
                } elseif (preg_match($vPat2, $vOne, $match)) { // 2cols
                    $returnInfo[$vName][trim($match[1])] = trim($match[2]);
                }
            }
        } elseif (true) {
        }
    }

    return $returnInfo;
}

/**
 * This function will take a string that has tokens like {0}, {1} and will replace
 * those tokens with the args provided.
 *
 * @param   $format string to format
 * @param   $args   args to replace
 *
 * @return $result a formatted string
 */
function string_format($format, $args, $escape = true)
{
    $result = $format;

    /* Bug47277 fix.
     * If args array has only one argument, and it's empty, so empty single quotes are used '' . That's because
     * IN () fails and IN ('') works.
     */
    if ((is_countable($args) ? count($args) : 0) == 1) {
        reset($args);
        $singleArgument = current($args);
        if (empty($singleArgument)) {
            return str_replace('{0}', "''", (string) $result);
        }
    }
    /* End of fix */

    if ($escape) {
        $db = DBManagerFactory::getInstance();
    }
    $argsCount = count($args);
    for ($i = 0; $i < $argsCount; ++$i) {
        if (strpos((string) $args[$i], ',') !== false) {
            $values = explode(',', $args[$i]);
            if ($escape) {
                foreach ($values as &$value) {
                    $value = $db->quote($value);
                }
            }
            $args[$i] = implode("','", $values);
            $result = str_replace('{'.$i.'}', $args[$i], (string) $result);
       }
        else if ($escape){
            $result = str_replace('{'.$i.'}', $db->quote($args[$i]), (string) $result);
        }
        else{
            $result = str_replace('{'.$i.'}', $args[$i], (string) $result);
        }
    }

    return $result;
}

/**
 * Generate a string for displaying a unique identifier that is composed
 * of a system_id and number.  This is use to allow us to generate quote
 * numbers using a DB auto-increment key from offline clients and still
 * have the number be unique (since it is modified by the system_id.
 *
 * @deprecated This function is unused and will be removed in a future release.
 *
 * @param   $num       of bean
 * @param   $system_id from system
 *
 * @return $result a formatted string
 */
function format_number_display($num, $system_id)
{
    global $sugar_config;
    if (isset($num) && !empty($num)) {
        $num = unformat_number($num);
        if (isset($system_id) && $system_id == 1) {
            return sprintf('%d', $num);
        }
        return sprintf('%d-%d', $num, $system_id);
    }
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function checkLoginUserStatus()
{
}

/**
 * This function will take a number and system_id and format.
 *
 * @param   $url  URL containing host to append port
 * @param   $port the port number - if '' is passed, no change to url
 *
 * @return $resulturl the new URL with the port appended to the host
 */
function appendPortToHost($url, $port)
{
    $resulturl = $url;

    // if no port, don't change the url
    if ($port != '') {
        $split = explode('/', $url);
        //check if it starts with http, in case they didn't include that in url
        if (str_begin($url, 'http')) {
            //third index ($split[2]) will be the host
            $split[2] .= ':' . $port;
        } else {
            // otherwise assumed to start with host name
            //first index ($split[0]) will be the host
            $split[0] .= ':' . $port;
        }

        $resulturl = implode('/', $split);
    }

    return $resulturl;
}

/**
 * Singleton to return JSON object.
 *
 * @return JSON object
 */
function getJSONobj()
{
    static $json = null;
    if (!isset($json)) {
        require_once 'include/JSON.php';
        $json = new JSON();
    }

    return $json;
}

require_once 'include/utils/db_utils.php';

/**
 * Set default php.ini settings for entry points.
 */
function setPhpIniSettings()
{
    // zlib module
    // Bug 37579 - Comment out force enabling zlib.output_compression, since it can cause problems on certain hosts
    /*
      if(function_exists('gzclose') && headers_sent() == false) {
      ini_set('zlib.output_compression', 1);
      }
     */
    // mbstring module
    //nsingh: breaks zip/unzip functionality. Commenting out 4/23/08

    /* if(function_exists('mb_strlen')) {
      ini_set('mbstring.func_overload', 7);
      ini_set('mbstring.internal_encoding', 'UTF-8');
      } */

    // http://us3.php.net/manual/en/ref.pcre.php#ini.pcre.backtrack-limit
    // starting with 5.2.0, backtrack_limit breaks JSON decoding
    $backtrack_limit = ini_get('pcre.backtrack_limit');
    if (!empty($backtrack_limit)) {
        ini_set('pcre.backtrack_limit', '-1');
    }
}

/**
 * Identical to sugarArrayMerge but with some speed improvements and used specifically to merge
 * language files.  Language file merges do not need to account for null values so we can get some
 * performance increases by using this specialized function. Note this merge function does not properly
 * handle null values.
 *
 * @param $gimp
 * @param $dom
 *
 * @return array
 */
function sugarLangArrayMerge($gimp, $dom)
{
    if (is_array($gimp) && is_array($dom)) {
        foreach ($dom as $domKey => $domVal) {
            if (isset($gimp[$domKey])) {
                if (is_array($domVal)) {
                    $tempArr = array();
                    foreach ($domVal as $domArrKey => $domArrVal) {
                        $tempArr[$domArrKey] = $domArrVal;
                    }
                    foreach ($gimp[$domKey] as $gimpArrKey => $gimpArrVal) {
                        if (!isset($tempArr[$gimpArrKey])) {
                            $tempArr[$gimpArrKey] = $gimpArrVal;
                        }
                    }
                    $gimp[$domKey] = $tempArr;
                } else {
                    $gimp[$domKey] = $domVal;
                }
            } else {
                $gimp[$domKey] = $domVal;
            }
        }
    } // if the passed value for gimp isn't an array, then return the $dom
    elseif (is_array($dom)) {
        return $dom;
    }

    return $gimp;
}

/**
 * like array_merge() but will handle array elements that are themselves arrays;
 * PHP's version just overwrites the element with the new one.
 *
 * @internal Note that this function deviates from the internal array_merge()
 *           functions in that it does does not treat numeric keys differently
 *           than string keys.  Additionally, it deviates from
 *           array_merge_recursive() by not creating an array when like values
 *           found.
 *
 * @param array gimp the array whose values will be overloaded
 * @param array dom the array whose values will pwn the gimp's
 *
 * @return array beaten gimp
 */
function sugarArrayMerge($gimp, $dom)
{
    if (is_array($gimp) && is_array($dom)) {
        foreach ($dom as $domKey => $domVal) {
            if (array_key_exists($domKey, $gimp)) {
                if (is_array($domVal)) {
                    $tempArr = array();
                    foreach ($domVal as $domArrKey => $domArrVal) {
                        $tempArr[$domArrKey] = $domArrVal;
                    }
                    foreach ($gimp[$domKey] as $gimpArrKey => $gimpArrVal) {
                        if (!array_key_exists($gimpArrKey, $tempArr)) {
                            $tempArr[$gimpArrKey] = $gimpArrVal;
                        }
                    }
                    $gimp[$domKey] = $tempArr;
                } else {
                    $gimp[$domKey] = $domVal;
                }
            } else {
                $gimp[$domKey] = $domVal;
            }
        }
    } // if the passed value for gimp isn't an array, then return the $dom
    elseif (is_array($dom)) {
        return $dom;
    }

    return $gimp;
}

/**
 * Similiar to sugarArrayMerge except arrays of N depth are merged.
 *
 * @param array gimp the array whose values will be overloaded
 * @param array dom the array whose values will pwn the gimp's
 *
 * @return array beaten gimp
 */
function sugarArrayMergeRecursive($gimp, $dom)
{
    if (is_array($gimp) && is_array($dom)) {
        foreach ($dom as $domKey => $domVal) {
            if (array_key_exists($domKey, $gimp)) {
                if (is_array($domVal) && is_array($gimp[$domKey])) {
                    $gimp[$domKey] = sugarArrayMergeRecursive($gimp[$domKey], $domVal);
                } else {
                    $gimp[$domKey] = $domVal;
                }
            } else {
                $gimp[$domKey] = $domVal;
            }
        }
    } // if the passed value for gimp isn't an array, then return the $dom
    elseif (is_array($dom)) {
        return $dom;
    }

    return $gimp;
}

/**
 * Finds the correctly working versions of PHP-JSON.
 * @deprecated This function is unused and will be removed in a future release.
 *
 * @return bool True if NOT found or WRONG version
 */
function returnPhpJsonStatus()
{
    if (function_exists('json_encode')) {
        $phpInfo = getPhpInfo(8);

        return version_compare($phpInfo['json']['json version'], '1.1.1', '<');
    }

    return true; // not found
}

/**
 * getTrackerSubstring.
 *
 * Returns a [number]-char or less string for the Tracker to display in the header
 * based on the tracker_max_display_length setting in config.php.  If not set,
 * or invalid length, then defaults to 15 for COM editions, 30 for others.
 *
 * @param string name field for a given Object
 *
 * @return string [number]-char formatted string if length of string exceeds the max allowed
 */
function getTrackerSubstring($name)
{
    static $max_tracker_item_length;

    //Trim the name
    $name = html_entity_decode((string) $name, ENT_QUOTES, 'UTF-8');
    $strlen = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);

    global $sugar_config;

    if (!isset($max_tracker_item_length)) {
        if (isset($sugar_config['tracker_max_display_length'])) {
            $max_tracker_item_length = (is_int($sugar_config['tracker_max_display_length']) && $sugar_config['tracker_max_display_length'] > 0 && $sugar_config['tracker_max_display_length'] < 50) ? $sugar_config['tracker_max_display_length'] : 15;
        } else {
            $max_tracker_item_length = 15;
        }
    }

    if ($strlen > $max_tracker_item_length) {
        $chopped = function_exists('mb_substr') ? mb_substr($name, 0, $max_tracker_item_length - 3, 'UTF-8') : substr($name, 0, $max_tracker_item_length - 3);
        $chopped .= '...';
    } else {
        $chopped = $name;
    }

    return $chopped;
}

/**
 * @param array $field_list
 * @param array $values
 * @param array $bean
 * @param bool $add_custom_fields
 * @param string $module
 * @return array
 */
function generate_search_where(
    $field_list,
    $values,
    &$bean = null,
    $add_custom_fields = false,
    $module = ''
) {
    $where_clauses = array();
    $like_char = '%';
    $table_name = $bean->object_name;
    foreach ($field_list[$module] as $field => $parms) {
        if (isset($values[$field]) && $values[$field] != '') {
            $operator = 'like';
            if (!empty($parms['operator'])) {
                $operator = $parms['operator'];
            }
            if (is_array($values[$field])) {
                $operator = 'in';
                $field_value = '';
                foreach ($values[$field] as $key => $val) {
                    if ($val != ' ' && $val != '') {
                        if (!empty($field_value)) {
                            $field_value .= ',';
                        }
                        $field_value .= "'" . DBManagerFactory::getInstance()->quote($val) . "'";
                    }
                }
            } else {
                $field_value = DBManagerFactory::getInstance()->quote($values[$field]);
            }
            //set db_fields array.
            if (!isset($parms['db_field'])) {
                $parms['db_field'] = array($field);
            }
            if (isset($parms['my_items']) && $parms['my_items'] == true) {
                global $current_user;
                $field_value = DBManagerFactory::getInstance()->quote($current_user->id);
                $operator = '=';
            }

            $where = '';
            $itr = 0;
            if ($field_value != '') {
                foreach ($parms['db_field'] as $db_field) {
                    if (strstr((string) $db_field, '.') === false) {
                        $db_field = $bean->table_name . '.' . $db_field;
                    }
                    if (DBManagerFactory::getInstance()->supports('case_sensitive') && isset($parms['query_type']) && $parms['query_type'] == 'case_insensitive') {
                        $db_field = 'upper(' . $db_field . ')';
                        $field_value = strtoupper($field_value);
                    }

                    ++$itr;
                    if (!empty($where)) {
                        $where .= ' OR ';
                    }
                    switch (strtolower($operator)) {
                        case 'like':
                            $where .= $db_field . " like '" . $field_value . $like_char . "'";
                            break;
                        case 'in':
                            $where .= $db_field . ' in (' . $field_value . ')';
                            break;
                        case '=':
                            $where .= $db_field . " = '" . $field_value . "'";
                            break;
                    }
                }
            }
            if (!empty($where)) {
                if ($itr > 1) {
                    array_push($where_clauses, '( ' . $where . ' )');
                } else {
                    array_push($where_clauses, $where);
                }
            }
        }
    }
    if ($add_custom_fields) {
        require_once 'modules/DynamicFields/DynamicField.php';
        $bean->setupCustomFields($module);
        $bean->custom_fields->setWhereClauses($where_clauses);
    }

    return $where_clauses;
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function add_quotes($str)
{
    return "'{$str}'";
}

/**
 * This function will rebuild the config file.
 *
 * @param   $sugar_config
 * @param   $sugar_version
 *
 * @return bool true if successful
 */
function rebuildConfigFile($sugar_config, $sugar_version)
{
    // add defaults to missing values of in-memory sugar_config
    $sugar_config = sugarArrayMerge(get_sugar_config_defaults(), $sugar_config);
    // need to override version with default no matter what
    $sugar_config['sugar_version'] = $sugar_version;

    ksort($sugar_config);

    if (write_array_to_file('sugar_config', $sugar_config, 'config.php')) {
        return true;
    }
    return false;
}

/**
 * Loads clean configuration, not overridden by config_override.php.
 *
 * @return array
 */
function loadCleanConfig()
{
    $sugar_config = array();
    require 'config.php';

    return $sugar_config;
}

/**
 * getJavascriptSiteURL
 * This function returns a URL for the client javascript calls to access
 * the site.  It uses $_SERVER['HTTP_REFERER'] in the event that Proxy servers
 * are used to access the site.  Thus, the hostname in the URL returned may
 * not always match that of $sugar_config['site_url'].  Basically, the
 * assumption is that however the user accessed the website is how they
 * will continue to with subsequent javascript requests.  If the variable
 * $_SERVER['HTTP_REFERER'] is not found then we default to old algorithm.
 *
 * @return $site_url The url used to refer to the website
 */
function getJavascriptSiteURL()
{
    global $sugar_config;
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $url = parse_url((string) $_SERVER['HTTP_REFERER']);
        $replacement_url = $url['scheme'] . '://' . $url['host'];
        if (!empty($url['port'])) {
            $replacement_url .= ':' . $url['port'];
        }
        $site_url = preg_replace('/^http[s]?\:\/\/[^\/]+/', $replacement_url, (string) $sugar_config['site_url']);
    } else {
        $site_url = preg_replace('/^http(s)?\:\/\/[^\/]+/', 'http$1://' . $_SERVER['HTTP_HOST'], (string) $sugar_config['site_url']);
        if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
            $site_url = preg_replace('/^http\:/', 'https:', $site_url);
        }
    }
    $GLOBALS['log']->debug('getJavascriptSiteURL(), site_url=' . $site_url);

    return $site_url;
}


/**
 * Works nicely with array_map() -- can be used to wrap single quotes around
 * each element in an array.
 *
 * @deprecated This function is unused and will be removed in a future release.
 */
function add_squotes($str)
{
    return "'" . $str . "'";
}


/**
 * Recursive function to count the number of levels within an array.
 * @deprecated This function is unused and will be removed in a future release.
 */
function array_depth($array, $depth_count = -1, $depth_array = array())
{
    ++$depth_count;
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $depth_array[] = array_depth($value, $depth_count);
        }
    } else {
        return $depth_count;
    }
    foreach ($depth_array as $value) {
        $depth_count = $value > $depth_count ? $value : $depth_count;
    }

    return $depth_count;
}

/**
 * Creates a new Group User.
 *
 * @param string $name Name of Group User
 *
 * @return string GUID of new Group User
 */
function createGroupUser($name)
{
    $group = BeanFactory::newBean('Users');
    $group->user_name = $name;
    $group->last_name = $name;
    $group->is_group = 1;
    $group->deleted = 0;
    $group->status = 'Active'; // cn: bug 6711
    $group->setPreference('timezone', TimeDate::userTimezone());
    $group->save();

    return $group->id;
}

/*
 * Helper function to locate an icon file given only a name
 * Searches through the various paths for the file
 * @param string iconFileName   The filename of the icon
 * @return string Relative pathname of the located icon, or '' if not found
 */

function _getIcon($iconFileName)
{
    if (file_exists(SugarThemeRegistry::current()->getImagePath() . DIRECTORY_SEPARATOR . 'icon_' . $iconFileName . '.svg')) {
        $iconName = "icon_{$iconFileName}.svg";
        $iconFound = SugarThemeRegistry::current()->getImageURL($iconName, false);
    } else {
        $iconName = "icon_{$iconFileName}.gif";
        $iconFound = SugarThemeRegistry::current()->getImageURL($iconName, false);
    }



    //First try un-ucfirst-ing the icon name
    if (empty($iconFound)) {
        $iconName = 'icon_' . strtolower(substr((string) $iconFileName, 0, 1)) . substr((string) $iconFileName, 1) . '.gif';
    }
    $iconFound = SugarThemeRegistry::current()->getImageURL($iconName, false);

    //Next try removing the icon prefix
    if (empty($iconFound)) {
        $iconName = "{$iconFileName}.gif";
    }
    $iconFound = SugarThemeRegistry::current()->getImageURL($iconName, false);

    if (empty($iconFound)) {
        $iconName = '';
    }

    return $iconName;
}

/**
 * Function to grab the correct icon image for Studio.
 *
 * @param string $iconFileName Name of the icon file
 * @param string $altfilename  Name of a fallback icon file (displayed if the imagefilename doesn't exist)
 * @param string $width        Width of image
 * @param string $height       Height of image
 * @param string $align        Alignment of image
 * @param string $alt          Alt tag of image
 *
 * @return string $string <img> tag with corresponding image
 */
function getStudioIcon($iconFileName = '', $altFileName = '', $width = '48', $height = '48', $align = 'baseline', $alt = '')
{
    global $app_strings, $theme;

    $iconName = _getIcon($iconFileName);
    if (empty($iconName)) {
        $iconName = _getIcon($altFileName);
        if (empty($iconName)) {
            return $app_strings['LBL_NO_IMAGE'];
        }
    }

    return SugarThemeRegistry::current()->getImage($iconName, "align=\"$align\" border=\"0\"", $width, $height);
}

/**
 * Function to grab the correct icon image for Dashlets Dialog.
 *
 * @param string $filename Location of the icon file
 * @param string $module   Name of the module to fall back onto if file does not exist
 * @param string $width    Width of image
 * @param string $height   Height of image
 * @param string $align    Alignment of image
 * @param string $alt      Alt tag of image
 *
 * @return string $string <img> tag with corresponding image
 */
function get_dashlets_dialog_icon($module = '', $width = '32', $height = '32', $align = 'absmiddle', $alt = '')
{
    global $app_strings, $theme;
    $iconName = _getIcon($module . '_32');
    if (empty($iconName)) {
        $iconName = _getIcon($module);
    }
    if (empty($iconName)) {
        return $app_strings['LBL_NO_IMAGE'];
    }

    return $iconName;
}

// works nicely to change UTF8 strings that are html entities - good for PDF conversions
function html_entity_decode_utf8($string)
{
    static $trans_tbl;
    // replace numeric entities
    //php will have issues with numbers with leading zeros, so do not include them in what we send to code2utf.

    $string = preg_replace_callback('~&#x0*([0-9a-f]+);~i', function ($matches) {
        return code2utf(hexdec($matches[1]));
    }, (string) $string);
    $string = preg_replace_callback('~&#0*([0-9]+);~', function ($matches) {
        return code2utf($matches[1]);
    }, $string);

    // replace literal entities
    if (!isset($trans_tbl)) {
        $trans_tbl = array();
        foreach (get_html_translation_table(HTML_ENTITIES) as $val => $key) {
            $trans_tbl[$key] = mb_convert_encoding($val, 'UTF-8', 'ISO-8859-1');
        }
    }

    return strtr($string, $trans_tbl);
}

// Returns the utf string corresponding to the unicode value
function code2utf($num)
{
    if ($num < 128) {
        return chr($num);
    }
    if ($num < 2048) {
        return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    }
    if ($num < 65536) {
        return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    }
    if ($num < 2097152) {
        return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    }

    return '';
}

/*
 * @deprecated use DBManagerFactory::isFreeTDS
 */

function is_freetds()
{
    return DBManagerFactory::isFreeTDS();
}

/**
 * Chart dashlet helper function that returns the correct CSS file, dependent on the current theme.
 *
 * @deprecated This function is unused and will be removed in a future release.
 *
 * @todo this won't work completely right until we impliment css compression and combination
 *       for now, we'll just include the last css file found.
 *
 * @return chart.css file to use
 */
function chartStyle()
{
    return SugarThemeRegistry::current()->getCSSURL('chart.css');
}

/**
 * Chart dashlet helper functions that returns the correct XML color file for charts,
 * dependent on the current theme.
 *
 * @deprecated This function is unused and will be removed in a future release.
 * @return sugarColors.xml to use
 */
function chartColors()
{
    if (SugarThemeRegistry::current()->getCSSURL('sugarColors.xml') == '') {
        return SugarThemeRegistry::current()->getImageURL('sugarColors.xml');
    }

    return SugarThemeRegistry::current()->getCSSURL('sugarColors.xml');
}

/* End Chart Dashlet helper functions */

/**
 * This function is designed to set up the php enviroment
 * for AJAX requests.
 *
 * @deprecated This function is unused and will be removed in a future release.
 */
function ajaxInit()
{
    //ini_set('display_errors', 'false');
}

/**
 * Returns an absolute path from the given path, determining if it is relative or absolute.
 *
 * @param string $path
 *
 * @return string
 */
function getAbsolutePath(
    $path,
    $currentServer = false
) {
    $path = trim($path);

    // try to match absolute paths like \\server\share, /directory or c:\
    if ((substr($path, 0, 2) == '\\\\') || ($path[0] == '/') || preg_match('/^[A-z]:/i', $path) || $currentServer
    ) {
        return $path;
    }

    return getcwd() . '/' . $path;
}

/**
 * Returns the bean object of the given module.
 *
 * @deprecated use SugarModule::loadBean() instead
 *
 * @param string $module
 *
 * @return object
 */
function loadBean(
    $module
) {
    return SugarModule::get($module)->loadBean();
}

/**
 * Returns true if the application is being accessed on a touch screen interface ( like an iPad ).
 */
function isTouchScreen()
{
    $ua = empty($_SERVER['HTTP_USER_AGENT']) ? 'undefined' : strtolower($_SERVER['HTTP_USER_AGENT']);

    // first check if we have forced use of the touch enhanced interface
    if (isset($_COOKIE['touchscreen']) && $_COOKIE['touchscreen'] == '1') {
        return true;
    }

    // next check if we should use the touch interface with our device
    if (strpos($ua, 'ipad') !== false) {
        return true;
    }

    return false;
}

/**
 * Returns the shortcut keys to access the shortcut links.  Shortcut
 * keys vary depending on browser versions and operating systems.
 *
 * @return string value of the shortcut keys
 */
function get_alt_hot_key()
{
    $ua = '';
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
    }
    $isMac = strpos($ua, 'mac') !== false;
    $isLinux = strpos($ua, 'linux') !== false;

    if (!$isMac && !$isLinux && strpos($ua, 'mozilla') !== false) {
        if (preg_match('/firefox\/(\d)?\./', $ua, $matches)) {
            return $matches[1] < 2 ? 'Alt+' : 'Alt+Shift+';
        }
    }

    return $isMac ? 'Ctrl+' : 'Alt+';
}

function can_start_session()
{
    if (!empty($_GET['PHPSESSID'])) {
        return true;
    }
    $session_id = session_id();

    return empty($session_id) ? true : false;
}

function load_link_class($properties)
{
    $class = 'Link2';
    if (!empty($properties['link_class']) && !empty($properties['link_file'])) {
        if (!file_exists($properties['link_file'])) {
            $GLOBALS['log']->fatal('File not found: ' . $properties['link_file']);
        } else {
            require_once $properties['link_file'];
            $class = $properties['link_class'];
        }
    }

    return $class;
}

function inDeveloperMode()
{
    return isset($GLOBALS['sugar_config']['developerMode']) && $GLOBALS['sugar_config']['developerMode'];
}

/**
 * Filter the protocol list for inbound email accounts.
 *
 * @param array $protocol
 */
function filterInboundEmailPopSelection($protocol)
{
    if (!isset($GLOBALS['sugar_config']['allow_pop_inbound']) || !$GLOBALS['sugar_config']['allow_pop_inbound']) {
        if (isset($protocol['pop3'])) {
            unset($protocol['pop3']);
        }
    } else {
        $protocol['pop3'] = 'POP3';
    }

    return $protocol;
}

/**
 * Get Inbound Email protocols
 *
 * @return array
 */
function getInboundEmailProtocols(): array
{
    global $app_list_strings, $sugar_config;

    $protocols = $app_list_strings['dom_email_server_type'];
    if (!isset($sugar_config['allow_pop_inbound']) || !$sugar_config['allow_pop_inbound']) {
        if (isset($protocols['pop3'])) {
            unset($protocols['pop3']);
        }
    } else {
        $protocols['pop3'] = 'POP3';
    }

    return $protocols;
}

/**
 * The function is used because currently we are not supporting mbstring.func_overload
 * For some user using mssql without FreeTDS, they may store multibyte charaters in varchar using latin_general collation. It cannot store so many mutilbyte characters, so we need to use strlen.
 * The varchar in MySQL, Orcale, and nvarchar in FreeTDS, we can store $length mutilbyte charaters in it. we need mb_substr to keep more info.
 *
 * @returns the substred strings.
 */
function sugar_substr($string, $length, $charset = 'UTF-8')
{
    if (mb_strlen($string, $charset) > $length) {
        $string = trim(mb_substr(trim($string), 0, $length, $charset));
    }

    return $string;
}

/**
 * The function is used because on FastCGI enviroment, the ucfirst(Chinese Characters) will produce bad charcters.
 * This will work even without setting the mbstring.*encoding.
 */
function sugar_ucfirst($string, $charset = 'UTF-8')
{
    return mb_strtoupper(mb_substr($string, 0, 1, $charset), $charset) . mb_substr($string, 1, mb_strlen($string), $charset);
}

/**
 * Given a multienum encoded as a string, convert it to an array of strings,
 * e.g. `"^Monday^,^Tuesday^,^Wednesday^,^Thursday^"` becomes
 * `["Monday", "Tuesday", "Wednesday", "Thursday"]`.
 *
 * @param string|string[] $string The encoded multienum value. If this is already an array, the array will be returned unchanged.
 * @return string[] An array of strings representing the multienum's values.
 */
function unencodeMultienum($string)
{
    if (is_array($string)) {
        return $string;
    }
    if (substr($string, 0, 1) == '^' && substr($string, -1) == '^') {
        $string = substr(substr($string, 1), 0, strlen($string) - 2);
    }

    return explode('^,^', $string);
}

function encodeMultienumValue($arr)
{
    if (!is_array($arr)) {
        return $arr;
    }

    if (empty($arr)) {
        return '';
    }

    $string = '^' . implode('^,^', $arr) . '^';

    return $string;
}

/**
 * create_export_query is used for export and massupdate
 * We haven't handle the these fields: $field['type'] == 'relate' && isset($field['link']
 * This function will correct the where clause and output necessary join condition for them.
 *
 * @param $module : the module name
 * @param $searchFields : searchFields which is got after $searchForm->populateFromArray()
 * @param $where : where clauses
 *
 * @return array
 */
function create_export_query_relate_link_patch($module, $searchFields, $where)
{
    $ret_array = [];
    $join = [];
    if (file_exists('modules/' . $module . '/SearchForm.html')) {
        $ret_array['where'] = $where;

        return $ret_array;
    }
    $seed = BeanFactory::getBean($module);
    foreach ($seed->field_defs as $name => $field) {
        if ($field['type'] == 'relate' && isset($field['link']) && !empty($searchFields[$name]['value'])) {
            $seed->load_relationship($field['link']);
            $params = array();
            if (empty($join_type)) {
                $params['join_type'] = ' LEFT JOIN ';
            } else {
                $params['join_type'] = $join_type;
            }
            if (isset($data['join_name'])) {
                $params['join_table_alias'] = $field['join_name'];
            } else {
                $params['join_table_alias'] = 'join_' . $field['name'];
            }
            if (isset($data['join_link_name'])) {
                $params['join_table_link_alias'] = $field['join_link_name'];
            } else {
                $params['join_table_link_alias'] = 'join_link_' . $field['name'];
            }
            $fieldLink = $field['link'];
            $join = $seed->$fieldLink->getJoin($params, true);
            $join_table_alias = 'join_' . $field['name'];
            if (isset($field['db_concat_fields'])) {
                $db_field = DBManager::concat($join_table_alias, $field['db_concat_fields']);
                $where = preg_replace('/' . $field['name'] . '/', $db_field, (string) $where);
            } else {
                $where = preg_replace('/(^|[\s(])' . $field['name'] . '/', '${1}' . $join_table_alias . '.' . $field['rname'], (string) $where);
            }
        }
    }
    $ret_array = array('where' => $where, 'join' => isset($join['join']) ? $join['join'] : '');

    return $ret_array;
}

/**
 * We need to clear all the js cache files, including the js language files  in serval places in MB. So I extract them into a util function here.
 *
 * @Depends on QuickRepairAndRebuild.php
 * @Relate bug 30642  ,23177
 */
function clearAllJsAndJsLangFilesWithoutOutput()
{
    global $current_language, $mod_strings;
    $MBmodStrings = $mod_strings;
    $mod_strings = return_module_language($current_language, 'Administration');
    include_once 'modules/Administration/QuickRepairAndRebuild.php';
    $repair = new RepairAndClear();
    $repair->module_list = array();
    $repair->show_output = false;
    $repair->clearJsLangFiles();
    $repair->clearJsFiles();
    $mod_strings = $MBmodStrings;
}

/**
 * This function will allow you to get a variable value from query string.
 */
function getVariableFromQueryString($variable, $string)
{
    $matches = array();
    $number = preg_match("/{$variable}=([a-zA-Z0-9_-]+)[&]?/", (string) $string, $matches);
    if ($number) {
        return $matches[1];
    }
    return false;
}

/**
 * should_hide_iframes
 * This is a helper method to determine whether or not to show iframes (My Sites) related
 * information in the application.
 *
 * @return bool flag indicating whether or not iframes module should be hidden
 */
function should_hide_iframes()
{
    //Remove the MySites module
    if (file_exists('modules/iFrames/iFrame.php')) {
        if (!class_exists('iFrame')) {
            require_once 'modules/iFrames/iFrame.php';
        }

        return false;
    }

    return true;
}

/**
 * Given a version such as 5.5.0RC1 return RC. If we have a version such as: 5.5 then return GA.
 *
 * @deprecated This function is unused and will be removed in a future release.
 *
 * @param string $version
 * @return string RC, BETA, GA
 */
function getVersionStatus($version)
{
    if (preg_match('/^[\d\.]+?([a-zA-Z]+?)[\d]*?$/si', $version, $matches)) {
        return strtoupper($matches[1]);
    }
    return 'GA';
}

/**
 * Return the numeric portion of a version. For example if passed 5.5.0RC1 then return 5.5. If given
 * 5.5.1RC1 then return 5.5.1.
 *
 * @deprecated This function is unused and will be removed in a future release.
 *
 * @param string $version
 *
 * @return version
 */
function getMajorMinorVersion($version)
{
    if (preg_match('/^([\d\.]+).*$/si', $version, $matches2)) {
        $version = $matches2[1];
        $arr = explode('.', $version);
        if (count($arr) > 2) {
            if ($arr[2] == '0') {
                $version = substr($version, 0, 3);
            }
        }
    }

    return $version;
}

/**
 * Return string composed of seconds & microseconds of current time, without dots.
 *
 * @return string
 */
function sugar_microtime()
{
    $now = explode(' ', microtime());
    $unique_id = $now[1] . str_replace('.', '', $now[0]);

    return $unique_id;
}

/**
 * Extract urls from a piece of text.
 *
 * @param  $string
 *
 * @return array of urls found in $string
 */
function getUrls($string)
{
    $lines = explode('<br>', trim($string));
    $urls = array();
    foreach ($lines as $line) {
        $regex = '/http?\:\/\/[^\" ]+/i';
        preg_match_all($regex, $line, $matches);
        foreach ($matches[0] as $match) {
            $urls[] = $match;
        }
    }

    return $urls;
}

/**
 * Sanitize image file from hostile content.
 *
 * @param string $path Image file
 * @param bool   $jpeg Accept only JPEGs?
 */
function verify_image_file($path, $jpeg = false)
{
    if (function_exists('imagepng') && function_exists('imagejpeg') && function_exists('imagecreatefromstring')) {
        $img = imagecreatefromstring(file_get_contents($path));
        if (!$img) {
            return false;
        }
        $img_size = getimagesize($path);
        $filetype = $img_size['mime'];
        //if filetype is jpeg or if we are only allowing jpegs, create jpg image
        if ($filetype == 'image/jpeg' || $jpeg) {
            ob_start();
            imagejpeg($img);
            $image = ob_get_clean();
            // not writing directly because imagejpeg does not work with streams
            if (file_put_contents($path, $image)) {
                return true;
            }
        } elseif ($filetype == 'image/png') {
            // else if the filetype is png, create png
            imagealphablending($img, true);
            imagesavealpha($img, true);
            ob_start();
            imagepng($img);
            $image = ob_get_clean();
            if (file_put_contents($path, $image)) {
                return true;
            }
        } else {
            return false;
        }
    } else {
        // check image manually
        $fp = fopen($path, 'rb');
        if (!$fp) {
            return false;
        }
        $data = '';
        // read the whole file in chunks
        while (!feof($fp)) {
            $data .= fread($fp, 8192);
        }

        fclose($fp);
        if (preg_match("/<(\?php|html|!doctype|script|body|head|plaintext|table|img |pre(>| )|frameset|iframe|object|link|base|style|font|applet|meta|center|form|isindex)/i", $data, $m)) {
            $GLOBALS['log']->fatal("Found {$m[0]} in $path, not allowing upload");

            return false;
        }

        return true;
    }

    return false;
}

/**
 * Verify uploaded image
 * Verifies that image has proper extension, MIME type and doesn't contain hostile content.
 *
 * @param string $path      Image path
 * @param bool   $jpeg_only Accept only JPEGs?
 */
function verify_uploaded_image($path, $jpeg_only = false)
{
    $supportedExtensions = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'tmp' => 'tmp');
    if (!$jpeg_only) {
        $supportedExtensions['png'] = 'image/png';
    }

    if (!file_exists($path) || !is_file($path)) {
        return false;
    }

    $img_size = getimagesize($path);
    $filetype = $img_size['mime'];
    $tmpArray = explode('.', $path);
    $ext = end($tmpArray);
    if (substr_count('..', $path) > 0 || ($ext !== $path && !isset($supportedExtensions[strtolower($ext)])) ||
            !in_array($filetype, array_values($supportedExtensions))
    ) {
        return false;
    }

    return verify_image_file($path, $jpeg_only);
}

function cmp_beans($a, $b)
{
    global $sugar_web_service_order_by;
    //If the order_by field is not valid, return 0;
    if (empty($sugar_web_service_order_by) || !isset($a->$sugar_web_service_order_by) || !isset($b->$sugar_web_service_order_by)) {
        return 0;
    }
    if (is_object($a->$sugar_web_service_order_by) || is_object($b->$sugar_web_service_order_by) || is_array($a->$sugar_web_service_order_by) || is_array($b->$sugar_web_service_order_by)
    ) {
        return 0;
    }
    if ($a->$sugar_web_service_order_by < $b->$sugar_web_service_order_by) {
        return -1;
    }
    return 1;
}

function order_beans($beans, $field_name)
{
    //Since php 5.2 doesn't include closures, we must use a global to pass the order field to cmp_beans.
    global $sugar_web_service_order_by;
    $sugar_web_service_order_by = $field_name;
    usort($beans, 'cmp_beans');

    return $beans;
}

/**
 * Return search like string
 * This function takes a user input string and returns a string that contains wild card(s) that can be used in db query.
 *
 * @param string $str       string to be searched
 * @param string $like_char Database like character, usually '%'
 *
 * @return string Returns a string to be searched in db query
 */
function sql_like_string($str, $like_char, $wildcard = '%', $appendWildcard = true)
{

    // override default wildcard character
    if (isset($GLOBALS['sugar_config']['search_wildcard_char']) &&
            strlen((string) $GLOBALS['sugar_config']['search_wildcard_char']) == 1
    ) {
        $wildcard = $GLOBALS['sugar_config']['search_wildcard_char'];
    }

    // add wildcard at the beginning of the search string
    if (isset($GLOBALS['sugar_config']['search_wildcard_infront']) &&
            $GLOBALS['sugar_config']['search_wildcard_infront'] == true
    ) {
        if (substr($str, 0, 1) != $wildcard) {
            $str = $wildcard . $str;
        }
    }

    // add wildcard at the end of search string (default)
    if ($appendWildcard) {
        if (substr($str, -1) != $wildcard) {
            $str .= $wildcard;
        }
    }

    return str_replace($wildcard, $like_char, $str);
}

//check to see if custom utils exists
if (file_exists('custom/include/custom_utils.php')) {
    include_once 'custom/include/custom_utils.php';
}

//check to see if custom utils exists in Extension framework
if (file_exists('custom/application/Ext/Utils/custom_utils.ext.php')) {
    include_once 'custom/application/Ext/Utils/custom_utils.ext.php';
}

/**
 * @param $input - the input string to sanitize
 * @param int    $quotes  - use quotes
 * @param string $charset - the default charset
 * @param bool   $remove  - strip tags or not
 *
 * @return string - the sanitized string
 */
function sanitize($input, $quotes = ENT_QUOTES, $charset = 'UTF-8', $remove = false)
{
    return htmlentities((string) $input, $quotes, $charset);
}

/**
 * @return string - the full text search engine name
 */
function getFTSEngineType()
{
    if (isset($GLOBALS['sugar_config']['full_text_engine']) && is_array($GLOBALS['sugar_config']['full_text_engine'])) {
        foreach ($GLOBALS['sugar_config']['full_text_engine'] as $name => $defs) {
            return $name;
        }
    }

    return '';
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 *
 * @param string $optionName - name of the option to be retrieved from app_list_strings
 * @return array - the array to be used in option element
 */
function getFTSBoostOptions($optionName)
{
    if (isset($GLOBALS['app_list_strings'][$optionName])) {
        return $GLOBALS['app_list_strings'][$optionName];
    }
    return array();
}

/**
 * utf8_recursive_encode.
 *
 * This function walks through an Array and recursively calls utf8_encode on the
 * values of each of the elements.
 *
 * @deprecated This function is unused and will be removed in a future release.
 *
 * @param $data Array of data to encode
 *
 * @return utf8 encoded Array data
 */
function utf8_recursive_encode($data)
{
    $result = array();
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            $result[$key] = utf8_recursive_encode($val);
        } else {
            $result[$key] = mb_convert_encoding($val, 'UTF-8', 'ISO-8859-1');
        }
    }

    return $result;
}

/**
 * get_language_header.
 *
 * This is a utility function for 508 Compliance.  It returns the lang=[Current Language] text string used
 * inside the <html> tag.  If no current language is specified, it defaults to lang='en'.
 *
 * @return string The lang=[Current Language] markup to insert into the <html> tag
 */
function get_language_header()
{
    return isset($GLOBALS['current_language']) ? "lang='{$GLOBALS['current_language']}'" : "lang='en'";
}

/**
 * get_custom_file_if_exists.
 *
 * This function handles the repetitive code we have where we first check if a file exists in the
 * custom directory to determine whether we should load it, require it, include it, etc.  This function returns the
 * path of the custom file if it exists.  It basically checks if custom/{$file} exists and returns this path if so;
 * otherwise it return $file
 *
 * @param $file String of filename to check
 *
 * @return $file String of filename including custom directory if found
 */
function get_custom_file_if_exists($file)
{
    return file_exists("custom/{$file}") ? "custom/{$file}" : $file;
}

/**
 * get_help_url.
 *
 * This will return the URL used to redirect the user to the help documentation.
 * It can be overriden completely by setting the custom_help_url or partially by setting the custom_help_base_url
 * in config.php or config_override.php.
 *
 * @deprecated This function is unused and will be removed in a future release.
 *
 * @param string $send_edition
 * @param string $send_version
 * @param string $send_lang
 * @param string $send_module
 * @param string $send_action
 * @param string $dev_status
 * @param string $send_key
 * @param string $send_anchor
 *
 * @return string the completed help URL
 */
function get_help_url($send_edition = '', $send_version = '', $send_lang = '', $send_module = '', $send_action = '', $dev_status = '', $send_key = '', $send_anchor = '')
{
    global $sugar_config;

    if (!empty($sugar_config['custom_help_url'])) {
        $sendUrl = $sugar_config['custom_help_url'];
    } else {
        if (!empty($sugar_config['custom_help_base_url'])) {
            $baseUrl = $sugar_config['custom_help_base_url'];
        } else {
            $baseUrl = 'http://www.sugarcrm.com/crm/product_doc.php';
        }
        $sendUrl = $baseUrl . "?edition={$send_edition}&version={$send_version}&lang={$send_lang}&module={$send_module}&help_action={$send_action}&status={$dev_status}&key={$send_key}";
        if (!empty($send_anchor)) {
            $sendUrl .= '&anchor=' . $send_anchor;
        }
    }

    return $sendUrl;
}

/**
 * generateETagHeader.
 *
 * This function generates the necessary cache headers for using ETags with dynamic content. You
 * simply have to generate the ETag, pass it in, and the function handles the rest.
 *
 * @param string $etag ETag to use for this content.
 */
function generateETagHeader($etag)
{
    header('cache-control:');
    header('Expires: ');
    header('ETag: ' . $etag);
    header('Pragma:');
    if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
        if ($etag == $_SERVER['HTTP_IF_NONE_MATCH']) {
            ob_clean();
            header('Status: 304 Not Modified');
            header('HTTP/1.0 304 Not Modified');
            die();
        }
    }
}

/**
 * getReportNameTranslation.
 *
 * Translates the report name if a translation exists,
 * otherwise just returns the name
 *
 * @param string $reportName
 *
 * @return string translated report name
 */
function getReportNameTranslation($reportName)
{
    global $current_language;

    // Used for translating reports
    $mod_strings = return_module_language($current_language, 'Reports');

    // Search for the report name in the default language and get the key
    $key = array_search($reportName, return_module_language('', 'Reports'), true);

    // If the key was found, use it to get a translation, otherwise just use report name
    if (!empty($key)) {
        $title = $mod_strings[$key];
    } else {
        $title = $reportName;
    }

    return $title;
}

/**
 * Remove vars marked senstitive from array.
 *
 * @param array           $defs
 * @param SugarBean|array $data
 *
 * @return mixed $data without sensitive fields
 */
function clean_sensitive_data($defs, $data)
{
    foreach ($defs as $field => $def) {
        if (!empty($def['sensitive'])) {
            if (is_array($data)) {
                $data[$field] = '';
            }
            if ($data instanceof SugarBean) {
                $data->$field = '';
            }
        }
    }

    return $data;
}

/**
 * Return relations with labels for duplicates.
 *
 * @deprecated This function is unused and will be removed in a future release.
 */
function getDuplicateRelationListWithTitle($def, $var_def, $module)
{
    global $current_language;
    $select_array = array_unique($def);
    if (count($select_array) < (is_countable($def) ? count($def) : 0)) {
        $temp_module_strings = return_module_language($current_language, $module);
        $temp_duplicate_array = array_diff_assoc($def, $select_array);
        $temp_duplicate_array = array_merge($temp_duplicate_array, array_intersect($select_array, $temp_duplicate_array));

        foreach ($temp_duplicate_array as $temp_key => $temp_value) {
            // Don't add duplicate relationships
            if (!empty($var_def[$temp_key]['relationship']) && array_key_exists($var_def[$temp_key]['relationship'], $select_array)) {
                continue;
            }
            $select_array[$temp_key] = $temp_value;
        }

        // Add the relationship name for easier recognition
        foreach ($select_array as $key => $value) {
            $select_array[$key] .= ' (' . $key . ')';
        }
    }
    asort($select_array);

    return $select_array;
}

/**
 * Gets the list of "*type_display*".
 *
 * @return array
 */
function getTypeDisplayList()
{
    return array('record_type_display', 'parent_type_display', 'record_type_display_notes');
}

/**
 * Breaks given string into substring according
 * to 'db_concat_fields' from field definition
 * and assigns values to corresponding properties
 * of bean.
 *
 * @param SugarBean $bean
 * @param array     $fieldDef
 * @param string    $value
 */
function assignConcatenatedValue(SugarBean $bean, $fieldDef, $value)
{
    $fieldName = '';
    $valueParts = explode(' ', $value);
    $valueParts = array_filter($valueParts);
    $fieldNum = is_countable($fieldDef['db_concat_fields']) ? count($fieldDef['db_concat_fields']) : 0;

    if (count($valueParts) == 1 && $fieldDef['db_concat_fields'] == array('first_name', 'last_name')) {
        $bean->last_name = $value;
    } // elseif ($fieldNum >= count($valueParts))
    else {
        for ($i = 0; $i < $fieldNum; ++$i) {
            $fieldValue = array_shift($valueParts);
            $fieldName = $fieldDef['db_concat_fields'][$i];
            $bean->$fieldName = $fieldValue !== false ? $fieldValue : '';
        }

        if (!empty($valueParts)) {
            $bean->$fieldName .= ' ' . implode(' ', $valueParts);
        }
    }
}

/**
 * Performs unserialization. Accepts all types except Objects.
 *
 * @param string $value Serialized value of any type except Object
 *
 * @return mixed False if Object, converted value for other cases
 */
function sugar_unserialize($value)
{
    preg_match('/[oc]:[^:]*\d+:/i', $value, $matches);

    if (count($matches)) {
        return false;
    }

    return unserialize($value);
}

define('DEFAULT_UTIL_SUITE_ENCODING', 'UTF-8');

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function suite_strlen($input, $encoding = DEFAULT_UTIL_SUITE_ENCODING)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($input, $encoding);
    }
    return strlen((string) $input);
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function suite_substr($input, $start, $length = null, $encoding = DEFAULT_UTIL_SUITE_ENCODING)
{
    if (function_exists('mb_substr')) {
        return mb_substr($input, $start, $length, $encoding);
    }
    return substr((string) $input, $start, $length);
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function suite_strtoupper($input, $encoding = DEFAULT_UTIL_SUITE_ENCODING)
{
    if (function_exists('mb_strtoupper')) {
        return mb_strtoupper($input, $encoding);
    }
    return strtoupper($input);
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function suite_strtolower($input, $encoding = DEFAULT_UTIL_SUITE_ENCODING)
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($input, $encoding);
    }
    return strtolower($input);
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function suite_strpos($haystack, $needle, $offset = 0, $encoding = DEFAULT_UTIL_SUITE_ENCODING)
{
    if (function_exists('mb_strpos')) {
        return mb_strpos((string) $haystack, (string) $needle, $offset, $encoding);
    }
    return strpos((string) $haystack, (string) $needle, $offset);
}

/**
 * @deprecated This function is unused and will be removed in a future release.
 */
function suite_strrpos($haystack, $needle, $offset = 0, $encoding = DEFAULT_UTIL_SUITE_ENCODING)
{
    if (function_exists('mb_strrpos')) {
        return mb_strrpos((string) $haystack, (string) $needle, $offset, $encoding);
    }
    return strrpos((string) $haystack, (string) $needle, $offset);
}

/**
 * @deprecated deprecated since version 7.10 please use the SuiteValidator class
 */
function isValidId($id)
{
    $deprecatedMessage = 'isValidId method is deprecated please update your code';
    if (isset($GLOBALS['log'])) {
        $GLOBALS['log']->deprecated($deprecatedMessage);
    } else {
        trigger_error($deprecatedMessage, E_USER_DEPRECATED);
    }
    $isValidator = new \SuiteCRM\Utility\SuiteValidator();
    $result = $isValidator->isValidId($id);
    return $result;
}

function isValidEmailAddress($email, $message = 'Invalid email address given', $orEmpty = true, $logInvalid = 'error')
{
    if ($orEmpty && !$email) {
        return true;
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    if ($logInvalid) {
        $trace = debug_backtrace();
        $where = "Called at {$trace[1]['file']}:{$trace[1]['line']} from function {$trace[1]['function']}.";
        \SuiteCRM\ErrorMessage::log("$message: [$email] $where", $logInvalid);
    }
    return false;
}

function displayAdminError($errorString)
{
    SugarApplication::appendErrorMessage($errorString);
}

function getAppString($key)
{
    global $app_strings;

    if (!isset($app_strings[$key])) {
        LoggerManager::getLogger()->warn('Language key not found: ' . $key);
        return $key;
    }

    if (!$app_strings[$key]) {
        LoggerManager::getLogger()->warn('Language string is empty at key: ' . $key);
        return $key;
    }

    return $app_strings[$key];
}

/**
 * Check if has valid image extension
 * @param string $fieldName
 * @param string $value
 * @return bool
 */
function has_valid_image_extension($fieldName, $name)
{
    global $sugar_config;

    $validExtensions = [
        'gif',
        'png',
        'jpg',
        'jpeg',
        'svg'
    ];

    if (isset($sugar_config['valid_image_ext']) && is_array($sugar_config['valid_image_ext'])){
        $validExtensions = $sugar_config['valid_image_ext'];
    }

    return has_valid_extension($fieldName, $name, $validExtensions);
}

/**
 * Check if has valid extension
 * @param string $fieldName
 * @param string $name
 * @param array $validExtensions
 * @return bool
 */
function has_valid_extension($fieldName, $name, $validExtensions)
{

    if ($name === '.' || empty($name)) {
        LoggerManager::getLogger()->security("Invalid ext  $fieldName : '$name'.");

        return false;
    }

    $validExtensions = array_map('strtolower', $validExtensions);

    $parts = explode('.', $name);

    if (empty($parts)) {
        LoggerManager::getLogger()->security("Invalid ext  $fieldName : '$name'.");

        return false;
    }

    $ext = array_pop($parts);
    $trimmedValue = preg_replace('/.*\.([^\.]+)$/', '\1', $ext);

    if (!in_array(strtolower($trimmedValue), $validExtensions, true)) {
        LoggerManager::getLogger()->security("Invalid $fieldName: '$name'.");

        return false;
    }

    return true;
}

/**
 * Check if value is one of the accepted true representations
 * @param $value
 * @return bool
 */
function isTrue($value): bool {
    return $value === true || $value === 'true' || $value === 1 || $value === '1' || $value === 'on';
}

/**
 * Check if value is one of the accepted false representations
 * @param $value
 * @return bool
 */
function isFalse($value): bool {
    return $value === false || $value === 'false' || $value === 0 || $value === '0';
}

/**
 * Get validation pattern
 * @return string
 */
function get_id_validation_pattern(): string {
    global $sugar_config;

    $pattern = '/^[a-zA-Z0-9_-]*$/i';
    if (!empty($sugar_config['id_validation_pattern'])){
        $pattern = $sugar_config['id_validation_pattern'];
    }

    return $pattern;
}

/**
 * Check if user has group and action acls defined
 * @param string $module
 * @param string $action
 * @return bool
 */
function has_group_action_acls_defined(string $module, string $action): bool
{
    global $current_user;

    $hasGroupActionAcls = true;

    $groups = SecurityGroup::getUserSecurityGroups($current_user->id);
    $hasGroups = !empty($groups);

    $aclActions = ACLAction::getUserActions($current_user->id, false, $module, 'module', $action);
    $isDefaultListACL = !empty($aclActions['isDefault']) && isTrue($aclActions['isDefault']);

    if (!$hasGroups) {
        $hasGroupActionAcls = false;
    }

    if ($isDefaultListACL) {
        $hasGroupActionAcls = false;
    }

    return $hasGroupActionAcls;
}

/**
 * Check if is value is smtp in a case-insensitive way
 * @param $value
 * @return bool
 */
function isSmtp($value): bool {
    if (empty($value) || !is_string($value)) {
        return false;
    }

    return strtolower($value)  === 'smtp';
}

/**
 * Check if is string is an allowed module name
 * @param string $value
 * @return bool
 */
function isAllowedModuleName(string $value): bool {
    if (empty($value)) {
        return false;
    }

    $result = preg_match("/^[\w\-\_\.]+$/", $value);

    if (!empty($result)) {
        return true;
    }

    return false;
}

/**
 * @param $endpoint
 * @return bool
 */
function isSelfRequest($endpoint) : bool {
    $domain = 'localhost';
    if (isset($_SERVER["HTTP_HOST"])) {
        $domain = $_SERVER["HTTP_HOST"];
    }

    $siteUrl = SugarConfig::getInstance()->get('site_url');
    if (empty($siteUrl)){
        $siteUrl = '';
    }

    return stripos((string) $endpoint, (string) $domain) !== false || stripos((string) $endpoint, (string) $siteUrl) !== false;
}

