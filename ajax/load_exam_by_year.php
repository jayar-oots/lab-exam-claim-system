<?php
require '../auth_check.php';
require '../config/db.php';


$year = (int)$_GET['year'];

$res = $conn->query("
SELECT e.*
FROM exams e
LEFT JOIN claims c ON c.exam_id=e.id
WHERE e.year=$year
AND c.id IS NULL
");

if($res->num_rows==0){
    echo "<p>No exams available.</p>";
    exit;
}

while($e=$res->fetch_assoc()):
?>
<form method="post" action="submit_paper_count.php" class="border p-3 mb-3">
<b><?= $e['subject_code']." - ".$e['subject_name']; ?></b><br>
Date: <?= $e['exam_date']; ?><br>

<input type="hidden" name="exam_id" value="<?= $e['id']; ?>">

<div class="row mt-2">
<div class="col">
<label>FN Papers</label>
<input type="number" name="fn" class="form-control" required>
</div>
<div class="col">
<label>AN Papers</label>
<input type="number" name="an" class="form-control" required>
</div>
</div>

<button class="btn btn-success mt-3">Submit</button>
</form>
<?php endwhile; ?>
