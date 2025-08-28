<?php
// dashboard.php - Main dashboard
require_once 'config.php';
require_once 'api_client.php';
requireAuth();

$user = getCurrentUser();
$apiClient = getApiClient();

// Get basic statistics
$studentsResponse = $apiClient->getAllStudents();
$totalStudents = $studentsResponse['data']['success'] ? count($studentsResponse['data']['data']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?php echo APP_NAME; ?></a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.php">Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendance.php">Attendance</a>
                    </li>
                    <?php if (hasRole('admin')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">Admin</a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1>Dashboard</h1>
                <p class="text-muted">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</p>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="h4 mb-0"><?php echo $totalStudents; ?></div>
                                <div>Total Students</div>
                            </div>
                            <div class="h1">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a class="text-white" href="students.php">
                            View Details <i class="fas fa-angle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="h4 mb-0">--</div>
                                <div>Present Today</div>
                            </div>
                            <div class="h1">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a class="text-white" href="attendance.php">
                            View Details <i class="fas fa-angle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="h4 mb-0">--</div>
                                <div>Absent Today</div>
                            </div>
                            <div class="h1">
                                <i class="fas fa-times"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a class="text-white" href="attendance.php">
                            View Details <i class="fas fa-angle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="h4 mb-0">--</div>
                                <div>Total Classes</div>
                            </div>
                            <div class="h1">
                                <i class="fas fa-chalkboard"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a class="text-white" href="classes.php">
                            View Details <i class="fas fa-angle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="add_student.php" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus"></i> Add Student
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="attendance.php" class="btn btn-success w-100">
                                    <i class="fas fa-clipboard-check"></i> Mark Attendance
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="students.php" class="btn btn-info w-100">
                                    <i class="fas fa-search"></i> View Students
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="reports.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-chart-bar"></i> Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// logout.php - Logout handler
require_once 'config.php';
logout();
?>

