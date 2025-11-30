				Settings::Set($index, $value);
			}
			// save to DB
			Settings::Flush();
			// all good
			return true;
		}
		throw new Exception("Invalid JSON data: " . json_last_error_msg());
	}
