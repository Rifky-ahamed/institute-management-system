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

// Step 1: Get the teacher's institute_id
$stmt = $conn->prepare("SELECT institute_id FROM teachers WHERE email = ?");
$stmt->bind_param("s", $teacher_email);
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

// Step 3: Get the teacher's name
$teacher_name = "teacher"; // default fallback
$stmt3 = $conn->prepare("SELECT name FROM teachers WHERE email = ?");
$stmt3->bind_param("s", $teacher_email);
$stmt3->execute();
$stmt3->bind_result($teacher_name);
$stmt3->fetch();
$stmt3->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
    /* General Reset & Fonts */
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f4f9;
        color: #333;
    }

    /* Header */
    header {
        background-color: #4a90e2;
        color: white;
        padding: 30px 20px;
        text-align: center;
    }

    header h1 {
        margin: 0;
        font-size: 28px;
    }

    header p {
        margin-top: 8px;
        font-size: 16px;
        font-weight: 400;
    }

    /* Navigation */
    nav {
        background-color: #fff;
        padding: 15px 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
    }

    nav a {
        text-decoration: none;
        color: #333;
        margin: 10px 15px;
        font-weight: 600;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: color 0.3s ease;
    }

    nav a:hover {
        color: #4a90e2;
    }

    /* Dashboard Grid */
    .dashboard {
        padding: 40px 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        max-width: 1200px;
        margin: auto;
    }

    .dashboard a {
        text-decoration: none;
        color: inherit;
    }

    /* Card Styling */
    .card {
        background-color: white;
        padding: 25px 20px;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        text-align: center;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        height: 100%;
    }

    .card:hover {
        transform: translateY(-6px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    }

    .card i {
        font-size: 44px;
        color: #4a90e2;
        margin-bottom: 15px;
    }

    .card h3 {
        margin: 10px 0 8px;
        font-size: 20px;
        font-weight: 600;
    }

    .card p {
        margin-top: 0;
        font-size: 14px;
        color: #555;
        line-height: 1.5;
    }

    /* Footer */
    footer {
        text-align: center;
        padding: 20px;
        background-color: #eee;
        color: #555;
        margin-top: 50px;
        font-size: 14px;
    }
</style>

</head>
<body>

<header>
    <h1>Welcome, <?php echo htmlspecialchars($teacher_name); ?></h1>
    <p>Your personalized teacher dashboard</p>
</header>

<nav>
    <a href="#"><i class="fa fa-home"></i> Home</a>
    <a href="teacher_subjects.php"><i class="fa fa-book-open"></i> Subjects</a>
    <a href="teacher_schedule.php"><i class="fa fa-calendar-alt"></i> Schedule</a>
    <a href="teacher_students.php"><i class="fa fa-users"></i> Students</a>
    <a href="teacher_profile.php"><i class="fa fa-user"></i> Profile</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')"><i class="fa fa-sign-out-alt"></i> Logout</a>
</nav>

<div class="dashboard">
    <a href="teacher_subjects.php">
        <div class="card">
            <i class="fa fa-book-open"></i>
            <h3>Assigned Subjects</h3>
            <p>View and manage your subjects</p>
        </div>
    </a>

    <a href="teacher_schedule.php">
        <div class="card">
            <i class="fa fa-calendar-alt"></i>
            <h3>Class Schedule</h3>
            <p>Check your teaching timetable</p>
        </div>
    </a>

    <a href="teacher_students.php">
        <div class="card">
            <i class="fa fa-users"></i>
            <h3>Student List</h3>
            <p>See students in your classes</p>
        </div>
    </a>

    <a href="teacher_messages.php">
        <div class="card">
            <i class="fa fa-envelope"></i>
            <h3>Messages</h3>
            <p>Communicate with students</p>
        </div>
    </a>
</div>

<footer>
    &copy; 2025 <?php echo htmlspecialchars($institute_name); ?>. All rights reserved.
</footer>

</body>
</html>
