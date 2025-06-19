<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php');

// Get logged-in student's email
$student_email = $_SESSION['email'];

// Get student details
$stmt = $conn->prepare("
    SELECT s.name, s.email, s.phone, s.dob, u.name AS institute_name, c.class, c.year
    FROM student s
    JOIN users u ON s.institute_id = u.id
    JOIN class c ON s.class_id = c.id
    WHERE s.email = ?
");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $dob, $institute_name, $class, $year);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #4a90e2;
            color: white;
            padding: 20px 10px;
            text-align: center;
        }

        .profile-container {
            max-width: 700px;
            margin: 30px auto;
            background: white;
            padding: 25px 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .profile-details {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .label {
            width: 30%;
            font-weight: 600;
            color: #444;
            text-align: right;
            padding-right: 15px;
            font-size: 14px;
        }

        .value {
            width: 65%;
            background-color: #f1f9ff;
            padding: 8px 12px;
            border-radius: 5px;
            color: #333;
            font-size: 14px;
            word-break: break-word;
        }

        .back-button {
            text-align: center;
            margin-top: 25px;
        }

        .back-button a {
            text-decoration: none;
            background-color: #4a90e2;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .back-button a:hover {
            background-color: #357abd;
        }
    </style>
</head>
<body>

<header>
    <h1 style="font-size: 24px;">Welcome, <?php echo htmlspecialchars($name); ?></h1>
</header>

<div class="profile-container">
    <h2><?php echo htmlspecialchars($name); ?>'s Details</h2>

    <div class="profile-details">
        <div class="row">
            <div class="label">Name:</div>
            <div class="value"><?php echo htmlspecialchars($name); ?></div>
        </div>
        <div class="row">
            <div class="label">Email:</div>
            <div class="value"><?php echo htmlspecialchars($email); ?></div>
        </div>
        <div class="row">
            <div class="label">Phone:</div>
            <div class="value"><?php echo htmlspecialchars($phone); ?></div>
        </div>
        <div class="row">
            <div class="label">Date of Birth:</div>
            <div class="value"><?php echo htmlspecialchars($dob); ?></div>
        </div>
        <div class="row">
            <div class="label">Institute:</div>
            <div class="value"><?php echo htmlspecialchars($institute_name); ?></div>
        </div>
        <div class="row">
            <div class="label">Class:</div>
            <div class="value"><?php echo htmlspecialchars($class . " - " . $year); ?></div>
        </div>
    </div>

    <div class="back-button">
        <a href="studentdashboard.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
