<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$package = null;
$isEdit = false;

// Fetch package details if editing
if (isset($_GET['id'])) {
    $isEdit = true;
    try {
        $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $package = $stmt->fetch();
        
        if (!$package) {
            $_SESSION['error'] = "Package not found";
            header("Location: manage_packages.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error fetching package: " . $e->getMessage();
        header("Location: manage_packages.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $duration = (int)$_POST['duration'];
    $price = (float)$_POST['price'];
    
    $errors = [];
    
    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($description)) $errors[] = "Description is required";
    if ($duration <= 0) $errors[] = "Duration must be greater than 0";
    if ($price <= 0) $errors[] = "Price must be greater than 0";
    
    if (empty($errors)) {
        try {
            if ($isEdit) {
                $stmt = $pdo->prepare("
                    UPDATE packages 
                    SET name = ?, description = ?, duration = ?, price = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$name, $description, $duration, $price, $_GET['id']]);
                $_SESSION['success'] = "Package updated successfully!";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO packages (name, description, duration, price) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$name, $description, $duration, $price]);
                $_SESSION['success'] = "Package added successfully!";
            }
            header("Location: manage_packages.php");
            exit();
        } catch(PDOException $e) {
            $errors[] = "Error " . ($isEdit ? "updating" : "adding") . " package: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Package - Gym Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
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
                        <a class="nav-link" href="manage_packages.php">Back to Packages</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><?php echo $isEdit ? 'Edit' : 'Add'; ?> Package</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <p class="mb-0"><?php echo $error; ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Package Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo isset($package['name']) ? htmlspecialchars($package['name']) : 
                                                   (isset($name) ? htmlspecialchars($name) : ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="6" required
                                          placeholder="Enter package features (one per line)"><?php 
                                    echo isset($package['description']) ? htmlspecialchars($package['description']) : 
                                         (isset($description) ? htmlspecialchars($description) : ''); 
                                ?></textarea>
                                <small class="text-muted">Enter each feature on a new line</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="duration" class="form-label">Duration (months)</label>
                                    <input type="number" class="form-control" id="duration" name="duration" min="1" 
                                           value="<?php echo isset($package['duration']) ? $package['duration'] : 
                                                       (isset($duration) ? $duration : '1'); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price ($)</label>
                                    <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" 
                                           value="<?php echo isset($package['price']) ? $package['price'] : 
                                                       (isset($price) ? $price : ''); ?>" required>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $isEdit ? 'Update' : 'Add'; ?> Package
                                </button>
                                <a href="manage_packages.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
