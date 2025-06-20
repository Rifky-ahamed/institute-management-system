<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php');

// Get current logged-in teacher's email
$teacher_email = $_SESSION['email'];

// Step 1: Get teacher_code and institute_id
$stmt = $conn->prepare("SELECT teacher_code, institute_id FROM teachers WHERE email = ?");
$stmt->bind_param("s", $teacher_email);
$stmt->execute();
$stmt->bind_result($teacher_code, $institute_id);
$stmt->fetch();
$stmt->close();

// Step 2: Check if teacher has any assigned subjects
$stmt = $conn->prepare("SELECT sub_id FROM assignsubjectstoteacher WHERE teacher_code = ? AND institute_id = ?");
$stmt->bind_param("si", $teacher_code, $institute_id);
$stmt->execute();
$result = $stmt->get_result();

$subject_ids = [];
while ($row = $result->fetch_assoc()) {
    $subject_ids[] = $row['sub_id'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Assigned Subjects</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef1f5;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin-top: 20px;
        }

        li {
            padding: 10px;
            background-color: #f6f8fa;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 6px;
            font-size: 16px;
        }

        .no-subjects {
            color: #c0392b;
            text-align: center;
            font-size: 18px;
            margin-top: 30px;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: #3498db;
            text-decoration: none;
        }

        .back-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<header>
    <h1>My Assigned Subjects</h1>
</header>

<div class="container">
    <h2>Subjects Assigned to You</h2>

    <?php if (empty($subject_ids)): ?>
        <p class="no-subjects">No subjects have been assigned to you.</p>
    <?php else: ?>
        <ul>
            <?php
            // Step 3: Get subject names from subjects table
            $placeholders = implode(',', array_fill(0, count($subject_ids), '?'));
            $types = str_repeat('i', count($subject_ids));
            $stmt = $conn->prepare("SELECT subject FROM subjects WHERE id IN ($placeholders)");

            $stmt->bind_param($types, ...$subject_ids);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['subject']) . "</li>";
            }

            $stmt->close();
            ?>
        </ul>
    <?php endif; ?>

    <a href="teacherdashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

</body>
</html>
