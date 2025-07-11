<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_member();

// Get member details
$stmt = $conn->prepare("SELECT u.*, m.* FROM users u JOIN member_details m ON u.id = m.user_id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<div class="container">
    <h2 class="my-4">Member Dashboard</h2>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">My Profile</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="https://via.placeholder.com/150" class="rounded-circle" alt="Profile">
                    </div>
                    <h4 class="text-center"><?php echo htmlspecialchars($member['full_name']); ?></h4>
                    <p class="text-center text-muted">Member</p>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Membership:</strong> <?php echo htmlspecialchars($member['membership_type']); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Expires:</strong> <?php echo date('M d, Y', strtotime($member['expiry_date'])); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Status:</strong> 
                            <span class="badge bg-<?php echo $member['status'] == 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($member['status']); ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="book_session.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-calendar-plus"></i> Book Session
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="workout_plans.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-dumbbell"></i> Workout Plans
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="payment_history.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-receipt"></i> Payments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">My Fitness Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Height:</strong> <?php echo htmlspecialchars($member['height']); ?> cm</p>
                            <p><strong>Weight:</strong> <?php echo htmlspecialchars($member['weight']); ?> kg</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fitness Goals:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($member['fitness_goals'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>