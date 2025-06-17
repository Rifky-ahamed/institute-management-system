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

if ($institute_result->num_rows > 0) {
    $institute_data = $institute_result->fetch_assoc();
    $institute_id = $institute_data['id'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Normalize: trim and convert to lowercase
        $classroom_number_raw = trim($_POST['classroom_number']);
        $classroom_number_normalized = strtolower($classroom_number_raw);
        $description = trim($_POST['description']);

        // **FIX HERE:** Define $classroom_number before use
        $classroom_number = $classroom_number_raw;

        // Step 2: Check if classroom already exists for the institute
        $check_stmt = $conn->prepare("SELECT * FROM classroom WHERE classroom = ? AND institute_id = ?");
        $check_stmt->bind_param("si", $classroom_number, $institute_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo "<script>alert('Classroom already exists under your institute.');</script>";
        } else {
            // Step 3: Insert classroom
            $insert_stmt = $conn->prepare("INSERT INTO classroom (classroom, description, institute_id) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("ssi", $classroom_number, $description, $institute_id);
            if ($insert_stmt->execute()) {
                echo "<script>alert('Classroom added successfully.'); window.location.href='#';</script>";
            } else {
                echo "<script>alert('Error adding classroom.');</script>";
            }
        }
    }
} else {
    echo "<script>alert('Institute not found.');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Classroom</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Add Classroom</h2>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <label for="classroom_number">Classroom Number</label>
        <input type="text" id="classroom_number" name="classroom_number"
       pattern="^Room\s\d+$"
       title="Please enter classroom as: Room 1, Room 10, Room 203 (Room + space + any digits)"
       placeholder="E.g., Room 1"
       required>


        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Enter a short description..."></textarea>

        <button type="submit">Add Classroom</button>
    </form>
</div>

</body>
</html>
