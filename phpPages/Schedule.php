<?php

session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 
// Get current logged-in user's email
$user_email = $_SESSION['email'];


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

    <form action="schedule_process.php" method="POST">
  <div class="form-row">
    <label for="class">Class</label>
    <select id="class" name="class" required>
      <option value="">Select Class</option>
     <option value="">-- Select Class --</option>
            <option value="class 09">class 09</option>
            <option value="class 10">class 10</option>
            <option value="class 11">class 11</option>
            <option value="class 12">class 12</option>
            <option value="class 13">class 13</option>
    </select>
  </div>

  <div class="form-row">
    <label for="year">Year</label>
    <input type="number" id="student_year" name="student_year" min="2024" max="2099" step="1" required>
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
          <th>Time</th>
          <th>Monday</th>
          <th>Tuesday</th>
          <th>Wednesday</th>
          <th>Thursday</th>
          <th>Friday</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>8:00 - 9:00</td>
          <td>Math</td>
          <td>Science</td>
          <td>English</td>
          <td>History</td>
          <td>ICT</td>
        </tr>
        <tr>
          <td>9:00 - 10:00</td>
          <td>Science</td>
          <td>Math</td>
          <td>ICT</td>
          <td>English</td>
          <td>History</td>
        </tr>
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