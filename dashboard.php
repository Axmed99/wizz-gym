<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Placeholder counts - These will be replaced with actual database queries later
$totalMembers = 0;
$totalTrainers = 0;
$totalPackages = 0;

// Get actual counts when database is ready
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'active'");
    $totalMembers = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM trainers WHERE status = 'active'");
    $totalTrainers = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM packages WHERE status = 'active'");
    $totalPackages = $stmt->fetchColumn();
} catch(PDOException $e) {
    // Silently handle the error as tables might not exist yet
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gym Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .icon-box {
            width: 65px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Gym Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                <p class="text-muted">Here's an overview of your gym management system</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Members Card -->
            <div class="col-md-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="bx bx-user bx-md"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="card-title mb-0">Total Members</h5>
                                <h2 class="mt-2 mb-0"><?php echo $totalMembers; ?></h2>
                            </div>
                        </div>
                        <a href="manage_members.php" class="btn btn-primary w-100">Manage Members</a>
                    </div>
                </div>
            </div>

            <!-- Trainers Card -->
            <div class="col-md-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-success bg-opacity-10 text-success">
                                <i class="bx bx-dumbbell bx-md"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="card-title mb-0">Total Trainers</h5>
                                <h2 class="mt-2 mb-0"><?php echo $totalTrainers; ?></h2>
                            </div>
                        </div>
                        <a href="manage_trainers.php" class="btn btn-success w-100">Manage Trainers</a>
                    </div>
                </div>
            </div>

            <!-- Packages Card -->
            <div class="col-md-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-warning bg-opacity-10 text-warning">
                                <i class="bx bx-package bx-md"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="card-title mb-0">Total Packages</h5>
                                <h2 class="mt-2 mb-0"><?php echo $totalPackages; ?></h2>
                            </div>
                        </div>
                        <a href="manage_packages.php" class="btn btn-warning w-100">Manage Packages</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
