<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

// Get current logged-in user's email
$user_email = $_SESSION['email'];
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'default';

$query = "
    SELECT COUNT(*) AS total_students 
    FROM student 
    INNER JOIN users ON student.institute_id = users.id 
    WHERE users.email = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalStudents = $row['total_students'];

$query = "
    SELECT COUNT(*) AS total_teachers 
    FROM teachers 
    INNER JOIN users ON teachers.institute_id = users.id 
    WHERE users.email = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalTeacers = $row['total_teachers'];

$query = "
    SELECT COUNT(*) AS total_classes 
    FROM class 
    INNER JOIN users ON class.institute_id = users.id 
    WHERE users.email = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalClasses = $row['total_classes'];

$query = "
    SELECT COUNT(*) AS total_subjects FROM subjects;
";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalSubjects = $row['total_subjects'];

$activities = [];
$actResult = $conn->query("SELECT activity FROM activity_log ORDER BY created_at DESC LIMIT 5");

if ($actResult && $actResult->num_rows > 0) {
    while ($actRow = $actResult->fetch_assoc()) {
        $activities[] = $actRow['activity'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head> 
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - Institute Class Management System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
<?php if (isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark'): ?>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #121212;
    color: #e0e0e0;
  }

  .sidebar {
    width: 200px;
    background: #1f1f1f;
    color: #fff;
    height: 100vh;
    position: fixed;
    padding: 20px;
  }

  .sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #00adb5;
  }

  .sidebar a {
    display: block;
    color: #ccc;
    text-decoration: none;
    margin: 15px 0;
    transition: padding-left 0.3s, background 0.3s;
  }

  .sidebar a:hover {
    background: #2a2a2a;
    padding-left: 10px;
    color: #00adb5;
  }

  .main {
    margin-left: 270px;
    padding: 20px;
  }

  .card, .chart-placeholder, .calendar, .log, .announcements {
    background: #1e1e1e;
    color: #ddd;
  }

  .card {
    box-shadow: 0 0 15px rgba(0,0,0,0.5);
  }

  ul li::before {
    color: #00ff99;
  }

<?php else: ?>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f4f4;
  }

  .sidebar {
    width: 200px;
    background: #2c3e50;
    color: #fff;
    height: 100vh;
    position: fixed;
    padding: 20px;
  }

  .sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
  }

  .sidebar a {
    display: block;
    color: #ecf0f1;
    text-decoration: none;
    margin: 15px 0;
  }

  .sidebar a:hover {
    background: #34495e;
    padding-left: 10px;
  }

  .main {
    margin-left: 270px;
    padding: 20px;
  }

  .card, .chart-placeholder, .calendar, .log, .announcements {
    background: #fff;
    color: #000;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
  }

  ul li::before {
    color: green;
  }

<?php endif; ?>

  .cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
  }

  .card {
    flex: 1 1 200px;
    padding: 20px;
    border-radius: 10px;
  }

  .section {
    margin-top: 40px;
  }

  .actions button {
    padding: 10px 15px;
    margin: 5px;
    background: #2980b9;
    color: #fff;
    border: none;
    border-radius: 5px;
  }

  .actions button:hover {
    background: #3498db;
  }

  ul {
    list-style: none;
    padding-left: 0;
  }
</style>

</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="#"><i class="fa fa-chart-line"></i> Dashboard</a>
    <a href="manageStudent.php"><i class="fa fa-user-graduate"></i> Manage Students</a>
    <a href="manageTeacher.php"><i class="fa fa-chalkboard-teacher"></i> Manage Teachers</a>
    <a href="Schedule.php"><i class="fa fa-calendar-alt"></i> Schedule</a>
    <a href="#"><i class="fa fa-check-square"></i> Attendance</a>
    <a href="#"><i class="fa fa-money-bill-wave"></i> Payments</a>
    <a href="#"><i class="fa fa-bell"></i> Notifications</a>
    <a href="setting.php"><i class="fa fa-cog"></i> Settings</a>
  </div>

  <div class="main">
    <h1>Welcome, Admin</h1>

    <div class="cards">
      <div class="card"><h3>Total Students</h3><p><?php echo $totalStudents; ?></p></div>
      <div class="card"><h3>Total teachers</h3><p><?php echo $totalTeacers; ?></p></div>
      <div class="card"><h3>Total Classes</h3><p><?php echo $totalClasses; ?></p></div>
      <div class="card"><h3>Total Subjects</h3><p><?php echo $totalSubjects; ?></p></div>
      <div class="card"><h3>Monthly Revenue</h3><p>$12,000.00</p></div>
    </div>

    <div class="section">
      <h3>Quick Actions</h3>
      <div class="actions">
        <button onclick="window.location.href='addStudent.php';">Add Student</button>
        <button onclick="window.location.href='addteacher.php';">Add Teacher</button>
        <button onclick="window.location.href='Schedule.php';">Update Timetable</button>
        
      </div>
    </div>

    <div class="section">
      <h3>Charts</h3>
      <div class="chart-placeholder">[Chart Area Placeholder: Use Chart.js or similar]</div>
    </div>

    <div class="section">
      <h3>Upcoming Events</h3>
      <div class="calendar">[Calendar Widget Placeholder]</div>
    </div>

    <div class="section">
  <h3>Recent Activities</h3>
  <div class="log">
    <ul>
      <?php foreach ($activities as $activity): ?>
        <li><?php echo htmlspecialchars($activity); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>


    <div class="section">
      <h3>Announcements</h3>
      <div class="announcements">
        <p><strong>Notice:</strong> Mid-term exams start next week. Timetables updated.</p>
      </div>
    </div>
  </div>
</body>
</html>
