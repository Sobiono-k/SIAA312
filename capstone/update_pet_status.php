<?php
require "db_connect.php";

$id = (int)$_POST['id'];
$type = $_POST['type'];
$action = $_POST['action'];

if($type == 'missing') $table = 'missing_pets';
elseif($type == 'found') $table = 'found_pets';
else exit('invalid type');

if($action == 'approve'){
    $conn->query("UPDATE $table SET status='approved' WHERE id=$id");
    echo "approved";
} elseif($action == 'delete'){
    // delete image if exists
    $res = $conn->query("SELECT image FROM $table WHERE id=$id LIMIT 1");
    if($res->num_rows){
        $row = $res->fetch_assoc();
        if(!empty($row['image']) && file_exists("uploads/".$row['image'])){
            unlink("uploads/".$row['image']);
        }
    }
    $conn->query("DELETE FROM $table WHERE id=$id");
    echo "deleted";
} else {
    echo "invalid action";
}
