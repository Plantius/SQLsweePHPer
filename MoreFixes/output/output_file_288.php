	private function correctErrorDocument($errdoc = null, $throw_exception = false)
	{
		if ($errdoc !== null && $errdoc != '') {
			// not a URL
			if ((strtoupper(substr($errdoc, 0, 5)) != 'HTTP:' && strtoupper(substr($errdoc, 0, 6)) != 'HTTPS:') || !Validate::validateUrl($errdoc)) {
				// a file
				if (substr($errdoc, 0, 1) != '"') {
					$errdoc = FileDir::makeCorrectFile($errdoc);
					// apache needs a starting-slash (starting at the domains-docroot)
					if (!substr($errdoc, 0, 1) == '/') {
						$errdoc = '/' . $errdoc;
					}
				} else {
					// a string (check for ending ")
					// string won't work for lighty
					if (Settings::Get('system.webserver') == 'lighttpd') {
						Response::standardError('stringerrordocumentnotvalidforlighty', '', $throw_exception);
					} elseif (substr($errdoc, -1) != '"') {
						$errdoc .= '"';
					}
				}
			} else {
				if (Settings::Get('system.webserver') == 'lighttpd') {
					Response::standardError('urlerrordocumentnotvalidforlighty', '', $throw_exception);
				}
			}
		}
		return $errdoc;
	}
