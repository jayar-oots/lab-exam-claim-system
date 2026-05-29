<?php
require '../auth_check.php';
require '../config/db.php';

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

$msg = "";

/* =========================
   DELETE USER
========================= */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    if ($id != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
        $msg = "User removed successfully.";
    }

    header("Location: create_user.php");
    exit;
}

/* =========================
   EDIT MODE LOAD
========================= */
$edit_mode   = false;
$edit_id     = "";
$edit_name   = "";
$edit_email  = "";
$edit_role   = "";
$edit_stream = "";

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE id='$edit_id'");
    if ($u = mysqli_fetch_assoc($res)) {
        $edit_mode   = true;
        $edit_name   = $u['name'];
        $edit_email  = $u['email'];
        $edit_role   = $u['role'];
        $edit_stream = $u['stream'] ?? '';
    }
}

/* =========================
   CREATE / UPDATE USER
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $role   = $_POST['role'];
    $stream = ($role === 'department') ? ($_POST['stream'] ?? null) : null;

    /* UPDATE USER */
    if (isset($_POST['update_id'])) {

        $id = (int)$_POST['update_id'];

        $stmt = $conn->prepare("
            UPDATE users SET name=?, email=?, role=?, stream=? WHERE id=?
        ");
        $stmt->bind_param("ssssi", $name, $email, $role, $stream, $id);
        $stmt->execute();

        $msg = "User updated successfully.";
    }

    /* CREATE USER */
    else {

        $default_password = "kasc";
        $pass = password_hash($default_password, PASSWORD_DEFAULT);

        $chk = $conn->prepare("SELECT id FROM users WHERE email=?");
        $chk->bind_param("s", $email);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $msg = "Email already exists.";
        } else {

            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password, role, stream)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssss", $name, $email, $pass, $role, $stream);
            $stmt->execute();

            $msg = "User created successfully. Default password is kasc.";
        }
    }
}

/* =========================
   FETCH USERS
========================= */
$users = mysqli_query($conn, "
    SELECT id, name, email, role, stream
    FROM users
    ORDER BY name
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Create User</title>
<meta charset="UTF-8">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#0f172a; }
.content { margin-left:260px; padding:40px; }
.card-box { border-radius:16px; }
</style>
</head>

<body>

<?php include 'admin_sidebar.php'; ?>

<div class="content">

<!-- CREATE / EDIT FORM -->
<div class="card card-box bg-white text-dark p-4 mx-auto" style="max-width:900px;">
<h4><?= $edit_mode ? "Edit User" : "Create New User" ?></h4>

<?php if ($msg): ?>
<div class="alert alert-info"><?= $msg ?></div>
<?php endif; ?>

<form method="post">

<?php if ($edit_mode): ?>
<input type="hidden" name="update_id" value="<?= $edit_id ?>">
<?php endif; ?>

<label class="form-label">Name</label>
<input type="text" name="name" class="form-control mb-3"
value="<?= htmlspecialchars($edit_name) ?>" required>

<label class="form-label">Email</label>
<input type="email" name="email" class="form-control mb-3"
value="<?= htmlspecialchars($edit_email) ?>" required>

<label class="form-label">Role</label>
<select name="role" id="roleSelect" class="form-select mb-3" required>
<option value="">-- Select Role --</option>
<option value="admin" <?= $edit_role=='admin'?'selected':'' ?>>Admin</option>
<option value="department" <?= $edit_role=='department'?'selected':'' ?>>Department</option>
</select>

<!-- STREAM FIELD -->
<label class="form-label">Stream</label>
<select name="stream" id="streamField"
        class="form-select mb-3"
        style="display:<?= ($edit_role=='department')?'block':'none' ?>;">
    <option value="">-- Select Stream --</option>
    <option value="Computer" <?= $edit_stream=='Computer'?'selected':'' ?>>Computer</option>
    <option value="Science" <?= $edit_stream=='Science'?'selected':'' ?>>Science</option>
</select>

<button class="btn btn-<?= $edit_mode ? 'warning' : 'primary' ?> w-100">
<?= $edit_mode ? 'Update User' : 'Create User' ?>
</button>

</form>
</div>

<!-- USERS LIST -->
<div class="card card-box bg-white text-dark p-4 mx-auto mt-4" style="max-width:900px;">

<h5 class="mb-3">Users List</h5>

<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Stream</th>
<th>Action</th>
</tr>
</thead>
<tbody>

<?php while ($u = mysqli_fetch_assoc($users)): ?>
<tr>
<td><?= htmlspecialchars($u['name']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= ucfirst($u['role']) ?></td>
<td><?= $u['role']=='department' ? htmlspecialchars($u['stream']) : '-' ?></td>
<td>
<a href="?edit=<?= $u['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
<a href="?delete=<?= $u['id'] ?>"
   onclick="return confirm('Remove this user?')"
   class="btn btn-danger btn-sm">Remove</a>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

</div>

<script>
const roleSelect = document.getElementById('roleSelect');
const streamField = document.getElementById('streamField');

roleSelect.addEventListener('change', function () {
    if (this.value === 'department') {
        streamField.style.display = 'block';
    } else {
        streamField.style.display = 'none';
        streamField.value = '';
    }
});
</script>

</body>
</html>
