<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

// Step 1: Get teacher_code, class_id, and institute_id
$teacher_email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT teacher_code, class_id, institute_id FROM teachers WHERE email = ?");
$stmt->bind_param("s", $teacher_email);
$stmt->execute();
$stmt->bind_result($teacher_code, $teacher_class_id, $institute_id);
$stmt->fetch();
$stmt->close();

// Step 2: Get class and year of teacher
$stmt = $conn->prepare("SELECT class, year FROM class WHERE id = ?");
$stmt->bind_param("i", $teacher_class_id);
$stmt->execute();
$stmt->bind_result($teacher_class, $teacher_year);
$stmt->fetch();
$stmt->close();

// Step 3: Fetch students under same class/year/institute and matching assigned subject
$stmt = $conn->prepare("
    SELECT 
        s.name AS student_name,
        s.email AS student_email,
        sub.subject AS subject_name,
        c.class,
        c.year
    FROM student s
    JOIN assignsubjects sa ON s.stupassword = sa.student_code
    JOIN assignsubjectstoteacher ta ON sa.sub_id = ta.sub_id
    JOIN subjects sub ON sa.sub_id = sub.id
    JOIN class c ON s.class_id = c.id
    WHERE s.institute_id = ? 
      AND c.class = ? 
      AND c.year = ?
      AND ta.teacher_code = ?
      AND ta.institute_id = ?
");
$stmt->bind_param("isssi", $institute_id, $teacher_class, $teacher_year, $teacher_code, $institute_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student List - Teacher Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f3;
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
            max-width: 1100px;
            margin: 30px auto;
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
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
            background-color: #2980b9;
        }

        .no-data {
            text-align: center;
            color: #c0392b;
            padding: 20px;
        }
    </style>
</head>
<body>

<header>
    <h1>Teacher Panel - Student List</h1>
</header>

<div class="container">
    <h2>All Students Assigned to Your Subjects</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>No</th>
                <th>Student Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Class</th>
                <th>Year</th>
            </tr>
            <?php 
            $no = 1;
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['student_email']) ?></td>
                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                    <td><?= htmlspecialchars($row['class']) ?></td>
                    <td><?= htmlspecialchars($row['year']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p class="no-data">No students assigned to your subjects found in your class.</p>
    <?php endif; ?>

    <a href="teacherdashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

</body>
</html>
