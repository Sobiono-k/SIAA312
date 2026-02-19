<?php
require "db_connect.php";
session_start();

if(!isset($_SESSION['user_id'])){
    echo 'error';
    exit;
}

if(isset($_POST['id'], $_POST['type'])){
    $id = (int)$_POST['id'];
    $type = $_POST['type'];

    $table = $type=='missing' ? 'missing_pets' : ($type=='found' ? 'found_pets' : '');
    if(!$table) { echo 'error'; exit; }

    // Delete image file first
    $res = $conn->query("SELECT image FROM $table WHERE id=$id LIMIT 1");
    if($res->num_rows){
        $row = $res->fetch_assoc();
        if(!empty($row['image']) && file_exists("uploads/".$row['image'])){
            unlink("uploads/".$row['image']);
        }
    }

    if($conn->query("DELETE FROM $table WHERE id=$id")){
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}
?>
