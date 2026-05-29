<?php
require '../auth_check.php';
require '../config/db.php';

if ($_SESSION['role'] !== 'department') {
    die("Access Denied");
}

$dept_id = $_SESSION['user_id'];
$activePage = 'dashboard';

date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');
$now   = time();

$msg = "";

/* ================= SAVE ATTENDANCE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exam_id'])) {

    $exam_id   = intval($_POST['exam_id']);
    $forenoon  = isset($_POST['forenoon']) ? intval($_POST['forenoon']) : 0;
    $afternoon = isset($_POST['afternoon']) ? intval($_POST['afternoon']) : 0;
    $setting   = intval($_POST['question_setting']);

    // Check if already submitted
    $check = $conn->prepare("SELECT id FROM attendance WHERE exam_id=?");
    $check->bind_param("i", $exam_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $msg = "Attendance already submitted.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO attendance
            (exam_id, forenoon_count, afternoon_count, question_setting, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param("iiii", $exam_id, $forenoon, $afternoon, $setting);

        if ($stmt->execute()) {
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $msg = "Error saving attendance.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Department Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    margin: 0;
    background: #0f172a;
    font-family: system-ui;
}

.content {
    margin-left: 260px;
    padding: 40px;
}

.card-box{
    background:#ffffff;
    border-radius:16px;
    padding:30px;
    box-shadow:0 20px 40px rgba(0,0,0,.4);
}

.exam-row{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid #e5e7eb;
}
.exam-row:last-child{border-bottom:none;}

.badge{
    background:#16a34a;
    color:white;
    padding:4px 10px;
    border-radius:10px;
    font-size:12px;
}
</style>
</head>

<body>

<?php include 'dept_sidebar.php'; ?>

<div class="content">

    <h4 class="text-white mb-4">
        Welcome, <?= htmlspecialchars($_SESSION['name']); ?> (Department)
    </h4>

    <div class="card-box">

        <?php if($msg): ?>
            <div class="alert alert-warning"><?= $msg ?></div>
        <?php endif; ?>

        <h4 class="mb-4">Today's Exam Panel</h4>

        <?php
        $sql = "
        SELECT *
        FROM exams
        WHERE department = ?
        AND exam_date = ?
        ORDER BY exam_date
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $dept_id, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0):
        ?>
            <p class="text-muted">No exams scheduled for today.</p>
        <?php
        endif;

        while ($row = $result->fetch_assoc()):

            $examDate = $row['exam_date'];
            $session  = $row['session'];

            $fn_start = strtotime("$examDate 09:30");
            $fn_end   = strtotime("$examDate 12:30");

            $an_start = strtotime("$examDate 13:30");
            $an_end   = strtotime("$examDate 16:30");

            $sessionOpen = false;

            if ($session === 'forenoon' && $now >= $fn_start && $now <= $fn_end) {
                $sessionOpen = true;
            }

            if ($session === 'afternoon' && $now >= $an_start && $now <= $an_end) {
                $sessionOpen = true;
            }

            if ($session === 'forenoon_afternoon' &&
                (($now >= $fn_start && $now <= $fn_end) ||
                 ($now >= $an_start && $now <= $an_end))) {
                $sessionOpen = true;
            }

            $attQ = $conn->prepare("
                SELECT forenoon_count, afternoon_count, question_setting
                FROM attendance
                WHERE exam_id = ?
            ");
            $attQ->bind_param("i", $row['id']);
            $attQ->execute();
            $attendance = $attQ->get_result()->fetch_assoc();
        ?>

        <hr>

        <div class="exam-row"><b>Subject Code</b><span><?= $row['subject_code'] ?></span></div>
        <div class="exam-row"><b>Subject Name</b><span><?= $row['subject_name'] ?></span></div>
        <div class="exam-row"><b>Semester</b><span><?= $row['semester'] ?></span></div>
        <div class="exam-row"><b>Lab No</b><span><?= $row['lab_no'] ?></span></div>
        <div class="exam-row"><b>Exam Date</b><span><?= date("d-m-Y", strtotime($row['exam_date'])) ?></span></div>
        <div class="exam-row"><b>Session</b><span><?= ucfirst(str_replace('_',' & ',$row['session'])) ?></span></div>

        <?php if ($attendance): ?>

            <table class="table table-bordered mt-3">
                <tr>
                    <th>Forenoon</th>
                    <th>Afternoon</th>
                    <th>Total</th>
                    <th>Setting</th>
                </tr>
                <tr>
                    <td><?= $attendance['forenoon_count'] ?></td>
                    <td><?= $attendance['afternoon_count'] ?></td>
                    <td><?= $attendance['forenoon_count'] + $attendance['afternoon_count'] ?></td>
                    <td><?= $attendance['question_setting'] ?></td>
                </tr>
            </table>

            <span class="badge">✔ Attendance Submitted</span>

        <?php elseif ($sessionOpen): ?>

            <form method="post" class="mt-3">
                <input type="hidden" name="exam_id" value="<?= $row['id'] ?>">

                <?php if ($session !== 'afternoon'): ?>
                    <label>Forenoon Attendance</label>
                    <input type="number" name="forenoon" class="form-control" required>
                <?php else: ?>
                    <input type="hidden" name="forenoon" value="0">
                <?php endif; ?>

                <?php if ($session !== 'forenoon'): ?>
                    <label class="mt-2">Afternoon Attendance</label>
                    <input type="number" name="afternoon" class="form-control" required>
                <?php else: ?>
                    <input type="hidden" name="afternoon" value="0">
                <?php endif; ?>

                <label class="mt-3">Question Paper Settings</label>
                <select name="question_setting" class="form-control" required>
                    <option value="">-- Select Setting --</option>
                    <option value="1">Setting 1</option>
                    <option value="2">Setting 2</option>
                    <option value="3">Setting 3</option>
                    <option value="4">Setting 4</option>
                    <option value="5">Setting 5</option>
                </select>

                <button class="btn btn-primary mt-3">Submit Attendance</button>
            </form>

        <?php else: ?>

            <p class="text-muted mt-3">
                Attendance window not active for this session.
            </p>

        <?php endif; ?>

        <?php endwhile; ?>

    </div>

</div>

</body>
</html>