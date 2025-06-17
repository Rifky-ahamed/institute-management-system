<?php
session_start();
include('db_connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode([]);
    exit;
}

$user_email = $_SESSION['email'];
$class = $_POST['class'] ?? '';
$year = $_POST['year'] ?? '';
$subject_id = $_POST['subject'] ?? '';

if (!$class || !$year || !$subject_id) {
    echo json_encode([]);
    exit;
}

// Get institute_id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$institute_id = $row['id'] ?? 0;

if (!$institute_id) {
    echo json_encode([]);
    exit;
}

// Get class_id
$class_stmt = $conn->prepare("SELECT id FROM class WHERE class = ? AND year = ? AND institute_id = ?");
$class_stmt->bind_param("ssi", $class, $year, $institute_id);
$class_stmt->execute();
$class_result = $class_stmt->get_result();
$class_row = $class_result->fetch_assoc();
$class_id = $class_row['id'] ?? 0;

if (!$class_id) {
    echo json_encode([]);
    exit;
}

// Now fetch teachers
$teacher_stmt = $conn->prepare("
    SELECT teachers.name, teachers.teacher_code 
    FROM teachers 
    JOIN assignsubjectstoteacher ast ON ast.teacher_code = teachers.teacher_code 
    WHERE teachers.class_id = ? AND ast.sub_id = ? AND ast.institute_id = ?
");
$teacher_stmt->bind_param("iii", $class_id, $subject_id, $institute_id);
$teacher_stmt->execute();
$res = $teacher_stmt->get_result();

$teachers = [];
while ($row = $res->fetch_assoc()) {
    $teachers[] = ['name' => $row['name'], 'code' => $row['teacher_code']];
}

echo json_encode($teachers);
