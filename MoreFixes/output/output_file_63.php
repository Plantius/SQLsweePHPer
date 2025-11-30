	} elseif ($log) {
		cacti_log("ERROR: SQL Row Failed!, Error:$en, SQL:'" . clean_up_lines($sql) . "'", false, 'DBCALL', POLLER_VERBOSITY_DEVDBG);
		cacti_log('ERROR: SQL Row Failed!, Error: ' . $errorinfo[2], false, 'DBCALL', POLLER_VERBOSITY_DEVDBG);
		cacti_debug_backtrace('SQL');
	}
