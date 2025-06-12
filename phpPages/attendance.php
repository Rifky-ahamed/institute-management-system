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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance - Institute Class Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;   
            padding-top: 40px; 
        }

        .attendance-container {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 600px;
            max-width: 90%;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            display: none;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .status-options {
            display: flex;
            gap: 10px;
        }

        button {
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            display: none;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="attendance-container">
        <h2><i class="fas fa-user-check"></i> Student Attendance</h2>

        <!-- Class Dropdown -->
        <label for="classSelect">Select Class:</label>
        <select id="classSelect" name="class_id" onchange="redirectToAttendance()">
    <option value="">-- Select Class --</option>
    <?php
// Get current logged-in user ID (you already have this)
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

$current_year = date("Y"); // get current year, e.g. 2025

// Fetch only classes with the current year
$stmt = $conn->prepare("SELECT id, class, year FROM class WHERE institute_id = ? AND year = ?");
$stmt->bind_param("is", $user_id, $current_year);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $class_id = $row['id'];
    $class_name = htmlspecialchars($row['class']);
    $class_year = htmlspecialchars($row['year']);
    echo "<option value=\"$class_id\">$class_name - $class_year</option>";
}

$stmt->close();
?>

</select>
    </div>
    <script>
function redirectToAttendance() {
    const select = document.getElementById('classSelect');
    const classId = select.value;
    if (classId) {
        window.location.href = 'attendancemarking.php?class_id=' + encodeURIComponent(classId);
    }
}
</script>
</body>
</html>
