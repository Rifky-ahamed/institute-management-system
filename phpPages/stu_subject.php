<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php');

// Get current student's email from session
$student_email = $_SESSION['email'];

// Fetch student_code, name, institute_id
$stmt = $conn->prepare("SELECT stupassword, name, institute_id FROM student WHERE email = ?");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$stmt->bind_result($student_code, $student_name, $institute_id);
$stmt->fetch();
$stmt->close();

// Fetch assigned subjects from assignsubjects → subjects
$subject_names = [];
$stmt2 = $conn->prepare("
    SELECT s.subject 
    FROM assignsubjects a
    JOIN subjects s ON a.sub_id = s.id
    WHERE a.student_code = ? AND a.institute_id = ?
");
$stmt2->bind_param("si", $student_code, $institute_id);
$stmt2->execute();
$result = $stmt2->get_result();

while ($row = $result->fetch_assoc()) {
    $subject_names[] = $row['subject'];
}
$stmt2->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Subjects</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #4a90e2;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        ul.subject-list {
            list-style-type: none;
            padding: 0;
        }

        ul.subject-list li {
            background-color: #e9f0fb;
            margin: 10px 0;
            padding: 15px;
            border-left: 6px solid #4a90e2;
            border-radius: 8px;
            font-size: 18px;
            color: #333;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background-color: #4a90e2;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #357abd;
        }
    </style>
</head>
<body>

<header>
    <h1>Welcome, <?php echo htmlspecialchars($student_name); ?></h1>
    <p>Your Assigned Subjects</p>
</header>

<div class="container">
    <h2>My Subjects</h2>

    <ul class="subject-list">
        <?php if (!empty($subject_names)): ?>
            <?php foreach ($subject_names as $subject): ?>
                <li><?php echo htmlspecialchars($subject); ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No subjects assigned.</li>
        <?php endif; ?>
    </ul>

    <a class="back-button" href="studentdashboard.php">← Back to Dashboard</a>
</div>

</body>
</html>
