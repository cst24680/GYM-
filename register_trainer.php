<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_admin();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    $full_name = sanitize_input($_POST['full_name']);
    $phone = sanitize_input($_POST['phone']);
    $specialization = sanitize_input($_POST['specialization']);
    $certification = sanitize_input($_POST['certification']);
    $salary = sanitize_input($_POST['salary']);
    $schedule = sanitize_input($_POST['schedule']);

    // Validation
    if(empty($username) || empty($email) || empty($password) || empty($full_name) || empty($specialization)) {
        $error = 'Please fill all required fields.';
    } elseif(strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if($stmt->rowCount() > 0) {
            $error = 'Username or email already exists.';
        } else {
            try {
                $conn->beginTransaction();
                
                // Insert into users table
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, user_type) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, USER_TRAINER]);
                $user_id = $conn->lastInsertId();
                
                // Insert into trainer_details
                $stmt = $conn->prepare("INSERT INTO trainer_details (user_id, specialization, certification, hire_date, salary, schedule) VALUES (?, ?, ?, CURDATE(), ?, ?)");
                $stmt->execute([$user_id, $specialization, $certification, $salary, $schedule]);
                
                $conn->commit();
                $success = 'Trainer registration successful!';
            } catch(PDOException $e) {
                $conn->rollBack();
                $error = 'Registration failed. Please try again. Error: ' . $e->getMessage();
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container py-4">
    <h2 class="mb-4">Register New Trainer</h2>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" class="needs-validation" novalidate>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="username" class="form-label">Username*</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            
            <div class="col-md-6">
                <label for="email" class="form-label">Email*</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="col-md-6">
                <label for="password" class="form-label">Password* (min 8 characters)</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="col-md-6">
                <label for="full_name" class="form-label">Full Name*</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel" class="form-control" id="phone" name="phone">
            </div>
            
            <div class="col-md-6">
                <label for="specialization" class="form-label">Specialization*</label>
                <input type="text" class="form-control" id="specialization" name="specialization" required>
            </div>
            
            <div class="col-12">
                <label for="certification" class="form-label">Certification</label>
                <textarea class="form-control" id="certification" name="certification" rows="2"></textarea>
            </div>
            
            <div class="col-md-4">
                <label for="salary" class="form-label">Salary</label>
                <input type="number" step="0.01" class="form-control" id="salary" name="salary">
            </div>
            
            <div class="col-md-8">
                <label for="schedule" class="form-label">Schedule</label>
                <input type="text" class="form-control" id="schedule" name="schedule" placeholder="e.g., Mon-Fri 9AM-5PM">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Register Trainer</button>
                <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>