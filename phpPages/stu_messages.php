<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

// Get current logged-in student's email
$student_email = $_SESSION['email'];

// Step 1: Get the student's institute_id
$stmt = $conn->prepare("SELECT institute_id FROM student WHERE email = ?");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$stmt->bind_result($institute_id);
$stmt->fetch();
$stmt->close();

?>