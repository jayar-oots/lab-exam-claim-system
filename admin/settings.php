<?php
require '../auth_check.php';
require '../config/db.php';

/* ---------- SAVE RATE SETTINGS ---------- */
if (isset($_POST['save_rates'])) {

    $da     = $_POST['da'];
    $ta     = $_POST['ta'];
    $paper  = $_POST['paper'];
    $paper_setting_amount = $_POST['paper_setting_amount'];

    $stmt = $conn->prepare("
        UPDATE rate_settings 
        SET da_per_day = ?,
            ta_per_km = ?,
            rate_per_paper = ?,
            paper_setting_amount = ?
        WHERE id = 1
    ");
    $stmt->bind_param(
        "dddd",
        $da,
        $ta,
        $paper,
        $paper_setting_amount
    );
    $stmt->execute();
}

/* ---------- SAVE / UPDATE COLLEGE DISTANCE ---------- */
if (isset($_POST['save_distance'])) {

    $college = $_POST['college'];
    $km = (int)$_POST['km'];

    if ($km > 50) {
        die("Distance cannot exceed 50 KM");
    }

    $stmt = $conn->prepare("
        INSERT INTO college_distance (college_name, distance_km)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE distance_km = VALUES(distance_km)
    ");
    $stmt->bind_param("si", $college, $km);
    $stmt->execute();
}

/* ---------- LOAD DATA ---------- */
$rates = $conn->query(
    "SELECT * FROM rate_settings WHERE id = 1"
)->fetch_assoc();

$colleges = $conn->query("
    SELECT DISTINCT college_name 
    FROM external_staff 
    ORDER BY college_name
");

$distanceList = $conn->query("
    SELECT * FROM college_distance ORDER BY college_name
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Settings</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.content { margin-left: 260px; padding: 30px; }
.card { border-radius: 12px; }
</style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="content">

<!-- ================= RATE SETTINGS ================= -->
<div class="card p-4 mb-4">
    <h4 class="mb-3">Rate Settings</h4>

    <form method="post">

        <div class="mb-3">
            <label class="form-label">DA per Day (₹)</label>
            <input type="number" step="0.01" name="da"
                   class="form-control"
                   value="<?= $rates['da_per_day'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">TA per KM (₹)</label>
            <input type="number" step="0.01" name="ta"
                   class="form-control"
                   value="<?= $rates['ta_per_km'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Rate per Paper (₹)</label>
            <input type="number" step="0.01" name="paper"
                   class="form-control"
                   value="<?= $rates['rate_per_paper'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Question Paper Setting Amount (₹)</label>
            <input type="number" step="0.01"
                   name="paper_setting_amount"
                   class="form-control"
                   value="<?= $rates['paper_setting_amount'] ?>"
                   required>
        </div>

        <button name="save_rates" class="btn btn-primary w-100">
            Save Rate Settings
        </button>
    </form>
</div>

<!-- ================= COLLEGE DISTANCE SETTINGS ================= -->
<div class="card p-4 mb-4">
    <h4 class="mb-3">College Distance Settings</h4>

    <form method="post" class="row g-3">

        <div class="col-md-6">
            <label class="form-label">College</label>
            <select name="college" class="form-select" required>
                <option value="">-- Select College --</option>
                <?php while($c = $colleges->fetch_assoc()): ?>
                    <option value="<?= $c['college_name'] ?>">
                        <?= $c['college_name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Distance (KM) – max 50</label>
            <input type="number" name="km"
                   class="form-control"
                   min="1" max="50" required>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button name="save_distance" class="btn btn-success w-100">
                Add / Update
            </button>
        </div>

    </form>
</div>

<!-- ================= COLLEGE DISTANCE LIST ================= -->
<div class="card p-4">
    <h4 class="mb-3">College Distance List</h4>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>College Name</th>
                <th>Distance (KM)</th>
            </tr>
        </thead>
        <tbody>
        <?php while($d = $distanceList->fetch_assoc()): ?>
            <tr>
                <td><?= $d['college_name'] ?></td>
                <td><?= $d['distance_km'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>
