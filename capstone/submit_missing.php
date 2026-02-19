<?php
require "db_connect.php";

$pet_name = $_POST["pet_name"];
$species = $_POST["species"];
$color = $_POST["color"];
$description = $_POST["description"];
$last_seen = $_POST["last_seen"];
$contact = $_POST["contact"];

$image_name = time() . "_" . $_FILES["image"]["name"];
$target = "uploads/" . $image_name;

move_uploaded_file($_FILES["image"]["tmp_name"], $target);

$sql = "INSERT INTO missing_pets (pet_name, species, color, description, last_seen, contact, image)
        VALUES ('$pet_name', '$species', '$color', '$description', '$last_seen', '$contact', '$image_name')";

$conn->query($sql);

header("Location: index.php");
?>
