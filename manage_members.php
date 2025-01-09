<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete member
if (isset($_POST['delete_member'])) {
    $member_id = $_POST['member_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $stmt->execute([$member_id]);
        $_SESSION['success'] = "Member deleted successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting member: " . $e->getMessage();
    }
    header("Location: manage_members.php");
    exit();
}

// Fetch all members with their package information
try {
    $stmt = $pdo->query("
        SELECT m.*, p.name as package_name 
        FROM members m 
        LEFT JOIN packages p ON m.package_id = p.id 
        ORDER BY m.name
    ");
    $members = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching members: " . $e->getMessage();
    $members = [];
}

// Fetch all packages for the filter dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM packages WHERE status = 'active' ORDER BY name");
    $packages = $stmt->fetchAll();
} catch(PDOException $e) {
    $packages = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - Gym Management</title>
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
            <h2>Manage Members</h2>
            <a href="add_member.php" class="btn btn-primary">
                <i class="bx bx-plus"></i> Add New Member
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
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-select" id="packageFilter">
                            <option value="">All Packages</option>
                            <?php foreach ($packages as $package): ?>
                                <option value="<?php echo htmlspecialchars($package['name']); ?>">
                                    <?php echo htmlspecialchars($package['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($members)): ?>
                    <p class="text-center text-muted my-5">No members found. Add your first member!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="membersTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Package</th>
                                    <th>Join Date</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                    <tr data-package="<?php echo htmlspecialchars($member['package_name']); ?>" 
                                        data-status="<?php echo htmlspecialchars($member['status']); ?>">
                                        <td><?php echo htmlspecialchars($member['name']); ?></td>
                                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                                        <td><?php echo htmlspecialchars($member['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($member['package_name']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($member['join_date'])); ?></td>
                                        <td>
                                            <?php 
                                                $expiry = strtotime($member['expiry_date']);
                                                $today = time();
                                                $class = $expiry < $today ? 'text-danger' : 'text-success';
                                                echo "<span class='$class'>" . date('Y-m-d', $expiry) . "</span>";
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $member['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($member['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_member.php?id=<?php echo $member['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="confirmDelete(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['name']); ?>')">
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
                    Are you sure you want to delete member <span id="memberName"></span>?
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="member_id" id="memberIdInput">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_member" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(memberId, memberName) {
            document.getElementById('memberName').textContent = memberName;
            document.getElementById('memberIdInput').value = memberId;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }

        // Filter functionality
        document.getElementById('packageFilter').addEventListener('change', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);

        function filterTable() {
            const packageFilter = document.getElementById('packageFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#membersTable tbody tr');

            rows.forEach(row => {
                const packageMatch = !packageFilter || row.dataset.package === packageFilter;
                const statusMatch = !statusFilter || row.dataset.status === statusFilter;
                row.style.display = packageMatch && statusMatch ? '' : 'none';
            });
        }
    </script>
</body>
</html>
