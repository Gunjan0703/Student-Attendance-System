<?php
session_start();
require_once 'api_client.php';
requireAuth();

if (!isset($_GET['id'])) {
    header('Location: students.php');
    exit();
}

$apiClient = getApiClient();
$response = $apiClient->getStudent($_GET['id']);

if (!$response['data']['success']) {
    $_SESSION['error_message'] = $response['data']['message'];
    header('Location: students.php');
    exit();
}

$student = $response['data']['data'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - <?php echo htmlspecialchars($student['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Student Details</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Name:</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($student['name']); ?></dd>
                            
                            <dt class="col-sm-3">Email:</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($student['email']); ?></dd>
                            
                            <dt class="col-sm-3">Student ID:</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($student['student_id']); ?></dd>
                            
                            <dt class="col-sm-3">Class:</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($student['class_id'] ?? 'Not assigned'); ?></dd>
                            
                            <dt class="col-sm-3">Phone:</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($student['phone'] ?? 'Not provided'); ?></dd>
                            
                            <dt class="col-sm-3">Address:</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($student['address'] ?? 'Not provided'); ?></dd>
                            
                            <dt class="col-sm-3">Created:</dt>
                            <dd class="col-sm-9"><?php echo date('F j, Y g:i A', strtotime($student['created_at'])); ?></dd>
                        </dl>
                        
                        <div class="mt-4">
                            <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-warning">Edit Student</a>
                            <a href="students.php" class="btn btn-secondary">Back to List</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
