    public function get_tab_data($serial_number = '')
    {
        jsonView([
            Softwareupdate_model::select('softwareupdate.automaticcheckenabled', 'softwareupdate.automaticdownload', 'softwareupdate.configdatainstall', 'softwareupdate.criticalupdateinstall', 'softwareupdate.auto_update', 'softwareupdate.auto_update_restart_required', 'softwareupdate.lastattemptsystemversion', 'softwareupdate.lastbackgroundsuccessfuldate', 'softwareupdate.lastfullsuccessfuldate', 'softwareupdate.lastsuccessfuldate', 'softwareupdate.lastresultcode', 'softwareupdate.lastsessionsuccessful', 'softwareupdate.lastupdatesavailable', 'softwareupdate.lastrecommendedupdatesavailable', 'softwareupdate.recommendedupdates', 'softwareupdate.inactiveupdates', 'softwareupdate.catalogurl', 'softwareupdate.skiplocalcdn', 'softwareupdate.skip_download_lack_space', 'softwareupdate.eval_critical_if_unchanged', 'softwareupdate.one_time_force_scan_enabled', 'softwareupdate.xprotect_version', 'softwareupdate.mrxprotect',  'softwareupdate.xprotect_payloads_version', 'softwareupdate.xprotect_payloads_last_modified', 'softwareupdate.gatekeeper_version', 'softwareupdate.gatekeeper_last_modified', 'softwareupdate.gatekeeper_disk_version', 'softwareupdate.gatekeeper_disk_last_modified', 'softwareupdate.kext_exclude_version', 'softwareupdate.kext_exclude_last_modified', 'softwareupdate.mrt_version', 'softwareupdate.mrt_last_modified', 'softwareupdate.enrolled_seed', 'softwareupdate.program_seed', 'softwareupdate.build_is_seed', 'softwareupdate.show_feedback_menu', 'softwareupdate.disable_seed_opt_out', 'softwareupdate.catalog_url_seed', 'softwareupdate.softwareupdate_history')
            ->whereSerialNumber($serial_number)
            ->filter()
            ->limit(1)
            ->first()
            ->toArray()
        ]);
    }
