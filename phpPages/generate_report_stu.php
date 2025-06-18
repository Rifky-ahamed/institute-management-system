<?php
require('fpdf/fpdf.php');
include('db_connect.php');

session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

$user_email = $_SESSION['email'];
$selected_class_id = $_GET['class_id'] ?? '';

// Get institute_id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$institute = $result->fetch_assoc();
$institute_id = $institute['id'] ?? 0;
$stmt->close();

// Get class name if filtered
$class_name = '';
if (!empty($selected_class_id)) {
    $stmt = $conn->prepare("SELECT class FROM class WHERE id = ?");
    $stmt->bind_param("i", $selected_class_id);
    $stmt->execute();
    $class_result = $stmt->get_result();
    if ($row = $class_result->fetch_assoc()) {
        $class_name = $row['class'];
    }
    $stmt->close();
}

// Build student query
$query = "
    SELECT s.name, s.email, c.class AS class_name, s.phone
    FROM student s
    JOIN class c ON s.class_id = c.id
    WHERE s.institute_id = ?
";

if (!empty($selected_class_id)) {
    $query .= " AND s.class_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $institute_id, $selected_class_id);
} else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $institute_id);
}

$stmt->execute();
$students = $stmt->get_result();

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Header
$pdf->SetTextColor(33, 47, 61);
$header_text = 'Student Details Report';
if (!empty($class_name)) {
    $header_text .= ' - Class: ' . $class_name;
}
$pdf->Cell(0, 10, $header_text, 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(5);

// Table header
$pdf->SetFillColor(52, 152, 219); // Blue
$pdf->SetTextColor(255);
$pdf->SetDrawColor(44, 62, 80);
$pdf->SetLineWidth(0.3);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(10, 10, 'No', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Name', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Email', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Class', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Phone', 1, 1, 'C', true);

// Table rows
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0);
$fill = false;
$counter = 1;
while ($row = $students->fetch_assoc()) {
    $pdf->SetFillColor(230, 240, 255); // Light blue
    $pdf->Cell(10, 10, $counter++, 1, 0, 'C', $fill);
    $pdf->Cell(40, 10, $row['name'], 1, 0, 'L', $fill);
    $pdf->Cell(60, 10, $row['email'], 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, $row['class_name'], 1, 0, 'C', $fill);
    $pdf->Cell(40, 10, $row['phone'], 1, 1, 'L', $fill);
    $fill = !$fill; // Alternate row color
}

// Footer
$pdf->SetY(-15);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(128);
$pdf->Cell(0, 10, 'Page ' . $pdf->PageNo(), 0, 0, 'C');

// Output
$pdf_filename = 'Student_Report.pdf';
$pdf->Output('D', $pdf_filename); // Force download
