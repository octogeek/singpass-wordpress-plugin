<?php

/**
 * Plugin Name: Singpass plugin
 * Plugin URI: http://www.octopus8.com
 * Description: Singpass WordPress plugin for Octopus8.
 * Version: 1.0
 * Author: Asliddin Oripov
 * Author Email: asliddin@octopus8.com
 */

require('views/qr-partial.php');
require_once dirname(__FILE__) . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;

function singpass_button()
{
	$path = plugin_dir_url(__FILE__);
	echo '<div class="d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#qr_code_modal">
	<a class="btn btn-outline-secondary btn-lg p-1"><img src=' . $path . 'assets/singpass_logo_fullcolours.png width=100px /></a>
	</div>';

	//	echo '<button type="button" class="btn btn-outline-secondary">Secondary</button>';
}

function singpass_jwks()
{
	//$file = file_get_contents('jwks_keys', true);
	echo get_option('public_jwks');
}

function oidc_signin_callback($params)
{
	$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $url = $protocol . "://$_SERVER[HTTP_HOST]";
    $plugin_name = explode('/', plugin_basename(__FILE__))[0];

	$sig_kid = '';
	$encPrivateKey = null;	

	//try {
	$code = $params->get_param('code');
	$state = $params->get_param('state');
	$token_url = get_option('token_url');//'https://stg-id.singpass.gov.sg/token';
	$callback_url = get_option('callback_url');//"$url/wp-json/singpass/v1/signin_oidc";
	$parser_url = get_option('token_parser_url');//'http://asliddin-jwt.socialservicesconnect.com:5000/parser';
	//$parser_url = 'http://ec2-52-59-238-40.eu-central-1.compute.amazonaws.com:5000/parser';

	$singpass_client = get_option('singpass_client');//'hCqn1a2gQFi6QLPeaw3LIWP3LQ2E5f0r';
	$sigPrivateKey = get_option('private_sig_key');

	$public_jwks = json_decode(get_option('public_jwks'));
	foreach($public_jwks->{'keys'} as $jwk){
		if(strcmp($jwk->{'use'},'sig')==0) $sig_kid = $jwk->{'kid'};
	}

	$private_jwks = json_decode(get_option('private_jwks'));
	foreach($private_jwks->{'keys'} as $jwk){
		if(strcmp($jwk->{'use'},'enc')==0) $encPrivateKey = $jwk;
	}

	// $sig_kid = 'octopus8_sig_key_01';
	// $encPrivateKey = json_decode('{"kty": "EC","d": "cV6QfdH46rZ1t5qYAq9IiZOmkxbQxoU1S_oYr0BDYdI","use": "enc","crv": "P-256","kid": "octopus8_enc_key_01","x": "OZ0iGy9uaK-esgDx021JalqAh8Kyop4m0v8OvSSq5UQ","y": "httcDJHMKWVQ3vtiBKXJRnUcPpYdojzXT2IhdFVpFLw","alg": "ECDH-ES+A128KW"}');
	// $sigPrivateKey = <<<EOD
	// 	-----BEGIN PRIVATE KEY-----
	// 	MEECAQAwEwYHKoZIzj0CAQYIKoZIzj0DAQcEJzAlAgEBBCAn2IkQq8dNpSxE+u5l
	// 	Awme+XPDnCkWp9+NvhrcW+tS7A==
	// 	-----END PRIVATE KEY-----
	// 	EOD;

	$domain = parse_url($token_url);
	$payload = array(
		"iss" => $singpass_client,
		"sub" => $singpass_client,
		"aud" => $domain['scheme'] . '://' . $domain['host'],
		"exp" => strtotime($Date . '+2 mins'),
		"iat" => strtotime($Date . '+0 mins')
	);

	// $token = JWT::encode($payload, $sigPrivateKey, 'ES256', $sig_kid);
	$token = JWT::encode($payload, $sigPrivateKey, 'ES256', $sig_kid);
	var_dump($token);
	$body = array(
		'code' => $code,
		'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
		'client_assertion' => $token,
		'client_id' => $singpass_client,
		'scope' => 'openid',
		'grant_type' => 'authorization_code',
		'redirect_uri' => $callback_url
	);

	$headers = array(
		'Accept: application/json',
		'charset: ISO-8859-1',
		'Content-Type: application/x-www-form-urlencoded'
	);

	$curlOptions = array(
		CURLOPT_URL => $token_url,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_FOLLOWLOCATION => TRUE,
		CURLOPT_VERBOSE => TRUE,
		CURLOPT_STDERR => $verbose = fopen('php://temp', 'rw+'),
		CURLOPT_FILETIME => TRUE,
		CURLOPT_POST => TRUE,
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_POSTFIELDS => http_build_query($body)
	);
	$curl = curl_init();
	curl_setopt_array($curl, $curlOptions);
	$response = curl_exec($curl);
	curl_close($curl);

	$jwt = json_decode($response);
	var_dump($response);

	$body = json_encode(array(
		'key' => $encPrivateKey,
		'jwt' => $jwt->{'id_token'}
	));
	var_dump($jwt->{'id_token'});
	$headers = [
		'Accept' => 'application/json',
		'charset' => 'UTF-8',
		'Content-Type' => 'application/json',
	];

	$client = new Client();
	$res = $client->request('POST', $parser_url, [
		'headers' => $headers,
		'body' => $body
	]);

	$response = $res->getBody();
	var_dump($response);
	$user_data = explode(',', $response->{'sub'});
	$username = explode('=', $user_data[0])[1];
	$nonce = $response->{'nonce'};

	$user_id =  username_exists($username);

	echo $username . PHP_EOL;
	echo $state . PHP_EOL;

	if ($user_id && strcmp($state, $nonce)) {
		//wp_set_auth_cookie($user_id);
		echo 'logged in' . PHP_EOL;
	}
	// } catch (Exception $e) {
	// 	wp_redirect(admin_url());
	// }
	// wp_redirect(admin_url());

	// exit();

}

function call_qr_code()
{
	show_qr_code();
}

function header_scripts()
{
	qr_code_scripts();
}

function add_admin_page()
{
	add_menu_page('Singpass plugin', 'Singpass', 'manage_options', 'singpass-page.php', 'singpass_admin_page', plugins_url('/assets/og_image_mini.png',  __FILE__));
}

function singpass_admin_page()
{
	require_once plugin_dir_path(__FILE__) . '/singpass-page.php';
}

function settings_link($links)
{
	$settins_link = '<a href="admin.php?page=singpass-page.php">Settings</a>';
	array_push($links, $settins_link);
	return $links;
}

function create_settings()
{
	$plugin_name = explode('/', plugin_basename(__FILE__))[0];
	register_setting("$plugin_name._settings", "token_url");
	register_setting("$plugin_name._settings", "callback_url");
	register_setting("$plugin_name._settings", "token_parser_url");
	register_setting("$plugin_name._settings", "singpass_client");
	register_setting("$plugin_name._settings", "jwk_endpoint");
	register_setting("$plugin_name._settings", "public_jwks");
	register_setting("$plugin_name._settings", "private_jwks");
	register_setting("$plugin_name._settings", "private_sig_key");
	register_setting("$plugin_name._settings", "private_enc_key");
}

$plugin_name = plugin_basename(__FILE__);
add_action('admin_init', 'create_settings');
add_filter("plugin_action_links_$plugin_name", 'settings_link');
add_action('admin_menu', 'add_admin_page');
add_action('login_head', 'header_scripts');
add_action('login_form', 'singpass_button');
add_action('login_form', 'call_qr_code');
add_action('rest_api_init', function () {
	register_rest_route('singpass/v1', '/jwks', array(
		'methods' => 'GET',
		'callback' => 'singpass_jwks',
	));
});

add_action('rest_api_init', function () {
	register_rest_route('singpass/v1', '/signin_oidc/', array(
		'methods' => 'GET',
		'callback' => 'oidc_signin_callback',
	));
});

wp_register_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js');
wp_enqueue_script('bootstrap-js');
wp_register_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
wp_enqueue_style('bootstrap-css');
///(?P<id>[\d]+)
//http://localhost/singpass/wp-json/singpass/v1/jwks
//http://asliddin.socialservicesconnect.com/wp-json/singpass/v1/jwks
//http://asliddin.socialservicesconnect.com/wp-json/singpass/v1/signin_oidc?code=wHmXksdAROOBM8mdRbkKLl5VBROhVfP_67jZIiJtmao&state=NGRlZThmNzQtZDU5YS00YTY1LWFkODItYmE4NDA4Y2UwY2Uw
//http://localhost/singpass/wp-json/singpass/v1/signin_oidc?code=wHmXksdAROOBM8mdRbkKLl5VBROhVfP_67jZIiJtmao&state=NGRlZThmNzQtZDU5YS00YTY1LWFkODItYmE4NDA4Y2UwY2Uw