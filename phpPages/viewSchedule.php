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

// Step 1: Get institute_id using email from session
$institute_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$institute_stmt->bind_param("s", $user_email);
$institute_stmt->execute();
$institute_result = $institute_stmt->get_result();
$institute_row = $institute_result->fetch_assoc();
$institute_id = $institute_row['id'];

// Step 2: Fetch schedule data
$schedule_stmt = $conn->prepare("SELECT 
    schedule.class,
    schedule.year,
    subjects.subject AS subject_name,
    schedule.day,
    schedule.start_time,
    schedule.end_time,
    schedule.teacher_name,
    schedule.hallNo
FROM schedule
JOIN subjects ON schedule.subject = subjects.id
WHERE schedule.institute_id = ?
ORDER BY 
    FIELD(schedule.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'),
    schedule.start_time");

$schedule_stmt->bind_param("i", $institute_id);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Schedule</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
      color: #333;
    }

    header {
      background-color: #2d6cdf;
      color: white;
      padding: 20px;
      text-align: center;
    }

    .container {
      max-width: 1000px;
      margin: 30px auto;
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #2d6cdf;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 12px;
      border: 1px solid #ccc;
      text-align: center;
    }

    th {
      background-color: #2d6cdf;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    tr:hover {
      background-color: #f1f1f1;
    }
    .edit-btn {
  display: inline-block;
  margin-bottom: 20px;
  padding: 10px 20px;
  background-color: #2d6cdf;
  color: white;
  text-decoration: none;
  border-radius: 5px;
  font-weight: bold;
  transition: background-color 0.3s ease;
}

.edit-btn:hover {
  background-color: #1b4eb3;
}

  </style>
</head>
<body>

  <header>
    <h1>Institute Management System</h1>
    <p>View Class Schedule</p>
  </header>

  <div class="container">
    <h2>Weekly Class Schedule</h2>
  <a href="editSchedule.php" class="edit-btn">✏️ Edit Schedule</a>

    <table>
      <thead>
        <tr>
          <th>Class</th>
          <th>Year</th>
          <th>Subject</th>
          <th>Day</th>
          <th>Hall No</th>
          <th>Time</th>
          <th>Teacher</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($schedule_result->num_rows > 0): ?>
          <?php while ($row = $schedule_result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['class']); ?></td>
              <td><?php echo htmlspecialchars($row['year']); ?></td>
              <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
              <td><?php echo htmlspecialchars($row['day']); ?></td>
              <td><?php echo htmlspecialchars($row['hallNo']); ?></td>
              <td><?php echo date('H:i', strtotime($row['start_time'])) . " - " . date('H:i', strtotime($row['end_time'])); ?></td>
              <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">No schedule data available.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</body>
</html>
