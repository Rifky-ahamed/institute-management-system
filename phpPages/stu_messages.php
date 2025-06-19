<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php');

// Get logged-in student's email
$student_email = $_SESSION['email'];

// Get student details
$stmt = $conn->prepare("
    SELECT s.stupassword, s.name, s.class_id, s.institute_id, c.class, c.year 
    FROM student s 
    JOIN class c ON s.class_id = c.id 
    WHERE s.email = ?
");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$stmt->bind_result($student_code, $student_name, $class_id, $institute_id, $class_name, $year);
$stmt->fetch();
$stmt->close();

// Save institute_id to session for later use
$_SESSION['institute_id'] = $institute_id;

// Get assigned subjects
$subjects = [];
$stmt = $conn->prepare("
    SELECT s.id, s.subject 
    FROM assignsubjects a 
    JOIN subjects s ON a.sub_id = s.id 
    WHERE a.student_code = ? AND a.institute_id = ?
");
$stmt->bind_param("si", $student_code, $institute_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    $message = trim($_POST['message']);
    $teacher_code = $_POST['teacher_code'];
    $teacher_name = $_POST['teacher_name'];
    $subject_id = $_POST['subject_id'];

    $class = $class_name;
    $msg_year = $year;

    $date = date('Y-m-d');
    $time = date('H:i:s');

    $insert = $conn->prepare("INSERT INTO messages 
        (stu_code, class, year, message, date, time, institute_id, teacher_code, teacher_name) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param(
        "sssssssss",
        $student_code,
        $class,
        $msg_year,
        $message,
        $date,
        $time,
        $institute_id,
        $teacher_code,
        $teacher_name
    );

    if ($insert->execute()) {
        echo "<script>alert('Message sent successfully!');</script>";
    } else {
        echo "<script>alert('Error sending message');</script>";
    }

    $insert->close();
}

// Fetch latest messages for the student
// Fetch the latest single message for the student
$message_list = [];

$stmt = $conn->prepare("SELECT message, date, time, teacher_name 
                        FROM messages 
                        WHERE stu_code = ? 
                        ORDER BY id DESC 
                        LIMIT 1");
$stmt->bind_param("s", $student_code);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $message_list[] = $row;
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Messages</title>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f6f8;
    }

    header {
        background-color: #4a90e2;
        color: white;
        padding: 20px;
        text-align: center;
    }

    .container {
        max-width: 600px;
        margin: 30px auto;
        background: white;
        border-radius: 10px;
        padding: 25px 30px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
        font-size: 20px;
    }

    label {
        font-weight: 600;
        color: #333;
        margin-top: 15px;
        display: block;
        font-size: 14px;
    }

    select, input[type="text"], textarea {
        width: 100%;
        padding: 10px 12px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
        margin-top: 5px;
        background-color: #f9f9f9;
        box-sizing: border-box;
    }

    .message-box {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 6px;
        margin-top: 20px;
        background-color: #f9f9f9;
    }

    .message {
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 8px;
        background-color: #e6f0ff;
        font-size: 14px;
    }

    .message.teacher {
        background-color: #d1f2eb;
    }

    .message-time {
        font-size: 12px;
        color: #888;
        text-align: right;
        margin-top: 5px;
    }

    form {
        display: flex;
        flex-direction: column;
        margin-top: 10px;
    }

    button {
        align-self: flex-end;
        background-color: #4a90e2;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-top: 10px;
    }

    button:hover {
        background-color: #357abd;
    }

    .back-button {
        text-align: center;
        margin-top: 25px;
    }

    .back-button a {
        text-decoration: none;
        background-color: #4a90e2;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
    }

    .back-button a:hover {
        background-color: #357abd;
    }
</style>



    <script>
        function fetchTeacherDetails(subjectId) {
            const teacherInput = document.getElementById("teacherInfo");
            teacherInput.value = "Loading...";

            const xhr = new XMLHttpRequest();
            xhr.open("GET", "get_teacher_info.php?sub_id=" + subjectId, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const [name, code] = xhr.responseText.split(' - ');
teacherInput.value = name + ' - ' + code;
document.getElementById("teacher_name").value = name;
document.getElementById("teacher_code").value = code;
document.getElementById("subject_id").value = subjectId;

                }
            };
            xhr.send();
        }
    </script>
</head>
<body>

<header>
    <h1>Messages</h1>
    <p>Chat with your teachers</p>
</header>

<div class="container">
    <h2>Message History</h2>

    <label for="subject">Select Subject:</label>
    <select id="subject" onchange="fetchTeacherDetails(this.value)">
        <option value="">-- Select Subject --</option>
        <?php foreach ($subjects as $sub): ?>
            <option value="<?= $sub['id']; ?>"><?= htmlspecialchars($sub['subject']); ?></option>
        <?php endforeach; ?>
    </select>

    <label for="teacherInfo">Teacher Info (Auto):</label>
    <input type="text" id="teacherInfo" placeholder="Teacher Name - Code" readonly>

 <div class="message-box">
    <?php if (empty($message_list)): ?>
        <div class="message">No messages sent yet.</div>
    <?php else: ?>
        <?php foreach ($message_list as $msg): ?>
            <div class="message student">
                <?= htmlspecialchars($msg['message']); ?>
                <div class="message-time">
                    You to <?= htmlspecialchars($msg['teacher_name']); ?> · <?= $msg['date']; ?> <?= $msg['time']; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


   <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
    <input type="hidden" name="teacher_code" id="teacher_code">
    <input type="hidden" name="teacher_name" id="teacher_name">
    <input type="hidden" name="subject_id" id="subject_id">

    <textarea name="message" placeholder="Type your message here..." required></textarea>
    <button type="submit" name="send">Send</button>
</form>


    <div class="back-button">
        <a href="studentdashboard.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
