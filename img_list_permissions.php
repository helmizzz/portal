<?php
require_once 'includes/db_connect.php';

$result = $conn->query("SELECT id, permission_name FROM permissions");
while ($row = $result->fetch_assoc()) {
    echo $row['id'] . ": " . $row['permission_name'] . "\n";
}
?>