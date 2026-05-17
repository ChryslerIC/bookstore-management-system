<?php

$localConfigPath = __DIR__ . '/config.local.php';
if (file_exists($localConfigPath)) {
    require_once $localConfigPath;
}

function configRead(string $name, string $default = ''): string
{
    $envValue = getenv($name);
    if ($envValue !== false && $envValue !== '') {
        return $envValue;
    }

    if (isset($_ENV[$name]) && $_ENV[$name] !== '') {
        return (string) $_ENV[$name];
    }

    $localConstant = 'LOCAL_' . $name;
    if (defined($localConstant) && constant($localConstant) !== '') {
        return (string) constant($localConstant);
    }

    return $default;
}

define('DB_HOST', configRead('DB_HOST', '127.0.0.1'));
define('DB_PORT', configRead('DB_PORT', '3306'));
define('DB_NAME', configRead('DB_NAME', 'bookstore_db'));
define('DB_USER', configRead('DB_USER', 'root'));
define('DB_PASSWORD', configRead('DB_PASSWORD', ''));

$keyHex = configRead('ENCRYPTION_KEY_HEX');
if ($keyHex === '') {
    throw new RuntimeException(
        'Missing ENCRYPTION_KEY_HEX configuration. Define it in an environment variable or backend/config.local.php.'
    );
}

$key = hex2bin($keyHex);
if ($key === false || strlen($key) !== 32) {
    throw new RuntimeException('Invalid ENCRYPTION_KEY_HEX. Expected a 64-character hexadecimal string.');
}

define('ENCRYPTION_KEY', $key);
define('ENCRYPTION_CIPHER', 'aes-256-gcm');
define('ENCRYPTION_IV_LENGTH', 12);
define('ENCRYPTION_TAG_LENGTH', 16);
