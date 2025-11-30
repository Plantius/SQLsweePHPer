				syslog(LOG_INFO, $environ . ': ' . $string);
			}

			closelog();
		}
   }

	/* print output to standard out if required */
	if ($output == true && isset($_SERVER['argv'][0])) {
		print $message;
	}
}
