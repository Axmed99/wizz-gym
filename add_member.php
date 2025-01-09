<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all active packages
try {
    $stmt = $pdo->query("SELECT * FROM packages WHERE status = 'active' ORDER BY name");
    $packages = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching packages: " . $e->getMessage();
    $packages = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $package_id = $_POST['package_id'];
    $join_date = $_POST['join_date'];
    
    $errors = [];
    
    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($package_id)) $errors[] = "Package is required";
    if (empty($join_date)) $errors[] = "Join date is required";
    
    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email already exists";
            }
        } catch(PDOException $e) {
            $errors[] = "Error checking email: " . $e->getMessage();
        }
    }
    
    // Get package duration and calculate expiry date
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT duration FROM packages WHERE id = ?");
            $stmt->execute([$package_id]);
            $package = $stmt->fetch();
            if ($package) {
                $expiry_date = date('Y-m-d', strtotime($join_date . ' + ' . $package['duration'] . ' months'));
            } else {
                $errors[] = "Invalid package selected";
            }
        } catch(PDOException $e) {
            $errors[] = "Error getting package details: " . $e->getMessage();
        }
    }
    
    // Insert member if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO members (name, email, phone, package_id, join_date, expiry_date) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $phone, $package_id, $join_date, $expiry_date]);
            
            $_SESSION['success'] = "Member added successfully!";
            header("Location: manage_members.php");
            exit();
        } catch(PDOException $e) {
            $errors[] = "Error adding member: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Member - Gym Management</title>
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
                        <a class="nav-link" href="manage_members.php">Back to Members</a>
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
                        <h4 class="mb-0">Add New Member</h4>
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
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="package_id" class="form-label">Package</label>
                                <select class="form-select" id="package_id" name="package_id" required>
                                    <option value="">Select Package</option>
                                    <?php foreach ($packages as $package): ?>
                                        <option value="<?php echo $package['id']; ?>" 
                                                <?php echo (isset($package_id) && $package_id == $package['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($package['name']); ?> 
                                            (<?php echo $package['duration']; ?> months - $<?php echo $package['price']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="join_date" class="form-label">Join Date</label>
                                <input type="date" class="form-control" id="join_date" name="join_date" 
                                       value="<?php echo isset($join_date) ? $join_date : date('Y-m-d'); ?>" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Add Member</button>
                                <a href="manage_members.php" class="btn btn-secondary">Cancel</a>
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
