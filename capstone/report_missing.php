<?php
include 'db_connect.php';

$error = "";
$success = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $pet_name = trim($_POST['pet_name']);
    $species = trim($_POST['species']);
    $color = trim($_POST['color']);
    $last_seen = trim($_POST['last_seen']);
    $contact = trim($_POST['contact']);

    // Handle image upload
    $filename = "";
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "uploads/";
        $filename = time() . "_" . basename($_FILES["image"]["name"]); // unique filename
        $target_file = $target_dir . $filename;

        if(!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)){
            $error = "Failed to upload image.";
        }
    }

    if(empty($error)){
        $stmt = $conn->prepare("INSERT INTO missing_pets (pet_name, species, color, last_seen, contact, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $pet_name, $species, $color, $last_seen, $contact, $filename);
        if($stmt->execute()){
            $success = "Missing pet report submitted successfully!";
        } else {
            $error = "Database error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Missing Pet</title>
    <style>
        body { font-family:Arial; background:#f2f6f9; }
        .box { width:420px; margin:40px auto; background:white; padding:20px; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,.2); }
        input, textarea, select { width:100%; padding:10px; margin:8px 0; border-radius:6px; border:1px solid #ccc; }
        button { width:100%; padding:12px; background:#115272; color:white; border:none; border-radius:6px; }
        img { width:100%; margin-top:10px; border-radius:8px; display:none; }
    </style>
</head>
<body>

<div class="box">
    <h2>Report Missing Pet</h2>

    <form method="POST" action="submit_missing.php" enctype="multipart/form-data">

        <input type="text" name="pet_name" placeholder="Pet Name" required>
        <select name="species">
            <option>Dog</option><option>Cat</option><option>Bird</option><option>Other</option>
        </select>

        <input type="text" name="color" placeholder="Color" required>
        <textarea name="description" placeholder="Describe your pet" required></textarea>
        <input type="text" name="last_seen" placeholder="Last Seen Location" required>
        <input type="text" name="contact" placeholder="Your Contact Number" required>

        <input type="file" name="image" accept="image/*" onchange="preview(event)">
        <img id="preview">

        <button type="submit">Submit Report</button>
    </form>
</div>

<script>
function preview(e) {
    let image = document.getElementById("preview");
    image.src = URL.createObjectURL(e.target.files[0]);
    image.style.display = "block";
}
</script>

</body>
</html>
