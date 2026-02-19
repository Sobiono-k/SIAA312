<?php
require "db_connect.php";

$missing = $conn->query("SELECT * FROM missing_pets WHERE status='approved' ORDER BY id DESC");
$found = $conn->query("SELECT * FROM found_pets WHERE status='approved' ORDER BY id DESC");

// Loop and generate HTML
foreach($missing as $row){
    echo "<div class='pet-card'>
        <img src='uploads/{$row['image']}' alt='Pet Image'>
        <h3>{$row['pet_name']}</h3>
        <p>{$row['species']} | {$row['color']}</p>
        <p>Last seen: {$row['last_seen']}</p>
    </div>";
}

foreach($found as $row){
    echo "<div class='pet-card'>
        <img src='uploads/{$row['image']}' alt='Pet Image'>
        <p>{$row['species']} | {$row['color']}</p>
        <p>Found at: {$row['found_location']}</p>
    </div>";
}
?>
