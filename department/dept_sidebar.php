<?php
if (!isset($activePage)) {
    $activePage = '';
}
?>

<style>
/* SIDEBAR */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 240px;
    height: 100vh;
    background: linear-gradient(180deg, #020617, #020617);
    padding: 20px;
    color: #fff;
}

.sidebar h2 {
    font-size: 22px;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    color: #cbd5f5;
    text-decoration: none;
    padding: 12px 14px;
    border-radius: 10px;
    margin-bottom: 10px;
    font-weight: 500;
}

.sidebar a:hover,
.sidebar a.active {
    background: #1e293b;
    color: #fff;
}
</style>

<div class="sidebar">
    <h2>Department Panel</h2>

    <a href="dept_dashboard.php" class="<?= $activePage=='dashboard'?'active':'' ?>">
        Dashboard
    </a>

    <a href="dept_staff.php" class="<?= $activePage=='staff'?'active':'' ?>">
        Internal Staff
    </a>

    <a href="dept_subjects.php" class="<?= $activePage=='subjects'?'active':'' ?>">
        Subjects
    </a>

    <a href="exam_panel.php" class="<?= $activePage=='exam'?'active':'' ?>">
        Exam
    </a>

    <a href="../auth/logout.php">Logout</a>
</div>
