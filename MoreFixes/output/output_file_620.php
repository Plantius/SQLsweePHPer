	rsvpmaker_tx_email($post, $mail);
	}

$send_confirmation = get_post_meta($post->ID,'_rsvp_rsvpmaker_send_confirmation_email',true);
$confirm_on_payment = get_post_meta($post->ID,'_rsvp_confirmation_after_payment',true);

if(($send_confirmation ||!is_numeric($send_confirmation)) && empty($confirm_on_payment) )//if it hasn't been set to 0, send it
{
$confirmation_subject = $templates['confirmation']['subject']; 
foreach($rsvpdata as $field => $value)
	$confirmation_subject = str_replace('['.$field.']',$value,$confirmation_subject);

$confirmation_body = $templates['confirmation']['body']; 
foreach($rsvpdata as $field => $value)
	$confirmation_body = str_replace('['.$field.']',$value,$confirmation_body);
	
	$confirmation_body = do_blocks(do_shortcode($confirmation_body));	
	$mail["html"] = wpautop($confirmation_body);
	if(isset($post->ID)) // not for replay
	$mail["ical"] = rsvpmaker_to_ical_email ($post->ID, $rsvp_to, $rsvp["email"]);
	$mail["to"] = $rsvp["email"];
	$mail["from"] = $rsvp_to_array[0];
	$mail["fromname"] = get_bloginfo('name');
	$mail["subject"] = $confirmation_subject;
	rsvpmaker_tx_email($post, $mail);	
}

}
