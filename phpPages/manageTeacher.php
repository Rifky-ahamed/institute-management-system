<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}
include('db_connect.php');

$user_email = $_SESSION['email'];

$query_user = "SELECT id FROM users WHERE email = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($row_user = $result_user->fetch_assoc()) {
    $institute_id = $row_user['id'];

    $search = $_GET['search'] ?? '';
    $class = $_GET['class'] ?? '';
    $year = $_GET['year'] ?? '';
    $subject = $_GET['subjects'] ?? '';

    $query = "
        SELECT teachers.*, subjects.subject, class.class AS class, class.year
        FROM teachers
        JOIN subjects ON teachers.subject_id = subjects.id
        JOIN class ON teachers.class_id = class.id
        WHERE teachers.institute_id = ?";

    $params = [$institute_id];
    $types = "i";

    if (!empty($search)) {
        $query .= " AND (teachers.name LIKE ? OR teachers.teacher_code LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }
    if (!empty($class)) {
        $query .= " AND class.class = ?";
        $params[] = $class;
        $types .= "s";
    }
    if (!empty($year)) {
        $query .= " AND class.year = ?";
        $params[] = (int)$year;
        $types .= "i";
    }
    if (!empty($subject)) {
        $query .= " AND subjects.subject_name = ?";
        $params[] = $subject;
        $types .= "s";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

} else {
    $result = false;
}

// Delete teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_teacher'])) {
    $teacher_id = $_POST['id'];

    $stmt_del = $conn->prepare("DELETE FROM teachers WHERE teacher_code = ? AND institute_id = ?");
    $stmt_del->bind_param("ii", $teacher_id, $institute_id);

    if ($stmt_del->execute()) {
        echo "<script>alert('Teacher deleted successfully'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting teacher');</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Teachers</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
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
      margin-left: 250px;
      padding: 20px;
    }

    h1 {
      color: #2c3e50;
    }

    .add-button {
      background: #2980b9;
      color: #fff;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      margin-bottom: 20px;
      cursor: pointer;
    }

    .add-button:hover {
      background: #3498db;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background: #2c3e50;
      color: #fff;
    }

    tr:hover {
      background: #f1f1f1;
    }


    .edit-btn {
      background-color: #27ae60;
      color: white;
    }

    .delete-btn {
      background-color: #c0392b;
      color: white;
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
    .search-filter {
      margin-bottom: 20px;
    }
    .search-filter input, .search-filter select {
      padding: 8px;
      margin-right: 10px;
    }
    .search-filter input{
      width: 205px;
    }
    #subject{
      width: 160px;
    }
    #student_year{
      width: 100px;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php"><i class="fa fa-chart-line"></i> Dashboard</a>
    <a href="manageStudent.php"><i class="fa fa-user-graduate"></i> Manage Students</a>
    <a href="#"><i class="fa fa-chalkboard-teacher"></i> Manage Teachers</a>
    <a href="#"><i class="fa fa-layer-group"></i> Manage Classes</a>
    <a href="#"><i class="fa fa-book"></i> Manage Subjects</a>
    <a href="#"><i class="fa fa-calendar-alt"></i> Schedule</a>
  </div>

  <div class="main">
    <h1>Manage Teachers</h1>
    <div class="actions">
    <button onclick="window.location.href='addteacher.php';">Add New teacher</button>
    <button onclick="window.location.href='teacLoginInform.php';">View teacher Login Information</button>
    <button onclick="window.location.href='edit-teacher.php';">Edit teacher Information</button>
  </div>
  <div class="search-filter">
  <form method="GET" action="">
    <input type="text" name="search" placeholder="Search by name or Teacher-code..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">

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

 <label for="subject">subject</label>
   <select name="subject" id="subject">
  <option value="">All Subjects</option>
  <option value="Mathematics" <?php if(($_GET['subject'] ?? '') == 'Mathematics') echo 'selected'; ?>>Mathematics</option>
  <option value="Science" <?php if(($_GET['subject'] ?? '') == 'Science') echo 'selected'; ?>>Science</option>
  <option value="English" <?php if(($_GET['subject'] ?? '') == 'English') echo 'selected'; ?>>English</option>
  <option value="History" <?php if(($_GET['subject'] ?? '') == 'Sinhala') echo 'selected'; ?>>Sinhala</option>
  <option value="Geography" <?php if(($_GET['subject'] ?? '') == 'Tamil') echo 'selected'; ?>>Tamil</option>
  <option value="ICT" <?php if(($_GET['subject'] ?? '') == 'Islam') echo 'selected'; ?>>Islam</option>
  <option value="Commerce" <?php if(($_GET['subject'] ?? '') == 'GEO') echo 'selected'; ?>>GEO</option>
  <option value="Biology" <?php if(($_GET['subject'] ?? '') == 'Civices') echo 'selected'; ?>>Civices</option>
  <option value="Physics" <?php if(($_GET['subject'] ?? '') == 'History') echo 'selected'; ?>>History</option>
</select>


    
    <button type="submit">Filter</button>
  </form>
</div>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Subject</th>
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
        <td><?php echo htmlspecialchars($row['subject']); ?></td>
        <td><?php echo htmlspecialchars($row['number']); ?></td>
        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?php echo $row['teacher_code']; ?>">
                <button type="submit" name="delete_teacher" class="delete-btn" onclick="return confirm('Are you sure you want to delete this teacher?');">Delete</button>
            </form>
        </td>
    </tr>
<?php
    endwhile;
else:
?>
    <tr>
        <td colspan="6" style="text-align: center;">No teachers found.</td>
    </tr>
<?php endif; ?>

</tbody>

    </table>
  </div>
</body>
</html>
