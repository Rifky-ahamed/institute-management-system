<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

// Get current logged-in student's email
$student_email = $_SESSION['email'];

// Step 1: Get the student's institute_id
$stmt = $conn->prepare("SELECT institute_id FROM student WHERE email = ?");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$stmt->bind_result($institute_id);
$stmt->fetch();
$stmt->close();

// Step 2: Get the institute name from users table
$institute_name = "Your Institute"; // default fallback
if ($institute_id) {
    $stmt2 = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt2->bind_param("i", $institute_id);
    $stmt2->execute();
    $stmt2->bind_result($institute_name);
    $stmt2->fetch();
    $stmt2->close();
}

// Step 3: Get the student's name
$student_name = "Student"; // default fallback
$stmt3 = $conn->prepare("SELECT name FROM student WHERE email = ?");
$stmt3->bind_param("s", $student_email);
$stmt3->execute();
$stmt3->bind_result($student_name);
$stmt3->fetch();
$stmt3->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
        }

        header {
            background-color: #4a90e2;
            color: white;
            padding: 20px;
            text-align: center;
        }

        nav {
            background-color: #fff;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: center;
        }

        nav a {
            text-decoration: none;
            color: #333;
            margin: 0 20px;
            font-weight: bold;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #4a90e2;
        }

        .dashboard {
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: auto;
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card i {
            font-size: 40px;
            color: #4a90e2;
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 10px 0;
            color: #333;
        }

        footer {
            text-align: center;
            padding: 15px;
            background-color: #eee;
            color: #555;
            margin-top: 50px;
        }
    </style>
</head>
<body>

<header>
    <h1>Welcome, <?php echo htmlspecialchars($student_name); ?></h1>
    <p>Your personalized dashboard</p>
</header>

<nav>
    <a href="#"><i class="fa fa-home"></i> Home</a>
    <a href="#"><i class="fa fa-book"></i> Subjects</a>
    <a href="#"><i class="fa fa-calendar-alt"></i> Schedule</a>
    <a href="#"><i class="fa fa-user"></i> Profile</a>
    <a href="#"><i class="fa fa-sign-out-alt"></i> Logout</a>
</nav>

<div class="dashboard">
    <div class="card">
        <i class="fa fa-book"></i>
        <h3>My Subjects</h3>
        <p>View all assigned subjects</p>
    </div>
    <div class="card">
        <i class="fa fa-calendar-alt"></i>
        <h3>Class Schedule</h3>
        <p>Check your daily schedule</p>
    </div>
    <div class="card">
        <i class="fa fa-user"></i>
        <h3>Profile</h3>
        <p>Update your personal details</p>
    </div>
    <div class="card">
        <i class="fa fa-envelope"></i>
        <h3>Messages</h3>
        <p>Communicate with teachers</p>
    </div>
</div>

<footer>
    &copy; 2025 <?php echo htmlspecialchars($institute_name); ?>. All rights reserved.
</footer>


</body>
</html>
