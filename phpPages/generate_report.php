<?php
require('fpdf/fpdf.php');
include('db_connect.php');

session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

$user_email = $_SESSION['email'];

// Fetch institute_id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$institute = $stmt->get_result()->fetch_assoc();
$institute_id = $institute['id'];

// Custom PDF class
class PDF extends FPDF {
    function headerTitle($title) {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(0, 0, 128);
        $this->Cell(190, 12, $title, 0, 1, 'C');
        $this->Ln(5);
    }

    function tableHeader($headers, $widths) {
        $this->SetFillColor(173, 216, 230); // Light blue
        $this->SetTextColor(0);
        $this->SetFont('Arial', 'B', 11);
        for ($i = 0; $i < count($headers); $i++) {
            $this->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0);
    }

    function tableRow($rowData, $widths, $fill = false) {
        for ($i = 0; $i < count($rowData); $i++) {
            $this->Cell($widths[$i], 8, $rowData[$i], 1, 0, 'C', $fill);
        }
        $this->Ln();
    }
}

$pdf = new PDF();

// 1. Schedule Details
$pdf->AddPage();
$pdf->headerTitle('1. Schedule Details');
$headers = ['Class', 'Year', 'Subject', 'Day', 'Time', 'Teacher', 'Hall'];
$widths = [20, 15, 30, 20, 30, 45, 30];
$pdf->tableHeader($headers, $widths);

$query = "SELECT class, year, subject, day, start_time, end_time, teacher_name, hallNo FROM schedule WHERE institute_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $institute_id);
$stmt->execute();
$result = $stmt->get_result();
$fill = false;
while ($row = $result->fetch_assoc()) {
    $time = "{$row['start_time']} - {$row['end_time']}";
    $data = [$row['class'], $row['year'], $row['subject'], $row['day'], $time, $row['teacher_name'], $row['hallNo']];
    $pdf->tableRow($data, $widths, $fill);
    $fill = !$fill;
}

// 2. Attendance Records
$pdf->AddPage();
$pdf->headerTitle('2. Attendance Records');
$headers = ['Date', 'Student Code - Name', 'Subject', 'Class', 'Year', 'Status'];
$widths = [30, 50, 30, 20, 20, 40];
$pdf->tableHeader($headers, $widths);

$query = "SELECT a.attendance_date, a.student_code, s.name AS student_name, a.subject, a.class, a.year, a.status
          FROM attendance a
          JOIN student s ON a.student_code = s.stupassword
          WHERE a.institute_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $institute_id);
$stmt->execute();
$result = $stmt->get_result();

$fill = false;
while ($row = $result->fetch_assoc()) {
    $student_display = "{$row['student_code']} - {$row['student_name']}";
    $data = [$row['attendance_date'], $student_display, $row['subject'], $row['class'], $row['year'], $row['status']];
    $pdf->tableRow($data, $widths, $fill);
    $fill = !$fill;
}


// 3. Payment Records
$pdf->AddPage();
$pdf->headerTitle('3. Payment Records');
$headers = ['Student Code - Name', 'Subject', 'Class', 'Year', 'Month', 'Amount', 'Status'];
$widths = [50, 25, 20, 15, 25, 30, 35];
$pdf->tableHeader($headers, $widths);

$query = "SELECT p.student_code, s.name AS student_name, p.subject, p.class, p.year, p.month, p.money, p.status
          FROM payment p
          JOIN student s ON p.student_code = s.stupassword
          WHERE p.institute_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $institute_id);
$stmt->execute();
$result = $stmt->get_result();

$fill = false;
while ($row = $result->fetch_assoc()) {
    $student_display = "{$row['student_code']} - {$row['student_name']}";
    $data = [$student_display, $row['subject'], $row['class'], $row['year'], $row['month'], "Rs. {$row['money']}", $row['status']];
    $pdf->tableRow($data, $widths, $fill);
    $fill = !$fill;
}


// 4. Classroom List
$pdf->AddPage();
$pdf->headerTitle('4. Classroom List');
$headers = ['Hall No', 'Description'];
$widths = [50, 140];
$pdf->tableHeader($headers, $widths);

$query = "SELECT classroom, description FROM classroom WHERE institute_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $institute_id);
$stmt->execute();
$result = $stmt->get_result();
$fill = false;
while ($row = $result->fetch_assoc()) {
    $data = [$row['classroom'], $row['description']];
    $pdf->tableRow($data, $widths, $fill);
    $fill = !$fill;
}

// 5. Assigned Subjects to Students (with name)
$pdf->AddPage();
$pdf->headerTitle('5. Assigned Subjects to Students');
$headers = ['Student Code - Name', 'Subject'];
$widths = [60, 130];
$pdf->tableHeader($headers, $widths);

$query = "SELECT a.student_code, s.name AS student_name, sub.subject
          FROM assignsubjects a
          JOIN student s ON a.student_code = s.stupassword
          JOIN subjects sub ON a.sub_id = sub.id
          WHERE a.institute_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $institute_id);
$stmt->execute();
$result = $stmt->get_result();

$fill = false;
while ($row = $result->fetch_assoc()) {
    $student_display = "{$row['student_code']} - {$row['student_name']}";
    $pdf->tableRow([$student_display, $row['subject']], $widths, $fill);
    $fill = !$fill;
}

// 6. Assigned Subjects to Teachers (with name)
$pdf->AddPage();
$pdf->headerTitle('6. Assigned Subjects to Teachers');
$headers = ['Teacher Code - Name', 'Subject'];
$widths = [60, 130];
$pdf->tableHeader($headers, $widths);

$query = "SELECT a.teacher_code, t.name AS teacher_name, s.subject 
          FROM assignsubjectstoteacher a 
          JOIN subjects s ON a.sub_id = s.id 
          JOIN teachers t ON a.teacher_code = t.teacher_code 
          WHERE a.institute_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $institute_id);
$stmt->execute();
$result = $stmt->get_result();
$fill = false;
while ($row = $result->fetch_assoc()) {
    $data = ["{$row['teacher_code']} - {$row['teacher_name']}", $row['subject']];
    $pdf->tableRow($data, $widths, $fill);
    $fill = !$fill;
}

// 7. Activity Log
$pdf->AddPage();
$pdf->headerTitle('7. Activity Log');
$headers = ['Date', 'Activity'];
$widths = [50, 140];
$pdf->tableHeader($headers, $widths);

$query = "SELECT activity, created_at FROM activity_log WHERE institute_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $institute_id);
$stmt->execute();
$result = $stmt->get_result();
$fill = false;
while ($row = $result->fetch_assoc()) {
    $data = [$row['created_at'], $row['activity']];
    $pdf->tableRow($data, $widths, $fill);
    $fill = !$fill;
}

// Force download
$pdf->Output('D', 'Institution_Report.pdf');

?>
