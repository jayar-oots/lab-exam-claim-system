<?php
require '../auth_check.php';
require '../config/db.php';

/* Success message after delete */
$deleted = isset($_GET['deleted']);

$q = $_GET['q'] ?? '';
$college = $_GET['college'] ?? '';

/* Build query */
$sql = "SELECT * FROM external_staff WHERE 1";

if ($q) {
    $sql .= " AND name LIKE '%$q%'";
}

if ($college) {
    $sql .= " AND college_name = '$college'";
}

$res = $conn->query($sql);

/* Colleges for dropdowns */
$colleges = $conn->query("SELECT DISTINCT college_name FROM external_staff");

include 'admin_sidebar.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>External Staff Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


<style>
body {
    background:#f1f5f9;
}
.card {
    border-radius:12px;
}
</style>
</head>

<body>

<div class="main-content">
<!--MAIN CONTENT-->

      <?php if($deleted): ?>
      <div class="alert alert-success">
        Staff deleted successfully.
      </div>
      <?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-body">

          <h4 class="mb-4">External Staff Details</h4>

          <!-- SEARCH & FILTER -->
          <form class="row g-3 mb-4" method="get">

            <div class="col-md-4">
              <input type="text"
                     name="q"
                     value="<?= htmlspecialchars($q) ?>"
                     class="form-control"
                     placeholder="Search Name">
            </div>

            <div class="col-md-3">
              <select name="college"
                      class="form-select"
                      onchange="this.form.submit()">
                <option value="">All Colleges</option>
                <?php
                $colleges->data_seek(0);
                while($c = $colleges->fetch_assoc()):
                ?>
                <option value="<?= $c['college_name'] ?>"
                  <?= ($college == $c['college_name']) ? 'selected' : '' ?>>
                  <?= $c['college_name'] ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-2">
              <button class="btn btn-primary w-100">Search</button>
            </div>

            <div class="col-md-2">
              <a href="external_staff_list.php"
                 class="btn btn-secondary w-100">Reset</a>
            </div>

          </form>

          <!-- BULK COLLEGE ENABLE / DISABLE -->
          <form method="post"
                action="external_staff_toggle.php"
                class="row g-3 mb-4">
            <input type="hidden" name="type" value="college">

            <div class="col-md-4">
              <select name="college" class="form-select" required>
                <option value="">-- Select College --</option>
                <?php
                $colleges->data_seek(0);
                while($c = $colleges->fetch_assoc()):
                ?>
                <option value="<?= $c['college_name'] ?>">
                  <?= $c['college_name'] ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
              <button name="action" value="disable"
                      class="btn btn-danger">
                Disable College
              </button>

              <button name="action" value="enable"
                      class="btn btn-success">
                Enable College
              </button>
            </div>
          </form>

          <!-- TABLE -->
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-dark">
                <tr>
                  <th>Name</th>
                  <th>College</th>
                  <th>Status</th>
                  <th style="width:220px">Actions</th>
                </tr>
              </thead>
              <tbody>

              <?php if($res->num_rows == 0): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted">
                    No records found
                  </td>
                </tr>
              <?php endif; ?>

              <?php while($r = $res->fetch_assoc()): ?>
                <tr>
                  <td><?= $r['name'] ?></td>
                  <td><?= $r['college_name'] ?></td>
                  <td>
                    <span class="badge bg-<?= $r['status']=='active'?'success':'danger' ?>">
                      <?= ucfirst($r['status']) ?>
                    </span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="external_staff_view.php?id=<?= $r['id'] ?>"
                         class="btn btn-info">View</a>

                      <a href="external_staff_edit.php?id=<?= $r['id'] ?>"
                         class="btn btn-warning">Edit</a>

                      <a href="external_staff_delete.php?id=<?= $r['id'] ?>"
                         class="btn btn-danger"
                         onclick="return confirm('Are you sure you want to delete this staff?')">
                         Delete
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>

              </tbody>
            </table>
          </div>

        </div>
      </div>
</div>

</body>
</html>
