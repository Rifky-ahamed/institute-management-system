<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php');

// Get current logged-in user's email
$user_email = $_SESSION['email'];

// Fetch the user's id (institute_id) from users table using email
$query_user = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($query_user);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result_user = $stmt->get_result();

if ($row_user = $result_user->fetch_assoc()) {
    $institute_id = $row_user['id'];

    // Filtering inputs
    $search = $_GET['search'] ?? '';
    $class = $_GET['class'] ?? '';
    $year = $_GET['year'] ?? '';

    // Query with JOIN
    $query_students = "
        SELECT student.*, class.class AS class_name, class.year 
        FROM student 
        JOIN class ON student.class_id = class.id 
        WHERE student.institute_id = ?";
        
    $params = [$institute_id];
    $types = "i";

    if (!empty($search)) {
        $query_students .= " AND (student.name LIKE ? OR student.stupassword  LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }

    if (!empty($class)) {
        $query_students .= " AND class.class = ?";
        $params[] = $class;
        $types .= "s";
    }

    if (!empty($year)) {
        $query_students .= " AND class.year = ?";
        $params[] = (int)$year;
        $types .= "i";
    }

    $stmt_students = $conn->prepare($query_students);
    $stmt_students->bind_param($types, ...$params);
    $stmt_students->execute();
    $result = $stmt_students->get_result();

} else {
    $result = false;
}

// Delete student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_student'])) {
    $student_code = $_POST['student_code'];

    $stmt_del = $conn->prepare("DELETE FROM student WHERE student_code = ? AND institute_id = ?");
    $stmt_del->bind_param("si", $student_code, $institute_id);

    if ($stmt_del->execute()) {
        echo "<script>alert('Student deleted successfully'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting student');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Students - Admin Panel</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f4f4f4;
    }
    .container {
      padding: 20px;
      margin-left: 250px;
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
    h1 {
      margin-bottom: 20px;
    }
    .actions {
      margin-bottom: 20px;
    }
    .actions button {
      padding: 10px 15px;
      background: #2980b9;
      color: white;
      border: none;
      border-radius: 5px;
      margin-right: 10px;
    }
    .actions button:hover {
      background: #3498db;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
    }
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #2980b9;
      color: white;
    }
    tr:hover {
      background-color: #f1f1f1;
    }
    .search-filter {
      margin-bottom: 20px;
    }
    .search-filter input, .search-filter select {
      padding: 8px;
      margin-right: 10px;
    }
    .search-filter input {
      width: 205px;
    }
    #student_year {
      width: 100px;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="dashboard.php"><i class="fa fa-chart-line"></i> Dashboard</a>
  <a href="#"><i class="fa fa-user-graduate"></i> Manage Students</a>
  <a href="manageTeacher.php"><i class="fa fa-chalkboard-teacher"></i> Manage Teachers</a>
  <a href="Schedule.php"><i class="fa fa-calendar-alt"></i> Schedule</a>
</div>

<div class="container">
  <h1>Manage Students</h1>

  <div class="actions">
    <button onclick="window.location.href='addStudent.php';">Add New Student</button>
    <button onclick="window.location.href='stuLoginInform.php';">View Student Login Information</button>
    <button onclick="window.location.href='edit-student.php';">Edit Student Information</button>
  </div>

  <div class="search-filter">
    <form method="GET" action="">
      <input type="text" name="search" placeholder="Search by name or Student-code..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">

      <select name="class">
        <option value="">All Classes</option>
        <option value="Class 09" <?php if(($_GET['class'] ?? '') == 'Class 09') echo 'selected'; ?>>Class 09</option>
        <option value="Class 10" <?php if(($_GET['class'] ?? '') == 'Class 10') echo 'selected'; ?>>Class 10</option>
        <option value="Class 11" <?php if(($_GET['class'] ?? '') == 'Class 11') echo 'selected'; ?>>Class 11</option>
        <option value="Class 12" <?php if(($_GET['class'] ?? '') == 'Class 12') echo 'selected'; ?>>Class 12</option>
        <option value="Class 13" <?php if(($_GET['class'] ?? '') == 'Class 13') echo 'selected'; ?>>Class 13</option>
      </select>

      <label for="student_year">Year</label>
      <input type="number" id="student_year" name="year" min="2020" max="2099" step="1" value="<?php echo htmlspecialchars($_GET['year'] ?? ''); ?>">

      <button type="submit">Filter</button>
    </form>
  </div>

  <table>
    <thead>
      <tr>
        <th>NO</th>
        <th>Name</th>
        <th>Email</th>
        <th>Class</th>
        <th>Phone</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
<?php
$counter = 1;
if ($result && $result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
      <tr>
        <td><?php echo $counter++; ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo htmlspecialchars($row['class_name']); ?></td>
        <td><?php echo htmlspecialchars($row['phone']); ?></td>
        <td>
          <form method="POST" onsubmit="return confirm('Are you sure you want to delete this student?');">
            <input type="hidden" name="student_code" value="<?php echo $row['student_code']; ?>">
            <button type="submit" name="delete_student" style="background-color:#FF6347; color:white; padding:6px 10px; border:none; border-radius:4px;">Delete</button>
          </form>
        </td>
      </tr>
<?php
    endwhile;
else:
?>
    <tr><td colspan="6">No students found.</td></tr>
<?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>
