<?php
require '../auth_check.php';
require '../config/db.php';

/* ============================
   FETCH INTERNAL PENDING CLAIMS
============================ */

$sql = "
SELECT 
    a.id,
    e.subject_code,
    (a.forenoon_count + a.afternoon_count) AS attendance
FROM attendance a
JOIN exams e ON a.exam_id = e.id
WHERE a.status = 'pending'
AND e.internal_staff_id IS NOT NULL
ORDER BY a.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Internal Pending Claims</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #f5f6fa;
        }

        .sidebar {
            width: 250px;
            background-color: #0b1a34;
            position: fixed;
            height: 100%;
            padding-top: 20px;
        }

        .sidebar h3 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #ddd;
            padding: 12px 20px;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #1f2d3d;
            color: #fff;
        }

        .main-content {
            margin-left: 250px;
            padding: 40px;
        }

        .table thead {
            background-color: #1f2d3d;
            color: #fff;
        }

        .btn-view {
            background-color: #0d6efd;
            color: #fff;
        }

        .btn-view:hover {
            background-color: #084298;
            color: #fff;
        }
    </style>
</head>
<?php include 'admin_sidebar.php'; ?>

<body>

<!-- ===== Sidebar ===== -->


<!-- ===== Main Content ===== -->
<div class="main-content">

    <h2>Internal Pending Claims</h2>

    <table class="table table-bordered align-middle mt-4">
        <thead>
            <tr>
                <th>Subject Code</th>
                <th>Total Attendance</th>
                <th width="150">Action</th>
            </tr>
        </thead>
        <tbody>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while($row = $result->fetch_assoc()): ?>

                <tr>
                    <td><?= htmlspecialchars($row['subject_code']) ?></td>
                    <td><?= htmlspecialchars($row['attendance']) ?></td>
                    <td>
                       <a href="../admin/internal_claim_details.php?id=<?= $row['id'] ?>"
   class="btn btn-view btn-sm">
   View
</a>
                           
                    </td>
                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="3" class="text-center text-muted">
                    No internal pending claims found.
                </td>
            </tr>

        <?php endif; ?>

        </tbody>
    </table>

</div>

</body>
</html>
