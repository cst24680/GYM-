<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_trainer();

// Get trainer details
$stmt = $conn->prepare("SELECT u.*, t.* FROM users u JOIN trainer_details t ON u.id = t.user_id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$trainer = $stmt->fetch(PDO::FETCH_ASSOC);

// Get upcoming sessions
$stmt = $conn->prepare("SELECT s.*, m.full_name as member_name FROM training_sessions s JOIN users m ON s.member_id = m.id WHERE s.trainer_id = ? AND s.session_date >= CURDATE() ORDER BY s.session_date LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$upcoming_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<div class="container">
    <h2 class="my-4">Trainer Dashboard</h2>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Trainer Profile</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="https://via.placeholder.com/150" class="rounded-circle" alt="Profile">
                    </div>
                    <h4 class="text-center"><?php echo htmlspecialchars($trainer['full_name']); ?></h4>
                    <p class="text-center text-muted">Trainer</p>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Specialization:</strong> <?php echo htmlspecialchars($trainer['specialization']); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Hire Date:</strong> <?php echo date('M d, Y', strtotime($trainer['hire_date'])); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Schedule:</strong> <?php echo htmlspecialchars($trainer['schedule']); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Upcoming Sessions (Next 5)</h5>
                </div>
                <div class="card-body">
                    <?php if(count($upcoming_sessions) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Member</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($upcoming_sessions as $session): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($session['session_date'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($session['session_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($session['member_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $session['status'] == 'scheduled' ? 'info' : ($session['status'] == 'completed' ? 'success' : 'warning'); ?>">
                                                    <?php echo ucfirst($session['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_session.php?id=<?php echo $session['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No upcoming sessions scheduled.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="my_schedule.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-calendar-alt"></i> My Schedule
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="member_progress.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-chart-line"></i> Member Progress
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="create_workout.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-dumbbell"></i> Create Workout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>