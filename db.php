<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
*/

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '4000';
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');


/*
|--------------------------------------------------------------------------
| VALIDATE ENVIRONMENT VARIABLES
|--------------------------------------------------------------------------
*/

if (
    empty($host) ||
    empty($dbname) ||
    empty($user) ||
    $password === false ||
    $password === ''
) {
    throw new RuntimeException(
        'Database configuration is missing. Please check DB_HOST, DB_PORT, DB_NAME, DB_USER and DB_PASSWORD.'
    );
}


/*
|--------------------------------------------------------------------------
| TIDB CA CERTIFICATE
|--------------------------------------------------------------------------
*/

$caFile = __DIR__ . '/isrgrootx1.pem';

if (!file_exists($caFile)) {
    throw new RuntimeException(
        'TiDB CA certificate is missing: ' . $caFile
    );
}


/*
|--------------------------------------------------------------------------
| PDO CONNECTION
|--------------------------------------------------------------------------
*/

try {

    $dsn =
        "mysql:host={$host};" .
        "port={$port};" .
        "dbname={$dbname};" .
        "charset=utf8mb4";


    $options = [

        PDO::ATTR_ERRMODE =>
            PDO::ERRMODE_EXCEPTION,

        PDO::ATTR_DEFAULT_FETCH_MODE =>
            PDO::FETCH_ASSOC,

        PDO::ATTR_EMULATE_PREPARES =>
            false

    ];


    /*
    |--------------------------------------------------------------------------
    | MYSQL SSL
    |--------------------------------------------------------------------------
    */

    if (defined('PDO::MYSQL_ATTR_SSL_CA')) {

        $options[PDO::MYSQL_ATTR_SSL_CA] =
            $caFile;
    }


    if (
        defined(
            'PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'
        )
    ) {

        $options[
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT
        ] = true;
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE CONNECTION
    |--------------------------------------------------------------------------
    */

    $connection = new PDO(
        $dsn,
        $user,
        $password,
        $options
    );

} catch (PDOException $e) {

    error_log(
        'CMO DATABASE CONNECTION ERROR: ' .
        $e->getMessage()
    );

    throw new RuntimeException(
        'Unable to connect to the database.'
    );
}