function rsvpmaker_stripe_checkout() {

	rsvpmaker_debug_log( 'rsvpmaker_stripe_checkout' );

	global $post, $rsvp_options, $current_user;

	if ( empty( $_GET['txid'] ) ) {

		return;
	}

	ob_start();

	$varkey = $idempotency_key = sanitize_text_field( $_GET['txid'] );

	$vars = get_option( $idempotency_key );

	if ( empty( $vars ) ) {

		return '<p>' . __( 'No pending payment found for', 'rsvpmaker' ) . ' ' . esc_html( $idempotency_key ) . '</p>';
	}

	if ( $vars['paymentType'] == 'donation' ) {

		if ( empty( $_GET['amount'] ) ) {

			return '<p>No amount given</p>';
		}

		$vars['amount'] = sanitize_text_field( $_GET['amount'] );

	}

	if(!empty($_GET['stripenote']))
		$vars['note'] = sanitize_text_field($_GET['stripenote']);

	update_option( $idempotency_key, $vars );
	
	require_once 'stripe-php/init.php';

	$keys = get_rsvpmaker_stripe_keys();

	if ( ! empty( $vars['email'] ) ) {

		$email = sanitize_email( $vars['email'] );

		$name = ( empty( $vars['name'] ) ) ? '' : sanitize_text_field( $vars['name'] );

	} else {

		$email = ( empty( $current_user->user_email ) ) ? '' : $current_user->user_email;

		$wpname = '';
		if ( ! empty( $current_user->ID ) ) {
			$userdata = get_userdata( $current_user->ID );
			if ( $userdata->first_name ) {
				$wpname = $userdata->first_name . ' ' . $userdata->last_name;
			} else {
				$wpname = $userdata->display_name;
			}
		}
		$name = ( empty( $wpname ) ) ? '' : $wpname;
	}

	$public = $keys['pk'];

	$secret = $keys['sk'];

	if ( strpos( $public, 'test' ) ) {

		$vars['test'] = 'TEST TRANSACTION';
	}

	$currency_symbol = '';

	if ( $vars['currency'] == 'usd' ) {

		$currency_symbol = '$';

	} elseif ( $vars['currency'] == 'eur' ) {

		$currency_symbol = '€';
	}

	$paylabel = __( 'Pay', 'rsvpmaker' ) . ' ' . $currency_symbol . esc_attr( $vars['amount'] ) . ' ' . esc_attr( strtoupper( $vars['currency'] ) );

	\Stripe\Stripe::setApiKey( $secret );

	\Stripe\Stripe::setAppInfo(
		'WordPress RSVPMaker events management plugin',
		get_rsvpversion(),
		'https://rsvpmaker.com'
	);

	rsvpmaker_debug_log( 'call to PaymentIntent' );

	$intent = \Stripe\PaymentIntent::create(
		array(

			'amount'               => $vars['amount'] * 100,

			'currency'             => $vars['currency'],

			'description'          => $vars['description'],

			'payment_method_types' => array( 'card' ),

			'statement_descriptor' => substr( 'Paid on ' . sanitize_text_field($_SERVER['SERVER_NAME']), 0, 21 ),

		),
		array( 'idempotency_key' => $idempotency_key )
	);

	update_post_meta( $post->ID, $varkey, $vars );

	$price = $vars['amount'] * 100;

	?>

<!-- Stripe library must be loaded stripe.com per https://stripe.com/docs/js/including -->

<script src="https://js.stripe.com/v3/"></script>

<!-- We'll put the success / error messages in this element -->

<div id="card-result" role="alert"></div>

<div id="stripe-checkout-form">

<form id="payee-form">

<div><input id="stripe-checkout-name" name="name" placeholder="<?php esc_html_e( 'Your Name Here', 'rsvpmaker' ); ?>" value="<?php echo esc_attr( $name ); ?>"></div>

<div><input id="stripe-checkout-email" name="email" placeholder="email@example.com" value="<?php echo esc_attr( $email ); ?>"></div>

<div id="card-element">

  <!-- Elements will create input elements here -->

</div>



<p><button id="card-button" class="stripebutton" data-secret="<?php echo esc_attr( $intent->client_secret ); ?>">

	<?php echo esc_html( $paylabel ); ?>

</button></p>

</form>

	<?php

	if ( strpos( $public, 'test' ) && ! isset( $_GET['hidetest'] ) ) {

		printf( '<p>%s</p>', __( 'Stripe is in TEST mode. To simulate a transaction, use:<br />Credit card 4111 1111 1111 1111<br />Any future date<br />Any three digit CVC code<br />Any 5-digit postal code', 'rsvpmaker' ) );
	}

	?>

</div>

<script>

var stripe = Stripe('<?php echo esc_attr($public); ?>');

var elements = stripe.elements();

var style = {

  base: {

	iconColor: '#111111',

	color: "#111111",

	fontWeight: 400,

	fontSize: '16px',

	'::placeholder': {

	color: '#333333',

	},

	'::-ms-clear': {

	backgroundColor: '#fff',

	},

	  },

	empty: {

	backgroundColor: '#fff',

	  },

	completed: {

	backgroundColor: '#eee',

	  },

};



var card = elements.create("card", { style: style });

card.mount("#card-element");



card.addEventListener('change', ({error}) => {

  const displayError = document.getElementById('card-result');

  if (error) {

	displayError.textContent = error.message;

  } else {

	displayError.textContent = '';

  }

});

var cardFields = document.getElementById('stripe-checkout-form');

var submitButton = document.getElementById('card-button');

var cardResult = document.getElementById('card-result');

var clientSecret = document.getElementById('card-button').getAttribute('data-secret');

submitButton.addEventListener('click', function(ev) {
ev.preventDefault();
var name = document.getElementById('stripe-checkout-name').value;
var email = document.getElementById('stripe-checkout-email').value;
var successurl = '<?php echo site_url( '/wp-json/rsvpmaker/v1/stripesuccess/' . $idempotency_key ); ?>';
if((name == '') || (email == '')){
	cardResult.innerHTML = 'Name and email are both required';
	return;
}
cardResult.innerHTML = '<?php esc_html_e( 'Please wait', 'rsvpmaker' ); ?>';
cardResult.style.cssText = 'background-color: #fff; padding: 10px;';

  stripe.confirmCardPayment(clientSecret, {
	payment_method: {
	  card: card,
	  billing_details: {
		name: name,
		email: email,
	  }
	}
  }).then(function(result) {
	if (result.error) {
		cardResult.innerHTML = result.error.message;
	  console.log(result.error.message);
	  console.log(result);
	} else {
	submitButton.style = 'display: none';
	cardFields.style = 'display: none';
	  if (result.paymentIntent.status === 'succeeded') {
		  console.log(result);
		cardResult.innerHTML = '<?php esc_html_e( 'Recording payment', 'rsvpmaker' ); ?> ...';
		const form = new FormData(document.getElementById('payee-form'));
		fetch(successurl, {
  method: 'POST',

  body: form,

})

		.then((response) => {

			return response.json();

		})

		.then((myJson) => {

			console.log(myJson);

			if(!myJson.name)			

				cardResult.innerHTML = '<?php esc_html_e( 'Payment processed, but may not have been recorded correctly', 'rsvpmaker' ); ?>';

			else

				cardResult.innerHTML = '<?php esc_html_e( 'Payment processed for', 'rsvpmaker' ); ?> '+myJson.name+', '+myJson.description+' <?php echo esc_attr($currency_symbol); ?>'+myJson.amount+' '+myJson.currency.toUpperCase();

		});

	  }

	}

  });

});

</script>

	<?php

	return ob_get_clean();

}
