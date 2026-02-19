<?php
require "db_connect.php";

$species = $_POST["species"];
$color = $_POST["color"];
$description = $_POST["description"];
$found_location = $_POST["found_location"];
$contact = $_POST["contact"];

$image_name = time() . "_" . $_FILES["image"]["name"];
$target = "uploads/" . $image_name;

move_uploaded_file($_FILES["image"]["tmp_name"], $target);

$sql = "INSERT INTO found_pets (species, color, description, found_location, contact, image)
        VALUES ('$species', '$color', '$description', '$found_location', '$contact', '$image_name')";

$conn->query($sql);

header("Location: index.php");
?>
