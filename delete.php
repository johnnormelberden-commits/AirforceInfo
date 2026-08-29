<?php

/*
 * ==========================================================
 * POSTGRESQL DATABASE CONNECTION
 * ==========================================================
 *
 * These values come from your Render Environment Variables:
 *
 * DB_HOST
 * DB_PORT
 * DB_NAME
 * DB_USER
 * DB_PASSWORD
 *
 * Do NOT put your database password directly in this file.
 * ==========================================================
 */

try {

    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: '5432';
    $database = getenv('DB_NAME');
    $dbUsername = getenv('DB_USER');
    $dbPassword = getenv('DB_PASSWORD');

    if (!$host || !$database || !$dbUsername || !$dbPassword) {
        throw new Exception("Database configuration is missing.");
    }

    $connection = new PDO(
        "pgsql:host={$host};port={$port};dbname={$database}",
        $dbUsername,
        $dbPassword
    );

    $connection->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (Exception $e) {

    /*
     * Do not expose database credentials
     * or detailed connection information.
     */
    die("Unable to connect to the database.");

}


/*
 * ==========================================================
 * CHECK ID
 * ==========================================================
 */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: index.php");
    exit;

}

$id = (int) $_GET['id'];


/*
 * ==========================================================
 * VALIDATE ID
 * ==========================================================
 */

if ($id <= 0) {

    header("Location: index.php");
    exit;

}


/*
 * ==========================================================
 * DELETE RECORD
 * ==========================================================
 */

try {

    /*
     * Prepared statement protects against SQL injection.
     */

    $stmt = $connection->prepare(
        "DELETE FROM military_personnel
         WHERE id = :id"
    );

    $stmt->execute([
        ':id' => $id
    ]);


    /*
     * ======================================================
     * REDIRECT AFTER SUCCESSFUL DELETE
     * ======================================================
     */

    header("Location: index.php?deleted=1");
    exit;


} catch (PDOException $e) {

    /*
     * Do not expose database error details.
     */

    die("Unable to delete the personnel record.");

}

?>