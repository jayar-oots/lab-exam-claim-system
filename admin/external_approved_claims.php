<?php
require '../auth_check.php';
require '../config/db.php';

$sql = "
SELECT 
    a.id AS attendance_id,
    e.subject_code,
    (a.forenoon_count + a.afternoon_count) AS total_attendance
FROM exams e
JOIN attendance a ON a.exam_id = e.id
WHERE e.external_claim_status = 'approved'
AND e.external_staff_id IS NOT NULL
ORDER BY e.exam_date DESC
";

$result = $conn->query($sql);
?>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">

        <h2 class="mb-4 fw-bold">External Approved Claims</h2>

        <div class="card shadow">
            <div class="card-body p-0">

                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Subject Code</th>
                            <th>Total Attendance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">

                    <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['subject_code']) ?></td>
            <td><?= $row['total_attendance'] ?></td>
            <td>
                <a href="../claims/claim_details.php?id=<?= $row['attendance_id'] ?>"
   class="btn btn-primary btn-sm px-3">
   View
</a>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="3" class="text-muted py-3">
            No approved claims found
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
