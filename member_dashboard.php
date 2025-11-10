<?php
session_start();
include "db.php";
include "helpers.php";

if (!isset($_SESSION['Mem_id'])) {
    header("Location: login.php");
    exit();

}

$member_id = $_SESSION['Mem_id'];

// Member details
$memberQuery = mysqli_query($conn, "SELECT * FROM member_registration WHERE Mem_id = $member_id LIMIT 1");
$member = mysqli_fetch_assoc($memberQuery);

// --- 1. GLOBAL AGE CALCULATION/CORRECTION ---
// This block ensures the age displayed is current and sets the definitive $calculated_age.
$dob_str = $member['Mem_dob'] ?? null;
$calculated_age = (int)$member['Mem_age']; // Start with stored age

if ($dob_str && $dob_str !== '0000-00-00') {
    try {
        $dob = new DateTime($dob_str);
        $today = new DateTime('today');
        $new_calculated_age = $dob->diff($today)->y;
        
        // CRITICAL CHECK: Only update age if the calculation yields a sensible, positive age (e.g., age > 0)
        if ($new_calculated_age > 0 && $new_calculated_age < 100) { 
            $calculated_age = $new_calculated_age; 
            
            // Update the Mem_age column in DB for consistency
            if ($new_calculated_age !== (int)$member['Mem_age']) {
                mysqli_query($conn, "UPDATE member_registration SET Mem_age = $new_calculated_age WHERE Mem_id = $member_id");
                $member['Mem_age'] = $new_calculated_age; // Update local array
            }
        }
    } catch (Exception $e) {
        // If DOB is invalid, calculated_age remains the stored value.
    }
}
// --- END GLOBAL AGE CALCULATION ---


// Fetch trainer
$trainer = null;
if (!empty($member['Trainer_id'])) {
    $trainerQuery = mysqli_query($conn, "SELECT * FROM trainer WHERE Trainer_id = {$member['Trainer_id']} LIMIT 1");
    $trainer = mysqli_fetch_assoc($trainerQuery);
}

// Fetch dietician
$dietician = null;
if (!empty($member['Dietician_id'])) {
    $dieticianQuery = mysqli_query($conn, "SELECT * FROM dietician WHERE Dietician_id = {$member['Dietician_id']} LIMIT 1");
    $dietician = mysqli_fetch_assoc($dieticianQuery);
}

// Default values
$diet_choice = "Veg";
$active_section = "dashboard"; // default section
$message = "";

// Helper function to get initials for the coach/dietician image placeholder
function getInitials($name) {
    if (empty($name)) return 'N/A';
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper($word[0]);
    }
    return substr($initials, 0, 2);
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assign trainer
    if (isset($_POST['assign_trainer_id'])) {
        $assignTrainerId = (int)$_POST['assign_trainer_id'];
        mysqli_query($conn, "UPDATE member_registration SET Trainer_id = $assignTrainerId WHERE Mem_id = $member_id");
        $trainerQuery = mysqli_query($conn, "SELECT * FROM trainer WHERE Trainer_id = $assignTrainerId LIMIT 1");
        $trainer = mysqli_fetch_assoc($trainerQuery);
        $message = "✅ Trainer assigned successfully.";
        $active_section = "trainer";
    }
    // Assign dietician
    if (isset($_POST['assign_dietician_id'])) {
        $assignDieticianId = (int)$_POST['assign_dietician_id'];
        mysqli_query($conn, "UPDATE member_registration SET Dietician_id = $assignDieticianId WHERE Mem_id = $member_id");
        $dieticianQuery = mysqli_query($conn, "SELECT * FROM dietician WHERE Dietician_id = $assignDieticianId LIMIT 1");
        $dietician = mysqli_fetch_assoc($dieticianQuery);
        $message = "✅ Dietician assigned successfully.";
        $active_section = "dietician";
    }
    if (isset($_POST['diet_choice'])) {
        $diet_choice = $_POST['diet_choice'];
        $active_section = "dietician";
    }

    // --- 2. HANDLE PROFILE UPDATE FORM ---
    if (isset($_POST['update_profile'])) {
        // 1. Sanitize and fetch new data
        $new_weight = (float)$_POST['new_weight'];
        $new_height_cm = (float)$_POST['new_height_cm']; 

        // 2. Recalculate BMI 
        $new_height_m = $new_height_cm / 100;
        $new_bmi = 0;
        
        if ($new_height_m > 0 && $new_weight > 0) {
            $new_bmi = round($new_weight / ($new_height_m * $new_height_m), 2);
        } else {
            $new_bmi = $member['BMI'];
        }
        
        // FIX: Use the robustly calculated age, $calculated_age, for saving.
        $current_age_to_save = $calculated_age; 

        // 3. Update the database 
        $update_sql = "
            UPDATE member_registration 
            SET Weight = $new_weight, 
                Height = $new_height_cm,
                BMI = $new_bmi,
                Mem_age = $current_age_to_save
            WHERE Mem_id = $member_id
        ";

        if (mysqli_query($conn, $update_sql)) {
            $message = "✅ Profile updated successfully! BMI: $new_bmi, Age: $current_age_to_save.";
            
            // Re-fetch member data array for immediate display
            $memberQuery = mysqli_query($conn, "SELECT * FROM member_registration WHERE Mem_id = $member_id LIMIT 1");
            $member = mysqli_fetch_assoc($memberQuery);
            
            // Update local variables for the rest of the script
            $calculated_age = (int)$member['Mem_age']; 

            $active_section = "dashboard";
        } else {
            $message = "❌ Error updating profile: " . mysqli_error($conn);
            $active_section = "dashboard";
        }
    }
    // --- END PROFILE UPDATE HANDLER ---

    // Handle Feedback Form
    if (isset($_POST['feedback_submit'])) {
        $target_type = mysqli_real_escape_string($conn, $_POST['target_type']);
        $target_id  = (int)$_POST['target_id'];
        $rating = (int)$_POST['rating'];
        $comments = mysqli_real_escape_string($conn, $_POST['comments']);

        $sql = "INSERT INTO feedback (Mem_id, target_id, target_type, rating, comments, created_at)
VALUES ($member_id, $target_id, '$target_type', $rating, '$comments', NOW())";
        if (mysqli_query($conn, $sql)) {
            $message = "✅ Feedback submitted successfully!";
            $active_section = "feedback";
        } else {
            $message = "❌ Error: " . mysqli_error($conn);
            $active_section = "feedback";
        }
    }

    // Attendance Check-in Logic
    if (isset($_POST['check_in_action'])) {
        $check_in_date = date("Y-m-d");
        $query = "SELECT COUNT(*) as count FROM attendance WHERE Mem_id = $member_id AND DATE(check_in_time) = '$check_in_date'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);

        if ($row['count'] == 0) {
            $insertQuery = "INSERT INTO attendance (Mem_id) VALUES ($member_id)";
            if (mysqli_query($conn, $insertQuery)) {
                $message = "✅ Check-in successful!";
            } else {
                $message = "❌ Error during check-in: " . mysqli_error($conn);
            }
        } else {
            $message = "❌ You have already checked in today.";
        }
    }
}


// Fetch attendance data for the member
$attendanceQuery = mysqli_query($conn, "SELECT check_in_time FROM attendance WHERE Mem_id = $member_id ORDER BY check_in_time DESC");
$attendanceRecords = [];
while ($row = mysqli_fetch_assoc($attendanceQuery)) {
    $attendanceRecords[] = $row;
}
$attendanceCount = count($attendanceRecords);

// Re-determine BMI & Age categories based on the updated $member array
$bmiCategory = function_exists('getBMICategory') ? getBMICategory($member['BMI']) : 'N/A';
$ageCategory = function_exists('getAgeCategory') ? getAgeCategory($member['Mem_age']) : 'N/A';

// Normalize diet choice
$diet_choice = strtolower(trim($diet_choice));
if ($diet_choice === "veg") $diet_choice = "Veg";
if (in_array($diet_choice, ["non veg", "non-veg", "nonveg"])) $diet_choice = "Non-Veg";

// Fetch latest assigned diet plan
$planQuery = mysqli_query($conn, "
    SELECT Diet_plan_id, Plan_name, Diet_type, Description, BMI_Category, Age_Category, Dietician_id
    FROM diet_plans
    WHERE Mem_id = {$member['Mem_id']}
      AND Diet_type = '{$diet_choice}'
    ORDER BY Diet_plan_id DESC
    LIMIT 1
");
$dietPlan = mysqli_fetch_assoc($planQuery);

if (!$dietPlan) {
    // If no assigned plan exists, fallback to use automated templates using CURRENT categories
    $dietPlan = function_exists('findTemplate') ? findTemplate($conn, $member['Goal_type'], $bmiCategory, $ageCategory, $diet_choice) : null;
}

// Fetch assigned workouts (for calendar + trainer tab)
$schedule_sql = "
    SELECT ms.Workout_date, p.Workout_type, ms.Notes
    FROM member_schedule ms
    JOIN plan_type p ON ms.Plan_type_id = p.Plan_type_id
    WHERE ms.Mem_id = $member_id
    ORDER BY ms.Workout_date ASC
";
$schedule_res = mysqli_query($conn, $schedule_sql);
$events = [];
while ($row = mysqli_fetch_assoc($schedule_res)) {
    $events[] = $row;
}

// ---------- Personalized Recommendations ----------
$memberGender = trim($member['Gender'] ?? '');
$goalType = trim($member['Goal_type'] ?? '');

function buildSpecialityPattern($goalType) {
    $g = strtolower($goalType);
    if (strpos($g, 'weight') !== false) return "%weight%";
    if (strpos($g, 'muscle') !== false) return "%muscle%";
    return "%fitness%";
}

$specPattern = buildSpecialityPattern($goalType);

// Trainers with avg ratings, prefer same gender and matching speciality
$safeGender = mysqli_real_escape_string($conn, $memberGender);
$safePattern = mysqli_real_escape_string($conn, $specPattern);
$trainer_sql = "
    SELECT t.*, 
           AVG(CASE WHEN f.target_type='Trainer' THEN f.rating END) AS avg_rating,
           COUNT(CASE WHEN f.target_type='Trainer' THEN 1 END) AS rating_count,
           (CASE WHEN '$safeGender' <> '' AND LOWER(t.Trainer_gender)=LOWER('$safeGender') THEN 1 ELSE 0 END) AS gender_match,
           (CASE WHEN LOWER(t.Speciality) LIKE LOWER('$safePattern') THEN 1 ELSE 0 END) AS spec_match
    FROM trainer t
    LEFT JOIN feedback f ON f.target_id = t.Trainer_id
    GROUP BY t.Trainer_id
    ORDER BY 
        spec_match DESC, 
        gender_match DESC,
        (AVG(CASE WHEN f.target_type='Trainer' THEN f.rating END) IS NULL) ASC,
        AVG(CASE WHEN f.target_type='Trainer' THEN f.rating END) DESC,
        rating_count DESC
    LIMIT 5
";

// Dieticians with avg ratings, prefer same gender (if stored) and goal relevance (simple)
$dietician_sql = "
    SELECT d.*, 
           AVG(CASE WHEN f.target_type='Dietician' THEN f.rating END) AS avg_rating,
           COUNT(CASE WHEN f.target_type='Dietician' THEN 1 END) AS rating_count
    FROM dietician d
    LEFT JOIN feedback f ON f.target_id = d.Dietician_id
    GROUP BY d.Dietician_id
    ORDER BY 
        (AVG(CASE WHEN f.target_type='Dietician' THEN f.rating END) IS NULL) ASC,
        AVG(CASE WHEN f.target_type='Dietician' THEN f.rating END) DESC,
        rating_count DESC
    LIMIT 5
";

// Execute with manual binding (mysqli lacks named params); build safe query
$recommended_trainers_res = mysqli_query($conn, $trainer_sql);
$recommended_trainers = [];
while ($r = mysqli_fetch_assoc($recommended_trainers_res)) { $recommended_trainers[] = $r; }

$recommended_dieticians_res = mysqli_query($conn, $dietician_sql);
$recommended_dieticians = [];
while ($r = mysqli_fetch_assoc($recommended_dieticians_res)) { $recommended_dieticians[] = $r; }

// Auto-assign a dietician if none is assigned: choose best recommendation
if (!$dietician && count($recommended_dieticians) > 0) {
    $autoDiet = $recommended_dieticians[0];
    $autoId = (int)$autoDiet['Dietician_id'];
    mysqli_query($conn, "UPDATE member_registration SET Dietician_id = $autoId WHERE Mem_id = $member_id");
    $dietician = $autoDiet; // Reflect immediately in UI
    $message = "✅ A dietician was automatically assigned based on your goal and ratings.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Member Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@300;400;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="member.css">
</head>
<body onload="showSection('<?php echo $active_section; ?>')">

<div class="sidebar">
    <h2>Member Panel</h2>
    <a href="#" class="nav-item" data-section="dashboard" onclick="setActive(this)"><i class="fas fa-home"></i> Dashboard</a>
    <a href="#" class="nav-item" data-section="dietician" onclick="setActive(this)"><i class="fas fa-carrot"></i> My Dietician</a>
    <a href="#" class="nav-item" data-section="trainer" onclick="setActive(this)"><i class="fas fa-user-tie"></i> My Trainer</a>
    <a href="#" class="nav-item" data-section="calendar" onclick="setActive(this)"><i class="fas fa-calendar-alt"></i> My Calendar</a>
    <a href="#" class="nav-item" data-section="feedback" onclick="setActive(this)"><i class="fas fa-comment-dots"></i> My Feedback</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="main-content">
    <header class="header" id="welcomeHeader" style="display:none;">
    <div class="greeting">
        <h1>Welcome, <?php echo htmlspecialchars($member['Mem_name']); ?>!</h1>
        <p><?php echo date("l, M j, Y"); ?></p>
    </div>
</header>
    
    <div id="dashboard" class="content-section dashboard-view">
        
        <?php if ($message): ?><div style="
            background: rgba(6, 214, 160, 0.2); 
            color: #06D6A0; 
            padding: 15px; 
            margin-bottom: 30px; /* Use bottom margin for spacing */
            border-radius: 8px; 
            font-weight: 600;
            border-left: 5px solid #06D6A0;
        "><?php echo $message; ?></div><?php endif; ?>
        
        <div class="dashboard-grid">
            
            <div class="card profile-card">
                <h4>Your Profile</h4>
                
                <div class="key-metrics-summary">
                    <p class="key-metric-line">
                        <strong>BMI:</strong> 
                        <span class="bmi-value" data-category="<?php echo strtolower(str_replace(' ', '-', $bmiCategory)); ?>">
                            <?php echo $member['BMI']; ?> (<?php echo $bmiCategory; ?>)
                        </span>
                    </p>
                    <p class="key-metric-line">
                        <strong>Goal:</strong> 
                        <span class="goal-value"><?php echo $member['Goal_type']; ?></span>
                    </p>
                </div>
                
                <div class="profile-details-list">
                    <p><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo $member['Mem_email']; ?></p>
                    <p><strong><i class="fas fa-birthday-cake"></i> Age:</strong> <?php echo $member['Mem_age']; ?></p>
                    <p><strong><i class="fas fa-ruler-vertical"></i> Height:</strong> <?php echo $member['Height']; ?> cm</p>
                    <p><strong><i class="fas fa-weight"></i> Weight:</strong> <?php echo $member['Weight']; ?> kg</p>
                </div>

                <button onclick="openProfileUpdateModal()" style="
                    background: #E63946; /* Accent Red */ 
                    color: white; 
                    padding: 8px 15px;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    margin-top: 15px;
                    font-weight: 600;
                    width: 100%;
                    transition: background 0.3s;
                ">
                    Update Vitals
                </button>
            </div>

            <div class="card check-in-action-card">
                <h4>Daily Check-in</h4>
                <div class="check-in-container">
                    <form method="post">
                        <button type="submit" name="check_in_action" class="check-in-btn">
                            <i class="fas fa-sign-in-alt"></i> CHECK IN NOW
                        </button>
                    </form>
                </div>
                
                <div class="attendance-status-display">
                    <p>
                        Total Check-ins: <span class="total-checkins-count"><?php echo $attendanceCount; ?></span>
                    </p>
                    <p class="last-check-in-time">
                        Last Check-in:
                        <?php echo $attendanceCount > 0 ? date('M j, g:i a', strtotime($attendanceRecords[0]['check_in_time'])) : 'N/A'; ?>
                    </p>
                </div>
            </div>
            
            <div class="card recent-checkins-card" style="grid-column: 1 / span 2;">
                <h4>Recent Check-ins History</h4>
                <div class="attendance-history">
                    <?php if ($attendanceCount > 0) { ?>
                        <ul class="attendance-history-list">
                            <?php 
                            // Limit to last 5 records for dashboard cleanliness
                            $recentRecords = array_slice($attendanceRecords, 0, 5); 
                            foreach ($recentRecords as $record) { 
                            ?>
                                <li>
                                    <i class="fas fa-calendar-check"></i>
                                    <?php echo date('F j, Y, g:i a', strtotime($record['check_in_time'])); ?>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } else { ?>
                        <p class="no-records">No attendance records found.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    
    <div id="dietician" class="content-section" style="display:none;">
        <?php if ($dietician) { ?>
            <h2>Dietician Details</h2>
            <p><strong>Name:</strong> <?php echo $dietician['Dietician_name']; ?></p>
            <p><strong>Email:</strong> <?php echo $dietician['Email']; ?></p>
            <p><strong>Phone:</strong> <?php echo $dietician['Dietician_phno']; ?></p>
        <?php } ?>

        <?php if (!$dietician) { ?>
            <p>A dietician will be assigned automatically based on your goal and ratings.</p>
        <?php } ?>

        <div class="diet-toggle">
            <form method="post">
                <button type="submit" name="diet_choice" value="Veg" class="<?php echo ($diet_choice == 'Veg') ? 'active' : ''; ?>">Veg Plan</button>
                <button type="submit" name="diet_choice" value="Non-Veg" class="<?php echo ($diet_choice == 'Non-Veg') ? 'active' : ''; ?>">Non-Veg Plan</button>
            </form>
        </div>

        <?php if ($dietPlan) { ?>
            <h3>Your <?php echo ucfirst($diet_choice); ?> Diet Plan</h3>
            <p><strong>Goal:</strong> <?php echo $member['Goal_type']; ?></p>
            <?php if (!empty($dietPlan['BMI_Category']) && !empty($dietPlan['Age_Category'])) { ?>
                <p><strong>BMI Category:</strong> <?php echo $bmiCategory; ?></p>
                <p><strong>Age Category:</strong> <?php echo $ageCategory; ?></p>
            <?php } ?>
            <pre><?php echo htmlspecialchars($dietPlan['Description']); ?></pre>
        <?php } else { ?>
            <p>No <?php echo ucfirst($diet_choice); ?> plan available for your profile yet. (Your current BMI is <?php echo $member['BMI']; ?> and Age is <?php echo $member['Mem_age']; ?>).</p>
        <?php } ?>
    </div>

    <div id="trainer" class="content-section" style="display:none;">
        <?php if ($trainer) { ?>
            <h2>Trainer Details</h2>
            <p><strong>Name:</strong> <?php echo $trainer['Trainer_name']; ?></p>
            <p><strong>Email:</strong> <?php echo $trainer['Email']; ?></p>
            <p><strong>Phone:</strong> <?php echo $trainer['Trainer_phno']; ?></p>
            <p><strong>Gender:</strong> <?php echo $trainer['Trainer_gender']; ?></p>
            <p><strong>Status:</strong> <?php echo $trainer['Trainer_status']; ?></p>
            <p><strong>Speciality:</strong> <?php echo $trainer['Speciality']; ?></p>
            <hr>
            
            <button type="button" onclick="toggleTrainerSection()" style="
                background: #ff9800; 
                color: #222; 
                padding: 10px 15px; 
                border: none; 
                border-radius: 4px; 
                cursor: pointer; 
                margin-bottom: 15px;
            ">
                Change Trainer
            </button>
            
            <div id="changeTrainerSection" style="display:none;">
                <h3>Want to change your trainer?</h3>
                <p>Here are recommended alternatives based on your goal and ratings.</p>
                <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
                    <tr style="background:#222;color:#ff9800;">
                        <th>Name</th><th>Gender</th><th>Speciality</th><th>Phone</th><th>Rating</th><th></th>
                    </tr>
                    <?php foreach ($recommended_trainers as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['Trainer_name']); ?></td>
                        <td><?php echo htmlspecialchars($t['Trainer_gender']); ?></td>
                        <td><?php echo htmlspecialchars($t['Speciality']); ?></td>
                        <td><?php echo htmlspecialchars($t['Trainer_phno']); ?></td>
                        <td>
                            <?php if ((int)$t['rating_count'] > 0) {
                                echo number_format((float)$t['avg_rating'],1) . "/5 (" . (int)$t['rating_count'] . ")";
                            } else {
                                echo "New — no ratings yet";
                            } ?>
                        </td>
                        <td>
                            <form method="post" style="margin:0" onsubmit="return confirm('Are you sure about the changes?');">
                                <input type="hidden" name="assign_trainer_id" value="<?php echo (int)$t['Trainer_id']; ?>">
                                <button type="submit">Choose</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            
            <h3>Your Current Vitals</h3>
            <p><strong>BMI:</strong> <?php echo $member['BMI']; ?> (<?php echo $bmiCategory; ?>)</p>
            <p><strong>Age:</strong> <?php echo $member['Mem_age']; ?></p>


            <h3>Assigned Workout Routine</h3>
            <?php if (!empty($events)) { ?>
                <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
                    <tr style="background:#222; color:limegreen;">
                        <th>Date</th>
                        <th>Workout Type</th>
                        <th>Notes</th>
                    </tr>
                    <?php foreach ($events as $e) { ?>
                        <tr>
                            <td><?php echo $e['Workout_date']; ?></td>
                            <td><?php echo $e['Workout_type']; ?></td>
                            <td><?php echo $e['Notes'] ?: 'No description'; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>No workouts assigned yet.</p>
            <?php } ?>
        <?php } else { ?>
            <p>No trainer assigned yet.</p>
            <h3>Recommended Trainers</h3>
            <p>Based on your goal (<?php echo htmlspecialchars($goalType); ?>), your gender<?php echo $memberGender?" (".htmlspecialchars($memberGender).")":""; ?>, and ratings.</p>
            <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
                <tr style="background:#222;color:#ff9800;">
                    <th>Name</th><th>Gender</th><th>Speciality</th><th>Phone</th><th>Rating</th><th></th>
                </tr>
                <?php foreach ($recommended_trainers as $t): ?>
                <tr>
                    <td><?php echo htmlspecialchars($t['Trainer_name']); ?></td>
                    <td><?php echo htmlspecialchars($t['Trainer_gender']); ?></td>
                    <td><?php echo htmlspecialchars($t['Speciality']); ?></td>
                    <td><?php echo htmlspecialchars($t['Trainer_phno']); ?></td>
                    <td>
                        <?php if ((int)$t['rating_count'] > 0) {
                            echo number_format((float)$t['avg_rating'],1) . "/5 (" . (int)$t['rating_count'] . ")";
                        } else {
                            echo "New — no ratings yet";
                        } ?>
                    </td>
                    <td>
                        <form method="post" style="margin:0" onsubmit="return confirm('Are you sure about the changes?');">
                            <input type="hidden" name="assign_trainer_id" value="<?php echo (int)$t['Trainer_id']; ?>">
                            <button type="submit">Choose</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php } ?>
    </div>

    <div id="calendar" class="content-section" style="display:none;">
        <h2>My Workout Calendar</h2>
        <div style="text-align:center;">
            <button class="nav-btn" onclick="changeMonth(-1)">&#8592; Previous</button>
            <span id="monthYear"></span>
            <button class="nav-btn" onclick="changeMonth(1)">Next &#8594;</button>
        </div>
        <div id="calendarGrid" class="calendar"></div>
    </div>

    <div id="feedback" class="content-section" style="display:none;">
        <h2>Give Feedback</h2>
        <?php if (isset($message) && $active_section === 'feedback') echo "<p>$message</p>"; ?>
        <form method="post">
            <label>Target Type:</label>
            <select name="target_type" id="target_type" required onchange="setTargetId()">
                <option value="">-- Select --</option>
                <?php if ($trainer) { ?>
                    <option value="Trainer">Trainer (<?php echo $trainer['Trainer_name']; ?>)</option>
                <?php } ?>
                <?php if ($dietician) { ?>
                    <option value="Dietician">Dietician (<?php echo $dietician['Dietician_name']; ?>)</option>
                <?php } ?>
                <option value="Gym">Gym</option>
            </select><br><br>

            <input type="hidden" id="target_id" name="target_id" value="">

            <label>Rating (1-5):</label>
            <input type="number" name="rating" min="1" max="5" required><br><br>

            <label>Comments:</label><br>
            <textarea name="comments" rows="4" cols="50" placeholder="Write your feedback..."></textarea><br><br>

            <button type="submit" name="feedback_submit">Submit Feedback</button>
        </form>

        <hr>
        <h2>My Previous Feedback</h2>
        <?php
        $feedbackQuery = mysqli_query($conn, "SELECT * FROM feedback WHERE Mem_id = $member_id ORDER BY created_at DESC");
        if ($feedbackQuery && mysqli_num_rows($feedbackQuery) > 0) {
            echo "<table border='1' cellpadding='8' cellspacing='0' style='width:100%; border-collapse:collapse;'>";
            echo "<tr style='background:#222; color:orange;'>
                    <th>ID</th>
                    <th>Target Type</th>
                    <th>Target ID</th>
                    <th>Rating</th>
                    <th>Comments</th>
                    <th>Date</th>
                </tr>";
            while ($fb = mysqli_fetch_assoc($feedbackQuery)) {
                echo "<tr>
                    <td>{$fb['feedback_id']}</td>
                    <td>{$fb['target_type']}</td>
                    <td>{$fb['target_id']}</td>
                    <td>{$fb['rating']}</td>
                    <td>{$fb['comments']}</td>
                    <td>{$fb['created_at']}</td>
                </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No feedback submitted yet.</p>";
        }
        ?>
    </div>

</div>

<div id="profileUpdateModal" class="modal" style="display:none;
    position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
    background: rgba(0, 0, 0, 0.8); z-index: 1000; justify-content: center; align-items: center;">
    
    <div class="modal-content" style="
        background: #1A1A1A; padding: 30px; border-radius: 12px; width: 400px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.5); color: #F5F5F5;">
        
        <h3 style="color: #FFD166; border-bottom: 2px solid #E63946; padding-bottom: 10px; margin-bottom: 20px;">
            Update Vitals
        </h3>
        
        <form method="post">
            <label style="display: block; margin-top: 10px;">Current Weight (kg):</label>
            <input type="number" step="0.01" name="new_weight" value="<?php echo $member['Weight']; ?>" required style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 4px; border: 1px solid #444; background: #121212; color: #fff;"><br>

            <label style="display: block; margin-top: 10px;">Current Height (cm):</label>
            <input type="number" step="1" name="new_height_cm" value="<?php echo $member['Height']; ?>" required style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 4px; border: 1px solid #444; background: #121212; color: #fff;"><br>
            
            <p style="color:#999; font-size: 0.85em; margin: 15px 0;">
                *Updating these values will recalculate your BMI and automatically fetch your new suggested plan.
            </p>

            <button type="submit" name="update_profile" style="background: #E63946; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px;">
                Calculate & Update
            </button>
            <button type="button" onclick="document.getElementById('profileUpdateModal').style.display='none'" style="background: #333; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; margin-left: 10px;">
                Cancel
            </button>
        </form>
    </div>
</div>

<div id="eventModal" class="modal">
    <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3 id="modalDate"></h3>
    <div id="modalEvents"></div>
    </div>
</div>


<script>
function setActive(element) {
    document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
    element.classList.add('active');
    showSection(element.getAttribute('data-section'));
}

function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(sec => sec.style.display = 'none');

    // Show the chosen section
    const section = document.getElementById(sectionId);
    if (section) section.style.display = 'block';

    // Toggle the welcome header visibility
    const header = document.getElementById("welcomeHeader");
    if (sectionId === "dashboard") {
        header.style.display = "block"; // Show only on Dashboard
    } else {
        header.style.display = "none";  // Hide elsewhere
    }

    // Optional: if you have a calendar loader
    if (sectionId === "calendar") {
        loadCalendar(events);
    }
}

// NEW JS function
function openProfileUpdateModal() {
    document.getElementById('profileUpdateModal').style.display = 'flex';
}

// *** MODIFICATION: Added this new function ***
function toggleTrainerSection() {
    var section = document.getElementById('changeTrainerSection');
    if (section.style.display === 'none' || section.style.display === '') {
        section.style.display = 'block';
    } else {
        section.style.display = 'none';
    }
}


// Auto-fill Target ID
function setTargetId() {
    const targetType = document.getElementById("target_type").value;
    let targetIdField = document.getElementById("target_id");
    
    const trainerId = "<?php echo $trainer ? $trainer['Trainer_id'] : ''; ?>";
    const dieticianId = "<?php echo $dietician ? $dietician['Dietician_id'] : ''; ?>";
    
    if (targetType === "Trainer") {
        targetIdField.value = trainerId;
    } else if (targetType === "Dietician") {
        targetIdField.value = dieticianId;
    } else if (targetType === "Gym") {
        targetIdField.value = "0";
    } else {
        targetIdField.value = "";
    }
}

// Calendar data
const events = <?php echo json_encode($events); ?>;
const today = new Date();
let year = today.getFullYear();
let month = today.getMonth();

const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

function loadCalendar(events, gridId = 'calendarGrid') {
    const calendar = document.getElementById(gridId);
    if (!calendar) return;

    document.getElementById("monthYear").innerText = `${monthNames[month]} ${year}`;
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    calendar.innerHTML = "";
    
    // Day labels
    const dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    calendar.innerHTML += dayLabels.map(label => `<div class='day-label'>${label}</div>`).join('');

    for (let i = 0; i < firstDay; i++) {
        calendar.innerHTML += `<div class='day empty'></div>`;
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
        const isToday = (new Date().toDateString() === new Date(year, month, d).toDateString());
        
        let dayEvents = events.filter(e => e.Workout_date === dateStr);
        let classes = '';

        if (isToday) classes += ' current ';
        if (dayEvents.length > 0) classes += ' planned ';
        
        const eventIcon = dayEvents.length > 0 ? '<i class="fas fa-dumbbell small-icon"></i>' : '';

        calendar.innerHTML += `
            <div class="day ${classes.trim()}" 
                 data-date="${dateStr}" 
                 onclick="showModal('${dateStr}')">
                <div class="date">${d}</div>
                ${eventIcon}
            </div>`;
    }
}

function changeMonth(step) {
    month += step;
    if (month < 0) {
        month = 11;
        year--;
    }
    if (month > 11) {
        month = 0;
        year++;
    }
    loadCalendar(events);
}

function showModal(dateStr) {
    let dayEvents = events.filter(e => e.Workout_date === dateStr);
    document.getElementById("modalDate").innerText = `Workouts on ${new Date(dateStr).toDateString()}`;
    
    if (dayEvents.length > 0) {
        document.getElementById("modalEvents").innerHTML = dayEvents.map(e =>
            `<p class="modal-event-item"><strong>${e.Notes || "No description"}</strong><br><small>(${e.Workout_type})</small></p>`
        ).join("");
    } else {
        document.getElementById("modalEvents").innerHTML = "<p class='no-plan'>No workouts assigned.</p>";
    }
    document.getElementById("eventModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("eventModal").style.display = "none";
}

document.addEventListener('DOMContentLoaded', () => {
    // Ensure initial state is set
    showSection('<?php echo $active_section; ?>'); 
    setTargetId();
});
</script>

</body>
</html>