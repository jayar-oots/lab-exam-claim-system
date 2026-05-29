<?php
require '../auth_check.php';
require '../config/db.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM external_staff WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();

include 'admin_sidebar.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>View External Staff</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-10 ms-auto p-4">

      <div class="card shadow-sm">
        <div class="card-body">

          <h4 class="mb-3">External Staff Details</h4>

          <table class="table table-bordered">
            <tr><th>Name</th><td><?= $staff['name'] ?></td></tr>
            <tr><th>College</th><td><?= $staff['college_name'] ?></td></tr>
            <tr><th>Designation</th><td><?= $staff['designation'] ?></td></tr>
            <tr><th>Email</th><td><?= $staff['email'] ?></td></tr>
            <tr><th>Phone</th><td><?= $staff['phone'] ?></td></tr>
            <tr><th>Bank Name</th><td><?= $staff['bank_name'] ?></td></tr>
            <tr><th>Account No</th><td><?= $staff['bank_account'] ?></td></tr>
            <tr><th>IFSC</th><td><?= $staff['ifsc_code'] ?></td></tr>
            <tr>
              <th>Status</th>
              <td>
                <span class="badge bg-<?= $staff['status']=='active'?'success':'danger' ?>">
                  <?= ucfirst($staff['status']) ?>
                </span>
              </td>
            </tr>
          </table>

          <a href="external_staff_list.php" class="btn btn-secondary">
            BackS
          </a>

        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>
