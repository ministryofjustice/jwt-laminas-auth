<?php

return [
    'jwt_laminas_auth' => [
        // Choose signing method for the tokens
        'signer' => \Lcobucci\JWT\Signer\Hmac\Sha256::class,
        /*
            You need to specify either a signing key or set read only to true.
            If tokens are read only, the implementation will not automatically
            refresh tokens which are close to expiry so you will need to handle
            this yourself.
        */
        'readOnly' => false,
        // Set the key to sign the token with, value is dependent on signer set.
        'signKey' => '',
        // Set the key to verify the token with, value is dependent on signer set.
        'verifyKey' => '',
        /*
            Default expiry for tokens. A token will expire after not being used
            for this number of seconds. A token which is used will automatically
            be extended provided a sign key is provided.
        */
        'expiry' => 600
    ]
];
