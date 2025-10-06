<?php
session_start();
include "db.php";

if (!isset($_SESSION['Dietician_id'])) {
    header("Location: login.php");
    exit();
}

$dietician_id = $_SESSION['Dietician_id'];
$flash = "";

// --- Function for BMI filter ---
function getPlanForBMI($description, $bmi) {
    $lines = explode("\n", $description);
    $selected = [];
    $current_block = "";

    foreach ($lines as $line) {
        $trim = trim($line);
        // Only include lines within the matching BMI block
        if (stripos($trim, "BMI < 18.5") !== false) {
            $current_block = "under";
        } elseif (stripos($trim, "BMI 18.5-24.9") !== false) {
            $current_block = "normal";
        } elseif (stripos($trim, "BMI >= 25") !== false) {
            $current_block = "over";
        }
        
        // If the line is part of a block AND the block matches the member's BMI
        $match = false;
        if ($current_block == "under" && $bmi < 18.5) {
            $match = true;
        } elseif ($current_block == "normal" && $bmi >= 18.5 && $bmi < 25) {
            $match = true;
        } elseif ($current_block == "over" && $bmi >= 25) {
            $match = true;
        }
        
        // Only add non-header, relevant lines
        if ($match && !preg_match('/^BMI/i', $trim)) {
            $selected[] = $trim;
        }
    }
    return implode("\n", $selected);
}

// Handle assign action
if (isset($_POST['assign_plan'])) {
    $member_id = (int)$_POST['member_id'];
    $template_id = (int)$_POST['template_id'];

    // Find if member already has a plan from this dietician
    $check_plan_sql = "SELECT Diet_plan_id FROM diet_plans WHERE Mem_id = $member_id AND Dietician_id = $dietician_id ORDER BY Diet_plan_id DESC LIMIT 1";
    $existing_plan = mysqli_fetch_assoc(mysqli_query($conn, $check_plan_sql));

    // If an existing plan exists, delete it first to ensure the latest one is applied
    if ($existing_plan) {
        mysqli_query($conn, "DELETE FROM diet_plans WHERE Diet_plan_id = {$existing_plan['Diet_plan_id']}");
    }


    $sql = "INSERT INTO diet_plans (Dietician_id, Mem_id, Plan_name, Diet_type, Description, BMI_Category, Age_Category)
             SELECT $dietician_id, $member_id, Goal, Diet_type, Description, BMI_Category, Age_Category
             FROM diet_templates WHERE Template_id = $template_id";

    if (mysqli_query($conn, $sql)) {
        $flash = "✅ Plan assigned successfully!";
    } else {
        $flash = "❌ Error: " . mysqli_error($conn);
    }
}

// Handle update action
if (isset($_POST['update_plan'])) {
    $plan_id = (int)$_POST['plan_id'];
    $plan_name = mysqli_real_escape_string($conn, $_POST['plan_name']);
    $diet_type = mysqli_real_escape_string($conn, $_POST['diet_type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "UPDATE diet_plans 
             SET Plan_name='$plan_name', Diet_type='$diet_type', Description='$description'
             WHERE Diet_plan_id=$plan_id AND Mem_id != 0 AND Dietician_id=$dietician_id";

    if (mysqli_query($conn, $sql)) {
        $flash = "✅ Plan updated successfully!";
    } else {
        $flash = "❌ Error: " . mysqli_error($conn);
    }
}

// Fetch dietician details
$dietician = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM dietician WHERE Dietician_id = $dietician_id"));
$dietician['Email'] = $dietician['Email'] ?? 'N/A'; // Add fallback for Email if not in DB

// Load predefined templates
$templates = [];
$templates_res = mysqli_query($conn, "SELECT * FROM diet_templates ORDER BY Goal, BMI_Category, Age_Category");
while ($t = mysqli_fetch_assoc($templates_res)) {
    $templates[] = $t;
}

// Load members assigned to this dietician
$members = [];
$members_res = mysqli_query($conn, "
    SELECT m.Mem_id, m.Mem_name, m.Mem_email, m.Mem_age, m.Goal_type, m.BMI,
            dp.Diet_plan_id, dp.Plan_name, dp.Diet_type, dp.Description
    FROM member_registration m
    LEFT JOIN diet_plans dp ON dp.Diet_plan_id = (
        SELECT MAX(d2.Diet_plan_id) FROM diet_plans d2 WHERE d2.Mem_id = m.Mem_id
    )
    WHERE m.Dietician_id = $dietician_id
");
while ($row = mysqli_fetch_assoc($members_res)) {
    $members[] = $row;
}

// Load feedback for this dietician
$feedback = [];
$feedback_res = mysqli_query($conn, "
    SELECT f.feedback_id, m.Mem_name, f.rating, f.comments, f.created_at
    FROM feedback f
    JOIN member_registration m ON f.Mem_id = m.Mem_id
    WHERE f.target_type = 'Dietician' AND f.target_id = $dietician_id
    ORDER BY f.created_at DESC
");
while ($fb = mysqli_fetch_assoc($feedback_res)) {
    $feedback[] = $fb;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dietician Dashboard</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Fonts used in Trainer/Member Dashboards -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dietician.css">
    
    <style>
        /* CSS FIXES TO ENFORCE SINGLE-VIEW NAVIGATION */
        /* All sections are hidden by default */
        .section { display: none; }
        /* Only the active section is shown */
        .section.active { display: block; }
        
        /* FIX for Assign Plans Form Layout */
        #assign form {
            display: flex;
            flex-direction: column;
            gap: 10px; /* Reduced vertical gap for compactness */
        }
    </style>
</head>
<body onload="showSection('dashboard', document.querySelector('.sidebar a[data-section=\"dashboard\"]'))">
<div class="sidebar">
    <h2>Dietician Panel</h2>
    <!-- Updated links to use onclick and pass the element/ID -->
    <a href="#" class="menu-link active" data-section="dashboard" onclick="showSection('dashboard', this)"><i class="fas fa-home"></i> Dashboard</a>
    <a href="#" class="menu-link" data-section="members" onclick="showSection('members', this)"><i class="fas fa-users"></i> Members</a>
    <a href="#" class="menu-link" data-section="assign" onclick="showSection('assign', this)"><i class="fas fa-utensils"></i> Assign Plans</a>
    <a href="#" class="menu-link" data-section="edit" onclick="showSection('edit', this)"><i class="fas fa-edit"></i> Edit Plans</a>
    <a href="#" class="menu-link" data-section="templates" onclick="showSection('templates', this)"><i class="fas fa-book"></i> Predefined Plans</a>
    <a href="#" class="menu-link" data-section="feedback" onclick="showSection('feedback', this)"><i class="fas fa-comments"></i> View Feedback</a>
    <a class="logout" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main">
    <?php if ($flash): ?><div class="flash"><?php echo $flash; ?></div><?php endif; ?>

    <!-- HEADER (Visibility controlled by showSection function) -->
    <div class="header" id="dashboard-header" style="display: none;">
        <div class="greeting">
            <h1>Welcome, <?php echo htmlspecialchars($dietician['Dietician_name']); ?>!</h1>
            <p><?php echo date("l, M j, Y"); ?></p>
        </div>
    </div>

    <!-- DASHBOARD -->
    <div class="section box active" id="dashboard">
        <h3>Your Profile Details</h3>
        <div class="profile-details-list">
            <p><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo $dietician['Email']; ?></p>
            <p><strong><i class="fas fa-phone"></i> Phone:</strong> <?php echo $dietician['Dietician_phno']; ?></p>
            <p><strong><i class="fas fa-check-circle"></i> Status:</strong> <?php echo $dietician['Dietician_status']; ?></p>
        </div>
    </div>

    <!-- MEMBERS -->
    <div class="section box" id="members">
        <h2>Members Assigned to You</h2>
        <table>
            <tr><th>Name</th><th>Email</th><th>Goal</th><th>BMI</th><th>Current Plan</th></tr>
            <?php foreach ($members as $row): ?>
            <tr>
                <td><?php echo $row['Mem_name']; ?></td>
                <td><?php echo $row['Mem_email']; ?></td>
                <td><?php echo $row['Goal_type']; ?></td>
                <td><?php echo $row['BMI']; ?></td>
                <td>
                    <?php 
                        if ($row['Plan_name']) {
                            echo "<b>".$row['Plan_name']."</b> (".$row['Diet_type'].")<br>";
                            echo nl2br(getPlanForBMI($row['Description'], $row['BMI']));
                        } else echo "No plan assigned";
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- ASSIGN PLANS -->
    <div class="section box" id="assign">
        <h2>Assign Diet Plan</h2>
        <form method="post">
            <label>Member:</label>
            <select id="memberSelect" name="member_id" required onchange="filterPlans()">
                <option value="">-- Select Member --</option>
                <?php foreach ($members as $m): 
                    // Calculate BMI & Age Category for filtering
                    $bmiCat = ($m['BMI'] < 18.5) ? "Underweight" : (($m['BMI'] < 25) ? "Normal" : "Overweight");
                    $ageCat = ($m['Mem_age'] < 25) ? "Young" : (($m['Mem_age'] <= 40) ? "Adult" : "Senior");
                ?>
                    <option value="<?php echo $m['Mem_id']; ?>" 
                            data-goal="<?php echo $m['Goal_type']; ?>" 
                            data-bmi="<?php echo $bmiCat; ?>" 
                            data-age="<?php echo $ageCat; ?>">
                        <?php echo $m['Mem_name']; ?> (BMI: <?php echo $m['BMI']; ?>, Goal: <?php echo $m['Goal_type']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Predefined Plan:</label>
            <select id="planSelect" name="template_id" required>
                <option value="">-- Select Plan --</option>
                <?php foreach ($templates as $t): ?>
                    <option value="<?php echo $t['Template_id']; ?>" 
                            data-goal="<?php echo $t['Goal']; ?>" 
                            data-bmi="<?php echo $t['BMI_Category']; ?>" 
                            data-age="<?php echo $t['Age_Category']; ?>">
                        <?php echo $t['Goal']." - ".$t['BMI_Category']." - ".$t['Age_Category']." (".$t['Diet_type'].")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="assign_plan" class="btn">Assign Plan</button>
        </form>
    </div>

    <!-- EDIT PLANS -->
    <div class="section box" id="edit">
        <h2>Edit Member Plans</h2>
        <?php foreach ($members as $row): ?>
            <?php if ($row['Diet_plan_id']): ?>
            <div class="box edit-plan-box">
                <h3><?php echo $row['Mem_name']; ?> (<?php echo $row['Mem_email']; ?>)</h3>
                <form method="post">
                    <input type="hidden" name="plan_id" value="<?php echo $row['Diet_plan_id']; ?>">
                    <label>Plan Name</label>
                    <input type="text" name="plan_name" value="<?php echo $row['Plan_name']; ?>" required>
                    <label>Diet Type</label>
                    <select name="diet_type" required>
                        <option value="Veg" <?php if ($row['Diet_type']=="Veg") echo "selected"; ?>>Veg</option>
                        <option value="Non-Veg" <?php if ($row['Diet_type']=="Non-Veg") echo "selected"; ?>>Non-Veg</option>
                        <option value="Mixed" <?php if ($row['Diet_type']=="Mixed") echo "selected"; ?>>Mixed</option>
                    </select>
                    <label>Diet Instructions</label>
                    <textarea name="description" rows="6"><?php echo $row['Description']; ?></textarea>
                    <button class="btn" type="submit" name="update_plan">Save Changes</button>
                </form>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- PREDEFINED -->
    <div class="section box" id="templates">
        <h2>Predefined Diet Plans</h2>
        <div class="plans">
            <?php foreach ($templates as $t): ?>
                <details class="plan-item">
                    <summary><b><?php echo $t['Goal']; ?></b> (<?php echo $t['Diet_type']; ?>) - <?php echo $t['BMI_Category']; ?> / <?php echo $t['Age_Category']; ?></summary>
                    <p><?php echo nl2br($t['Description']); ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- FEEDBACK -->
    <div class="section box" id="feedback">
        <h2>Feedback from Members</h2>
        <?php if (!empty($feedback)): ?>
            <table>
                <tr>
                    <th>Member Name</th>
                    <th>Rating</th>
                    <th>Comments</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($feedback as $fb): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fb['Mem_name']); ?></td>
                        <td><?php echo $fb['rating']; ?>/5</td>
                        <td><?php echo htmlspecialchars($fb['comments']); ?></td>
                        <td><?php echo $fb['created_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No feedback received yet.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    // --- Single-View Navigation Function ---
    function showSection(sectionId, clickedElement) {
        // 1. Hide all content sections and remove 'active' class
        document.querySelectorAll('.section.box').forEach(sec => {
            sec.classList.remove('active');
        });
        document.querySelectorAll('.sidebar a.menu-link').forEach(link => {
            link.classList.remove('active');
        });

        // 2. Show the selected content section by adding 'active' class
        const section = document.getElementById(sectionId);
        if (section) {
            section.classList.add('active');
        }

        // 3. Set the clicked sidebar link to active
        if (clickedElement) {
            clickedElement.classList.add('active');
        }
        
        // 4. Handle visibility of the main header (Only show on Dashboard)
        const header = document.getElementById("dashboard-header");
        if (header) {
             header.style.display = (sectionId === "dashboard") ? "block" : "none";
        }
    }


    // --- Filter Logic for Assign Plans ---
    function filterPlans() {
        const member = document.querySelector("#memberSelect option:checked");
        if (!member || !member.value) return;

        const goal = member.dataset.goal;
        const bmi = member.dataset.bmi;
        const age = member.dataset.age;

        document.querySelectorAll("#planSelect option").forEach(opt => {
            if (!opt.value) return; // Skip the '-- Select Plan --' option

            const matches = (opt.dataset.goal === goal &&
                             opt.dataset.bmi === bmi &&
                             opt.dataset.age === age);
            
            // Show or hide based on match
            opt.style.display = matches ? "block" : "none";
        });

        // Reset plan selection
        document.getElementById("planSelect").value = "";
    }

    // Set initial active state on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Find the link that points to the first active section (or dashboard)
        const initialLink = document.querySelector('.sidebar a[data-section="dashboard"]');
        if (initialLink) {
             showSection('dashboard', initialLink);
        }
    });

</script>
</body>
</html>
