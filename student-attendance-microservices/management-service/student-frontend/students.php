<?php
// students.php - List all students
session_start();
require_once 'api_client.php';

$apiClient = getApiClient();
$response = $apiClient->getAllStudents();
$students = [];

if ($response['data']['success']) {
    $students = $response['data']['data'];
} else {
    $_SESSION['error_message'] = $response['data']['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Student Management</h2>
        
        <?php displayFlashMessages(); ?>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <a href="add_student.php" class="btn btn-primary">Add New Student</a>
            </div>
            <div class="col-md-6">
                <form method="GET" action="search_students.php" class="d-flex">
                    <input type="text" name="query" class="form-control me-2" placeholder="Search students..." required>
                    <button type="submit" class="btn btn-outline-secondary">Search</button>
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Student ID</th>
                        <th>Class</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No students found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['class_id'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="view_student.php?id=<?php echo $student['id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// add_student.php - Add new student form and processing
session_start();
require_once 'api_client.php';

$apiClient = getApiClient();

if ($_POST) {
    $studentData = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'student_id' => $_POST['student_id'],
        'class_id' => $_POST['class_id'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address']
    ];
    
    $response = $apiClient->createStudent($studentData);
    
    if (handleApiResponse($response, 'students.php')) {
        // Success handled in function
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Add New Student</h2>
        
        <?php displayFlashMessages(); ?>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name*</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email*</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student ID*</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="class_id" class="form-label">Class ID</label>
                        <input type="text" class="form-control" id="class_id" name="class_id">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Add Student</button>
                <a href="students.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// edit_student.php - Edit student form and processing
session_start();
require_once 'api_client.php';

$apiClient = getApiClient();
$student = null;

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = 'Student ID is required';
    header('Location: students.php');
    exit();
}

$studentId = $_GET['id'];

// Fetch student data
$response = $apiClient->getStudent($studentId);
if ($response['data']['success']) {
    $student = $response['data']['data'];
} else {
    $_SESSION['error_message'] = $response['data']['message'];
    header('Location: students.php');
    exit();
}

// Handle form submission
if ($_POST) {
    $studentData = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'student_id' => $_POST['student_id'],
        'class_id' => $_POST['class_id'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address']
    ];
    
    $response = $apiClient->updateStudent($studentId, $studentData);
    
    if (handleApiResponse($response, 'students.php')) {
        // Success handled in function
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Student</h2>
        
        <?php displayFlashMessages(); ?>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name*</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email*</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student ID*</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="class_id" class="form-label">Class ID</label>
                        <input type="text" class="form-control" id="class_id" name="class_id" value="<?php echo htmlspecialchars($student['class_id'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Update Student</button>
                <a href="students.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// delete_student.php - Delete student processing
session_start();
require_once 'api_client.php';

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = 'Student ID is required';
    header('Location: students.php');
    exit();
}

$apiClient = getApiClient();
$studentId = $_GET['id'];

$response = $apiClient->deleteStudent($studentId);
handleApiResponse($response);

header('Location: students.php');
exit();
?>
