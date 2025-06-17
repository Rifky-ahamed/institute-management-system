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
  </style>
</head>
<body>

  <header>
    <h1>Institute Management System</h1>
    <p>View Class Schedule</p>
  </header>

  <div class="container">
    <h2>Weekly Class Schedule</h2>
    <table>
      <thead>
        <tr>
          <th>Class</th>
          <th>Year</th>
          <th>Subject</th>
          <th>Day</th>
          <th>Time</th>
          <th>Teacher</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sample Static Data (Replace with PHP if dynamic) -->
        <tr>
          <td>Class 10</td>
          <td>2025</td>
          <td>Mathematics</td>
          <td>Monday</td>
          <td>09:00 - 10:30</td>
          <td>Mr. John</td>
        </tr>
        <tr>
          <td>Class 10</td>
          <td>2025</td>
          <td>Science</td>
          <td>Wednesday</td>
          <td>10:45 - 12:15</td>
          <td>Ms. Sarah</td>
        </tr>
        <tr>
          <td>Class 11</td>
          <td>2025</td>
          <td>English</td>
          <td>Friday</td>
          <td>08:00 - 09:30</td>
          <td>Mr. Ahmed</td>
        </tr>
        <!-- You can loop your DB data here -->
      </tbody>
    </table>
  </div>

</body>
</html>
