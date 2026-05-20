<?php
require_once '../includes/db_connect.php';

$sql = "ALTER TABLE documents 
        ADD COLUMN is_active TINYINT(1) DEFAULT 1";

if ($conn->query($sql) === TRUE) {
    echo "Column is_active added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}
?>
