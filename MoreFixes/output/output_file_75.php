function addHighscore($name, $score, $level) {

	$db = new SQLite3('pacman.db');
	$date = date('Y-m-d h:i:s', time());
	createDataBase($db);
	$ref = isset($_SERVER[ 'HTTP_REFERER']) ? $_SERVER[ 'HTTP_REFERER'] : "";
	$ua = isset($_SERVER[ 'HTTP_USER_AGENT']) ? $_SERVER[ 'HTTP_USER_AGENT'] : "";
	$remA = isset($_SERVER[ 'REMOTE_ADDR']) ? $_SERVER[ 'REMOTE_ADDR'] : "";
	$remH = isset($_SERVER[ 'REMOTE_HOST']) ? $_SERVER[ 'REMOTE_HOST'] : "";

	// some simple checks to avoid cheaters
	$ref_assert = preg_match('/http(s)?:\/\/.*' . $hostdomain . '/', $ref) > 0;
	$ua_assert = ($ua != "");
	$cheater = 0;
	if (!$ref_assert || !$ua_assert) {
		$cheater = 1;
	}

	$maxlvlpoints_pills = 104 * 10;
	$maxlvlpoints_powerpills = 4 * 50;
	$maxlvlpoints_ghosts = 4 * 4 * 100;
	$maxlvlpoints = $maxlvlpoints_pills + $maxlvlpoints_powerpills + $maxlvlpoints_ghosts;

	// check if score is even possible
	if ($level < 1 || $level > 10) {
		$cheater = 1;
	} else if (($score / $level) > $maxlvlpoints) {
		$cheater = 1;
	}

	$name_clean = htmlspecialchars($name);
	$score_clean = htmlspecialchars($score);

	$db->exec('INSERT INTO highscore (name, score, level, date, log_referer, log_user_agent, log_remote_addr, log_remote_host, cheater) '
		. 'VALUES ("' 
			. $name . '", ' 
			. $score . ', ' 
			. $level . ', "' 
			. $date . '", "' 
			. $ref .'", "'
			. $ua . '", "'
			. $remA .'", "'
			. $remH . '", "'
			. $cheater
		.'")'
	);

	$response['status'] = "success";
	$response['level'] = $level;
	$response['name'] = $name;
	$response['score'] = $score;
	$response['cheater'] = $cheater;
	return json_encode($response);
}
