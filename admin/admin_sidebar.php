<?php
if (!isset($_SESSION)) {
    session_start();
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #f1f5f9;
}

/* Sidebar */
.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: #020617;
    padding: 20px;
}

.sidebar h4 {
    color: #ffffff;
    margin-bottom: 30px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #cbd5f5;
    padding: 10px 12px;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 6px;
    font-size: 15px;
    transition: 0.2s;
}

.sidebar a:hover {
    background: #1e293b;
    color: #ffffff;
}

.sidebar .logout {
    margin-top: 30px;
    color: #facc15;
}

/* Main Content */
.main-content {
    margin-left: 260px;   /* Must match sidebar width */
    padding: 30px;
    min-height: 100vh;
    background: #f1f5f9;
}

/* Card Styling */
.card {
    border-radius: 14px;
    border: none;
}

/* Table Styling */
.table thead th {
    background: #1f2937;
    color: #ffffff;
    text-align: center;
}

.table tbody td {
    vertical-align: middle;
}
</style>

<div class="sidebar">
    <h4>Lab Claim System</h4>

    <a href="admin_dashboard.php">🏠 Dashboard</a>
    <a href="create_exam.php">📝 Create Exam</a>
    <a href="add_external.php">👨‍🏫 External Examiner</a>
    <a href="external_staff_list.php">👨‍🏫 External Staff Details</a>
    <a href="settings.php">⚙ Rate Settings</a>
    <a href="create_user.php">👤 Create User</a>
   <a href="../auth/logout.php">
    Logout
</a>

</div>
