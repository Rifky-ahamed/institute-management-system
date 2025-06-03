<?php

session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 
// Get current logged-in user's email
$user_email = $_SESSION['email'];

 // Step 2: Fetch institute ID from users table
    $institute_id = null;
    $institute_query = "SELECT id FROM users WHERE email = '$user_email' LIMIT 1";
    $institute_result = mysqli_query($conn, $institute_query);
    if ($institute_result && mysqli_num_rows($institute_result) > 0) {
        $row = mysqli_fetch_assoc($institute_result);
        $institute_id = $row['id'];
    } else {
        die("Error: Institute not found.");
    }
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    // Step 3: Extract class and year
    if (isset($_POST['class_and_year'])) {
        list($class_value, $year) = explode('|', $_POST['class_and_year']);
        $class = 'Class ' . $class_value;
    }

    // Step 4: Other fields
    $subject = $_POST['subject'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $teacher_id = $_POST['teacher'];

    // Step 5: Get teacher name
    $teacher_name = '';
    $teacher_query = "SELECT name FROM teachers WHERE teacher_code = '$teacher_id'";
    $teacher_result = mysqli_query($conn, $teacher_query);
    if ($teacher_result && mysqli_num_rows($teacher_result) > 0) {
        $teacher_row = mysqli_fetch_assoc($teacher_result);
        $teacher_name = $teacher_row['name'];
    }

    // Step 6: Insert into schedule
    $insert_query = "INSERT INTO schedule (class, year, subject, day, start_time, end_time, teacher_name, institute_id)
                     VALUES ('$class', '$year', '$subject', '$day', '$start_time', '$end_time', '$teacher_name', '$institute_id')";

    if (mysqli_query($conn, $insert_query)) {
        echo "<script>alert('Schedule added successfully!'); window.location.href='schedule.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Class Schedule - Institute Management</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f2f2f2;
    }

    header {
      background-color: #2d6cdf;
      color: white;
      padding: 20px;
      text-align: center;
    }

    .container {
      max-width: 1000px;
      margin: 20px auto;
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    form {
  margin-bottom: 30px;
}

.form-row {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.form-row label {
  width: 150px;
  font-weight: 500;
}

.form-row select,
.form-row input {
  width: 300px;
  padding: 8px;
  border-radius: 5px;
  border: 1px solid #ccc;
  box-sizing: border-box;
}

.form-row button {
  margin-left: 150px;
  padding: 10px 20px;
  background-color: #2d6cdf;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
    align-items: flex-start;
  }

  .form-row label {
    width: 100%;
    margin-bottom: 5px;
  }

  .form-row select,
  .form-row input,
  .form-row button {
    width: 100%;
    margin-left: 0;
  }
}

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: center;
    }

    th {
      background-color: #2d6cdf;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    @media (max-width: 768px) {
      .form-group {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1>Institute Management System</h1>
    <p>Class Schedule</p>
  </header>

  <div class="container">
    <h2>Add New Schedule Entry</h2>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
<div class="form-row">
  <label for="class_and_year">Class and Year</label>
  <select id="class_and_year" name="class_and_year" required>
    <option value="">-- Select Class and Year --</option>
    <?php
      $class_query = "SELECT class, year FROM class";
      $class_result = mysqli_query($conn, $class_query);
      if ($class_result && mysqli_num_rows($class_result) > 0) {
          while ($row = mysqli_fetch_assoc($class_result)) {
              $class = $row['class'];
              $year = $row['year'];
              echo "<option value='{$class}|{$year}'>Class $class - $year</option>";
          }
      }
    ?>
  </select>
</div>


  <div class="form-row">
    <label for="subject">Subject</label>
    <select id="subject" name="subject" required>
        <option value="">Select Subject</option>
        <option value="1">Mathematics</option>
        <option value="2">Science</option>
        <option value="3">English</option>
        <option value="4">Sinhala</option>
        <option value="5">Tamil</option>
        <option value="6">Islam</option>
        <option value="7">GEO</option>
        <option value="8">Civices</option>
        <option value="9">History</option>
    </select>
  </div>

  <div class="form-row">
    <label for="day">Day</label>
    <select id="day" name="day" required>
      <option value="">Select Day</option>
      <option>Monday</option>
      <option>Tuesday</option>
      <option>Wednesday</option>
      <option>Thursday</option>
      <option>Friday</option>
      <option>Saturday</option>
      <option>Sunday</option>
    </select>
  </div>

  <div class="form-row">
    <label for="start_time">Start Time</label>
    <input type="time" id="start_time" name="start_time" required>
  </div>

  <div class="form-row">
    <label for="end_time">End Time</label>
    <input type="time" id="end_time" name="end_time" required>
  </div>

  <div class="form-row">
    <label for="teacher">Teacher</label>
   <select id="teacher" name="teacher" required>
      <option value="">Select Teacher</option>
      <option>Mr. John</option>
      <option>Ms. Sarah</option>
      <option>Mr. Ahmed</option>
    </select>
  </div>

  <div class="form-row">
    <button type="submit">Add Schedule</button>
  </div>
</form>


    <h2>Weekly Class Schedule</h2>

    <table>
      <thead>
        <tr>
          <th>Class & Year</th>
          <th>Subject</th>
          <th>Day</th>
          <th>Time</th>
          <th>Teacher</th>
          
        </tr>
      </thead>
   <tbody>
  <?php
    // Subject ID to name mapping
    $subject_names = [
        1 => 'Mathematics',
        2 => 'Science',
        3 => 'English',
        4 => 'Sinhala',
        5 => 'Tamil',
        6 => 'Islam',
        7 => 'GEO',
        8 => 'Civics',
        9 => 'History'
    ];

    $schedule_query = "SELECT * FROM schedule WHERE institute_id = '$institute_id' ORDER BY day, start_time";
    $schedule_result = mysqli_query($conn, $schedule_query);

    if ($schedule_result && mysqli_num_rows($schedule_result) > 0) {
        while ($row = mysqli_fetch_assoc($schedule_result)) {
            $class_year = $row['class'] . ' - ' . $row['year'];

            $subject_id = $row['subject'];
            $subject = isset($subject_names[$subject_id]) ? $subject_names[$subject_id] : 'Unknown';

            $day = $row['day'];
            $time = date('H:i', strtotime($row['start_time'])) . ' to ' . date('H:i', strtotime($row['end_time']));
            $teacher = $row['teacher_name'];

            echo "<tr>
                    <td>{$class_year}</td>
                    <td>{$subject}</td>
                    <td>{$day}</td>
                    <td>{$time}</td>
                    <td>{$teacher}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No schedule entries found.</td></tr>";
    }
  ?>
</tbody>

    </table>
  </div>
<script>
  document.getElementById('subject').addEventListener('change', function () {
    const subjectId = this.value;
    const teacherSelect = document.getElementById('teacher');

    if (subjectId !== "") {
      fetch('get_teachers.php?subject_id=' + subjectId)
        .then(response => response.json())
        .then(data => {
          // Clear current options
          teacherSelect.innerHTML = '<option value="">Select Teacher</option>';
          
          data.forEach(teacher => {
            const option = document.createElement('option');
            option.value = teacher.id;
            option.textContent = teacher.name;
            teacherSelect.appendChild(option);
          });
        })
        .catch(error => {
          console.error('Error fetching teachers:', error);
        });
    } else {
      teacherSelect.innerHTML = '<option value="">Select Teacher</option>';
    }
  });
</script>

</body>
</html>