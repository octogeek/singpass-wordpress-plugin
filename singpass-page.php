<h1>Singpass plugin configuration</h1>
<h6 class="text-danger">You have to use only Elliptic Curve encryption and algorithm ES256</h6>
<form method="post" action="options.php">
<?php
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $url = $protocol . "://$_SERVER[HTTP_HOST]";
    $plugin_name = $plugin_name = explode('/', plugin_basename(__FILE__))[0];
    settings_fields("$plugin_name._settings");
    do_settings_sections("$plugin_name._settings");
?>
    <div class="mb-3">
        <label for="token_url" class="form-label">Token Endpoint</label>
        <input type="text" class="form-control" id="token_url" name="token_url" value="<?php echo get_option('token_url');?>" placeholder="singpass endpoint url e.g. https://stg-id.singpass.gov.sg/token">
    </div>
    <div class="mb-3">
        <label for="callback_url" class="form-label">Callback url</label>
        <input type="text" class="form-control" id="callback_url" name="callback_url" placeholder="<?php echo $url; ?>/wp-json/singpass/v1/signin_oidc" value="<?php echo get_option('callback_url');?>">
    </div>
    <div class="mb-3">
        <label for="token_parser_url" class="form-label">Token parser microservice</label>
        <input type="text" class="form-control" id="token_parser_url" name="token_parser_url" value="<?php echo get_option('token_parser_url');?>" placeholder="http://">
    </div>
    <div class="mb-3">
        <label for="singpass_client" class="form-label">Singpass ClientID</label>
        <input type="text" class="form-control" id="singpass_client" name="singpass_client" value="<?php echo get_option('singpass_client');?>" placeholder="singpass client id">
    </div>

    <h3>JWKS configuration</h3>
    <hr>
    <div class="mb-3">
        <label for="jwk_endpoint" class="form-label">JWKs endpoint</label>
        <input type="text" class="form-control" id="jwk_endpoint" name="jwk_endpoint" placeholder="<?php echo $url; ?>/wp-json/singpass/v1/jwks" value="<?php echo get_option('jwk_endpoint');?>">
    </div>
    <div class="mb-3">
        <label for="public_jwks" class="form-label">Public JWKS</label>
        <textarea class="form-control" id="public_jwks" name="public_jwks" rows="4"><?php echo get_option('public_jwks');?></textarea>
    </div>     
    <div class="mb-3">
        <label for="private_jwks" class="form-label">Private JWKS</label>
        <textarea class="form-control" id="private_jwks" name="private_jwks" rows="4"><?php echo get_option('private_jwks');?></textarea>
    </div>       
    <div class="mb-3">
        <label for="private_sig_key" class="form-label">Private signing X.509 key</label>
        <textarea class="form-control" id="private_sig_key" name="private_sig_key" rows="4"><?php echo get_option('private_sig_key');?></textarea>
    </div>
    <div class="mb-3">
        <label for="private_enc_key" class="form-label">Private encryption X.509 key</label>
        <textarea class="form-control" id="private_enc_key" name="private_enc_key" rows="4"><?php echo get_option('private_enc_key');?></textarea>
    </div>
    <div class="d-flex justify-content-center">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

<?php



//$token_url = 'https://stg-id.singpass.gov.sg/token';
//$callback_url = 'https://asliddin.socialservicesconnect.com/wp-json/singpass/v1/signin_oidc';
//$parser_url = 'http://asliddin-jwt.socialservicesconnect.com:5000/parser';
////$parser_url = 'http://ec2-52-59-238-40.eu-central-1.compute.amazonaws.com:5000/parser';
//
//$singpass_client = 'hCqn1a2gQFi6QLPeaw3LIWP3LQ2E5f0r';
//$encPrivateKey = json_decode('{"kty": "EC","d": "cV6QfdH46rZ1t5qYAq9IiZOmkxbQxoU1S_oYr0BDYdI","use": "enc","crv": "P-256","kid": "octopus8_enc_key_01","x": "OZ0iGy9uaK-esgDx021JalqAh8Kyop4m0v8OvSSq5UQ","y": "httcDJHMKWVQ3vtiBKXJRnUcPpYdojzXT2IhdFVpFLw","alg": "ECDH-ES+A128KW"}');
//
//$sigPrivateKey = <<<EOD
//    -----BEGIN PRIVATE KEY-----
//    MEECAQAwEwYHKoZIzj0CAQYIKoZIzj0DAQcEJzAlAgEBBCAn2IkQq8dNpSxE+u5l
//    Awme+XPDnCkWp9+NvhrcW+tS7A==
//    -----END PRIVATE KEY-----
//    EOD;