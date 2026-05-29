<?php
session_start();
require '../auth_check.php';
require '../config/db.php';

if ($_SESSION['role'] !== 'department') {
    die("Access Denied");
}

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_FILES['excel']) || $_FILES['excel']['error'] != 0) {
        die("Please select a valid Excel file.");
    }

    $file = $_FILES['excel']['tmp_name'];

    try {

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $dept_id = $_SESSION['user_id'];   // department id

        // Skip header row (row 0)
        for ($i = 1; $i < count($rows); $i++) {

            $staff_id       = strtoupper(trim($rows[$i][0] ?? ''));
            $name           = trim($rows[$i][1] ?? '');
            $email          = trim($rows[$i][2] ?? '');
            $phone          = trim($rows[$i][3] ?? '');
            $address        = trim($rows[$i][4] ?? '');
            $designation    = trim($rows[$i][5] ?? '');
            $bank_name      = trim($rows[$i][6] ?? '');
            $account_number = trim($rows[$i][7] ?? '');
            $branch_name    = trim($rows[$i][8] ?? '');
            $ifsc_code      = strtoupper(trim($rows[$i][9] ?? ''));

            // Basic validation
            if (!$staff_id || !$name || !$email || !$designation) {
                continue;
            }

            // Check duplicate staff_id
            $check = $conn->prepare("SELECT id FROM internal_staff WHERE staff_id = ?");
            $check->bind_param("s", $staff_id);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                continue; // skip duplicate
            }

            $stmt = $conn->prepare("
                INSERT INTO internal_staff
                (staff_id, department_id, name, email, phone, address, designation,
                 bank_name, account_number, branch_name, ifsc_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sisssssssss",
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
                $ifsc_code
            );

            $stmt->execute();
        }

        header("Location: dept_staff.php?uploaded=1");
        exit;


    } catch (Exception $e) {
        die("Error loading file: " . $e->getMessage());
    }
}
?>
