<?php
require_once('lib/stripe-php-7.49.0/init.php');

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

define( 'HEXA_DONATION_REOCCURANCE_ONCE', 0);
define( 'HEXA_DONATION_REOCCURANCE_SEMESTERLY', 2);
define( 'HEXA_DONATION_REOCCURANCE_MONTHLY', 12);

define( "BHRLD_DONATION_FORM_NONCE_ACTION", 'donation-form-action');

/**
 * Add stripe when the donate form is used.
 */
function bhrld_donation_form( $atts ) {
	return '<bhrld-donation-form class="shadow" pk="' . STRIPE_PUBLISHABLE_KEY . '" no="' . wp_create_nonce( BHRLD_DONATION_FORM_NONCE_ACTION ) . '"></bhrld-donation-form>';
}
add_shortcode( 'badgerherald_donation_form', 'bhrld_donation_form' );

/**
 * Enqueue hexa scripts and styles.
 */
function hrld_donate_enqueue() {
    wp_enqueue_script( '', 'https://js.stripe.com/v3/', null, null, false );
}
add_action( 'wp_enqueue_scripts', 'hrld_donate_enqueue' );

/**
 * Entrypoint of process-donation route
 */
function hexa_process_donation( WP_REST_Request $request ) {
   $amount = $request->get_param( 'amount' );
   $token = $request->get_param( 'token' );
	$nonce = $request->get_param( 'nonce' );

	wp_verify_nonce( $nonce, BHRLD_DONATION_FORM_NONCE_ACTION );

	$anonymous = $request->get_param( 'anonymous' );
	$comment = $request->get_param( 'comment' );
	
	$email = $request->get_param( 'email' );
	$phone = $request->get_param( 'phone' );

	$first = $request->get_param( 'firstName' );
	$last = $request->get_param( 'lastName' );

	$finalYear = $request->get_param( 'comment' );

	$name = $first . ' ' . $last;

	$address = [
		"street" => $request->get_param( 'street' ),
		"apt" => $request->get_param( 'apt' ),
		"city" => $request->get_param( 'country' ),
		"country" => $request->get_param( 'country' ),
		"zip" => $request->get_param( 'zip' )
	];

	$reoccurance = $request->get_param( 'reoccurance' );

	$contact_info = array(
		"address" => $address,
		"email" => $email,
		"phone" => $phone
	);

	$amountInCents = $amount * 100;
	
	$error = "";
	try {

		$customer = \Stripe\Customer::create([
			'source' => $token,
			'email' => $email,
			'name' => $name,
			'phone' => $phone,
			'metadata' => $address
		]);
	
		if(HEXA_DONATION_REOCCURANCE_ONCE == $reoccurance) {
			$charge = \Stripe\Charge::create([
				"amount" => $amountInCents,
				"currency" => "usd",
				"description" => "Donation to The Badger Herald",
				"customer" => $customer->id,
				"metadata" => ["anonymous" => $anonymous, "comment" => $comment, 'graduated' => $finalYear]
			]);
		} else if(HEXA_DONATION_REOCCURANCE_SEMESTERLY == $reoccurance) {
			\Stripe\Subscription::create([
				"metadata" => ["anonymous" => $anonymous, "comment" => $comment, 'graduated' => $finalYear],
				"customer" => $customer->id,
				"items" => [
				  [
					"plan" => STRIPE_SEMESTERLY_PLAN,
					"quantity" => $amountInCents // charged at 0.01¢ each 
				  ],
				]
			  ]);
		} else if(HEXA_DONATION_REOCCURANCE_MONTHLY == $reoccurance) {
			\Stripe\Subscription::create([
				"metadata" => ["anonymous" => $anonymous, "comment" => $comment, 'graduated' => $finalYear],
				"customer" => $customer->id,
				"items" => [
				  [
					"plan" => STRIPE_MONTHLY_PLAN,
					"quantity" => $amountInCents // charged at 0.01¢ each 
				  ],
				]
			  ]);
		}
	  } catch(\Stripe\Exception\CardException $e) {
		// Since it's a decline, \Stripe\Exception\CardException will be caught
		//echo 'Status is:' . $e->getHttpStatus() . '\n';
		//echo 'Type is:' . $e->getError()->type . '\n';
		//echo 'Code is:' . $e->getError()->code . '\n';
		// param is '' in this case
		//echo 'Param is:' . $e->getError()->param . '\n';
		$error = $e->getError()->message;
		//echo 'Message is:' . $e->getError()->message . '\n';
	  } catch (\Stripe\Exception\RateLimitException $e) {
		// Too many requests made to the API too quickly
		bhrld_send_admin_error_email("RateLimitException", $e);
		$error = "Something wen't wrong. Administrators have been notified";
	  } catch (\Stripe\Exception\InvalidRequestException $e) {
		// Invalid parameters were supplied to Stripe's API
		bhrld_send_admin_error_email("InvalidRequestException", $e);
		$error = $e->getError()->message;
	  } catch (\Stripe\Exception\AuthenticationException $e) {
		// Authentication with Stripe's API failed
		// (maybe you changed API keys recently)
		bhrld_send_admin_error_email("AuthenticationException", $e);
		$error = "Something went wrong. Administrators have been notified";
	  } catch (\Stripe\Exception\ApiConnectionException $e) {
		// Network communication with Stripe failed
		bhrld_send_admin_error_email("ApiConnectionException", $e);
		$error = "Something went wrong. Administrators have been notified";
	  } catch (\Stripe\Exception\ApiErrorException $e) {
		// Display a very generic error to the user, and maybe send
		// yourself an email
		bhrld_send_admin_error_email("ApiErrorException", $e);
		$error = "Something went wrong. Administrators have been notified";
	  } catch (Exception $e) {
		// Something else happened, completely unrelated to Stripe
	  }

	if($error == "") {
		hexa_donate_save_donation_from_form( $email, $amount, "asdf" , null, $contact_info );
		hexa_donate_send_reciept( $first + " " + $last, $email, $amount, $reoccurance );
		wp_send_json(array(
			"success" => true
		));
	}  else {
		wp_send_json(array(
			"success" => false,
			"error" => $error
		));
	}

}

function hexa_donate_send_reciept( $name, $email, $amount, $reoccurance ) {
	$frequency = "One Time";
	
	if ($reoccurance == HEXA_DONATION_REOCCURANCE_MONTHLY) {
		$frequency = "Monthly";
	} else if ($reoccurance == HEXA_DONATION_REOCCURANCE_SEMESTERLY) {
		$frequency == "Each Semester";
	}

	$headers = 'From: editor@badgerherald.com' . "\r\n" .
    'Reply-To: editor@badgerherald.com' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();
	
	$message = "$name, 

	Thank you for your donation to The Badger Herald! 

	Amount: \$$amount
	Frequency: $frequency
	
	The Badger Herald is a 501c(3). All donations tax deductable. EIN 39-1129947.

	The Badger Herald
	";
	wp_mail( $email, "Thank you for your donation to The Badger Herald!", $message, $headers, null );
}

function bhrld_send_admin_error_email($subject, $error) {

	return; // todo, test.

	$headers = 'From: <email>' . "\r\n" .
   'Reply-To: <email>' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();
	
	$message = "The following error occurred:" . "\r\n". "\r\n";
	$message .= print_r($error,true);

	wp_mail( "<email>" , $subject, $message, $headers, null );
}

function hexa_donate_save_donation_from_form( $email, $amount, $transaction_id, $frequency, array $contact_info ) {
	$user_id = username_exists( $email );

	if ( !$user_id and email_exists($user_email) == false ) {
		$random_password = wp_generate_password( 12, false );
		$user_id = wp_create_user( $email, $random_password, $email );
	}

	$index = "";

	$contact_index = hexa_donate_save_contact_info( $user_id, $contact_info );
	hexa_donation_save_charge( $user_id, $amount, $index, $transaction_id, $contact_index );
}

function hexa_donation_save_charge( $user_id, $amount, $reoccurance_index, $transaction_id, $contact_index ) {
	$key = 'hexa_donation_reoccurances';

	$donation = array(
		"amount" => $amount,
		"transaction" => $transaction_id,
		"reoccurance_index" => $reoccurance_index,
		"date" => current_time('timestamp'),
		"contact_index" => $contact_index
	);

	add_user_meta( $user_id, $key, $donation, false );
}

function hexa_donate_save_contact_info( $user_id, $contact_info ) {
	$key = 'hexa_donation_contact_info';

	$contact = get_user_meta( $user_id, $key, true );
	
	if( !$contact ) {
		$contact = array();
	}

	$contact[] = $contact_info;

	update_user_meta( $user_id, $key, $contact );

	return count($contact);
}

function hexa_donate_register_rest_route() {
    register_rest_route( 'hexa/v1', 'process-donation', array(
        'methods'  => 'POST',
        'callback' => 'hexa_process_donation',
        'args' => array(
        'amount' => array(
            'validate_callback' => function($param, $request, $key) {
                return is_numeric( $param );
            },
            'required'=> true
        ),
        'token' => array('required'=> true),
        'nonce' => array('required'=> true)
        ),
    ));
}
add_action('rest_api_init', 'hexa_donate_register_rest_route');

function hexa_count_campaign($html, $charge_response) {
	global $post;
	$payment_details = array( 
		'charge' => $charge_response['amount'],
		'id' => $charge_response['id'],
		'email' => $charge_response['name']
	);
	$payment_ids = get_post_meta($post->ID,'_stripe_payment_ids');
	if( !in_array($charge_response['id'],$payment_ids) ) {
		$payment_ids[] = $charge_response['id'];
		add_post_meta($post->ID,'_stripe_payment',$payment_details);
		update_post_meta($post->ID,'_stripe_total', get_post_meta($post->ID,'_stripe_total',true) + $charge_response['amount']);
		update_post_meta($post->ID,'_stripe_payment_ids',$payment_ids);
	}
	return $html;
}
add_action('sc_payment_details','hexa_count_campaign',10,2);

function hexa_stripe_print_stripe_total( $atts ) {
	$atts = shortcode_atts( array(
        'target' => 50000,
        'post' => get_post(null)
    ), $atts );
	$post = get_post($atts["post"]);
	$total = get_post_meta($post->ID,'_stripe_total',true);
	$target = $atts["target"];
	$totalStr = '<span class="stripe-campaign-number">$' . number_format($total/100, 2) . '</span> donated';
	$targetStr = 'of <span class="stripe-campaign-number">$' . number_format($target/100, 2) . "</span> goal.";
	$percentage = 100*( intval($total)/intval($target) );
	$ret = '';
	$ret .= "<div class='stripe-campaign-ticker' style='position:relative;display:block;' />";
		$ret .= "<div class='stripe-campaign-ticker-strip-outer'><div class='stripe-campaign-ticker-strip-inner' style='width:{$percentage}%' ></div></div>";
		$ret .= "<span stripe-campaign-string' >$totalStr</span>";
		$ret .= "<span stripe-campaign-string' style='position:absolute;right:0'>$targetStr</span>";
	$ret .= "!!</div>";
	return $ret;
} 
add_shortcode( 'stripe_campaign_total', 'hexa_stripe_print_stripe_total' );
