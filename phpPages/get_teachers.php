<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

$user_email = $_SESSION['email'];

// Get institute_id from users table
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

    // Use join to fetch teacher info from assigned subjects
    $stmt = $conn->prepare("
        SELECT t.teacher_code, t.name 
        FROM teachers t
        INNER JOIN assignsubjectstoteacher a ON t.teacher_code = a.teacher_code
        WHERE a.sub_id = ? AND a.institute_id = ?
    ");
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
