<?php
require "db_connect.php";
session_start();
if(!isset($_SESSION['user_id'])) exit;

$missing_id = $_POST['missing_id'];
$found_id = $_POST['found_id'];

// Mark found pet as approved
$conn->query("UPDATE found_pets SET status='approved' WHERE id=$found_id");
// Optionally mark missing pet as resolved
$conn->query("UPDATE missing_pets SET status='approved' WHERE id=$missing_id");

echo "success";
?>
