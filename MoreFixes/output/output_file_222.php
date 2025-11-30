function rsvpmaker_stripecharge( $atts ) {

	if ( is_admin() || wp_is_json_request() ) {

		return;
	}

	global $current_user;

	$vars['description'] = ( ! empty( $atts['description'] ) ) ? $atts['description'] : __( 'charge from', 'rsvpmaker' ) . ' ' . get_bloginfo( 'name' );

	$vars['paymentType'] = $paymentType = ( empty( $atts['paymentType'] ) ) ? 'once' : $atts['paymentType'];

	$vars['paypal'] = (empty($atts['paypal'])) ? 0 : $atts['paypal'];

	$show = ( ! empty( $atts['showdescription'] ) && ( $atts['showdescription'] == 'yes' ) ) ? true : false;

	if ( $paymentType == 'schedule' ) {

		$months = array( 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december' );

		$index = date( 'n' ) - 1;

		if ( isset( $_GET['next'] ) ) {

			if ( $index == 11 ) {

				$index = 0;

			} else {
				$index++;
			}
		}

		$month = $months[ $index ];

		$vars['amount'] = $atts[ $month ];

		$vars['description'] = $vars['description'] . ': ' . ucfirst( $month );

		if ( ! empty( $current_user->user_email ) ) {

			$vars['email'] = $current_user->user_email;
		}

		return rsvpmaker_stripe_form( $vars, $show );

	}

	$vars['amount'] = ( ! empty( $atts['amount'] ) ) ? $atts['amount'] : '';

	if ( $paymentType != 'once' ) {

		$vars['description'] .= ' ' . $paymentType;
	}

	return rsvpmaker_stripe_form( $vars, $show );

	// return rsvpmaker_stripe_form($vars,$show);
}
