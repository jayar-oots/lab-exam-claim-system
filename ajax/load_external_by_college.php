<?php

require '../config/db.php';


$college = $_GET['college'] ?? '';

$res = $conn->query("SELECT name, designation 
    FROM external_staff 
    WHERE college_name='$college'
");

echo '<option value="">-- Select External Staff --</option>';
while($r=$res->fetch_assoc()){
echo '<option value="'.$r['name'].'">'.$r['name'].' ('.$r['designation'].')</option>';
}
