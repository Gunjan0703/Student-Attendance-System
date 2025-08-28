<?php
include 'config.php';
include 'auth.php';

$id = $_GET['id'] ?? null;
if (!$id) { die("Student ID required"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $roll = $_POST['roll'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE students SET name=?, roll=?, email=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $roll, $email, $id);
    $stmt->execute();
    header("Location: students.php?msg=Updated successfully");
    exit;
}

$result = $conn->query("SELECT * FROM students WHERE id=$id");
$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head><title>Edit Student</title></head>
<body>
    <h2>Edit Student</h2>
    <form method="POST">
        Name: <input type="text" name="name" value="<?= $student['name'] ?>" required><br>
        Roll: <input type="text" name="roll" value="<?= $student['roll'] ?>" required><br>
        Email: <input type="email" name="email" value="<?= $student['email'] ?>" required><br>
        <button type="submit">Update</button>
    </form>
</body>
</html>
