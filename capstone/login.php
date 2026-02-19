<?php
session_start();
include 'db_connect.php';

$error = "";

if(isset($_SESSION['user_id'])){
    header("Location: admin_dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $admin = $result->fetch_assoc();
        if(password_verify($password, $admin['password'])){
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { margin:0; padding:0; background:#115272; font-family:Arial; height:100vh; display:flex; justify-content:center; align-items:center; }
        .box { width:330px; padding:25px; background:#fff; border-radius:12px; text-align:center; box-shadow:0 5px 20px rgba(0,0,0,0.3); }
        .box h2 { color:#115272; margin-bottom:20px; }
        input { width:92%; padding:12px; margin:10px 0; border:1px solid #bdbdbd; border-radius:6px; font-size:14px; }
        input:focus { outline:none; border-color:#115272; box-shadow:0 0 5px rgba(17,82,114,0.5); }
        button { width:100%; padding:12px; background:#115272; color:white; border:none; border-radius:6px; font-size:15px; cursor:pointer; margin-top:10px; }
        button:hover { background:#0d3b5e; }
        .switch { margin-top:15px; font-size:14px; }
        .switch a { color:#115272; text-decoration:none; font-weight:bold; }
        .switch a:hover { text-decoration:underline; }
        .error { color:red; margin-top:10px; font-size:14px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Admin Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Log In</button>
        </form>

        <div class="switch">
            <a href="reset_password.php">Forgot Password?</a>
        </div>

        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
