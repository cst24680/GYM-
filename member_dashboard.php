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
    if (isset($_POST['diet_choice'])) {
        $diet_choice = $_POST['diet_choice'];
        $active_section = "dietician";
    }

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
            // This is the message for double check-in
            $message = "❌ You have already checked in today.";
        }
        // Ensure the dashboard remains the active section to show the message
        $active_section = "dashboard";
    }
}


// Fetch attendance data for the member
$attendanceQuery = mysqli_query($conn, "SELECT check_in_time FROM attendance WHERE Mem_id = $member_id ORDER BY check_in_time DESC");
$attendanceRecords = [];
while ($row = mysqli_fetch_assoc($attendanceQuery)) {
    $attendanceRecords[] = $row;
}
$attendanceCount = count($attendanceRecords);

// Determine BMI & Age categories
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
    // fallback to use templates
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
        
        <div class="dashboard-grid">
            
            <div class="card profile-card">
                <h4>Your Profile</h4>
                
                <!-- REMOVED: The block that displayed the initials 'L' has been removed here. -->

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
            </div>

            <div class="card check-in-action-card">
                <h4>Daily Check-in</h4>
                <div class="check-in-container">
                    <form method="post">
                        <button type="submit" name="check_in_action" class="check-in-btn">
                            <i class="fas fa-sign-in-alt"></i> CHECK IN NOW
                        </button>
                    </form>
                    <!-- FIX: Display the message with clear color/border for visibility -->
                    <?php 
                        if ($message) {
                            // Determine style based on success (green) or error/warning (red)
                            $style = strpos($message, '✅') !== false 
                                ? 'color: #06D6A0; border: 1px solid #06D6A0;' // Success (Green)
                                : 'color: #E63946; border: 1px solid #E63946;'; // Error (Red)

                            echo "<div class='status-message' style='{$style} background: rgba(0,0,0,0.2); padding: 10px; border-radius: 5px; margin-top: 15px; font-weight: bold;'>$message</div>";
                        }
                    ?>
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
                <p><strong>BMI Category:</strong> <?php echo $dietPlan['BMI_Category']; ?></p>
                <p><strong>Age Category:</strong> <?php echo $dietPlan['Age_Category']; ?></p>
            <?php } ?>
            <pre><?php echo htmlspecialchars($dietPlan['Description']); ?></pre>
        <?php } else { ?>
            <p>No <?php echo ucfirst($diet_choice); ?> plan available for your profile yet.</p>
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
        <?php if ($message) echo "<p>$message</p>"; ?>
        <form method="post" id="feedback-form">
            <!-- Target Type -->
            <div class="form-group">
                <label for="target_type">Target Type:</label>
                <select name="target_type" id="target_type" required onchange="setTargetId()">
                    <option value="">-- Select --</option>
                    <?php if ($trainer) { ?>
                        <option value="Trainer">Trainer (<?php echo $trainer['Trainer_name']; ?>)</option>
                    <?php } ?>
                    <?php if ($dietician) { ?>
                        <option value="Dietician">Dietician (<?php echo $dietician['Dietician_name']; ?>)</option>
                    <?php } ?>
                    <option value="Gym">Gym</option>
                </select>
            </div>

            <input type="hidden" id="target_id" name="target_id" value="">

            <!-- Rating -->
            <div class="form-group">
                <label for="rating">Rating (1-5):</label>
                <input type="number" name="rating" id="rating" min="1" max="5" required>
            </div>

            <!-- Comments -->
            <div class="form-group">
                <label for="comments">Comments:</label>
                <textarea name="comments" id="comments" rows="4" cols="50" placeholder="Write your feedback..."></textarea>
            </div>

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
        
        // --- UPDATED FIX: Display the workout NOTES instead of the TYPE ---
        let workoutContent = '';
        if (dayEvents.length > 0) {
            // Display the Notes/Description of the first event found
            const firstWorkoutNotes = dayEvents[0].Notes || dayEvents[0].Workout_type; // Fallback to type if notes are missing
            // Trim to fit the small box
            const displayNotes = firstWorkoutNotes.length > 15 ? firstWorkoutNotes.substring(0, 12) + '...' : firstWorkoutNotes; 
            workoutContent = `<div class="workout-label" title="${firstWorkoutNotes}">${displayNotes}</div>`;
        }
        // --- END UPDATED FIX ---

        calendar.innerHTML += `
            <div class="day ${classes.trim()}" 
                 data-date="${dateStr}" 
                 onclick="showModal('${dateStr}')">
                <div class="date">${d}</div>
                ${workoutContent}
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

<div id="eventModal" class="modal">
   <div class="modal-content">
   <span class="close" onclick="closeModal()">&times;</span>
   <h3 id="modalDate"></h3>
   <div id="modalEvents"></div>
   </div>
</div>

</body>
</html>
