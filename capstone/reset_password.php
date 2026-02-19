<?php
include 'db_connect.php';

$error = "";
$success = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = trim($_POST['username']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if($new_password !== $confirm_password){
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin SET password=? WHERE username=?");
        $stmt->bind_param("ss", $hashed, $username);
        $stmt->execute();

        if($stmt->affected_rows > 0){
            $success = "Password reset successful! <a href='login.php'>Login</a>";
        } else {
            $error = "Username not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body { margin:0; padding:0; background:#115272; font-family:Arial; height:100vh; display:flex; justify-content:center; align-items:center; }
        .box { width:350px; padding:25px; background:#fff; border-radius:12px; text-align:center; box-shadow:0 5px 20px rgba(0,0,0,0.3); }
        .box h2 { color:#115272; margin-bottom:20px; }
        input { width:92%; padding:12px; margin:10px 0; border:1px solid #bdbdbd; border-radius:6px; font-size:14px; }
        input:focus { outline:none; border-color:#115272; box-shadow:0 0 5px rgba(17,82,114,0.5); }
        button { width:100%; padding:12px; background:#115272; color:white; border:none; border-radius:6px; font-size:15px; cursor:pointer; margin-top:10px; }
        button:hover { background:#0d3b5e; }
        .message { margin-top:10px; font-size:14px; }
        .message.error { color:red; }
        .message.success { color:green; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Reset Password</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="new_password" placeholder="New Password" required><br>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
            <button type="submit">Reset Password</button>
        </form>

        <?php if($error): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="message success"><?php echo $success; ?></div><?php endif; ?>
    </div>
</body>
</html>
