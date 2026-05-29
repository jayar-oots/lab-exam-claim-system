<?php
require '../auth_check.php';
require '../config/db.php';

if ($_SESSION['role'] !== 'department') {
    die("Access Denied");
}
$dept_id = $_SESSION['user_id'];

$msg = "";
$msg_type = "";

if (isset($_GET['uploaded'])) {
    $msg = "Excel file uploaded successfully.";
    $msg_type = "success";
}

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM internal_staff WHERE id=$id AND department_id=$dept_id");
    header("Location: dept_staff.php?deleted=1");
    exit;
}

/* ================= FETCH EDIT ================= */
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
   $res = $conn->query("SELECT * FROM internal_staff WHERE id=$id AND department_id=$dept_id");
    if ($res->num_rows > 0) {
        $edit_data = $res->fetch_assoc();
    }
}

/* ================= INSERT / UPDATE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $staff_id      = strtoupper(trim($_POST['staff_id'] ?? ''));
    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $address       = trim($_POST['address'] ?? '');
    $designation   = trim($_POST['designation'] ?? '');
    $bank_name     = trim($_POST['bank_name'] ?? '');
    $account_number= trim($_POST['account_number'] ?? '');
    $branch_name   = trim($_POST['branch_name'] ?? '');
    $ifsc_code     = strtoupper(trim($_POST['ifsc_code'] ?? ''));

    /* ===== VALIDATION ===== */
    if (!$staff_id || !$name || !$email || !$designation) {
        $msg = "Staff ID, Name, Email and Designation are required.";
        $msg_type = "danger";
    }
    elseif (!preg_match("/^[A-Z]{3}[0-9]{3}$/", $staff_id)) {
        $msg = "Staff ID format must be like TSU101";
        $msg_type = "danger";
    }
    elseif (!preg_match("/^[6-9][0-9]{9}$/", $phone)) {
        $msg = "Invalid phone number";
        $msg_type = "danger";
    }
    elseif (!preg_match("/^[0-9]{9,18}$/", $account_number)) {
        $msg = "Invalid account number";
        $msg_type = "danger";
    }
    elseif (!preg_match("/^[A-Z]{4}0[A-Z0-9]{6}$/", $ifsc_code)) {
        $msg = "Invalid IFSC code";
        $msg_type = "danger";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email address";
        $msg_type = "danger";
    }
    else {

        if (isset($_POST['update_id'])) {

            /* ===== UPDATE ===== */
            $id = (int)$_POST['update_id'];

            $stmt = $conn->prepare("
                UPDATE internal_staff SET
                staff_id=?, name=?, email=?, phone=?, address=?, designation=?,
                bank_name=?, account_number=?, branch_name=?, ifsc_code=?
                WHERE id=?
            ");

           
            $stmt->bind_param(
                "ssssssssssi",
                $staff_id, $name, $email, $phone, $address, $designation,
                $bank_name, $account_number, $branch_name, $ifsc_code,
                $id
            );

            $stmt->execute();
            header("Location: dept_staff.php?updated=1");
            exit;
                $msg = "staff  updated.";
             $msg_type = "success";

        } else {

            /* ===== CHECK UNIQUE STAFF ID ===== */
            $check = $conn->prepare("SELECT id FROM internal_staff WHERE staff_id=?");
            $check->bind_param("s", $staff_id);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $msg = "Staff ID already exists!";
                $msg_type = "danger";
            } else {

                $created_by = $_SESSION['user_id'] ?? 1;

                $stmt = $conn->prepare("
                   INSERT INTO internal_staff
                   (staff_id, department_id, name, email, phone, address, designation,
                   bank_name, account_number, branch_name, ifsc_code, created_by)
                   VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
                ");

                $msg = "staff added successfully.";
                $msg_type = "success";

                $stmt->bind_param(
    "sisssssssssi",
    $staff_id,
    $dept_id,
    $name,
    $email,
    $phone,
    $address,
    $designation,
    $bank_name,
    $account_number,
    $branch_name,
    $ifsc_code,
    $created_by
);


                $stmt->execute();
                header("Location: dept_staff.php?added=1");
                exit;
            }
        }
    }
}

/* ================= FETCH STAFF LIST ================= */
$staff_list = [];
$stmt = $conn->prepare("SELECT * FROM internal_staff WHERE department_id=? ORDER BY id DESC");
$stmt->bind_param("i", $dept_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $staff_list[] = $row;
}

$msg = "";
$msg_type = "";

if (isset($_GET['added'])) {
    $msg = "Staff added successfully.";
    $msg_type = "success";
}
if (isset($_GET['updated'])) {
    $msg = "Staff updated successfully.";
    $msg_type = "success";
}
if (isset($_GET['deleted'])) {
    $msg = "Staff deleted successfully.";
    $msg_type = "success";
}
?>





<!DOCTYPE html>
<html>
<head>
<title>Internal Staff</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#0f172a; font-family:system-ui; }
.content { margin-left:260px; padding:40px; }
.card-box {
    background:#fff;
    border-radius:16px;
    padding:30px;
    box-shadow:0 20px 40px rgba(0,0,0,.4);
}
</style>
</head>

<body>

<?php include 'dept_sidebar.php'; ?>

<div class="content">
<div class="card-box">

<h4 class="mb-4">Internal Staff</h4>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
<?php endif; ?>

<!-- ========================
     ADD / EDIT FORM
======================== -->
<form method="post" class="row g-3 mb-4">

<?php if ($edit_data): ?>
<input type="hidden" name="update_id" value="<?= $edit_data['id'] ?>">
<?php endif; ?>

<div class="col-md-6">
<label>Staff ID</label>
<input type="text" name="staff_id"
       class="form-control"
       pattern="^[A-Z]{3}[0-9]{3}$"
       title="Format: TSU101"
       oninput="this.value=this.value.toUpperCase()"
       value="<?= htmlspecialchars($edit_data['staff_id'] ?? '') ?>"
       required>
</div>

<div class="col-md-6">
<label>Name </label>
<input type="text" name="name" class="form-control"
value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>" required>
</div>

<div class="col-md-6">
<label>Email </label>
<input type="email" name="email" class="form-control" pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,}$"
value="<?= htmlspecialchars($edit_data['email'] ?? '') ?>" required>
</div>

<div class="col-md-6">
<label>Phone</label>
<input type="text" name="phone" class="form-control"pattern="[6-9][0-9]{9}" maxlength="10"
value="<?= htmlspecialchars($edit_data['phone'] ?? '') ?>"required>
</div>

<div class="col-md-6">
<label>Designation *</label>
<select name="designation" class="form-select" required>
<option value="">-- Select --</option>
<option <?= (($edit_data['designation'] ?? '')=="Assistant Professor")?'selected':'' ?>>Assistant Professor</option>
<option <?= (($edit_data['designation'] ?? '')=="Associate Professor")?'selected':'' ?>>Associate Professor</option>
</select>
</div>

<div class="col-md-12">
<label>Address</label>
<textarea name="address" class="form-control"required><?= htmlspecialchars($edit_data['address'] ?? '') ?></textarea>
</div>

<hr class="mt-4">

<h5>Bank Details</h5>

<div class="col-md-6">
<label>Bank Name</label>
<input type="text" name="bank_name" class="form-control"required
value="<?= htmlspecialchars($edit_data['bank_name'] ?? '') ?>">
</div>

<div class="col-md-6">
<label>Account Number</label>
<input type="text" name="account_number" class="form-control"pattern="[0-9]{9,18}"required
value="<?= htmlspecialchars($edit_data['account_number'] ?? '') ?>">
</div>

<div class="col-md-6">
<label>Branch Name</label>
<input type="text" name="branch_name" class="form-control"required
value="<?= htmlspecialchars($edit_data['branch_name'] ?? '') ?>">
</div>

<div class="col-md-6">
<label>IFSC Code</label>
<input type="text" name="ifsc_code" class="form-control"pattern="[A-Z]{4}0[A-Z0-9]{6}"
 oninput="this.value=this.value.toUpperCase()"required
value="<?= htmlspecialchars($edit_data['ifsc_code'] ?? '') ?>">
</div>

<div class="col-12 mt-3">
<button class="btn btn-<?= $edit_data ? 'warning':'primary' ?> w-100">
<?= $edit_data ? 'Update Staff' : 'Add Staff' ?>
</button>
</div>

</form>

<!-- ========================
     EXCEL UPLOAD
======================== -->
<hr>
<h5>Upload via Excel</h5>

<form action="upload_internal_excel.php" method="post" enctype="multipart/form-data" class="mb-4">
<input type="file" name="excel" required class="form-control mb-3">
<button class="btn btn-success w-100">Upload Excel</button>
</form>

<!-- ========================
     STAFF TABLE
======================== -->
<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>Staff ID</th>
<th>Name</th>
<th>Email</th>
<th>Designation</th>
<th>Action</th>
</tr>
</thead>

<tbody>
<?php foreach ($staff_list as $row): ?>
<tr>
<td><?= htmlspecialchars($row['staff_id'] ?? '') ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><?= htmlspecialchars($row['designation']) ?></td>

<td class="d-flex gap-2">

<button class="btn btn-info btn-sm text-white"
data-bs-toggle="modal"
data-bs-target="#viewModal<?= $row['id'] ?>">
View
</button>

<a href="?edit=<?= $row['id'] ?>"
class="btn btn-warning btn-sm">Edit</a>

<a href="?delete=<?= $row['id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this staff?')">Delete</a>

</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</div>

<!-- ========================
     VIEW MODALS
======================== -->
<?php foreach ($staff_list as $row): ?>
<div class="modal fade" id="viewModal<?= $row['id'] ?>" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Staff Details (ID: <?= $row['id'] ?>)</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<table class="table table-bordered">
<tr><th>Staff ID</th><td><?= htmlspecialchars($row['staff_id'] ?? '') ?></td></tr>
<tr><th>Name</th><td><?= htmlspecialchars($row['name']) ?></td></tr>
<tr><th>Email</th><td><?= htmlspecialchars($row['email']) ?></td></tr>
<tr><th>Phone</th><td><?= htmlspecialchars($row['phone']) ?></td></tr>
<tr><th>Address</th><td><?= htmlspecialchars($row['address']) ?></td></tr>
<tr><th>Designation</th><td><?= htmlspecialchars($row['designation']) ?></td></tr>

<tr class="table-secondary"><th colspan="2">Bank Details</th></tr>

<tr><th>Bank Name</th><td><?= htmlspecialchars($row['bank_name']) ?></td></tr>
<tr><th>Account Number</th><td><?= htmlspecialchars($row['account_number']) ?></td></tr>
<tr><th>Branch Name</th><td><?= htmlspecialchars($row['branch_name']) ?></td></tr>
<tr><th>IFSC Code</th><td><?= htmlspecialchars($row['ifsc_code']) ?></td></tr>

</table>

</div>
</div>
</div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 