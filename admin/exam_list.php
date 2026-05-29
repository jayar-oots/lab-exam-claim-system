<?php
require '../auth_check.php';
require '../config/db.php';

$type = $_GET['type'] ?? 'all';

$where = "";
if ($type === 'pending') {
    $where = "WHERE claim_status = 'pending'";
} elseif ($type === 'approved') {
    $where = "WHERE claim_status = 'approved'";
}

$sql = "
SELECT e.id,
       u.name AS department_name,
       e.subject_code,
       e.exam_date
FROM exams e
LEFT JOIN users u ON e.department = u.id
$where
ORDER BY e.exam_date DESC
";

$res = $conn->query($sql);

include 'admin_sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Exam List</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* Adjust this width to match your sidebar */
.main-content {
    margin-left: 260px;
    padding: 30px;
    min-height: 100vh;
    background: #f1f5f9;
}

.card {
    border-radius: 14px;
}

.table thead th {
    background: #1f2933;
    color: #fff;
}
</style>
</head>

<body>

<div class="main-content">
  <div class="container-fluid">

    <div class="card shadow-sm">
      <div class="card-body">

        <h4 class="mb-4">
          <?= ucfirst($type) ?> Exams
        </h4>

        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead>
              <tr>
                <th>Department</th>
                <th>Subject Code</th>
                <th>Exam Date</th>
                <th style="width:120px">Action</th>
              </tr>
            </thead>

            <tbody>
            <?php if ($res->num_rows > 0): ?>
              <?php while($r = $res->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($r['department_name'] ?: 'Unknown Department') ?></td>
                  <td><?= htmlspecialchars($r['subject_code']) ?></td>
                  <td><?= date('d-m-Y', strtotime($r['exam_date'])) ?></td>
                  <td>
                    <a href="exam_view.php?id=<?= $r['id'] ?>"
                       class="btn btn-sm btn-info">
                       View
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="text-center text-muted">
                  No exams found
                </td>
              </tr>
            <?php endif; ?>
            </tbody>

          </table>
        </div>

      </div>
    </div>

  </div>
</div>

</body>
</html>
