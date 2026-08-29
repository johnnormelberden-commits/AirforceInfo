<?php

/*
 * ==========================================================
 * POSTGRESQL DATABASE CONNECTION
 * ==========================================================
 *
 * These values are provided by Render Environment Variables:
 *
 * DB_HOST
 * DB_PORT
 * DB_NAME
 * DB_USER
 * DB_PASSWORD
 *
 * Do NOT put your actual database password in this file.
 * ==========================================================
 */

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');


/*
 * ==========================================================
 * CHECK DATABASE CONFIGURATION
 * ==========================================================
 */

if (
    empty($host) ||
    empty($dbname) ||
    empty($user) ||
    empty($password)
) {
    die("Database configuration is missing.");
}


/*
 * ==========================================================
 * CONNECT TO POSTGRESQL
 * ==========================================================
 */

try {

    $connection = new PDO(
        "pgsql:host={$host};port={$port};dbname={$dbname}",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (PDOException $e) {

    /*
     * Do not expose database credentials
     * or detailed connection errors.
     */

    die("Unable to connect to the database.");

}