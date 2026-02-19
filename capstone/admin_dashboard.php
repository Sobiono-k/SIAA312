<?php
require "db_connect.php";
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle filter tabs
$missing_filter = $_GET['missing_filter'] ?? 'all';
$found_filter = $_GET['found_filter'] ?? 'all';

// SQL queries for tables
$missing_sql = "SELECT * FROM missing_pets";
if($missing_filter != 'all') $missing_sql .= " WHERE status='$missing_filter'";
$missing_sql .= " ORDER BY id DESC";

$found_sql = "SELECT * FROM found_pets";
if($found_filter != 'all') $found_sql .= " WHERE status='$found_filter'";
$found_sql .= " ORDER BY id DESC";

// --- SIMPLE HASH-BASED MATCHING ---
function get_top_matches_simple($found_image_file, $conn, $top_n = 3){
    $matches = [];

    $found_path = "uploads/found/" . $found_image_file;
    if(!file_exists($found_path)) return $matches;

    // Compute MD5 hash of the found image
    $found_hash = md5_file($found_path);

    // Compare with missing pets
    $res_missing = $conn->query("SELECT id, pet_name, image FROM missing_pets WHERE status='pending'");
    while($missing = $res_missing->fetch_assoc()){
        $missing_file = $missing['image'];
        $missing_path = "uploads/missing/" . $missing_file;

        if(file_exists($missing_path)){
            $missing_hash = md5_file($missing_path);

            // If hashes match, similarity = 1
            $similarity = ($found_hash === $missing_hash) ? 1 : 0;

            if($similarity > 0){
                $matches[] = [
                    'id' => $missing['id'],
                    'pet_name' => $missing['pet_name'],
                    'image' => $missing_file,
                    'similarity' => $similarity
                ];
            }
        }
    }

    return array_slice($matches, 0, $top_n);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
body { margin:0; font-family:Arial; background:#f2f6f9; }
header { background:#115272; color:white; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }
header h1 { margin:0; font-size:24px; }
header a { color:white; text-decoration:none; background:#0d3b5e; padding:8px 15px; border-radius:6px; }
header a:hover { background:#0a2c48; }

.container { max-width:1200px; margin:20px auto; padding:0 15px; }
h2 { color:#115272; text-align:center; margin-bottom:10px; }
.tabs { text-align:center; margin-bottom:15px; }
.tabs a { margin:0 10px; text-decoration:none; padding:6px 12px; border-radius:5px; background:#ccc; color:#000; }
.tabs a.active { background:#115272; color:white; }

table { width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1); margin-bottom:30px; }
th, td { padding:12px; text-align:center; border-bottom:1px solid #ddd; }
th { background:#115272; color:white; }
td img { width:60px; height:60px; object-fit:cover; border-radius:6px; }
button { padding:5px 10px; margin:2px; border:none; border-radius:5px; cursor:pointer; }
button.approve { background:#28a745; color:white; }
button.delete { background:#dc3545; color:white; }
button.match-btn { background:#0069d9; color:white; }

.status-pending { color:#ff8c00; font-weight:bold; }
.status-approved { color:#28a745; font-weight:bold; }

@media screen and (max-width:768px){
    table, thead, tbody, th, td, tr { display:block; }
    th { text-align:left; background:#115272; color:white; padding:10px; }
    td { text-align:left; padding:10px; border-bottom:1px solid #ddd; position:relative; }
    td img { width:100px; height:100px; display:block; margin-bottom:5px; }
    tr { margin-bottom:15px; border:1px solid #ddd; border-radius:8px; padding:10px; background:white; }
    td:before { content: attr(data-label); font-weight:bold; display:block; }
}
</style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
    <a href="logout.php">Logout</a>
</header>

<div class="container">

<h2>Missing Pets</h2>
<div class="tabs">
    <a href="?missing_filter=all" class="<?= $missing_filter=='all'?'active':'' ?>">All</a>
    <a href="?missing_filter=pending" class="<?= $missing_filter=='pending'?'active':'' ?>">Pending</a>
    <a href="?missing_filter=approved" class="<?= $missing_filter=='approved'?'active':'' ?>">Approved</a>
</div>
<table>
<thead>
<tr>
<th>Name</th><th>Species</th><th>Color</th><th>Last Seen</th><th>Contact</th><th>Image</th><th>Status</th><th>Actions</th>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query($missing_sql);
while($row = $result->fetch_assoc()):
    $img = !empty($row['image']) ? $row['image'] : 'placeholder.png';
    $status_class = $row['status']=='approved' ? 'status-approved' : 'status-pending';
?>
<tr>
    <td data-label="Name"><?= $row['pet_name'] ?></td>
    <td data-label="Species"><?= $row['species'] ?></td>
    <td data-label="Color"><?= $row['color'] ?></td>
    <td data-label="Last Seen"><?= $row['last_seen'] ?></td>
    <td data-label="Contact"><?= $row['contact'] ?></td>
    <td data-label="Image"><img src="uploads/<?= $img ?>" alt="Pet Image"></td>
    <td data-label="Status" class="<?= $status_class ?>"><?= $row['status'] ?></td>
    <td data-label="Actions">
        <?php if($row['status'] != 'approved'): ?>
            <button class="action-btn approve" data-id="<?= $row['id'] ?>" data-type="missing" data-action="approve">Approve</button>
        <?php endif; ?>
        <button class="action-btn delete" data-id="<?= $row['id'] ?>" data-type="missing" data-action="delete">Delete</button>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<h2>Found Pets</h2>
<div class="tabs">
    <a href="?found_filter=all" class="<?= $found_filter=='all'?'active':'' ?>">All</a>
    <a href="?found_filter=pending" class="<?= $found_filter=='pending'?'active':'' ?>">Pending</a>
    <a href="?found_filter=approved" class="<?= $found_filter=='approved'?'active':'' ?>">Approved</a>
</div>
<table>
<thead>
<tr>
<th>Species</th><th>Color</th><th>Location</th><th>Contact</th><th>Image</th><th>Status</th><th>Suggested Matches</th><th>Actions</th>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query($found_sql);
while($row = $result->fetch_assoc()):
    $img = !empty($row['image']) ? $row['image'] : 'placeholder.png';
    $status_class = $row['status']=='approved' ? 'status-approved' : 'status-pending';

    // Top AI matches
    $top_matches = get_top_matches_simple($row['image'], $conn);

?>
<tr>
    <td data-label="Species"><?= $row['species'] ?></td>
    <td data-label="Color"><?= $row['color'] ?></td>
    <td data-label="Location"><?= $row['found_location'] ?></td>
    <td data-label="Contact"><?= $row['contact'] ?></td>
    <td data-label="Image"><img src="uploads/<?= $img ?>" alt="Pet Image"></td>
    <td data-label="Status" class="<?= $status_class ?>"><?= $row['status'] ?></td>

    <td data-label="Suggested Matches">
        <?php if(!empty($top_matches)): ?>
          <ul style="list-style:none; padding:0; margin:0;">
            <?php foreach($top_matches as $m): ?>
              <li style="margin-bottom:5px;">
                <img src="uploads/<?= $m['image'] ?>" width="50" style="vertical-align:middle; border-radius:4px;">
                <?= htmlspecialchars($m['pet_name']) ?> (<?= round($m['similarity']*100,1) ?>%)
                <button class="match-btn" data-missing-id="<?= $m['id'] ?>" data-found-id="<?= $row['id'] ?>">Confirm Match</button>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <span>No similar pets found</span>
        <?php endif; ?>
    </td>

    <td data-label="Actions">
        <?php if($row['status'] != 'approved'): ?>
            <button class="action-btn approve" data-id="<?= $row['id'] ?>" data-type="found" data-action="approve">Approve</button>
        <?php endif; ?>
        <button class="action-btn delete" data-id="<?= $row['id'] ?>" data-type="found" data-action="delete">Delete</button>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    // Approve/Delete buttons
    $(document).on('click', '.action-btn', function(){
        var btn = $(this);
        var id = btn.data('id');
        var type = btn.data('type');
        var action = btn.data('action');

        $.post('update_pet_status.php', {id:id, type:type, action:action}, function(res){
            res = res.trim();
            if(res == 'approved'){
                btn.closest('tr').find('td[data-label="Status"]').text('approved')
                   .removeClass('status-pending').addClass('status-approved');
                btn.remove();
            } else if(res == 'deleted'){
                btn.closest('tr').remove();
            } else {
                alert('Error: ' + res);
            }
        });
    });

    // Confirm Match buttons
    $(document).on('click', '.match-btn', function(){
        var btn = $(this);
        var missing_id = btn.data('missing-id');
        var found_id = btn.data('found-id');

        $.post('confirm_match.php', {missing_id:missing_id, found_id:found_id}, function(res){
            if(res.trim() == 'success'){
                alert('Match confirmed! Pet is marked as found.');
                location.reload();
            } else {
                alert('Error confirming match: ' + res);
            }
        });
    });
});
</script>

</body>
</html>
