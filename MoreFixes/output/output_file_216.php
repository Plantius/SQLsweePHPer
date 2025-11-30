function raise_message_javascript($title, $header, $message) {
	?>
	<script type='text/javascript'>
	var mixedReasonTitle = '<?php print $title;?>';
	var mixedOnPage      = '<?php print $header;?>';
	sessionMessage   = {
		message: '<?php print $message;?>',
		level: MESSAGE_LEVEL_MIXED
	};

	$(function() {
		displayMessages();
	});
	</script>
	<?php

	exit;
}
