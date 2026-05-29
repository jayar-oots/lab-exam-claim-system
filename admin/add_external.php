<?php
require '../auth_check.php';
require '../config/db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$success = "";
$error = "";

/* ===== SAVE FORM DATA ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {

    $name         = trim($_POST['name']);
    $designation  = trim($_POST['designation']);
    $college      = trim($_POST['college_name']);
    $email        = trim($_POST['email']);
    $phone        = trim($_POST['phone']);
    $address      = trim($_POST['address']);
    $bank_account = trim($_POST['bank_account']);
    $bank_name    = trim($_POST['bank_name']);
    $branch       = trim($_POST['branch_name']);
    $ifsc         = trim($_POST['ifsc_code']);

    $stmt = $conn->prepare("
        INSERT INTO external_staff
        (name, designation, college_name, email, phone, address,
         bank_account, bank_name, branch_name, ifsc_code)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssssssss",
        $name, $designation, $college,
        $email, $phone, $address,
        $bank_account, $bank_name, $branch, $ifsc
    );

    if ($stmt->execute()) {
        $success = "External examiner added successfully.";
    } else {
        $error = "Database error. Please try again.";
    }
}

/* ===== EXCEL UPLOAD ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'excel') {

    if (empty($_FILES['excel_file']['name'])) {
        $error = "Please choose an Excel file.";
    } else {

        $filePath = $_FILES['excel_file']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $inserted = 0;

            // start from row 2 (skip header)
            for ($i = 1; $i < count($rows); $i++) {

                [
                    $name,
                    $designation,
                    $college,
                    $email,
                    $phone,
                    $address,
                    $bank_account,
                    $bank_name,
                    $branch,
                    $ifsc
                ] = $rows[$i];

                if (empty($name)) continue;

                $stmt = $conn->prepare("
                    INSERT INTO external_staff
                    (name, designation, college_name, email, phone, address,
                     bank_account, bank_name, branch_name, ifsc_code)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "ssssssssss",
                    $name, $designation, $college,
                    $email, $phone, $address,
                    $bank_account, $bank_name, $branch, $ifsc
                );

                if ($stmt->execute()) {
                    $inserted++;
                }
            }

            $success = "Excel import completed. Inserted: $inserted";

        } catch (Exception $e) {
            $error = "Excel error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add External Examiner</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">

<style>
body { background:#0f172a; }
.content { margin-left:260px; padding:40px; }
.card-main {
    background:#fff;
    border-radius:16px;
    padding:30px;
    max-width:700px;
    box-shadow:0 20px 40px rgba(0,0,0,.4);
}
</style>
</head>

<body>

<?php include 'admin_sidebar.php'; ?>

<div class="content">
<div class="card-main">

<h4 class="mb-4">Add External Examiner</h4>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="action" id="action">

<h6>Basic Details</h6>

<div class="mb-3">
<label>Name *</label>
<input type="text" name="name" class="form-control" required>
</div>

<div class="mb-3">
<label>Designation</label>
<select name="designation" class="form-select" required>
<option value="">-- Select --</option>
<option>Assistant Professor</option>
<option>Associate Professor</option>
</select>
</div>

<div class="mb-3">
<label>College *</label>
<input type="text" name="college_name" class="form-control" required>
</div>

<hr>

<h6>Contact</h6>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control"
pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" required>
</div>

<div class="mb-3">
<label>Phone</label>
<input type="text" name="phone" class="form-control"
pattern="[0-9]{10}" required>
</div>

<div class="mb-3">
<label>Address</label>
<textarea name="address" class="form-control" required></textarea>
</div>

<hr>

<h6>Bank Details</h6>

<div class="mb-3">
<input type="text" name="bank_account" class="form-control"
pattern="[0-9]{9,18}" placeholder="Bank Account" required>
</div>

<div class="mb-3">
<input type="text" name="bank_name" class="form-control"
pattern="[A-Za-z ]{3,50}" placeholder="Bank Name" required>
</div>

<div class="mb-3">
<input type="text" name="branch_name" class="form-control"
pattern="[A-Za-z .]{3,50}" placeholder="Branch Name" required>
</div>

<div class="mb-3">
<input type="text" name="ifsc_code" class="form-control"
pattern="[A-Z]{4}0[A-Z0-9]{6}" placeholder="IFSC Code" required>
</div>

<hr>

<h6>Upload via Excel</h6>
<input type="file" name="excel_file" class="form-control mb-3">

<div class="d-grid gap-2">
<button type="submit" class="btn btn-primary"
onclick="setAction('save')">
Save External (Form)
</button>

<button type="submit" class="btn btn-success"
onclick="setAction('excel')">
Upload Excel
</button>
</div>

</form>

</div>
</div>

<script>
function setAction(type){
    document.getElementById('action').value = type;

    if(type === 'excel'){
        document.querySelectorAll('[required]').forEach(el=>{
            el.removeAttribute('required');
        });
    }
}
</script>

</body>
</html>
