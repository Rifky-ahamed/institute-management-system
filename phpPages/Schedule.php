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
        // If your class column stores values like "Class 09", uncomment the next line
        // $class = 'Class ' . $class_value;
        // Otherwise use directly
        $class = $class_value;
    }

    // Get class_id for conflict checking
    $class_id = null;
    $class_id_query = "SELECT id FROM class WHERE class = '$class' AND year = '$year' AND institute_id = '$institute_id' LIMIT 1";
    $class_id_result = mysqli_query($conn, $class_id_query);
    if ($class_id_result && mysqli_num_rows($class_id_result) > 0) {
        $class_id_row = mysqli_fetch_assoc($class_id_result);
        $class_id = $class_id_row['id'];
    } else {
        die("Error: Class ID not found for class '$class' and year '$year'.");
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

    $hall_no = $_POST['hall_no'];

    // Step 6: Check for hall conflict
    $conflict_query = "SELECT * FROM schedule 
        WHERE hallNo = '$hall_no' 
        AND day = '$day' 
        AND (
            (start_time <= '$start_time' AND end_time > '$start_time') OR
            (start_time < '$end_time' AND end_time >= '$end_time') OR
            (start_time >= '$start_time' AND end_time <= '$end_time')
        )";

    $conflict_result = mysqli_query($conn, $conflict_query);
    if (mysqli_num_rows($conflict_result) > 0) {
        echo "<script>alert('This classroom is not available at the selected time.'); window.history.back();</script>";
        exit();
    }

    // Step 6.2: Check for teacher time conflict
    $teacher_conflict_query = "SELECT * FROM schedule 
        WHERE teacher_name = (
            SELECT name FROM teachers WHERE teacher_code = '$teacher_id'
        )
        AND day = '$day'
        AND (
            (start_time <= '$start_time' AND end_time > '$start_time') OR
            (start_time < '$end_time' AND end_time >= '$end_time') OR
            (start_time >= '$start_time' AND end_time <= '$end_time')
        )";

    $teacher_conflict_result = mysqli_query($conn, $teacher_conflict_query);
    if (mysqli_num_rows($teacher_conflict_result) > 0) {
        echo "<script>alert('This teacher is already scheduled at the selected time.'); window.history.back();</script>";
        exit();
    }

    // Step 6.3: Check for class & year conflict
    $class_conflict_query = "SELECT * FROM schedule 
        WHERE class = (
            SELECT class FROM class WHERE id = '$class_id'
        )
        AND year = (
            SELECT year FROM class WHERE id = '$class_id'
        )
        AND day = '$day'
        AND (
            (start_time <= '$start_time' AND end_time > '$start_time') OR
            (start_time < '$end_time' AND end_time >= '$end_time') OR
            (start_time >= '$start_time' AND end_time <= '$end_time')
        )";

    $class_conflict_result = mysqli_query($conn, $class_conflict_query);
    if (mysqli_num_rows($class_conflict_result) > 0) {
        echo "<script>alert('This class already has a scheduled subject during the selected time.'); window.history.back();</script>";
        exit();
    }

    // Step 7: Insert into schedule
    $insert_query = "INSERT INTO schedule (class, year, subject, day, start_time, end_time, teacher_name, teacher_code, institute_id, hallNo)
                 VALUES ('$class', '$year', '$subject', '$day', '$start_time', '$end_time', '$teacher_name', '$teacher_id', '$institute_id', '$hall_no')";


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
  <?php if (isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark'): ?>
    /* Dark Mode CSS */
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #121212;
      color: #e0e0e0;
    }

    header {
      background-color: #1e40af;
      color: #e0e0e0;
      padding: 20px;
      text-align: center;
      border-bottom: 1px solid #333;
    }

    .container {
      max-width: 1000px;
      margin: 20px auto;
      background: #1f2937;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.8);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #f3f4f6;
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
      color: #d1d5db;
    }

    .form-row select,
    .form-row input {
      width: 300px;
      padding: 8px;
      border-radius: 5px;
      border: 1px solid #374151;
      background-color: #374151;
      color: #e5e7eb;
      box-sizing: border-box;
    }

    .form-row select option {
      background-color: #374151;
      color: #e5e7eb;
    }

    .form-row button {
      margin-left: 150px;
      padding: 10px 20px;
      background-color: #2563eb;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .form-row button:hover {
      background-color: #1d4ed8;
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
      background-color: #1f2937;
      color: #e5e7eb;
    }

    th, td {
      padding: 12px;
      border: 1px solid #374151;
      text-align: center;
    }

    th {
      background-color: #2563eb;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #374151;
    }

    @media (max-width: 768px) {
      .form-group {
        flex-direction: column;
      }
    }

  <?php else: ?>
    /* Light Mode CSS */
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f2f2f2;
      color: #000;
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
      color: #000;
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
      color: #000;
    }

    .form-row select,
    .form-row input {
      width: 300px;
      padding: 8px;
      border-radius: 5px;
      border: 1px solid #ccc;
      box-sizing: border-box;
      background-color: white;
      color: #000;
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
      background-color: white;
      color: #000;
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
  <?php endif; ?>
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
      // Filter by institute ID
      $class_query = "SELECT class, year FROM class WHERE institute_id = '$institute_id'";
      $class_result = mysqli_query($conn, $class_query);
      if ($class_result && mysqli_num_rows($class_result) > 0) {
          while ($row = mysqli_fetch_assoc($class_result)) {
              $class = $row['class'];
              $year = $row['year'];
              echo "<option value='{$class}|{$year}'> $class - $year</option>";
          }
      } else {
          echo "<option value=''>No classes available</option>";
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
  <label for="hall_no">Hall No</label>
  <select id="hall_no" name="hall_no" required>
    <option value="">Select Hall</option>
    <?php
      $hall_query = "SELECT classroom FROM classroom WHERE institute_id = '$institute_id'";
      $hall_result = mysqli_query($conn, $hall_query);
      if ($hall_result && mysqli_num_rows($hall_result) > 0) {
          while ($hall_row = mysqli_fetch_assoc($hall_result)) {
              echo "<option value='{$hall_row['classroom']}'>{$hall_row['classroom']}</option>";
          }
      } else {
          echo "<option value=''>No halls available</option>";
      }
    ?>
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
          <th>Hall No</th>
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
          $hall_no = $row['hallNo'];
            echo "<tr>
        <td>{$class_year}</td>
        <td>{$subject}</td>
        <td>{$day}</td>
        <td>{$hall_no}</td>
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
const subjectSelect = document.getElementById('subject');
const classYearSelect = document.getElementById('class_and_year');
const teacherSelect = document.getElementById('teacher');

function fetchTeachers() {
  const subjectId = subjectSelect.value;
  const classYear = classYearSelect.value;

  // Only fetch if both are selected
  if (subjectId !== "" && classYear !== "") {
    // Split class and year
    const [classValue, year] = classYear.split('|');

    // Build query params
    const params = new URLSearchParams({
      subject_id: subjectId,
      class: classValue,
      year: year
    });

    fetch('get_teachers.php?' + params.toString())
      .then(response => response.json())
      .then(data => {
        teacherSelect.innerHTML = '<option value="">Select Teacher</option>';
        data.forEach(teacher => {
          const option = document.createElement('option');
          option.value = teacher.id;
          option.textContent = `${teacher.name} (${teacher.id})`;
          teacherSelect.appendChild(option);
        });
      })
      .catch(error => {
        console.error('Error fetching teachers:', error);
      });
  } else {
    teacherSelect.innerHTML = '<option value="">Select Teacher</option>';
  }
}

// Add event listeners to both selects
subjectSelect.addEventListener('change', fetchTeachers);
classYearSelect.addEventListener('change', fetchTeachers);

</script>

</body>
</html>