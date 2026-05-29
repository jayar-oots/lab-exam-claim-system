<?php
require '../auth_check.php';
require '../config/db.php';

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

/* Fetch External Colleges */
$collegeRes = $conn->query("
    SELECT DISTINCT college_name 
    FROM external_staff 
    WHERE status = 'active'
");

$msg = "";

/* Handle Form Submit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stream            = $_POST['stream'];
    $department        = $_POST['department'];
    $semester          = $_POST['semester'];
    $subject_code      = $_POST['subject_code'];
    $subject_name      = $_POST['subject_name'];
    $exam_date         = $_POST['exam_date'];
    $session           = $_POST['session'];
    $lab_no            = $_POST['lab_no'];
    $internal_staff_id = $_POST['internal_staff'];
    $external_staff_id = $_POST['external_staff'];
    $created_by        = $_SESSION['user_id'];  // ✅ important

    /* Subject Code Validation */
    if (!preg_match('/^[0-9]{2}[A-Z]{3}[0-9][A-Z]{2}$/', $subject_code)) {

        $msg = "Invalid Subject Code format (Eg: 23UCT1AA)";

    } else {

        /* ✅ CHECK IF EXAM ALREADY EXISTS */
        $check = $conn->prepare("
            SELECT id FROM exams
            WHERE subject_code = ?
            AND department = ?
            AND exam_date = ?
            AND session = ?
        ");

        $check->bind_param("siss", $subject_code, $department, $exam_date, $session);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {

            $msg = "Exam already created for this subject, date and session.";

        } else {

            /* ✅ INSERT ONLY IF NOT EXISTS */
            $stmt = $conn->prepare("
                INSERT INTO exams
                (stream, department, semester,
                 subject_code, subject_name,
                 exam_date, session, lab_no,
                 internal_staff_id, external_staff_id, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "siisssssiii",
                $stream,
                $department,
                $semester,
                $subject_code,
                $subject_name,
                $exam_date,
                $session,
                $lab_no,
                $internal_staff_id,
                $external_staff_id,
                $created_by
            );
try {

    $stmt->execute();
    $msg = "Exam created successfully.";

} catch (mysqli_sql_exception $e) {

    if ($e->getCode() == 1062) {
        $msg = "Exam already created for this Subject Code.";
    } else {
        $msg = "Something went wrong.";
    }

}
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Create Lab Exam</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<?php include 'admin_sidebar.php'; ?>

<div class="container py-4">
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card shadow">
<div class="card-body">

<h4>Create Lab Exam</h4>

<?php if ($msg): ?>
<div class="alert alert-info"><?= $msg ?></div>
<?php endif; ?>

<form method="post">

<!-- STREAM -->
<label class="form-label mt-3">Stream</label>
<select name="stream"
        id="stream"
        class="form-select"
        required
        onchange="loadDepartments(this.value); updateLabFromStream();">

    <option value="">-- Select Stream --</option>
    <option value="Computer">Computer</option>
    <option value="Science">Science</option>
</select>


<!-- DEPARTMENT -->
<label class="form-label mt-3">Department</label>
<select name="department" id="department"
        class="form-select" required
        onchange="loadInternalStaff();loadSubjects();"">

    <option value="">-- Select Department --</option>
</select>

<!-- SEMESTER -->
<label class="form-label mt-3">Semester</label>
<select id="semester" name="semester"
        class="form-select"
        onchange="loadSubjects()"
        required>
    <option value="">-- Select Semester --</option>
    <?php for($i=1;$i<=6;$i++): ?>
    <option value="<?= $i ?>">Semester <?= $i ?></option>
    <?php endfor; ?>
</select>

<!-- SUBJECT -->
<label class="form-label mt-3">Subject Code</label>
<select name="subject_code" id="subject_code"
        class="form-select"
        required
        onchange="fillSubjectName()">
    <option value="">-- Select --</option>
</select>

<label class="form-label mt-2">Subject Name</label>
<input type="text"
       name="subject_name"
       id="subject_name"
       class="form-control"
       required>

<!-- EXAM DATE -->
<label class="form-label mt-3">Exam Date</label>
<input type="date"
       name="exam_date"
       id="exam_date"
       class="form-control"
       required>

<!-- LAB -->
<label class="form-label mt-3">Lab</label>
<select name="lab_no" id="lab_no" class="form-select" required>
    <option value="">-- Select Lab --</option>
</select>

<!-- SESSION -->
<label class="form-label mt-3">Session</label>
<select name="session" class="form-select" required>
    <option value="">-- Select Session --</option>
    <option value="forenoon">Forenoon (9:30 AM – 12:30 PM)</option>
    <option value="afternoon">Afternoon (1:30 PM – 4:30 PM)</option>
    <option value="forenoon_afternoon">Forenoon & Afternoon</option>
</select>


<!-- INTERNAL -->
<label class="form-label mt-3">Internal Examiner</label>
<select name="internal_staff" id="internal_staff"
        class="form-select"
        required>
    <option value="">-- Select Internal Staff --</option>
</select>

<!-- EXTERNAL COLLEGE -->
<label class="form-label mt-3">College</label>
<select class="form-select"
        onchange="loadExternal(this.value)">
    <option value="">-- Select College --</option>
    <?php while($c = $collegeRes->fetch_assoc()): ?>
    <option value="<?= $c['college_name']; ?>">
        <?= $c['college_name']; ?>
    </option>
    <?php endwhile; ?>
</select>

<!-- EXTERNAL -->
<label class="form-label mt-3">External Examiner</label>
<select name="external_staff" id="external_staff"
        class="form-select"
        required>
    <option value="">-- Select External Staff --</option>
</select>

<button class="btn btn-primary w-100 mt-4">Save Exam</button>

</form>

</div>
</div>

</div>
</div>
</div>

<script>

/* Prevent past dates */
const today = new Date().toISOString().split('T')[0];
document.getElementById('exam_date').setAttribute('min', today);

/* ============================= */
/* LOAD DEPARTMENTS BY STREAM   */
/* ============================= */
function loadDepartments(stream) {

    const dept = document.getElementById('department');
    dept.innerHTML = '<option>Loading...</option>';

    if (!stream) {
        dept.innerHTML = '<option value="">-- Select Department --</option>';
        return;
    }

    fetch('../ajax/load_departments.php?stream=' + stream)
        .then(r => r.text())
        .then(data => {
            dept.innerHTML = data;
            document.getElementById('internal_staff').innerHTML =
                '<option value="">-- Select Internal Staff --</option>';
        });
}

/* ============================= */
/* LOAD LABS BY STREAM          */
/* ============================= */
function updateLabFromStream() {

    const stream = document.getElementById('stream').value;
    const lab = document.getElementById('lab_no');

    lab.innerHTML = '<option value="">-- Select Lab --</option>';

    if (stream === 'Computer') {
        for (let i = 1; i <= 8; i++) {
            lab.innerHTML += `<option value="Lab ${i}">Lab ${i}</option>`;
        }
    }

    if (stream === 'Science') {
        ['Physics Lab','Chemistry Lab','Botany Lab','Zoology Lab']
        .forEach(l => {
            lab.innerHTML += `<option value="${l}">${l}</option>`;
        });
    }
}

/* ============================= */
/* LOAD SUBJECTS BY SEMESTER    */
/* ============================= */
function loadSubjects() {

    const sem  = document.getElementById('semester').value;
    const dept = document.getElementById('department').value;
    const sub  = document.getElementById('subject_code');

    if (!sem || !dept) {
        sub.innerHTML = '<option value="">-- Select --</option>';
        return;
    }

    sub.innerHTML = '<option>Loading...</option>';

    fetch(`../ajax/load_subjects.php?sem=${sem}&dept=${dept}`)
        .then(r => r.json())
        .then(data => {

            sub.innerHTML = '<option value="">-- Select --</option>';

            data.forEach(s => {
                sub.innerHTML += `
                    <option value="${s.code}" data-name="${s.name}">
                        ${s.code}
                    </option>
                `;
            });

        });
}
function fillSubjectName() {

    const sel = document.getElementById('subject_code');
    const name = document.getElementById('subject_name');

    const selected = sel.options[sel.selectedIndex];

    name.value = selected ? selected.getAttribute('data-name') || '' : '';
}

/* ============================= */
/* LOAD INTERNAL STAFF          */
/* ============================= */
function loadInternalStaff() {

    const dept = document.getElementById('department').value;
    const staff = document.getElementById('internal_staff');

    if (!dept) {
        staff.innerHTML = '<option value="">-- Select Internal Staff --</option>';
        return;
    }

    staff.innerHTML = '<option>Loading...</option>';

    fetch('../ajax/load_internal_staff.php?dept_id=' + dept)
        .then(r => r.text())
        .then(data => staff.innerHTML = data);
}

/* ============================= */
/* LOAD EXTERNAL STAFF          */
/* ============================= */
function loadExternal(college){

    const ext = document.getElementById('external_staff');

    if (!college) {
        ext.innerHTML = '<option value="">-- Select External Staff --</option>';
        return;
    }

    ext.innerHTML = '<option>Loading...</option>';

    fetch('../ajax/load_external_staff.php?college='+college)
        .then(r=>r.text())
        .then(data=> ext.innerHTML = data);
}

</script>


</body>
</html>
