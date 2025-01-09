<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle package status toggle
if (isset($_POST['toggle_status'])) {
    $package_id = $_POST['package_id'];
    $new_status = $_POST['new_status'];
    try {
        $stmt = $pdo->prepare("UPDATE packages SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $package_id]);
        $_SESSION['success'] = "Package status updated successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating package status: " . $e->getMessage();
    }
    header("Location: manage_packages.php");
    exit();
}

// Fetch all packages with member count
try {
    $stmt = $pdo->query("
        SELECT p.*, COUNT(m.id) as member_count 
        FROM packages p 
        LEFT JOIN members m ON p.id = m.package_id 
        GROUP BY p.id 
        ORDER BY p.price
    ");
    $packages = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching packages: " . $e->getMessage();
    $packages = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - Gym Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        .package-card {
            height: 100%;
            transition: transform 0.3s;
        }
        .package-card:hover {
            transform: translateY(-5px);
        }
        .feature-list {
            list-style: none;
            padding-left: 0;
        }
        .feature-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Gym Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Packages</h2>
            <a href="edit_package.php" class="btn btn-primary">
                <i class="bx bx-plus"></i> Add New Package
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach ($packages as $package): ?>
                <div class="col-md-4">
                    <div class="card package-card">
                        <div class="card-header bg-<?php echo $package['status'] === 'active' ? 'success' : 'danger'; ?> text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($package['name']); ?></h5>
                                <span class="badge bg-white text-<?php echo $package['status'] === 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($package['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <h3>$<?php echo number_format($package['price'], 2); ?></h3>
                                <p class="text-muted"><?php echo $package['duration']; ?> month<?php echo $package['duration'] > 1 ? 's' : ''; ?></p>
                            </div>
                            
                            <ul class="feature-list mb-4">
                                <?php 
                                $features = explode("\n", $package['description']);
                                foreach ($features as $feature):
                                    if (trim($feature) !== ''):
                                ?>
                                    <li>
                                        <i class="bx bx-check text-success"></i>
                                        <?php echo htmlspecialchars($feature); ?>
                                    </li>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </ul>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="bx bx-user"></i> <?php echo $package['member_count']; ?> members
                                </span>
                                <div class="btn-group">
                                    <a href="edit_package.php?id=<?php echo $package['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bx bx-edit"></i> Edit
                                    </a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                                        <input type="hidden" name="new_status" 
                                               value="<?php echo $package['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                        <button type="submit" name="toggle_status" 
                                                class="btn btn-sm btn-<?php echo $package['status'] === 'active' ? 'danger' : 'success'; ?>">
                                            <i class="bx bx-power-off"></i>
                                            <?php echo $package['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
