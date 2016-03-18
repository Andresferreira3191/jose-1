<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

require_once __DIR__.'/../vendor/autoload.php';

use Jose\Checker\AudienceChecker;
use Jose\Factory\CheckerManagerFactory;
use Jose\Factory\JWKFactory;
use Jose\Loader;

// In this example, our input is a JWS string in compact serialization format
// See Signature1.php to know to generate such string
$input = 'eyJhbGciOiJSUzI1NiJ9.eyJuYmYiOjE0NTE0NjkwMTcsImlhdCI6MTQ1MTQ2OTAxNywiZXhwIjoxNDUxNDcyNjE3LCJpc3MiOiJNZSIsImF1ZCI6IllvdSIsInN1YiI6Ik15IGZyaWVuZCJ9.mplHfnyXzUdlEkPmykForVM0FstqgiihfDRTd2Zd09j6CZzANBJbZNbisLerjO3lR9waRlYvhnZu_ewIAahDwmVTfpSeKKABbAyoTHXTH2WLgMPLtOAsoausUf584eAAj_kyldIOV8a83Qz1NztZHVD3DbGTiCN0BOj-qnc65yQmEDEYK5cxG1xC22YK5aohZ3xm8ixwNZpxYr8cNOkauASYjPGODbHqY_gjQ-aKA21kxbYgwM6mDYSc3QRej1_3m6bD3jKPsK4jv3yzosVMEXOparf4sEb8q_zCPMDJAJgZZ8VICwJdgYnJkQuIutS-w3_iT-riKl8fkgmJezQVkg';

// To verify a JWS, we need a key.
// We create our key object (JWK) using a RSA public key stored in a file
// Additional parameters ('kid' and 'use') are set for this key (not mandatory).
$key = JWKFactory::createFromKeyFile(
    __DIR__.'/../tests/Unit/Keys/RSA/public.key',
    null,
    [
        'kid' => 'My public RSA key',
        'use' => 'sig',
    ]
);

// We load the input and we verify it.
// RS256 is the only algorithm we allow
// Now the variable $jws contains a JWSInterface object
$jws = Loader::loadAndVerifySignatureUsingKey(
    $input,
    $key,
    ['RS256']
);

// Note that if the input contain claims, these claims have to be checked.
// We create a Claim Checker Manager and we want to check the claims 'exp', 'iat' and 'nbf'.
// We also want to check if the protected header 'crit' is present.
// 
$checker = CheckerManagerFactory::createClaimCheckerManager(
    ['exp', 'iat', 'nbf'],
    ['crit']
);

// We can add other claim checkers. We add one for the 'aud' claim.
$checker->addClaimChecker(new AudienceChecker('You'));

// We check our JWS. The second argument is the index of the signatures headers to check (0 = the first signature headers).
// This method will throw an exception in case of failure (e.g. expired JWS).
$checker->checkJWS($jws, 0);

// You can get headers or claims contained in this object
$jws->getSignature(0)->hasProtectedHeader('alg'); // true
$jws->getSignature(0)->hasProtectedHeader('alg'); // RS256
$jws->getSignature(0)->getProtectedHeaders(); // ['alg'=>'RS256']
$jws->hasClaim('foo'); // false
$jws->hasClaim('iss'); // true

// Our JWS is now verified (claims and signature) and we can use it
