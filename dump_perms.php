<?php
require_once 'includes/db_connect.php';

$result = $conn->query("SELECT permission_name FROM permissions");
$perms = [];
while ($row = $result->fetch_assoc()) {
    $perms[] = $row['permission_name'];
}
file_put_contents('perms_dump.txt', implode("\n", $perms));
echo "Permissions dumped to perms_dump.txt";
?>