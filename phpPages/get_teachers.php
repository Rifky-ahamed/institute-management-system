<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

// Get current logged-in user's email
$user_email = $_SESSION['email'];

// Always fetch and update institute_id for the current logged-in user
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($institute_id);
$stmt->fetch();
$_SESSION['institute_id'] = $institute_id;
$stmt->close();

if (isset($_GET['subject_id']) && isset($_SESSION['institute_id'])) {
    $subject_id = intval($_GET['subject_id']);
    $institute_id = intval($_SESSION['institute_id']);

    $stmt = $conn->prepare("SELECT teacher_code, name FROM teachers WHERE subject_id = ? AND institute_id = ?");
    $stmt->bind_param("ii", $subject_id, $institute_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $teachers = [];

    while ($row = $result->fetch_assoc()) {
        $teachers[] = [
            'id' => $row['teacher_code'],
            'name' => $row['name']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($teachers);
}
?>
