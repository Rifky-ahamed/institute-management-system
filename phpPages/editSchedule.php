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

// Get institute_id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$institute_id = $row['id'];

// === HANDLE SCHEDULE UPDATE POST ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $class = $_POST['class'] ?? '';
    $year = $_POST['year'] ?? '';
    $subject_id = intval($_POST['subject'] ?? 0);
    $day = $_POST['day'] ?? '';
    $hallNo = $_POST['hallNo'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $teacher_name = $_POST['teacher_name'] ?? '';

    // Basic validation
    if (!$class || !$year || !$subject_id || !$day || !$hallNo || !$start_time || !$end_time || !$teacher_name) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } else {
        // === Step 1: Hall conflict check ===
        $conflict_sql = "SELECT * FROM schedule 
                         WHERE hallNo = ? AND day = ? AND id != ? 
                           AND start_time < ? AND end_time > ? 
                           AND institute_id = ?";
        $stmt_conflict = $conn->prepare($conflict_sql);
        $stmt_conflict->bind_param("ssissi", $hallNo, $day, $id, $end_time, $start_time, $institute_id);
        $stmt_conflict->execute();
        $conflict_result = $stmt_conflict->get_result();

        if ($conflict_result->num_rows > 0) {
            $_SESSION['error'] = "Schedule conflict detected in the selected hall and time.";
        } else {
            // === Step 2: Teacher time conflict ===
            $teacher_conflict_sql = "SELECT * FROM schedule 
                WHERE teacher_name = ? AND id != ? AND day = ? 
                AND (
                    (start_time <= ? AND end_time > ?) OR
                    (start_time < ? AND end_time >= ?) OR
                    (start_time >= ? AND end_time <= ?)
                ) AND institute_id = ?";
            $stmt_teacher = $conn->prepare($teacher_conflict_sql);
            $stmt_teacher->bind_param(
                "sisssssssi",
                $teacher_name, $id, $day,
                $start_time, $start_time,
                $end_time, $end_time,
                $start_time, $end_time,
                $institute_id
            );
            $stmt_teacher->execute();
            $teacher_conflict_result = $stmt_teacher->get_result();

            if ($teacher_conflict_result->num_rows > 0) {
                $_SESSION['error'] = "This teacher is already scheduled at the selected time.";
            } else {
                // === Step 3: Class & Year time conflict ===
                $class_conflict_sql = "SELECT * FROM schedule 
                    WHERE class = ? AND year = ? AND day = ? AND id != ?
                    AND (
                        (start_time <= ? AND end_time > ?) OR
                        (start_time < ? AND end_time >= ?) OR
                        (start_time >= ? AND end_time <= ?)
                    ) AND institute_id = ?";
                $stmt_class = $conn->prepare($class_conflict_sql);
                $stmt_class->bind_param(
                    "sisissssssi",
                    $class, $year, $day, $id,
                    $start_time, $start_time,
                    $end_time, $end_time,
                    $start_time, $end_time,
                    $institute_id
                );
                $stmt_class->execute();
                $class_conflict_result = $stmt_class->get_result();

                if ($class_conflict_result->num_rows > 0) {
                    $_SESSION['error'] = "This class already has a scheduled subject during the selected time.";
                } else {
                    // === Final Step: Update schedule ===
                    $update_sql = "UPDATE schedule SET class = ?, year = ?, subject = ?, day = ?, hallNo = ?, start_time = ?, end_time = ?, teacher_name = ? WHERE id = ? AND institute_id = ?";
                    $stmt_update = $conn->prepare($update_sql);
                    $stmt_update->bind_param(
                        "ssisssssii",
                        $class, $year, $subject_id, $day, $hallNo, $start_time, $end_time, $teacher_name, $id, $institute_id
                    );

                    if ($stmt_update->execute()) {
                        $_SESSION['success'] = "Schedule updated successfully.";
                        header("Location: editSchedule.php");
                        exit();
                    } else {
                        $_SESSION['error'] = "Failed to update schedule.";
                    }
                }
            }
        }
    }
}

// === END OF UPDATE HANDLER ===

// Fetch dropdown data
$class_query = $conn->prepare("SELECT id, class, year FROM class WHERE institute_id = ?");
$class_query->bind_param("i", $institute_id);
$class_query->execute();
$class_result = $class_query->get_result();
$class_options = [];
while ($row = $class_result->fetch_assoc()) {
    $class_options[] = $row;
}

$subject_query = $conn->prepare("SELECT id, subject FROM subjects");
$subject_query->execute();
$subject_result = $subject_query->get_result();
$subject_options = [];
while ($row = $subject_result->fetch_assoc()) {
    $subject_options[] = $row;
}

$hall_query = $conn->prepare("SELECT classroom FROM classroom WHERE institute_id = ?");
$hall_query->bind_param("i", $institute_id);
$hall_query->execute();
$hall_result = $hall_query->get_result();
$hall_options = [];
while ($row = $hall_result->fetch_assoc()) {
    $hall_options[] = $row['classroom'];
}

// Fetch current schedules
$schedule_query = "SELECT schedule.id, schedule.class, schedule.year, subjects.subject AS subject_name,
                  schedule.subject AS subject_id, schedule.day, schedule.start_time, schedule.end_time, 
                  schedule.teacher_name, schedule.hallNo
                  FROM schedule 
                  JOIN subjects ON schedule.subject = subjects.id 
                  WHERE schedule.institute_id = ?";
$stmt2 = $conn->prepare($schedule_query);
$stmt2->bind_param("i", $institute_id);
$stmt2->execute();
$schedule_result = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Schedule</title>
 <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    header {
      background-color: #2d6cdf;
      color: white;
      padding: 20px;
      text-align: center;
    }

    .container {
      max-width: 100%;
      padding: 20px;
      margin: 0 auto;
    }

    h2 {
      text-align: center;
      color: #2d6cdf;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      table-layout: fixed;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: center;
      vertical-align: middle;
      word-wrap: break-word;
    }

    th {
      background-color: #2d6cdf;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    input[type="time"],
    select {
      width: 100%;
      padding: 6px;
      font-size: 14px;
      border-radius: 4px;
      border: 1px solid #ccc;
      background-color: #fff;
    }

    .submit-btn {
      background-color: #2d6cdf;
      color: white;
      border: none;
      padding: 8px 14px;
      border-radius: 4px;
      cursor: pointer;
    }

    .submit-btn:hover {
      background-color: #1b4eb3;
    }

    @media (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      tr {
        margin-bottom: 15px;
      }

      td {
        text-align: left;
        padding-left: 40%;
        position: relative;
      }

      td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        top: 10px;
        font-weight: bold;
      }

      th {
        display: none;
      }
    }
  </style>
</head>
<body>

<header>
  <h1>Institute Management System</h1>
  <p>Edit Class Schedule</p>
</header>

<div class="container">
  <h2>Edit Schedule Entries</h2>

  <?php if (isset($_SESSION['error'])): ?>
    <p style="color: red;"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['success'])): ?>
    <p style="color: green;"><?= htmlspecialchars($_SESSION['success']) ?></p>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>Class</th>
        <th>Year</th>
        <th>Subject</th>
        <th>Day</th>
        <th>Hall No</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Teacher</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $schedule_result->fetch_assoc()): ?>
        <tr>
          <form method="POST" action="editSchedule.php">
            <!-- Class Dropdown -->
            <td>
              <select name="class" class="class-select">
                <?php foreach ($class_options as $opt): ?>
                  <option value="<?= htmlspecialchars($opt['class']) ?>" <?= ($opt['class'] == $row['class']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opt['class']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>

            <!-- Year Dropdown -->
            <td>
              <select name="year" class="year-select">
                <?php foreach ($class_options as $opt): ?>
                  <option value="<?= htmlspecialchars($opt['year']) ?>" <?= ($opt['year'] == $row['year']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opt['year']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>

            <!-- Subject Dropdown -->
            <td>
              <select name="subject" class="subject-select">
                <?php foreach ($subject_options as $sub): ?>
                  <option value="<?= $sub['id'] ?>" <?= ($sub['id'] == $row['subject_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sub['subject']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>

            <!-- Day Dropdown -->
            <td>
              <select name="day">
                <?php
                $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                foreach ($days as $day) {
                  $selected = $row['day'] === $day ? "selected" : "";
                  echo "<option value='$day' $selected>$day</option>";
                }
                ?>
              </select>
            </td>

            <!-- Hall No Dropdown -->
            <td>
              <select name="hallNo">
                <?php foreach ($hall_options as $hall): ?>
                  <option value="<?= htmlspecialchars($hall) ?>" <?= ($hall == $row['hallNo']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($hall) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>

            <!-- Time -->
            <td><input type="time" name="start_time" value="<?= htmlspecialchars($row['start_time']) ?>"></td>
            <td><input type="time" name="end_time" value="<?= htmlspecialchars($row['end_time']) ?>"></td>

            <!-- Teacher Dropdown -->
            <td>
              <select name="teacher_name" class="teacher-dropdown">
                <option value="<?= htmlspecialchars($row['teacher_name']) ?>"><?= htmlspecialchars($row['teacher_name']) ?></option>
              </select>
            </td>

            <td>
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" class="submit-btn">Update</button>
            </td>
          </form>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("tr").forEach(row => {
    const classSelect = row.querySelector(".class-select");
    const yearSelect = row.querySelector(".year-select");
    const subjectSelect = row.querySelector(".subject-select");
    const teacherDropdown = row.querySelector(".teacher-dropdown");

    if (!classSelect || !yearSelect || !subjectSelect || !teacherDropdown) return;

    const fetchTeachers = () => {
      const classVal = classSelect.value;
      const yearVal = yearSelect.value;
      const subjectVal = subjectSelect.value;

      if (classVal && yearVal && subjectVal) {
        fetch("get_teachers_to_edit.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: `class=${encodeURIComponent(classVal)}&year=${encodeURIComponent(yearVal)}&subject=${encodeURIComponent(subjectVal)}`
        })
        .then(res => res.json())
        .then(data => {
          teacherDropdown.innerHTML = "";

          if (data.length === 0) {
            teacherDropdown.innerHTML = `<option value="">No teacher found</option>`;
          } else {
            data.forEach(teacher => {
              teacherDropdown.innerHTML += `<option value="${teacher.name}">${teacher.name} (${teacher.code})</option>`;
            });
          }
        })
        .catch(error => {
          console.error("Fetch error:", error);
        });
      }
    };

    // Run once on page load
    fetchTeachers();

    classSelect.addEventListener("change", fetchTeachers);
    yearSelect.addEventListener("change", fetchTeachers);
    subjectSelect.addEventListener("change", fetchTeachers);
  });
});
</script>

</body>
</html>
