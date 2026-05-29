<?php
require '../auth_check.php';
require '../config/db.php';

/* ===============================
   FETCH APPROVED INTERNAL CLAIMS
================================= */

$sql = "
SELECT 
    a.id AS attendance_id,
    e.subject_code,
    (a.forenoon_count + a.afternoon_count) AS total_attendance
FROM attendance a
JOIN exams e ON a.exam_id = e.id
WHERE e.internal_claim_status = 'approved'
AND e.internal_staff_id IS NOT NULL
ORDER BY a.id DESC
";

$listRes = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Internal Approved Claims</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css"> <!-- same css used by external -->

</head>

<body>

<div class="d-flex">

    <!-- ===== SIDEBAR (Same as External Page) ===== -->
    <div class="sidebar bg-dark text-white p-3" style="width:250px; min-height:100vh;">
        <h4 class="mb-4">Lab Claim System</h4>

        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a href="admin_dashboard.php" class="nav-link text-white">Dashboard</a>
            </li>
            <li class="nav-item mb-2">
                <a href="create_exam.php" class="nav-link text-white">Create Exam</a>
            </li>
            <li class="nav-item mb-2">
                <a href="external_examiner.php" class="nav-link text-white">External Examiner</a>
            </li>
            <li class="nav-item mb-2">
                <a href="external_staff_details.php" class="nav-link text-white">External Staff Details</a>
            </li>
            <li class="nav-item mb-2">
                <a href="rate_settings.php" class="nav-link text-white">Rate Settings</a>
            </li>
            <li class="nav-item mb-2">
                <a href="create_user.php" class="nav-link text-white">Create User</a>
            </li>
            <li class="nav-item mt-4">
                <a href="../logout.php" class="nav-link text-white">Logout</a>
            </li>
        </ul>
    </div>


    <!-- ===== MAIN CONTENT ===== -->
    <div class="flex-grow-1 p-5" style="background:#f4f6f9;">

        <h1 class="mb-4">Internal Approved Claims</h1>

        <div class="card shadow">
            <div class="card-body">

                <table class="table table-bordered text-center align-middle">

                    <thead style="background:#1f2d3d; color:white;">
                        <tr>
                            <th>Subject Code</th>
                            <th>Total Attendance</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($listRes && $listRes->num_rows > 0): ?>
                        <?php while ($row = $listRes->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['subject_code']) ?></td>
                                <td><?= $row['total_attendance'] ?></td>
                                <td>
                                    <a href="internal_claim_details.php?id=<?= $row['attendance_id'] ?>" 
                                       class="btn btn-primary btn-sm px-4">
                                       View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-muted">
                                No approved internal claims found
                            </td>
                        </tr>
                    <?php endif; ?>

                    </tbody>

                </table>

            </div>
        </div>

    </div>
</div>

</body>
</html>