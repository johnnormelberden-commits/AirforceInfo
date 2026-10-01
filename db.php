<?php

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '4000';
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

if (
    empty($host) ||
    empty($dbname) ||
    empty($user) ||
    empty($password)
) {
    die("Database configuration is missing.");
}

/*
 * TiDB Cloud TLS certificate
 */
$caFile = __DIR__ . '/isrgrootx1.pem';

if (!file_exists($caFile)) {
    die("TiDB CA certificate is missing.");
}

try {

    $connection = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,

            PDO::MYSQL_ATTR_SSL_CA => $caFile,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true
        ]
    );

} catch (PDOException $e) {

    die("Unable to connect to the database.");

}
