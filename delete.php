<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "airforce_info";

$connection = new mysqli($servername, $username, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (isset($_GET["id"])) {
    $id = intval($_GET["id"]); // sanitize ID

    if ($id > 0) {
        // Delete the record
        $stmt = $connection->prepare("DELETE FROM military_personnel WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // RESET IDs back to 1
        $connection->query("SET @num := 0");
        $connection->query("UPDATE military_personnel SET id = @num := (@num + 1)");
        $connection->query("ALTER TABLE military_personnel AUTO_INCREMENT = 1");
    }
}

$connection->close();

// Redirect back to index
header("location: /airforceinfo/index.php?deleted=1");
exit;
?>
