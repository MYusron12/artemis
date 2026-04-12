<?php

// Set path openssl.cnf untuk XAMPP
putenv('OPENSSL_CONF=C:/xampp/apache/conf/openssl.cnf');

$config = [
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'config'           => 'C:/xampp/apache/conf/openssl.cnf',
];

$res = openssl_pkey_new($config);

if (!$res) {
    echo "Error: " . openssl_error_string() . "\n";
    exit;
}

openssl_pkey_export($res, $privateKey, null, $config);
$publicKey = openssl_pkey_get_details($res)['key'];

file_put_contents('private_key.pem', $privateKey);
file_put_contents('public_key.pem', $publicKey);

echo "Keys generated!\n";
echo "private_key.pem — OK\n";
echo "public_key.pem  — OK\n";