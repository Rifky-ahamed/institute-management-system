<?php
include('db_connect.php');
session_start();

$sub_id = $_GET['sub_id'] ?? '';
$institute_id = $_SESSION['institute_id'] ?? null;
$student_email = $_SESSION['email'] ?? '';

if (!$sub_id || !$institute_id || !$student_email) {
    echo "Invalid data";
    exit();
}

// Step 1: Get student class and year
$stmt = $conn->prepare("
    SELECT c.class, c.year
    FROM student s
    JOIN class c ON s.class_id = c.id
    WHERE s.email = ?
");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$stmt->bind_result($student_class, $student_year);
$stmt->fetch();
$stmt->close();

// Step 2: Find teacher for subject, class, year
$stmt2 = $conn->prepare("
    SELECT teacher_name, teacher_code 
    FROM schedule 
    WHERE subject = ? AND class = ? AND year = ? AND institute_id = ?
    LIMIT 1
");
$stmt2->bind_param("sssi", $sub_id, $student_class, $student_year, $institute_id);
$stmt2->execute();
$stmt2->bind_result($teacher_name, $teacher_code);
if ($stmt2->fetch()) {
    echo "$teacher_name - $teacher_code";
} else {
    echo "No teacher assigned";
}
$stmt2->close();
?>
