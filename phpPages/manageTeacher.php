<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}
include('db_connect.php');
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
      width: 120px;
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
 <label for="subject">subject</label>
    <input type="text" id="subject" value="<?php echo htmlspecialchars($_GET['subject'] ?? ''); ?>">

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
        <tr>
          <td>101</td>
          <td>John Doe</td>
          <td>john@example.com</td>
          <td>Mathematics</td>
          <td>+94 77 123 4567</td>
          <td>
             <button type="submit" name="delete_student" style="background-color:#FF6347; color:white; padding:6px 10px; border:none; border-radius:4px;">Delete</button>
          </td>
        </tr>
       
        
      </tbody>
    </table>
  </div>
</body>
</html>
