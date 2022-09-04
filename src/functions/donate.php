<?php

require_once('donate/lib/stripe-php-7.49.0/init.php');
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

define( 'BHRLD_DONATION_REOCCURANCE_ONCE', 0);
define( 'BHRLD_DONATION_REOCCURANCE_SEMESTERLY', 2);
define( 'BHRLD_DONATION_REOCCURANCE_MONTHLY', 12);
define( "BHRLD_DONATION_FORM_NONCE_ACTION", 'donation-form-action');


//
// Main business logic for creating a customer
// and establishing a one-time or reoccuring charge
// 
function bhrld_donate_process_donation( $token, $name, $email, $amount, $commment, $reoccurance ) {
	$error;
	$amountInCents = $amount * 100;

	try {
		$customerID = bhrld_donate_create_customer( $token, $name, $email );
	
		if (BHRLD_DONATION_REOCCURANCE_ONCE == $reoccurance) {
			$charge = bhrld_get_stripe_charge_object( $customerID, $amountInCents, $comment );
			\Stripe\Charge::create( $charge );
		} else {
			$subscription = bhrld_get_stripe_subscription_object( $customerID, $amountInCents, $comment, $reoccurance);
			\Stripe\Subscription::create( $subscription );
		}

	} catch(\Stripe\Exception\CardException $e) {
		$error = $e->getError()->message;
	} catch (\Stripe\Exception\RateLimitException $e) {
		bhrld_donate_send_webmaster_error_email("RateLimitException", $e);
		$error = "Something wen't wrong and you were not charged. Administrators have been notified";
	} catch (\Stripe\Exception\InvalidRequestException $e) {
		bhrld_donate_send_webmaster_error_email("InvalidRequestException", $e);
		$error = $e->getError()->message;
	} catch (\Stripe\Exception\AuthenticationException $e) {
		bhrld_donate_send_webmaster_error_email("AuthenticationException", $e);
		$error = "Something went wrong and you were not charged. Administrators have been notified";
	} catch (\Stripe\Exception\ApiConnectionException $e) {
		bhrld_donate_send_webmaster_error_email("ApiConnectionException", $e);
		$error = "Something went wrong and you were not charged. Administrators have been notified";
	} catch (\Stripe\Exception\ApiErrorException $e) {
		bhrld_donate_send_webmaster_error_email("ApiErrorException", $e);
		$error = "Something went wrong and you were not charged. Administrators have been notified";
	} catch (Exception $e) {
		bhrld_donate_send_webmaster_error_email("UnknownException", $e);
		$error = "Something went wrong and you were not charged. Administrators have been notified";
	}

	if( !$error ) {
		bhrld_donate_send_reciept( $name, $email, $amount, $reoccurance );
		wp_send_json(array(
			"success" => true
		));
	} else {
		wp_send_json(array(
			"success" => false,
			"error" => $error
		));
	}
}



//
// REGION: Creating customers
//

function bhrld_donate_create_customer( $token, $name, $email ) {
	$customer = \Stripe\Customer::create([
		'source' => $token,
		'email' => $email,
		'name' => $name,
	]);
	return $customer->id;
}



//
// REGION: Creating charges
//

function bhrld_get_stripe_base_charge_object( $customerID, $comment ) {
	return [
		"metadata" => [ "comment" => $comment ],
		"customer" => $customerID,
	];
}

function bhrld_get_stripe_charge_object( $customerID, $amountInCents, $comment ) {
	$baseCharge = bhrld_get_stripe_base_charge_object( $customerID, $comment );
	$chargeDetails = [
		"amount" => $amountInCents,
		"currency" => "usd",
		"description" => "Donation to The Badger Herald",
	];

	return array_merge( $baseCharge, $chargeDetails );
}

function bhrld_get_stripe_subscription_object( $customerID, $amountInCents, $comment, $reoccurance ) {
	if ($reoccurance == BHRLD_DONATION_REOCCURANCE_SEMESTERLY) {
		$product = STRIPE_SEMESTERLY_PROD;
		$interval = 6;
	} else if ($reoccurance == BHRLD_DONATION_REOCCURANCE_MONTHLY) {
		$product = STRIPE_MONTHLY_PROD;
		$interval = 1;
	} else {
		return;
	}

	$baseCharge = bhrld_get_stripe_base_charge_object( $customerID, $comment );
	$subscription = [
		"items" => [
			[
				"price_data" => [
					"currency" => "USD",
					"product" => $product,
					"recurring" => [
						"interval_count" => $interval,
						"interval" => "month"
					],
					"unit_amount" => $amountInCents
				]
			],
		]
	];

	return array_merge( $baseCharge, $subscription );
}



//
// REGION: emails
//

function bhrld_donate_send_reciept( $name, $email, $amount, $reoccurance ) {
	$frequency = "One Time";
	
	if ($reoccurance == BHRLD_DONATION_REOCCURANCE_MONTHLY) {
		$frequency = "Monthly";
	} else if ($reoccurance == BHRLD_DONATION_REOCCURANCE_SEMESTERLY) {
		$frequency = "Each Semester";
	}

	$headers = 'From: ' . BHRLD_SENDFROM_EMAIL . "\r\n" .
	'Reply-To: ' . BHRLD_REPLYTO_EMAIL . "\r\n" .
	'X-Mailer: PHP/' . phpversion();
	
	$message = "$name, \n\r";
	$message .= "\n\r";
	$message .= "Thank you for your donation to The Badger Herald! \n\r";
	$message .= "\n\r";
	$message .= "Amount: \$$amount \n\r";
	$message .= "Frequency: $frequency \n\r";
	$message .= "\n\r";
	$message .= "The Badger Herald is a 501c(3). All donations tax deductable. EIN 39-1129947. \n\r";
	$message .= "\n\r";
	$message .= "Please reach out to editor@badgerherald.com to manage your subscription \n\r";
	$message .= "\n\r";
	$message .= "— The Badger Herald \n\r";

	wp_mail( $email, "Thank you for your donation to The Badger Herald!", $message, $headers, null );
}

function bhrld_donate_send_webmaster_error_email($subject, $error) {
	$headers = 'From: ' . BHRLD_SENDFROM_EMAIL . "\r\n" .
	'Reply-To: ' . BHRLD_REPLYTO_EMAIL . "\r\n" .
	'X-Mailer: PHP/' . phpversion();
	
	$message = "The following error occurred:" . "\r\n". "\r\n";
	$message .= print_r($error,true);

	wp_mail( BHRLD_WEBMASTER_EMAIL , $subject, $message, $headers, null );
}



//
// REGION: Routes
//

function bhrld_donate_register_rest_route() {
	register_rest_route( 'donate/v1', 'process-donation', array(
		'methods'  => 'POST',
		'callback' => 'bhrld_donate_process_donation_entry',
		'args' => array(
			'amount' => array(
				'required'=> true,
				'validate_callback' => function($param, $request, $key) {
					return is_numeric( $param );
				}
			),
			'nonce' => array(
				'required' => true,
				'validate_callback' => function($param, $request, $key) {
					return 1 == wp_verify_nonce( $param, BHRLD_DONATION_FORM_NONCE_ACTION );
				}
			),
			'token' => array(
				'required' => true,
			),
			'recaptcha' => array(
				'required' => true,
				'validate_callback' => function($param, $request, $key) {
					$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
					$recaptcha_secret = RECAPTCHA_SECRET_KEY;
					$recaptcha_response = $param;
					
					$recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
					$recaptcha = json_decode($recaptcha);
					
					return $recaptcha->score >= 0.5;
				}
			),
			'reoccurance' => array(
				'required' => true,
				'validate_callback' => function($param, $request, $key) {
					return $param == BHRLD_DONATION_REOCCURANCE_ONCE ||
						$param == BHRLD_DONATION_REOCCURANCE_SEMESTERLY ||
						$param == BHRLD_DONATION_REOCCURANCE_MONTHLY;
					}
				)
			)
		)
	);
}
add_action('rest_api_init', 'bhrld_donate_register_rest_route');

function bhrld_donate_process_donation_entry( WP_REST_Request $request ) {
	$token = $request->get_param( 'token' );
	$amount = $request->get_param( 'amount' );
	$comment = $request->get_param( 'comment' );
	$email = $request->get_param( 'email' );
	$first = $request->get_param( 'first' );
	$last = $request->get_param( 'last' );
	$name = $first . ' ' . $last;

	$reoccurance = $request->get_param( 'reoccurance' );
	$amountInCents = $amount * 100;

	bhrld_donate_process_donation( $token, $name, $email, $amount, $commment, $reoccurance );
}


//
// REGION: Other WordPress Registrations
// 

// Shortcode
function bhrld_donation_form( $atts ) {
	$atts = shortcode_atts( array(
        'title' => 'Donate',
        'subhead' => 'Support the Herald!'
    ), $atts, 'badgerherald_donation_form' );

	wp_enqueue_script( 'donate-stripe-js' );
	wp_enqueue_script( 'google-recaptcha' );

	
	return apply_filters( 'bhrld_donate_form_shortcode', 
								'<bhrld-donation-form class="shadow" 
									formTitle="' . $atts["title"] . '"
									subhead="' . $atts["subhead"] . '"
									rk="' . RECAPTCHA_SITE_KEY . '"
									pk="' . STRIPE_PUBLISHABLE_KEY . '" 
									no="' . wp_create_nonce( BHRLD_DONATION_FORM_NONCE_ACTION ) . '"
									ht="' . wp_create_nonce( 'wp_rest' ) . '"
									></bhrld-donation-form>');	 
}
add_shortcode( 'badgerherald_donation_form', 'bhrld_donation_form' );

function bhrld_donate_enqueue() {
	wp_register_script( 'donate-stripe-js', 'https://js.stripe.com/v3/', null, null, true );
	wp_register_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . RECAPTCHA_SITE_KEY , null, null, true );
}
add_action( 'wp_enqueue_scripts', 'bhrld_donate_enqueue' );