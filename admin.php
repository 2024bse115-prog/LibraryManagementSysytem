<?php
session_start();
require_once 'config.php';

// Check if user is admin
if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_result = $conn->query("SELECT * FROM users WHERE id = {$_SESSION['user_id']}");
$user = $user_result->fetch_assoc();

if($user['role'] != 'admin') {
    $_SESSION['alerts'][] = [
        'type' => 'error',
        'message' => 'Access denied! Admin only.'
    ];
    header('Location: index.php');
    exit();
}

$name = $_SESSION['name'];

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$total_resources = $conn->query("SELECT COUNT(*) as total FROM resources")->fetch_assoc()['total'];
$total_downloads = $conn->query("SELECT SUM(downloads) as total FROM resources")->fetch_assoc()['total'] ?? 0;
$total_discussions = $conn->query("SELECT COUNT(*) as total FROM discussions")->fetch_assoc()['total'];

// Recent uploads
$recent_uploads = $conn->query("SELECT r.*, c.course_code 
                                FROM resources r 
                                JOIN courses c ON r.course_id = c.id 
                                ORDER BY r.created_at DESC LIMIT 10");

// All users
$users = $conn->query("SELECT u.*, COUNT(DISTINCT r.id) as uploads 
                       FROM users u 
                       LEFT JOIN resources r ON u.id = r.uploader_id 
                       GROUP BY u.id 
                       ORDER BY u.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard - Pass.Papers</title>
<link rel="stylesheet" href="styles.css">
<link href='https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
.admin-container {
    min-height: 100vh;
    padding: 120px 100px 60px;
    background: #75f4dcff;
}

.admin-header {
    margin-bottom: 40px;
}

.admin-header h1 {
    font-size: 42px;
    color: var(--dark);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.admin-header p {
    font-size: 18px;
    color: var(--gray);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, #87abedff 0%, #d4b8f0ff 100%);
    padding: 35px;
    border-radius: 20px;
    color: #fff;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    transition: 0.3s;
}

.stat-card:nth-child(2) {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card:nth-child(3) {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-card:nth-child(4) {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stat-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
}

.stat-card i {
    font-size: 50px;
    margin-bottom: 20px;
    opacity: 0.9;
    color: #fff;
}

.stat-card h3 {
    font-size: 40px;
    margin-bottom: 10px;
    font-weight: 700;
    color: #fff;
}

.stat-card p {
    opacity: 0.95;
    font-size: 16px;
    font-weight: 500;
     color: #fff;
}

.admin-section {
    background: #fff;
    padding: 35px;
    border-radius: 20px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.admin-section h2 {
    font-size: 28px;
    color: var(--dark);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}

.data-table th,
.data-table td {
    padding: 18px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.data-table th {
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table tbody tr {
    transition: 0.2s;
}

.data-table tbody tr:hover {
    background: #f8fafc;
    transform: scale(1.01);
}

.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.badge-admin {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: #fff;
}

.badge-student {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: #fff;
}

.badge-lecturer {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: #fff;
}

.action-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    margin-right: 8px;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-view {
    background: #3b82f6;
    color: #fff;
}

.btn-view:hover {
    background: #2563eb;
    transform: translateY(-2px);
}

.btn-delete {
    background: #ef4444;
    color: #fff;
}

.btn-delete:hover {
    background: #dc2626;
    transform: translateY(-2px);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--gray);
}

.empty-state i {
    font-size: 80px;
    margin-bottom: 20px;
    opacity: 0.3;
}

@media (max-width: 768px) {
    .admin-container {
        padding: 100px 20px 40px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .data-table {
        font-size: 12px;
    }
    
    .data-table th,
    .data-table td {
        padding: 12px 8px;
    }
}
</style>
</head>
<body>

<header>
<a href="index.php" class="logo">Pass.Papers</a>
<nav>
<a href="index.php">Home</a>
<a href="browse.php">Browse</a>
<a href="upload.php">Upload</a>
<a href="forum.php">Forum</a>
<a href="admin.php" class="active">Admin</a>
</nav>
<div class="user-auth">
<div class="profile-box">
<div class="avatar-circle"><?= strtoupper($name[0])?></div>
<div class="dropdown">
<a href="profile.php">My Account</a>
<a href="logout.php">Logout</a>
</div>
</div>
</div>
</header>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class='bx bxs-dashboard'></i> Admin Dashboard</h1>
        <p>Manage and monitor your Pass.Papers platform</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <i class='bx bxs-user-circle'></i>
            <h3><?= $total_users ?></h3>
            <p>Total Users</p>
        </div>
        <div class="stat-card">
            <i class='bx bxs-file-doc'></i>
            <h3><?= $total_resources ?></h3>
            <p>Total Resources</p>
        </div>
        <div class="stat-card">
            <i class='bx bxs-download'></i>
            <h3><?= number_format($total_downloads) ?></h3>
            <p>Total Downloads</p>
        </div>
        <div class="stat-card">
            <i class='bx bxs-message-square-dots'></i>
            <h3><?= $total_discussions ?></h3>
            <p>Forum Discussions</p>
        </div>
    </div>

    <div class="admin-section">
        <h2><i class='bx bx-time-five'></i> Recent Uploads</h2>
        <?php if($recent_uploads->num_rows > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Uploader</th>
                    <th>Downloads</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($resource = $recent_uploads->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($resource['title']) ?></strong></td>
                    <td><span class="badge" style="background: #667eea; color: #fff;"><?= htmlspecialchars($resource['course_code']) ?></span></td>
                    <td><?= htmlspecialchars($resource['uploader_name']) ?></td>
                    <td><?= $resource['downloads'] ?></td>
                    <td><?= $resource['views'] ?></td>
                    <td><?= date('M d, Y', strtotime($resource['created_at'])) ?></td>
                    <td>
                        <a href="view.php?id=<?= $resource['id'] ?>" class="action-btn btn-view" target="_blank">
                            <i class='bx bx-show'></i> View
                        </a>
                        <button onclick="deleteResource(<?= $resource['id'] ?>)" class="action-btn btn-delete">
                            <i class='bx bx-trash'></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class='bx bx-folder-open'></i>
            <p>No resources uploaded yet</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="admin-section">
        <h2><i class='bx bx-group'></i> All Users</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Faculty</th>
                    <th>Uploads</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= htmlspecialchars($u['faculty'] ?? 'N/A') ?></td>
                    <td><?= $u['uploads'] ?></td>
                    <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                        <button onclick="deleteUser(<?= $u['id'] ?>)" class="action-btn btn-delete">
                            <i class='bx bx-trash'></i> Delete
                        </button>
                        <?php else: ?>
                        <span style="color: var(--gray); font-size: 12px;">Current User</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="script.js"></script>
<script>
function deleteResource(id) {
    if(confirm('⚠ Are you sure you want to delete this resource? This cannot be undone!')) {
        fetch('admin_actions.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=delete_resource&id=' + id
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if(data.success) {
                alert('✅ Resource deleted successfully!');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(function(error) {
            alert('❌ Error: ' + error);
        });
    }
}

function deleteUser(id) {
    if(confirm('⚠ Are you sure you want to delete this user? All their uploads will also be deleted!')) {
        fetch('admin_actions.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=delete_user&id=' + id
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if(data.success) {
                alert('✅ User deleted successfully!');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(function(error) {
            alert('❌ Error: ' + error);
        });
    }
}
</script>
</body>
</html>