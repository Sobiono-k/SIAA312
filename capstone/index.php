<?php
require "db_connect.php";
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

// Handle filters
$filter_type = $_GET['type'] ?? 'all';
$filter_color = $_GET['color'] ?? 'all';

// Build missing pets query
$missing_sql = "SELECT * FROM missing_pets WHERE status='approved'";
if($filter_type != 'all') $missing_sql .= " AND species='". $conn->real_escape_string($filter_type) ."'";
if($filter_color != 'all') $missing_sql .= " AND color='". $conn->real_escape_string($filter_color) ."'";
$missing_sql .= " ORDER BY id DESC";
$missing_result = $conn->query($missing_sql);

// Build found pets query
$found_sql = "SELECT * FROM found_pets WHERE status='approved'";
if($filter_type != 'all') $found_sql .= " AND species='". $conn->real_escape_string($filter_type) ."'";
if($filter_color != 'all') $found_sql .= " AND color='". $conn->real_escape_string($filter_color) ."'";
$found_sql .= " ORDER BY id DESC";
$found_result = $conn->query($found_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pet Finder | Home</title>
    <style>
        body { margin:0; padding:0; font-family:Arial; background:#f2f6f9; }
        .navbar { background:#115272; color:white; padding:15px 40px; display:flex; justify-content:space-between; flex-wrap:wrap; }
        .navbar a { color:white; margin-left:20px; text-decoration:none; }
        .container { max-width:1200px; margin:40px auto; text-align:center; }
        .cards { display:flex; gap:30px; justify-content:center; flex-wrap:wrap; margin-bottom:40px; }
        .card { width:260px; background:white; padding:20px; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,.15); }
        .card img { width:100%; height:180px; object-fit:cover; border-radius:8px; margin-bottom:10px; }
        .card h3 { margin:10px 0 5px; }
        .card p { font-size:14px; color:#555; margin:3px 0; }
        button { width:100%; padding:10px; background:#115272; border:none; color:white; border-radius:6px; cursor:pointer; }
        .filter { margin-bottom:20px; }
        .filter select { padding:6px 10px; border-radius:6px; border:1px solid #ccc; margin-left:10px; }
        h2 { color:#115272; margin-bottom:10px; }

        /* Two-column layout */
        .pets-columns { display:flex; gap:30px; flex-wrap:wrap; justify-content:space-between; }
        .column { flex:1; min-width:300px; }

        /* Responsive */
        @media (max-width: 768px) {
            .pets-columns { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Pet Finder PH</h1>
    <div>
        <a href="index.php">Home</a>
        <a href="public_listing.php">Listings</a>
        <?php if ($isLoggedIn): ?>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Admin</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <h2>Help Reunite Pets with Their Families</h2>
    <p>Select an option below.</p>

    <div class="cards">
        <div class="card">
            <h3>Report Missing Pet</h3>
            <p>Submit your missing pet information.</p>
            <button onclick="window.location.href='report_missing.php'">Report Missing</button>
        </div>

        <div class="card">
            <h3>Report Found Pet</h3>
            <p>Report a pet you found.</p>
            <button onclick="window.location.href='report_found.php'">Report Found</button>
        </div>
    </div>

    <!-- Filter dropdowns -->
    <div class="filter">
        <form method="GET" action="">
            <label for="type">Type:</label>
            <select name="type" id="type" onchange="this.form.submit()">
                <option value="all" <?= $filter_type=='all'?'selected':'' ?>>All</option>
                <option value="dog" <?= $filter_type=='dog'?'selected':'' ?>>Dog</option>
                <option value="cat" <?= $filter_type=='cat'?'selected':'' ?>>Cat</option>
                <option value="other" <?= $filter_type=='other'?'selected':'' ?>>Other</option>
            </select>

            <label for="color">Color:</label>
            <select name="color" id="color" onchange="this.form.submit()">
                <option value="all" <?= $filter_color=='all'?'selected':'' ?>>All</option>
                <option value="black" <?= $filter_color=='black'?'selected':'' ?>>Black</option>
                <option value="white" <?= $filter_color=='white'?'selected':'' ?>>White</option>
                <option value="brown" <?= $filter_color=='brown'?'selected':'' ?>>Brown</option>
                <option value="mixed" <?= $filter_color=='mixed'?'selected':'' ?>>Mixed</option>
            </select>
        </form>
    </div>

    <!-- Two-column layout -->
    <div class="pets-columns">
        <!-- Missing Pets Left -->
        <div class="column">
            <h2>Missing Pets</h2>
            <?php if($missing_result->num_rows > 0): ?>
                <div class="cards">
                <?php while($row = $missing_result->fetch_assoc()): ?>
                    <div class="card">
                        <img src="uploads/<?= !empty($row['image'])?$row['image']:'placeholder.png' ?>" alt="<?= htmlspecialchars($row['pet_name']) ?>">
                        <h3><?= htmlspecialchars($row['pet_name']) ?></h3>
                        <p>Species: <?= htmlspecialchars($row['species']) ?></p>
                        <p>Color: <?= htmlspecialchars($row['color']) ?></p>
                        <p>Last Seen: <?= htmlspecialchars($row['last_seen']) ?></p>
                        <p>Contact: <?= htmlspecialchars($row['contact']) ?></p>
                    </div>
                <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No missing pets found.</p>
            <?php endif; ?>
        </div>

        <!-- Found Pets Right -->
        <div class="column">
            <h2>Found Pets</h2>
            <?php if($found_result->num_rows > 0): ?>
                <div class="cards">
                <?php while($row = $found_result->fetch_assoc()): ?>
                    <div class="card">
                        <img src="uploads/<?= !empty($row['image'])?$row['image']:'placeholder.png' ?>" alt="<?= htmlspecialchars($row['species']) ?>">
                        <h3><?= htmlspecialchars($row['species']) ?></h3>
                        <p>Color: <?= htmlspecialchars($row['color']) ?></p>
                        <p>Location Found: <?= htmlspecialchars($row['found_location']) ?></p>
                        <p>Contact: <?= htmlspecialchars($row['contact']) ?></p>
                    </div>
                <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No found pets listed.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
