<?php
require '../auth_check.php';
require '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = "";

/* ===============================
   FETCH EXISTING DATA
=================================*/
$stmt = $conn->prepare("SELECT * FROM external_staff WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Staff not found");
}

$staff = $result->fetch_assoc();

/* ===============================
   UPDATE LOGIC
=================================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name         = trim($_POST['name']);
    $designation  = trim($_POST['designation']);
    $email        = trim($_POST['email']);
    $phone        = trim($_POST['phone']);
    $bank_name    = trim($_POST['bank_name']);
    $bank_account = trim($_POST['bank_account']);
    $ifsc_code    = strtoupper(trim($_POST['ifsc_code']));

    $update = $conn->prepare("
        UPDATE external_staff 
        SET name=?, 
            designation=?, 
            email=?, 
            phone=?, 
            bank_name=?, 
            bank_account=?, 
            ifsc_code=? 
        WHERE id=?
    ");

    $update->bind_param(
        "sssssssi",
        $name,
        $designation,
        $email,
        $phone,
        $bank_name,
        $bank_account,
        $ifsc_code,
        $id
    );

    if ($update->execute()) {
        header("Location: external_staff_list.php?updated=1");
        exit;
    } else {
        $msg = "Update failed!";
    }
}

include 'admin_sidebar.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit External Staff</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script>
function confirmSave() {
    return confirm("Are you sure you want to save changes?");
}
</script>
</head>

<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-10 ms-auto p-4">

      <div class="card shadow-sm">
        <div class="card-body">

          <h4 class="mb-3">Edit External Staff</h4>

          <?php if($msg): ?>
            <div class="alert alert-danger"><?= $msg ?></div>
          <?php endif; ?>

          <form method="post" onsubmit="return confirmSave()">

            <div class="row g-3">

              <div class="col-md-6">
                <label>College</label>
                <input class="form-control"
                       value="<?= htmlspecialchars($staff['college_name']) ?>" readonly>
              </div>

              <div class="col-md-6">
                <label>Name</label>
                <input name="name" class="form-control"
                       value="<?= htmlspecialchars($staff['name']) ?>" required>
              </div>

              <div class="col-md-6">
                <label>Designation</label>
                <input name="designation" class="form-control"
                       value="<?= htmlspecialchars($staff['designation']) ?>">
              </div>

              <div class="col-md-6">
                <label>Email</label>
                <input name="email" class="form-control"
                       value="<?= htmlspecialchars($staff['email']) ?>">
              </div>

              <div class="col-md-6">
                <label>Phone</label>
                <input name="phone" class="form-control"
                       value="<?= htmlspecialchars($staff['phone']) ?>">
              </div>

              <div class="col-md-6">
                <label>Bank Name</label>
                <input name="bank_name" class="form-control"
                       value="<?= htmlspecialchars($staff['bank_name']) ?>">
              </div>

              <div class="col-md-6">
                <label>Account Number</label>
                <input name="bank_account" class="form-control"
                       value="<?= htmlspecialchars($staff['bank_account']) ?>">
              </div>

              <div class="col-md-6">
                <label>IFSC Code</label>
                <input name="ifsc_code" class="form-control"
                       value="<?= htmlspecialchars($staff['ifsc_code']) ?>">
              </div>

            </div>

            <div class="mt-4">
              <button class="btn btn-success">Save Changes</button>
              <a href="external_staff_list.php"
                 class="btn btn-secondary">Cancel</a>
            </div>

          </form>

        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>
