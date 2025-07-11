<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_admin();

// Get stats
$stmt = $conn->query("SELECT COUNT(*) as total_members FROM users WHERE user_type = 'member' AND status = 'active'");
$total_members = $stmt->fetch(PDO::FETCH_ASSOC)['total_members'];

$stmt = $conn->query("SELECT COUNT(*) as total_trainers FROM users WHERE user_type = 'trainer'");
$total_trainers = $stmt->fetch(PDO::FETCH_ASSOC)['total_trainers'];

$stmt = $conn->query("SELECT COUNT(*) as pending_payments FROM payments WHERE status = 'pending'");
$pending_payments = $stmt->fetch(PDO::FETCH_ASSOC)['pending_payments'];

require_once 'includes/header.php';
?>

<div class="container">
    <h2 class="my-4">Admin Dashboard</h2>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Members</h5>
                    <h1 class="display-4"><?php echo $total_members; ?></h1>
                    <a href="manage_members.php" class="text-white">View Members <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Trainers</h5>
                    <h1 class="display-4"><?php echo $total_trainers; ?></h1>
                    <a href="manage_trainers.php" class="text-white">View Trainers <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Pending Payments</h5>
                    <h1 class="display-4"><?php echo $pending_payments; ?></h1>
                    <a href="manage_payments.php" class="text-white">View Payments <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="register_trainer.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-plus me-2"></i> Register New Trainer
                        </a>
                        <a href="manage_memberships.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-id-card me-2"></i> Manage Memberships
                        </a>
                        <a href="reports.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-pie me-2"></i> Generate Reports
                        </a>
                        <a href="system_settings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog me-2"></i> System Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>5 new members joined today</span>
                            <span class="badge bg-primary rounded-pill">Today</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>3 membership renewals</span>
                            <span class="badge bg-primary rounded-pill">Today</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>10 training sessions completed</span>
                            <span class="badge bg-primary rounded-pill">Yesterday</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>2 trainers on leave</span>
                            <span class="badge bg-primary rounded-pill">Yesterday</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>