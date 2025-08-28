<?php
include 'config.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $roll = $_POST['roll'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("INSERT INTO students (name, roll, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $roll, $email);

    if ($stmt->execute()) {
        header("Location: students.php?msg=Student added successfully");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Add Student</title></head>
<body>
    <h2>Add Student</h2>
    <form method="POST">
        Name: <input type="text" name="name" required><br>
        Roll: <input type="text" name="roll" required><br>
        Email: <input type="email" name="email" required><br>
        <button type="submit">Add</button>
    </form>
</body>
</html>
