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
$teacher_email = $_SESSION['email'];$teacher_email = $_SESSION['email'];


// Step 1: Get teacher_code and institute_id
$stmt = $conn->prepare("SELECT teacher_code, institute_id FROM teachers WHERE email = ?");
$stmt->bind_param("s", $teacher_email);
$stmt->execute();
$stmt->bind_result($teacher_code, $institute_id);
$stmt->fetch();
$stmt->close();

// Step 2: Fetch teacher's schedule from schedule table
$stmt = $conn->prepare("
    SELECT s.day, s.start_time, s.end_time, s.subject, s.class, s.year, s.hallNo, c.description, sub.subject AS subject_name
    FROM schedule s
    JOIN classroom c ON s.hallNo = c.classroom AND s.institute_id = c.institute_id
    JOIN subjects sub ON s.subject = sub.id
    WHERE s.teacher_code = ? AND s.institute_id = ?
    ORDER BY FIELD(s.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time
");
$stmt->bind_param("si", $teacher_code, $institute_id);
$stmt->execute();
$result = $stmt->get_result();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Schedule</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ccc;
        }

        th {
            background-color: #34495e;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background: #2980b9;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #c0392b;
        }
    </style>
</head>
<body>

<header>
    <h1>Teacher Panel - Schedule</h1>
</header>

<div class="container">
    <h2>My Teaching Schedule</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Day</th>
                <th>Time</th>
                <th>Subject</th>
                <th>Class</th>
                <th>Hall No</th>
                <th>Hall Description</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['day']); ?></td>
                    <td><?php echo htmlspecialchars($row['start_time']) . " - " . htmlspecialchars($row['end_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['class']) . " - " . htmlspecialchars($row['year']); ?></td>
                    <td><?php echo htmlspecialchars($row['hallNo']); ?></td>
                   <td><?php echo htmlspecialchars($row['description']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p class="no-data">No schedule found for you at this time.</p>
    <?php endif; ?>

    <a href="teacherdashboard.php" class="back-btn">Back to Dashboard</a>
</div>

</body>
</html>
