function make_sugar_config(&$sugar_config)
{
    /* used to convert non-array config.php file to array format */
    global $admin_export_only;
    global $cache_dir;
    global $calculate_response_time;
    global $create_default_user;
    global $dateFormats;
    global $dbconfig;
    global $dbconfigoption;
    global $default_action;
    global $default_charset;
    global $default_currency_name;
    global $default_currency_symbol;
    global $default_currency_iso4217;
    global $defaultDateFormat;
    global $default_language;
    global $default_module;
    global $default_password;
    global $default_theme;
    global $defaultTimeFormat;
    global $default_user_is_admin;
    global $default_user_name;
    global $disable_export;
    global $disable_persistent_connections;
    global $display_email_template_variable_chooser;
    global $display_inbound_email_buttons;
    global $google_auth_json;
    global $history_max_viewed;
    global $host_name;
    global $import_dir;
    global $languages;
    global $list_max_entries_per_page;
    global $lock_default_user_name;
    global $log_memory_usage;
    global $nameFormats;
    global $requireAccounts;
    global $RSS_CACHE_TIME;
    global $session_dir;
    global $site_URL;
    global $site_url;
    global $sugar_version;
    global $timeFormats;
    global $tmp_dir;
    global $translation_string_prefix;
    global $unique_key;
    global $upload_badext;
    global $upload_dir;
    global $upload_maxsize;
    global $import_max_execution_time;
    global $list_max_entries_per_subpanel;
    global $passwordsetting;

    // assumes the following variables must be set:
    // $dbconfig, $dbconfigoption, $cache_dir,  $session_dir, $site_URL, $upload_dir

    $sugar_config = array(
        'admin_export_only' => empty($admin_export_only) ? false : $admin_export_only,
        'export_delimiter' => empty($export_delimiter) ? ',' : $export_delimiter,
        'cache_dir' => empty($cache_dir) ? 'cache/' : $cache_dir,
        'calculate_response_time' => empty($calculate_response_time) ? true : $calculate_response_time,
        'create_default_user' => empty($create_default_user) ? false : $create_default_user,
        'chartEngine' => 'Jit',
        'pdf' => [
            'defaultEngine' => 'TCPDFEngine'
        ],
        'date_formats' => empty($dateFormats) ? array(
            'Y-m-d' => '2010-12-23',
            'd-m-Y' => '23-12-2010',
            'm-d-Y' => '12-23-2010',
            'Y/m/d' => '2010/12/23',
            'd/m/Y' => '23/12/2010',
            'm/d/Y' => '12/23/2010',
            'Y.m.d' => '2010.12.23',
            'd.m.Y' => '23.12.2010',
            'm.d.Y' => '12.23.2010',
        ) : $dateFormats,
        'dbconfig' => $dbconfig, // this must be set!!
        'dbconfigoption' => $dbconfigoption, // this must be set!!
        'default_action' => empty($default_action) ? 'index' : $default_action,
        'default_charset' => empty($default_charset) ? 'UTF-8' : $default_charset,
        'default_currency_name' => empty($default_currency_name) ? 'US Dollar' : $default_currency_name,
        'default_currency_symbol' => empty($default_currency_symbol) ? '$' : $default_currency_symbol,
        'default_currency_iso4217' => empty($default_currency_iso4217) ? '$' : $default_currency_iso4217,
        'default_date_format' => empty($defaultDateFormat) ? 'm/d/Y' : $defaultDateFormat,
        'default_locale_name_format' => empty($defaultNameFormat) ? 's f l' : $defaultNameFormat,
        'default_export_charset' => 'UTF-8',
        'default_language' => empty($default_language) ? 'en_us' : $default_language,
        'default_module' => empty($default_module) ? 'Home' : $default_module,
        'default_password' => empty($default_password) ? '' : $default_password,
        'default_permissions' => array(
            'dir_mode' => 02770,
            'file_mode' => 0755,
            'chown' => '',
            'chgrp' => '',
        ),
        'default_theme' => empty($default_theme) ? 'SuiteP' : $default_theme,
        'default_time_format' => empty($defaultTimeFormat) ? 'h:ia' : $defaultTimeFormat,
        'default_user_is_admin' => empty($default_user_is_admin) ? false : $default_user_is_admin,
        'default_user_name' => empty($default_user_name) ? '' : $default_user_name,
        'disable_export' => empty($disable_export) ? false : $disable_export,
        'disable_persistent_connections' => empty($disable_persistent_connections) ? false : $disable_persistent_connections,
        'display_email_template_variable_chooser' => empty($display_email_template_variable_chooser) ? false : $display_email_template_variable_chooser,
        'display_inbound_email_buttons' => empty($display_inbound_email_buttons) ? false : $display_inbound_email_buttons,
        'google_auth_json' => empty($google_auth_json) ? '' : $google_auth_json,
        'history_max_viewed' => empty($history_max_viewed) ? 50 : $history_max_viewed,
        'host_name' => empty($host_name) ? 'localhost' : $host_name,
        'import_dir' => $import_dir, // this must be set!!
        'import_max_records_per_file' => 100,
        'import_max_records_total_limit' => '',
        'languages' => empty($languages) ? array('en_us' => 'English (US)') : $languages,
        'list_max_entries_per_page' => empty($list_max_entries_per_page) ? 20 : $list_max_entries_per_page,
        'list_max_entries_per_subpanel' => empty($list_max_entries_per_subpanel) ? 10 : $list_max_entries_per_subpanel,
        'lock_default_user_name' => empty($lock_default_user_name) ? false : $lock_default_user_name,
        'log_memory_usage' => empty($log_memory_usage) ? false : $log_memory_usage,
        'name_formats' => empty($nameFormats) ? array(
            's f l' => 's f l',
            'f l' => 'f l',
            's l' => 's l',
            'l, s f' => 'l, s f',
            'l, f' => 'l, f',
            's l, f' => 's l, f',
            'l s f' => 'l s f',
            'l f s' => 'l f s',
        ) : $nameFormats,
        'oauth2_encryption_key' => base64_encode(random_bytes(32)),
        'portal_view' => 'single_user',
        'resource_management' => array(
            'special_query_limit' => 50000,
            'special_query_modules' => array('AOR_Reports', 'Export', 'Import', 'Administration', 'Sync'),
            'default_limit' => 1000,
        ),
        'require_accounts' => empty($requireAccounts) ? true : $requireAccounts,
        'rss_cache_time' => empty($RSS_CACHE_TIME) ? '10800' : $RSS_CACHE_TIME,
        'session_dir' => $session_dir, // this must be set!!
        'site_url' => empty($site_URL) ? $site_url : $site_URL, // this must be set!!
        'showDetailData' => true, // if true, read-only ACL fields will still appear on EditViews as non-editable
        'showThemePicker' => true,
        'sugar_version' => empty($sugar_version) ? 'unknown' : $sugar_version,
        'time_formats' => empty($timeFormats) ? array(
            'H:i' => '23:00',
            'h:ia' => '11:00 pm',
            'h:iA' => '11:00PM',
            'H.i' => '23.00',
            'h.ia' => '11.00 pm',
            'h.iA' => '11.00PM',
        ) : $timeFormats,
        'tmp_dir' => $tmp_dir, // this must be set!!
        'translation_string_prefix' => empty($translation_string_prefix) ? false : $translation_string_prefix,
        'unique_key' => empty($unique_key) ? md5(create_guid()) : $unique_key,
        'upload_badext' => empty($upload_badext) ? array(
            'php',
            'php3',
            'php4',
            'php5',
            'php6',
            'php7',
            'php8',
            'pl',
            'cgi',
            'py',
            'asp',
            'cfm',
            'js',
            'vbs',
            'html',
            'htm',
            'phtml',
            'phar',
        ) : $upload_badext,
        'valid_image_ext' => [
            'gif',
            'png',
            'jpg',
            'jpeg',
            'svg'
        ],
        'upload_dir' => $upload_dir, // this must be set!!
        'upload_maxsize' => empty($upload_maxsize) ? 30000000 : $upload_maxsize,
        'allowed_preview' => [
            'pdf',
            'gif',
            'png',
            'jpeg',
            'jpg'
        ],
        'import_max_execution_time' => empty($import_max_execution_time) ? 3600 : $import_max_execution_time,
        'lock_homepage' => false,
        'lock_subpanels' => false,
        'max_dashlets_homepage' => 15,
        'dashlet_display_row_options' => array('1', '3', '5', '10'),
        'default_max_tabs' => empty($max_tabs) ? 10 : $max_tabs,
        'default_subpanel_tabs' => empty($subpanel_tabs) ? true : $subpanel_tabs,
        'default_subpanel_links' => empty($subpanel_links) ? false : $subpanel_links,
        'default_swap_last_viewed' => empty($swap_last_viewed) ? false : $swap_last_viewed,
        'default_swap_shortcuts' => empty($swap_shortcuts) ? false : $swap_shortcuts,
        'default_navigation_paradigm' => empty($navigation_paradigm) ? 'gm' : $navigation_paradigm,
        'default_call_status' => 'Planned',
        'js_lang_version' => 1,
        'passwordsetting' => empty($passwordsetting) ? array(
            'SystemGeneratedPasswordON' => '',
            'generatepasswordtmpl' => '',
            'lostpasswordtmpl' => '',
            'factoremailtmpl' => '',
            'forgotpasswordON' => true,
            'linkexpiration' => '1',
            'linkexpirationtime' => '30',
            'linkexpirationtype' => '1',
            'systexpiration' => '0',
            'systexpirationtime' => '',
            'systexpirationtype' => '0',
            'systexpirationlogin' => '',
        ) : $passwordsetting,
        'use_sprites' => function_exists('imagecreatetruecolor'),
        'search_wildcard_infront' => false,
        'search_wildcard_char' => '%',
        'jobs' => array(
            'min_retry_interval' => 60, // minimal job retry delay
            'max_retries' => 5, // how many times to retry the job
            'timeout' => 86400, // how long a job may spend as running before being force-failed
            'soft_lifetime' => 7, // how many days until job record will be soft deleted after completion
            'hard_lifetime' => 21, // how many days until job record will be purged from DB
        ),
        'cron' => array(
            'max_cron_jobs' => 10, // max jobs per cron schedule run
            'max_cron_runtime' => 60, // max runtime for cron jobs
            'min_cron_interval' => 30, // minimal interval between cron jobs
        ),
        'strict_id_validation' => false,
        'legacy_email_behaviour' => false,
    );
}
