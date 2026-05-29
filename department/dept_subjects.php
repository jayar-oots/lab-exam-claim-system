<?php
$activePage = 'subjects';

require '../auth_check.php';
require '../config/db.php';

if ($_SESSION['role'] !== 'department') {
    die("Access Denied");
}
$dept_id = $_SESSION['user_id'];

/* stop fatal errors */
mysqli_report(MYSQLI_REPORT_OFF);

$msg = "";
$msg_type = "";

/* EDIT MODE VARIABLES */
$edit_mode = false;
$edit_id = "";
$edit_year = "";
$edit_semester = "";
$edit_code = "";
$edit_name = "";

/* DELETE SUBJECT */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM subjects WHERE id='$id' AND department_id='$dept_id'");
    header("Location: dept_subjects.php");
    exit;
}

/* LOAD SUBJECT FOR EDIT */
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM subjects WHERE id='$edit_id' AND department_id='$dept_id'");
    if ($row = mysqli_fetch_assoc($res)) {
        $edit_mode = true;
        $edit_year = $row['year'];
        $edit_semester = $row['semester'];
        $edit_code = $row['subject_code'];
        $edit_name = $row['subject_name'];
    }
}

/* ADD / UPDATE SUBJECT */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $year = $_POST['year'] ?? "";
    $semester = $_POST['semester'] ?? "";
    $subject_code = strtoupper(trim($_POST['subject_code'] ?? ""));
    $subject_name = trim($_POST['subject_name'] ?? "");

    if ($year == "" || $semester == "" || $subject_code == "" || $subject_name == "") {
        $msg = "All fields are required.";
        $msg_type = "danger";
    }
    elseif (!preg_match("/^[0-9]{2}[UP][A-Z]T[1-8][A-Z]{2}$/", $subject_code)) {
    $msg = "Invalid Subject Code format (Eg: 23UAA1AA)";
    $msg_type = "danger";
}

    else {
        $code_sem = (int)$subject_code[5];
        if ($code_sem != $semester) {
            $msg = "Semester does not match Subject Code.";
            $msg_type = "danger";
        }
        else {

            /* UPDATE */
            if (isset($_POST['update_id'])) {
                $id = (int)$_POST['update_id'];

                mysqli_query($conn, "
                    UPDATE subjects SET
                    subject_code='$subject_code',
                    subject_name='$subject_name',
                    year='$year',
                    semester='$semester'
                    WHERE id='$id' AND department_id='$dept_id'

                ");

                $msg = "Subject updated successfully.";
                $msg_type = "success";
            }
            /* INSERT */
            else {

                $chk = mysqli_query($conn,
                    "SELECT id FROM subjects WHERE subject_code='$subject_code' LIMIT 1"
                );

                if (mysqli_num_rows($chk) > 0) {
                    $msg = "Subject Code already exists.";
                    $msg_type = "warning";
                }
                else {
                    mysqli_query($conn, "
                       INSERT INTO subjects (subject_code, subject_name, year, semester, department_id)
                       VALUES ('$subject_code','$subject_name','$year','$semester','$dept_id')
                        ");

                    $msg = "Subject added successfully.";
                    $msg_type = "success";
                }
            }
        }
    }
}

/* FETCH SUBJECTS */
$list = mysqli_query($conn, "
    SELECT id, subject_code, subject_name, year, semester
    FROM subjects
    WHERE department_id='$dept_id'
    ORDER BY year, semester
");

?>

<!DOCTYPE html>
<html>
<head>
<title>Subjects</title>
<meta charset="UTF-8">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    margin: 0;
    background:#0f172a;
    font-family: system-ui;
}

/* ===== SIDEBAR FIX (IMPORTANT) ===== */
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
    font-size: 20px;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    color: #cbd5f5;
    text-decoration: none;
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #1e293b;
    color: #fff;
}

/* ===== CONTENT ===== */
.content {
    margin-left:260px;
    padding:40px;
}

.card-main {
    background:#fff;
    border-radius:16px;
    padding:30px;
    box-shadow:0 20px 40px rgba(0,0,0,0.4);
}
</style>

<script>
function updateSemester() {
    const year = document.getElementById("year").value;
    const sem = document.getElementById("semester");
    sem.innerHTML = '<option value="">-- Select Semester --</option>';

    let list = [];
    if (year == 1) list = [1,2];
    if (year == 2) list = [3,4];
    if (year == 3) list = [5,6];

    list.forEach(s => {
        let o = document.createElement("option");
        o.value = s;
        o.text = s;
        sem.appendChild(o);
    });
}
</script>
</head>

<body>

<?php include 'dept_sidebar.php'; ?>

<div class="content">
<div class="card-main">

<h4 class="mb-4">Subjects</h4>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
<?php endif; ?>

<form method="post" class="mb-4">

<?php if ($edit_mode): ?>
<input type="hidden" name="update_id" value="<?= $edit_id ?>">
<?php endif; ?>

<div class="mb-3">
<label class="form-label">Year</label>
<select name="year" id="year" class="form-select" onchange="updateSemester()" required>
<option value="">-- Select Year --</option>
<?php for ($i=1;$i<=3;$i++): ?>
<option value="<?= $i ?>" <?= ($edit_year==$i)?'selected':'' ?>>
<?= $i ?> Year
</option>
<?php endfor; ?>
</select>
</div>

<div class="mb-3">
<label class="form-label">Semester</label>
<select name="semester" id="semester" class="form-select" required>
<?php if ($edit_mode): ?>
<option value="<?= $edit_semester ?>" selected><?= $edit_semester ?></option>
<?php else: ?>
<option value="">-- Select Semester --</option>
<?php endif; ?>
</select>
</div>

<div class="mb-3">
<label class="form-label">Subject Code</label>
<input type="text" name="subject_code" class="form-control"
value="<?= $edit_code ?>" required
oninput="this.value=this.value.toUpperCase()">
</div>

<div class="mb-3">
<label class="form-label">Subject Name</label>
<input type="text" name="subject_name" class="form-control"
value="<?= $edit_name ?>" required
oninput="this.value=this.value.toUpperCase()">
</div>

<button class="btn btn-<?= $edit_mode?'warning':'primary' ?> w-100">
<?= $edit_mode ? 'Update Subject' : 'Add Subject' ?>
</button>

</form>

<table class="table table-bordered">
<thead class="table-light">
<tr>
<th>Subject Code</th>
<th>Subject Name</th>
<th>Year</th>
<th>Semester</th>
<th>Action</th>
</tr>
</thead>

<tbody>
<?php while ($r = mysqli_fetch_assoc($list)): ?>
<tr>
<td><?= $r['subject_code'] ?></td>
<td><?= $r['subject_name'] ?></td>
<td><?= $r['year'] ?></td>
<td><?= $r['semester'] ?></td>
<td>
<a href="?edit=<?= $r['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
<a href="?delete=<?= $r['id'] ?>"
   class="btn btn-danger btn-sm"
   onclick="return confirm('Delete this subject?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</div>

</body>
</html>
