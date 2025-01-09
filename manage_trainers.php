<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete trainer
if (isset($_POST['delete_trainer'])) {
    $trainer_id = $_POST['trainer_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM trainers WHERE id = ?");
        $stmt->execute([$trainer_id]);
        $_SESSION['success'] = "Trainer deleted successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting trainer: " . $e->getMessage();
    }
    header("Location: manage_trainers.php");
    exit();
}

// Fetch all trainers
try {
    $stmt = $pdo->query("SELECT * FROM trainers ORDER BY name");
    $trainers = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching trainers: " . $e->getMessage();
    $trainers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trainers - Gym Management</title>
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
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Trainers</h2>
            <a href="add_trainer.php" class="btn btn-primary">
                <i class="bx bx-plus"></i> Add New Trainer
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

        <div class="card">
            <div class="card-body">
                <?php if (empty($trainers)): ?>
                    <p class="text-center text-muted my-5">No trainers found. Add your first trainer!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Specialization</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trainers as $trainer): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($trainer['name']); ?></td>
                                        <td><?php echo htmlspecialchars($trainer['specialization']); ?></td>
                                        <td><?php echo htmlspecialchars($trainer['email']); ?></td>
                                        <td><?php echo htmlspecialchars($trainer['phone']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $trainer['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($trainer['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_trainer.php?id=<?php echo $trainer['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="confirmDelete(<?php echo $trainer['id']; ?>, '<?php echo htmlspecialchars($trainer['name']); ?>')">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete trainer <span id="trainerName"></span>?
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="trainer_id" id="trainerIdInput">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_trainer" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(trainerId, trainerName) {
            document.getElementById('trainerName').textContent = trainerName;
            document.getElementById('trainerIdInput').value = trainerId;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>
