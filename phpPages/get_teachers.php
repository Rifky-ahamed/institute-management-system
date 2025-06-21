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
$stmt->close();

// Check required GET parameters
if (isset($_GET['subject_id'], $_GET['class'], $_GET['year'])) {
    $subject_id = intval($_GET['subject_id']);
    $class = $_GET['class'];
    $year = intval($_GET['year']);
    
    // Find class_id based on class, year, and institute_id
    $stmt = $conn->prepare("SELECT id FROM class WHERE class = ? AND year = ? AND institute_id = ?");
    $stmt->bind_param("sii", $class, $year, $institute_id);
    $stmt->execute();
    $stmt->bind_result($class_id);
    $stmt->fetch();
    $stmt->close();

    if (!$class_id) {
        // No matching class found
        echo json_encode([]);
        exit();
    }

    // Now fetch teachers assigned to this subject AND belong to this class_id
    $stmt = $conn->prepare("
        SELECT t.teacher_code, t.name 
        FROM teachers t
        INNER JOIN assignsubjectstoteacher a ON t.teacher_code = a.teacher_code
        WHERE a.sub_id = ? AND a.institute_id = ? AND t.class_id = ?
    ");
    $stmt->bind_param("iii", $subject_id, $institute_id, $class_id);
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
} else {
    // Missing parameters
    echo json_encode([]);
}
?>
