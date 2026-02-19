<?php
include 'db_connect.php';

$error = "";
$success = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $species = trim($_POST['species']);
    $color = trim($_POST['color']);
    $found_location = trim($_POST['found_location']);
    $contact = trim($_POST['contact']);

    $filename = "";
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "uploads/";
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;

        if(!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)){
            $error = "Failed to upload image.";
        }
    }

    if(empty($error)){
        $stmt = $conn->prepare("INSERT INTO found_pets (species, color, found_location, contact, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $species, $color, $found_location, $contact, $filename);
        if($stmt->execute()){
            $success = "Found pet report submitted successfully!";
        } else {
            $error = "Database error: " . $stmt->error;
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Report Found Pet</title>
    <style>
        body { font-family:Arial; background:#f2f6f9; }
        .box { width:420px; margin:40px auto; background:white; padding:20px; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,.2); }
        input, textarea, select { width:100%; padding:10px; margin:8px 0; border-radius:6px; border:1px solid #ccc; }
        button { width:100%; padding:12px; background:#115272; color:white; border:none; border-radius:6px; }
        img { display:none; width:100%; border-radius:8px; }
    </style>
</head>
<body>

<div class="box">
    <h2>Report Found Pet</h2>

    <form method="POST" action="submit_found.php" enctype="multipart/form-data">
        <select name="species"><option>Dog</option><option>Cat</option><option>Bird</option></select>

        <input type="text" name="color" placeholder="Color" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <input type="text" name="found_location" placeholder="Found Location" required>
        <input type="text" name="contact" placeholder="Your Contact Number" required>

        <input type="file" name="image" accept="image/*" onchange="prev(event)">
        <img id="imgPrev">

        <button type="submit">Submit Report</button>
    </form>
</div>

<script>
function prev(e) {
    let img = document.getElementById("imgPrev");
    img.src = URL.createObjectURL(e.target.files[0]);
    img.style.display = "block";
}
</script>

</body>
</html>
