<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}
include('db_connect.php');

$user_email = $_SESSION['email'];
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'default';

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
    $subject = $_GET['subject'] ?? '';

    $query = "
        SELECT teachers.*, class.class AS class, class.year,
               GROUP_CONCAT(subjects.subject SEPARATOR ', ') AS subject
        FROM teachers
        JOIN class ON teachers.class_id = class.id
        LEFT JOIN assignsubjectstoteacher ON teachers.teacher_code = assignsubjectstoteacher.teacher_code
        LEFT JOIN subjects ON assignsubjectstoteacher.sub_id = subjects.id
        WHERE teachers.institute_id = ?
    ";

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
        $query .= " AND subjects.subject = ?";
        $params[] = $subject;
        $types .= "s";
    }

    // Group by teacher_code to avoid duplication
    $query .= " GROUP BY teachers.teacher_code";

    // Prepare and execute the final query
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        echo "Query prepare failed: " . $conn->error;
        $result = false;
    }
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
<?php if ($theme === 'dark'): ?>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #121212;
    color: #e0e0e0;
  }
  .sidebar {
    width: 200px;
    background-color: #1f1f2e;
    color: #ffffff;
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
    color: #dcdcdc;
    text-decoration: none;
    margin: 15px 0;
    transition: 0.3s ease;
  }
  .sidebar a:hover {
    background-color: #2c2c3c;
    padding-left: 10px;
    color: #00adb5;
  }
  .main {
    padding: 20px;
    margin-left: 250px;
  }
  h1 {
    color: #00adb5;
  }
  .add-button,
  .actions button,
  .search-filter button {
    background-color: #00adb5;
    color: #ffffff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    margin-bottom: 10px;
    cursor: pointer;
  }
  .add-button:hover,
  .actions button:hover,
  .search-filter button:hover {
    background-color: #03c3cc;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    background-color: #1e1e2f;
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
    border-radius: 10px;
    overflow: hidden;
  }
  th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #444;
  }
  th {
    background-color: #2c2c3c;
    color: #00adb5;
  }
  tr:hover {
    background-color: #2a2a3d;
  }
  .edit-btn {
    background-color: #4caf50;
    color: #fff;
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
  }
  .delete-btn {
    background-color: #e74c3c;
    color: #fff;
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
  }
  .search-filter {
    margin-bottom: 20px;
  }
  .search-filter input,
  .search-filter select {
    padding: 8px;
    margin-right: 10px;
    background-color: #2a2a3c;
    color: #f1f1f1;
    border: 1px solid #555;
    border-radius: 4px;
  }
<?php else: ?>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    color: #2c3e50;
  }
  .sidebar {
    width: 200px;
    background-color: #2c3e50;
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
    background-color: #34495e;
    padding-left: 10px;
  }
  .main {
    padding: 20px;
    margin-left: 250px;
  
  }
  h1 {
    color: #2c3e50;
  }
  .add-button,
  .actions button,
  .search-filter button {
    background-color: #2980b9;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    margin-bottom: 10px;
    cursor: pointer;
  }
  .add-button:hover,
  .actions button:hover,
  .search-filter button:hover {
    background-color: #3498db;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
  }
  th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }
  th {
    background-color: #2c3e50;
    color: #ffffff;
  }
  tr:hover {
    background-color: #f1f1f1;
  }
  .edit-btn {
    background-color: #27ae60;
    color: #fff;
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
  }
  .delete-btn {
    background-color: #c0392b;
    color: #fff;
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
  }
  .search-filter {
    margin-bottom: 20px;
  }
  .search-filter input,
  .search-filter select {
    padding: 8px;
    margin-right: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
<?php endif; ?>
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php"><i class="fa fa-chart-line"></i> Dashboard</a>
    <a href="manageStudent.php"><i class="fa fa-user-graduate"></i> Manage Students</a>
    <a href="#"><i class="fa fa-chalkboard-teacher"></i> Manage Teachers</a>
    <a href="Schedule.php"><i class="fa fa-calendar-alt"></i> Schedule</a>
  </div>

  <div class="main">
    <h1>Manage Teachers</h1>
    <div class="actions">
      <button onclick="window.location.href='addteacher.php';">Add New Teacher</button>
      <button onclick="window.location.href='teacLoginInform.php';">View Teacher Login Info</button>
      <button onclick="window.location.href='edit-teacher.php';">Edit Teacher Info</button>
      <button onclick="window.location.href='addTeacherToSubject.php';">Add The teacher To Subjects</button>
    </div>
    <div class="search-filter">
      <form method="GET">
        <input type="text" name="search" placeholder="Search by name or teacher code" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        <select name="class">
          <option value="">All Classes</option>
          <?php
            $classes = ["Class 09", "Class 10", "Class 11", "Class 12", "Class 13"];
            foreach ($classes as $cls) {
              $selected = ($_GET['class'] ?? '') === $cls ? "selected" : "";
              echo "<option value=\"$cls\" $selected>$cls</option>";
            }
          ?>
        </select>
        <input type="number" name="year" placeholder="Year" min="2020" max="2099" value="<?php echo htmlspecialchars($_GET['year'] ?? ''); ?>">
        <select name="subject">
          <option value="">All Subjects</option>
          <?php
            $subjects = ["Mathematics", "Science", "English", "Sinhala", "Tamil", "Islam", "GEO", "Civices", "History"];
            foreach ($subjects as $subj) {
              $selected = ($_GET['subject'] ?? '') === $subj ? "selected" : "";
              echo "<option value=\"$subj\" $selected>$subj</option>";
            }
          ?>
        </select>
        <button type="submit">Filter</button>
      </form>
    </div>

    <table>
      <thead>
        <tr>
          <th>NO</th>
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
          <td colspan="6" style="text-align:center;">No teachers found.</td>
        </tr>
<?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
