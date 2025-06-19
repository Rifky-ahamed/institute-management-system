<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php');

// Step 1: Get current student's email
$student_email = $_SESSION['email'];

// Step 2: Get student class_id and institute_id
$stmt = $conn->prepare("SELECT class_id, name, institute_id FROM student WHERE email = ?");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$stmt->bind_result($class_id, $student_name, $institute_id);
$stmt->fetch();
$stmt->close();

// Step 3: Get class and year from class table
$stmt2 = $conn->prepare("SELECT class, year FROM class WHERE id = ? AND institute_id = ?");
$stmt2->bind_param("ii", $class_id, $institute_id);
$stmt2->execute();
$stmt2->bind_result($student_class, $student_year);
$stmt2->fetch();
$stmt2->close();

// Step 4: Fetch schedule matching class & year
// Step 4: Fetch schedule matching class & year and get subject name
$stmt3 = $conn->prepare("
    SELECT sch.day, sch.start_time, sch.end_time, sb.subject AS subject_name, 
           sch.teacher_name, sch.hallNo, cr.description
    FROM schedule sch
    JOIN subjects sb ON sch.subject = sb.id
    JOIN classroom cr ON sch.hallNo = cr.classroom AND cr.institute_id = sch.institute_id
    WHERE sch.class = ? AND sch.year = ? AND sch.institute_id = ?
    ORDER BY FIELD(sch.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), sch.start_time
");

$stmt3->bind_param("ssi", $student_class, $student_year, $institute_id);
$stmt3->execute();
$result = $stmt3->get_result();
$schedule_data = [];
while ($row = $result->fetch_assoc()) {
    $schedule_data[] = $row;
}
$stmt3->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Schedule</title>
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
            padding: 20px;
            text-align: center;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 14px;
            text-align: center;
            border: 1px solid #ccc;
        }

        th {
            background-color: #4a90e2;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f1f9ff;
        }

        tr:hover {
            background-color: #e0efff;
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
    <p>Your Class Schedule</p>
</header>

<div class="container">
    <h2>My Weekly Schedule</h2>

    <table>
        <thead>
            <tr>
                <th>Day</th>
                <th>Time</th>
                <th>Subject</th>
                <th>Teacher</th>
                <th>Hall No</th>
                <th>Hall Description</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($schedule_data)): ?>
                <?php foreach ($schedule_data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['day']); ?></td>
                        <td>
                            <?php
                                $start = date("g:i A", strtotime($row['start_time']));
                                $end = date("g:i A", strtotime($row['end_time']));
                                echo "$start - $end";
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['hallNo']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No schedule available for your class.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="text-align: center;">
        <a class="back-button" href="studentdashboard.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
