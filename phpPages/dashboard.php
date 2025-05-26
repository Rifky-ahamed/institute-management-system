<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 
// Fetch total students
$result = $conn->query("SELECT COUNT(*) AS total_students FROM student");
$row = $result->fetch_assoc();
$totalStudents = $row['total_students'];

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
    body { margin: 0; font-family: Arial, sans-serif; background: #f4f4f4; }
    .sidebar { width: 200px; background: #2c3e50; color: #fff; height: 100vh; position: fixed; padding: 20px; }
    .sidebar h2 { text-align: center; margin-bottom: 30px; }
    .sidebar a { display: block; color: #ecf0f1; text-decoration: none; margin: 15px 0; }
    .sidebar a:hover { background: #34495e; padding-left: 10px; }
    .main { margin-left: 270px; padding: 20px; }
    .cards { display: flex; flex-wrap: wrap; gap: 20px; }
    .card { flex: 1 1 200px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .section { margin-top: 40px; }
    .section h3 { margin-bottom: 10px; }
    .actions button { padding: 10px 15px; margin: 5px; background: #2980b9; color: #fff; border: none; border-radius: 5px; }
    .actions button:hover { background: #3498db; }
    .chart-placeholder, .calendar, .log, .announcements { background: #fff; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
      ul {
    list-style: none;
    padding-left: 0;
  }
  ul li::before {
    content: "➤ ";
    color: green;
    font-weight: bold;
    margin-right: 8px;
  }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="#"><i class="fa fa-chart-line"></i> Dashboard</a>
    <a href="manageStudent.php"><i class="fa fa-user-graduate"></i> Manage Students</a>
    <a href="manageTeacher.php"><i class="fa fa-chalkboard-teacher"></i> Manage Teachers</a>
    <a href="#"><i class="fa fa-layer-group"></i> Manage Classes</a>
    <a href="#"><i class="fa fa-book"></i> Manage Subjects</a>
    <a href="#"><i class="fa fa-calendar-alt"></i> Schedule</a>
    <a href="#"><i class="fa fa-check-square"></i> Attendance</a>
    <a href="#"><i class="fa fa-money-bill-wave"></i> Payments</a>
    <a href="#"><i class="fa fa-bell"></i> Notifications</a>
    <a href="#"><i class="fa fa-cog"></i> Settings</a>
  </div>

  <div class="main">
    <h1>Welcome, Admin</h1>

    <div class="cards">
      <div class="card"><h3>Total Students</h3><p><?php echo $totalStudents; ?></p></div>
      <div class="card"><h3>Total Instructors</h3><p>20</p></div>
      <div class="card"><h3>Total Classes</h3><p>15</p></div>
      <div class="card"><h3>Total Subjects</h3><p>40</p></div>
      <div class="card"><h3>Monthly Revenue</h3><p>$12,000.00</p></div>
    </div>

    <div class="section">
      <h3>Quick Actions</h3>
      <div class="actions">
        <button>Add New Class</button>
        <button onclick="window.location.href='addStudent.php';">Add Student</button>
        <button onclick="window.location.href='manageTeacher.php';">Add Teacher</button>
        <button>Update Timetable</button>
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
