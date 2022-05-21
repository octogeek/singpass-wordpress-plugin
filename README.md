# Wordpress Singpass plugin ```v1.0```
---
Wordpress plagin for authorization using Singpass API

Plugin includes control panel to configure interaction between singpass service and web site
---
![alt text](/assets/plugin_screenshot01.png)
#### Fields:
###### Token Endpoint:
URL of the OP’s OAuth 2.0 Token Endpoint. This contains the signing key(s) the RP uses to validate signatures from the OP.
In the follwing you can see staging endpoint url:
https://stg-id.singpass.gov.sg/token

###### Callback url:
your callback url should be registered on singpass portal
https://<your_domain_name>/wp-json/singpass/v1/signin_oidc
![alt text](/assets/plugin_screenshot02.png)

###### Token parser microservice
PHP JWT parser did not work properly that's why used python jwt parser as a separate microservice
you can find parser source code inside of ```jwt_parser``` folder of the repository

json format to parse jwt:
```
{
    "jwt":"ey<token_id from singpass token endpoint>",
    "key":   {    
             "kty": "EC",
             "d": "<generated key>",
             "use": "enc",
             "crv": "P-256",
             "kid": "example_enc_key",
             "x": "<generated key>",
             "y": "<generated key>",
             "alg": "ECDH-ES+A128KW"}
}
```

###### Singpass ClientID
clien ID from singpass service

#### JWKS configuration
You have to use only Elliptic Curve encryption and algorithm ES256,
and you can generate keys using https://mkjwk.org/ service

###### JWKs endpoint
this endpoint also must be registered on portal
jwks endpoint returns public singning and encryption keys to genate token from singpass service:
http://<your_domain_name>/wp-json/singpass/v1/jwks

###### Public JWKS
you have to set collection of public keys as following:
```
{
    "keys": [
        {
            "kty": "EC",
            "use": "sig",
            "crv": "P-256",
            "kid": "example_sig_key",
            "x": "<generated key>",
            "y": "<generated key>",
            "alg": "ES256"
        },
        {
            "kty": "EC",
            "use": "enc",
            "crv": "P-256",
            "kid": "example_enc_key",
            "x": "<generated key>",
            "y": "<generated key>",
            "alg": "ECDH-ES+A128KW"
        }
    ]
}
```

###### Private JWKS
youi have to set private keys collection as following
```
{
    "keys": [
        {
            "kty": "EC",
            "d": "<generated key>",
            "use": "sig",
            "crv": "P-256",
            "kid": "example_sig_key",
            "x": "<generated key>",
            "y": "<generated key>",
            "alg": "ES256"
        },
        {
            "kty": "EC",
            "d": "<generated key>",
            "use": "enc",
            "crv": "P-256",
            "kid": "example_enc_key",
            "x": "<generated key>",
            "y": "<generated key>",
            "alg": "ECDH-ES+A128KW"
        }
    ]
}
```
###### Private signing X.509 key
``` X.509``` format generated key, used to sign data,
https://mkjwk.org/ service provides generating such kind of keys together with JSON view
```
-----BEGIN PRIVATE KEY-----
<generated key>
-----END PRIVATE KEY-----
```


###### Private encryption X.509 key
``` X.509``` format generated key, used to encrypt data,
https://mkjwk.org/ service provides generating such kind of keys together with JSON view
```
-----BEGIN PRIVATE KEY-----
<generated key>
-----END PRIVATE KEY-----
```
