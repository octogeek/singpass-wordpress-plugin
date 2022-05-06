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
// use Firebase\JWT\Key;

function singpass_button()
{
	$path = plugin_dir_url(__FILE__);
	echo '<br>';
	echo 'Login with <img src=' . $path . 'assets/singpass_logo_fullcolours.png width=100px>';
	//	echo '<button type="button" class="btn btn-outline-secondary">Secondary</button>';
	echo '<br>';
}

function singpass_jwks()
{
	$file = file_get_contents('jwks_keys', true);
	echo $file;
}

function oidc_signin_callback($params)
{
	//try {
	$code = $params->get_param('code');
	$state = $params->get_param('state');

	$sigPrivateKey = <<<EOD
		-----BEGIN PRIVATE KEY-----
		MEECAQAwEwYHKoZIzj0CAQYIKoZIzj0DAQcEJzAlAgEBBCAn2IkQq8dNpSxE+u5l
		Awme+XPDnCkWp9+NvhrcW+tS7A==
		-----END PRIVATE KEY-----
		EOD;

	$payload = array(
		"iss" => "hCqn1a2gQFi6QLPeaw3LIWP3LQ2E5f0r",
		"sub" => "hCqn1a2gQFi6QLPeaw3LIWP3LQ2E5f0r",
		"aud" => "https://stg-id.singpass.gov.sg",
		"exp" => strtotime($Date . '+2 mins'),
		"iat" => strtotime($Date . '+0 mins')
	);

	$token = JWT::encode($payload, $sigPrivateKey, 'ES256', 'octopus8_sig_key_01');
	$token_url = 'https://stg-id.singpass.gov.sg/token';

	$body = array(
		'code' => $code,
		'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
		'client_assertion' => $token,
		'client_id' => 'hCqn1a2gQFi6QLPeaw3LIWP3LQ2E5f0r',
		'scope' => 'openid',
		'grant_type' => 'authorization_code',
		'redirect_uri' => 'https://asliddin.socialservicesconnect.com/wp-json/singpass/v1/signin_oidc'
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
	$encPrivateKey = json_decode('{"kty": "EC","d": "cV6QfdH46rZ1t5qYAq9IiZOmkxbQxoU1S_oYr0BDYdI","use": "enc","crv": "P-256","kid": "octopus8_enc_key_01","x": "OZ0iGy9uaK-esgDx021JalqAh8Kyop4m0v8OvSSq5UQ","y": "httcDJHMKWVQ3vtiBKXJRnUcPpYdojzXT2IhdFVpFLw","alg": "ECDH-ES+A128KW"}');

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

	//$url = 'http://ec2-52-59-238-40.eu-central-1.compute.amazonaws.com:5000/parser';
	$url = 'http://asliddin-jwt.socialservicesconnect.com:5000/parser';
	
	$client = new Client();
	$res = $client->request('POST', $url, [
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

// function add_admin_page()
// {
// 	// add_admin_page('Singpass', 'Singpass plugin', 'manage_options', 'singpass_plugin', array('admin_page'), plugin_url('/assets/og_image_mini.svg', __FILE__), null);
// 	add_menu_page('Singpass', 'Singpass plugin', 'manage_options', '');
// }

// function admin_page(){

// }
function add_admin_page() {
    add_menu_page( 'Singpass plugin', 'Singpass', 'manage_options', 'singpass-page.php', 'singpass_admin_page', plugins_url('/assets/og_image_mini.png',  __FILE__) );
}

function singpass_admin_page(){
    require_once plugin_dir_path(__FILE__).'/singpass-page.php';
}

function settings_link($links){
	$settins_link = '<a href="admin.php?page=singpass-page.php">Settings</a>';
	array_push($links, $settins_link);
	return $links;
}

$plugin_name = plugin_basename(__FILE__);

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

///(?P<id>[\d]+)
//http://localhost/singpass/wp-json/singpass/v1/jwks
//http://asliddin.socialservicesconnect.com/wp-json/singpass/v1/jwks
//http://asliddin.socialservicesconnect.com/wp-json/singpass/v1/signin_oidc?code=wHmXksdAROOBM8mdRbkKLl5VBROhVfP_67jZIiJtmao&state=NGRlZThmNzQtZDU5YS00YTY1LWFkODItYmE4NDA4Y2UwY2Uw
//http://localhost/singpass/wp-json/singpass/v1/signin_oidc?code=wHmXksdAROOBM8mdRbkKLl5VBROhVfP_67jZIiJtmao&state=NGRlZThmNzQtZDU5YS00YTY1LWFkODItYmE4NDA4Y2UwY2Uw