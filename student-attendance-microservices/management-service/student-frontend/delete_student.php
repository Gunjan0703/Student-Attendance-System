<?php
include 'config.php';
include 'auth.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: students.php?msg=Deleted successfully");
exit;
