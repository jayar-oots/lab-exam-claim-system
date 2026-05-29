<?php
require '../auth_check.php';
require '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ===============================
   FETCH EXAM DETAILS
   =============================== */
$sql = "
SELECT 
    e.*,
    u.name AS department_name,
    i.name AS internal_name,
    es.name AS external_name
FROM exams e
LEFT JOIN users u ON e.department = u.id
LEFT JOIN internal_staff i ON e.internal_staff_id = i.id
LEFT JOIN external_staff es ON e.external_staff_id = es.id
WHERE e.id = ?
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$exam = $result->fetch_assoc();

if (!$exam) {
    die("Exam not found.");
}

/* ===============================
   SESSION & TIME LOGIC
   =============================== */
$sessionText = '-';
$timeText = '-';

if (!empty($exam['session'])) {

    if ($exam['session'] === 'forenoon') {
        $sessionText = 'Forenoon';
        $timeText = '9:30 AM - 12:30 PM';
    } 
    elseif ($exam['session'] === 'afternoon') {
        $sessionText = 'Afternoon';
        $timeText = '1:30 PM - 4:30 PM';
    } 
    elseif ($exam['session'] === 'forenoon_afternoon') {
        $sessionText = 'Forenoon & Afternoon';
        $timeText = '9:30 AM - 12:30 PM & 1:30 PM - 4:30 PM';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f1f5f9;
        }
        .card {
            border-radius: 12px;
        }
        .table th {
            width: 30%;
            background: #f8fafc;
        }
    </style>
</head>

<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content p-4">

    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="mb-4">Exam Details</h4>

            <table class="table table-bordered align-middle">

                <tr>
                    <th>Stream</th>
                    <td><?= htmlspecialchars($exam['stream'] ?? '-') ?></td>
                </tr>

                <tr>
                    <th>Department</th>
                    <td><?= htmlspecialchars($exam['department_name'] ?? '-') ?></td>
                </tr>

                <tr>
                    <th>Semester</th>
                    <td><?= htmlspecialchars($exam['semester'] ?? '-') ?></td>
                </tr>

                <tr>
                    <th>Lab</th>
                    <td><?= htmlspecialchars($exam['lab_no'] ?? '-') ?></td>
                </tr>

                <tr>
                    <th>Subject Code</th>
                    <td><?= htmlspecialchars($exam['subject_code'] ?? '-') ?></td>
                </tr>

                <tr>
                    <th>Subject Name</th>
                    <td><?= htmlspecialchars($exam['subject_name'] ?? '-') ?></td>
                </tr>

                <tr>
                    <th>Exam Date</th>
                    <td>
                        <?= !empty($exam['exam_date']) 
                            ? date('d-m-Y', strtotime($exam['exam_date'])) 
                            : '-' ?>
                    </td>
                </tr>

                <tr>
                    <th>Session</th>
                    <td>
                        <strong><?= $sessionText ?></strong><br>
                        <small><?= $timeText ?></small>
                    </td>
                </tr>

                <tr>
                    <th>Internal Examiner</th>
                    <td>
                        <?= !empty($exam['internal_name']) 
                            ? htmlspecialchars($exam['internal_name']) 
                            : 'Not Assigned' ?>
                    </td>
                </tr>

                <tr>
                    <th>External Examiner</th>
                    <td>
                        <?= !empty($exam['external_name']) 
                            ? htmlspecialchars($exam['external_name']) 
                            : 'Not Assigned' ?>
                    </td>
                </tr>

            </table>

            <a href="exam_list.php" class="btn btn-secondary mt-3">
                ← Back to Exam List
            </a>

        </div>
    </div>

</div>

</body>
</html>
