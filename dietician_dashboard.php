<?php
session_start();
include "db.php";
include "helpers.php";

if (!isset($_SESSION['Dietician_id'])) {
    header("Location: login.php");
    exit();
}

$dietician_id = $_SESSION['Dietician_id'];
$flash = "";

// Handle update OR create action
if (isset($_POST['update_plan'])) {
    $plan_id = (int)$_POST['plan_id'];
    $plan_name = mysqli_real_escape_string($conn, $_POST['plan_name']);
    $diet_type = mysqli_real_escape_string($conn, $_POST['diet_type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    if ($plan_id > 0) {
        // This is an UPDATE for an existing plan
        $sql = "UPDATE diet_plans 
                SET Plan_name='$plan_name', Diet_type='$diet_type', Description='$description'
                WHERE Diet_plan_id=$plan_id AND Dietician_id=$dietician_id";
        
        if (mysqli_query($conn, $sql)) {
            $flash = "✅ Plan updated successfully!";
        } else {
            $flash = "❌ Error: " . mysqli_error($conn);
        }
    } else {
        // This is an INSERT for a new plan
        $mem_id = (int)$_POST['mem_id']; // Get the member ID from the form
        
        // Find member's categories to save with the plan
        $mem_res = mysqli_query($conn, "SELECT Goal_type, BMI, Mem_age FROM member_registration WHERE Mem_id = $mem_id LIMIT 1");
        $mem_data = mysqli_fetch_assoc($mem_res);
        $bmi_cat = getBMICategory($mem_data['BMI']);
        $age_cat = getAgeCategory($mem_data['Mem_age']);

        $sql = "INSERT INTO diet_plans (Mem_id, Dietician_id, Plan_name, Diet_type, Description, BMI_Category, Age_Category)
                VALUES ($mem_id, $dietician_id, '$plan_name', '$diet_type', '$description', '$bmi_cat', '$age_cat')";

        if (mysqli_query($conn, $sql)) {
            $flash = "✅ Plan created successfully for the member!";
        } else {
            $flash = "❌ Error: " . mysqli_error($conn);
        }
    }
}

// Fetch dietician details
$dietician = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM dietician WHERE Dietician_id = $dietician_id"));
$dietician['Email'] = $dietician['Email'] ?? 'N/A';

// Load predefined templates
$templates = [];
$templates_res = mysqli_query($conn, "SELECT * FROM diet_templates ORDER BY Goal, BMI_Category, Age_Category");
while ($t = mysqli_fetch_assoc($templates_res)) $templates[] = $t;

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
    $row['calculated_bmi_cat'] = getBMICategory($row['BMI']);
    $row['calculated_age_cat'] = getAgeCategory($row['Mem_age']);
    $members[] = $row;
}

// Load feedback
$feedback = [];
$feedback_res = mysqli_query($conn, "
    SELECT f.feedback_id, m.Mem_name, f.rating, f.comments, f.created_at
    FROM feedback f
    JOIN member_registration m ON f.Mem_id = m.Mem_id
    WHERE f.target_type = 'Dietician' AND f.target_id = $dietician_id
    ORDER BY f.created_at DESC
");
while ($fb = mysqli_fetch_assoc($feedback_res)) $feedback[] = $fb;

// Set default active section
$active_section = 'dashboard';
if ($flash) $active_section = 'edit'; // If we just saved a plan, show the edit tab
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dietician Dashboard</title>
    <!-- REMOVED Font Awesome Link -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dietician.css">
    <style>
        .section { display: none; }
        .section.active { display: block; }
        details.plan-item[open] summary::after { content: "▲"; float:right; }
        details.plan-item summary::after { content: "▼"; float:right; }
        
        /* This style is now in dietician.css, but kept as fallback */
        .flash {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #4caf50;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 999;
            opacity: 1;
            transition: opacity 0.5s ease;
        }
    </style>
</head>
<body onload="setActiveSection('<?php echo $active_section; ?>')">

<div class="sidebar">
    <h2>Dietician Panel</h2>
    <!-- UPDATED: Replaced icons with Emojis -->
    <a href="#" class="menu-link active" data-section="dashboard" onclick="showSection('dashboard', this)">🏠 Dashboard</a>
    <a href="#" class="menu-link" data-section="members" onclick="showSection('members', this)">👥 Members</a>
    <a href="#" class="menu-link" data-section="edit" onclick="showSection('edit', this)">✏️ Edit Plans</a>
    <a href="#" class="menu-link" data-section="templates" onclick="showSection('templates', this)">📚 Predefined Plans</a>
    <a href="#" class="menu-link" data-section="feedback" onclick="showSection('feedback', this)">💬 View Feedback</a>
    <a class="logout" href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <?php if ($flash): ?><div class="flash" id="flash"><?php echo $flash; ?></div><?php endif; ?>

    <div class="header" id="dashboard-header" style="display: none;">
        <div class="greeting">
            <h1>Welcome, <?php echo htmlspecialchars($dietician['Dietician_name']); ?>!</h1>
            <p><?php echo date("l, M j, Y"); ?></p>
        </div>
    </div>

    <div class="section box active" id="dashboard">
        <h2>Your Profile Details</h2>
        <div class="profile-details-list">
            <!-- UPDATED: Replaced icons with Emojis -->
            <p><strong>📧 Email:</strong> <?php echo $dietician['Email']; ?></p>
            <p><strong>📞 Phone:</strong> <?php echo $dietician['Dietician_phno']; ?></p>
            <p><strong>✅ Status:</strong> <?php echo $dietician['Dietician_status']; ?></p>
        </div>
    </div>

    <div class="section box" id="members">
        <h2>Members Assigned to You</h2>
        <table>
            <tr><th>Name</th><th>Email</th><th>Goal</th><th>BMI</th></tr>
            <?php foreach ($members as $row): ?>
            <tr>
                <td><?php echo $row['Mem_name']; ?></td>
                <td><?php echo $row['Mem_email']; ?></td>
                <td><?php echo $row['Goal_type']; ?></td>
                
                <td><?php echo $row['BMI']; ?> (<?php echo $row['calculated_bmi_cat']; ?>)</td>
                
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="section box" id="edit">
        <h2>Edit Member Plans</h2>
        <label>Select Member:</label>
        <select id="editMemberSelect" onchange="showMemberPlan()">
            <option value="">-- Select a member to begin --</option>
            <?php foreach ($members as $row): ?>
                <option value="<?php echo $row['Mem_id']; ?>"><?php echo $row['Mem_name']; ?> (<?php echo $row['Mem_email']; ?>)</option>
            <?php endforeach; ?>
        </select>

        <div id="editPlansContainer">
            </div>
    </div>

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
// --- JAVASCRIPT DATA (from PHP) ---
const membersData = <?php
    $jsArr = [];
    foreach ($members as $m) {
        $jsArr[$m['Mem_id']] = [
            'Plan_name' => $m['Plan_name'] ?? '',
            'Diet_type' => $m['Diet_type'] ?? 'Veg',
            'Description' => $m['Description'] ?? '',
            'Diet_plan_id' => $m['Diet_plan_id'] ?? 0,
            'Goal_type' => $m['Goal_type'],
            'calculated_bmi_cat' => $m['calculated_bmi_cat'],
            'calculated_age_cat' => $m['calculated_age_cat']
        ];
    }
    echo json_encode($jsArr);
?>;

const allTemplates = <?php echo json_encode($templates); ?>;

// --- JAVASCRIPT HELPER FUNCTIONS ---

// findTemplate now accepts dietType
function findTemplate(goal, bmiCat, ageCat, dietType) {
    // Find a template matching all four categories
    let found = allTemplates.find(t => 
        t.Goal === goal && 
        t.BMI_Category === bmiCat && 
        t.Age_Category === ageCat &&
        t.Diet_type === dietType // <-- New condition
    );
    // If no exact match, try finding one with just Goal and DietType
    if (!found) {
        found = allTemplates.find(t => 
            t.Goal === goal &&
            t.Diet_type === dietType
        );
    }
    return found; // Returns the template object or undefined
}

// This function triggers when you change the Diet Type dropdown
function changeDietTemplate(selectElement) {
    const form = selectElement.closest('form');
    if (!form) return;

    const newDietType = selectElement.value;
    const memberId = form.querySelector('input[name="mem_id"]').value;
    
    // If this is not a member form (e.g. template editor), do nothing
    if (!memberId || !membersData[memberId]) return;

    const member = membersData[memberId];
    
    // Find a new template based on the new diet type
    const newTemplate = findTemplate(
        member.Goal_type,
        member.calculated_bmi_cat,
        member.calculated_age_cat,
        newDietType
    );

    const planNameInput = form.querySelector('input[name="plan_name"]');
    const descriptionTextarea = form.querySelector('textarea[name="description"]');

    if (newTemplate) {
        // Update the form fields with the new template's info
        planNameInput.value = newTemplate.Goal;
        descriptionTextarea.value = newTemplate.Description;
    } else {
        // No template found, clear the fields
        descriptionTextarea.value = `No template found for ${member.Goal_type}, ${newDietType}, ${member.calculated_bmi_cat}, ${member.calculated_age_cat}.`;
    }
}

// Show section
function showSection(sectionId, clickedElement) {
    // Update URL hash
    window.location.hash = sectionId;

    document.querySelectorAll('.section.box').forEach(sec => sec.classList.remove('active'));
    document.querySelectorAll('.sidebar a.menu-link').forEach(link => link.classList.remove('active'));
    
    const section = document.getElementById(sectionId);
    if (section) section.classList.add('active');
    if (clickedElement) clickedElement.classList.add('active');
    
    const header = document.getElementById("dashboard-header");
    if (header) header.style.display = (sectionId === "dashboard") ? "block" : "none";
    
    if (sectionId === 'edit') {
        showMemberPlan(); // Load default view for "Edit Plans"
    }
}

// Set active section based on hash or default
function setActiveSection(defaultId) {
    let targetId = window.location.hash.substring(1);
    if (!targetId || !document.getElementById(targetId)) {
        targetId = defaultId;
    }

    const initialSection = document.getElementById(targetId);
    const initialLink = document.querySelector(`.menu-link[data-section="${targetId}"]`);

    showSection(targetId, initialLink);
}

// HEAVILY MODIFIED: showMemberPlan()
function showMemberPlan() {
    const memberId = document.getElementById('editMemberSelect').value;
    const plansContainer = document.getElementById('editPlansContainer');
    plansContainer.innerHTML = ''; // Clear the container

    if (!memberId) {
        // --- Mode 1: No Member Selected ---
        // Do nothing, container is cleared.
        return;
    }

    const plan = membersData[memberId];
    let planData = {
        plan_id: 0,
        mem_id: memberId,
        plan_name: "",
        diet_type: "Veg",
        description: ""
    };

    if (plan && plan.Diet_plan_id > 0) {
        // --- Mode 2: Member Selected & Has a Saved Plan ---
        planData.plan_id = plan.Diet_plan_id;
        planData.plan_name = plan.Plan_name;
        planData.diet_type = plan.Diet_type;
        planData.description = plan.Description;
        
    } else if (plan) {
        // --- Mode 3: Member Selected & NO Saved Plan (Load Template) ---
        // Default to loading the 'Veg' template first
        const template = findTemplate(
            plan.Goal_type, 
            plan.calculated_bmi_cat, 
            plan.calculated_age_cat, 
            'Veg' // Default to Veg
        );
        
        if (template) {
            planData.plan_name = template.Goal; // Use template goal as plan name
            planData.diet_type = template.Diet_type;
            planData.description = template.Description;
        } else {
            // No template found, form will be blank
            planData.plan_name = plan.Goal_type; // Default to member's goal
            planData.description = "No template found. Please create a plan manually.";
        }
    }
    
    // --- Build the HTML for Mode 2 or 3 ---
    // Safely escape backticks and dollar signs for template literals
    const safeDescription = planData.description.replace(/`/g, '\\`').replace(/\$/g, '\\$');
    plansContainer.innerHTML = `
    <div class="box edit-plan-box" style="margin-top: 20px;">
        <form method="post">
            <input type="hidden" name="plan_id" value="${planData.plan_id}">
            <input type="hidden" name="mem_id" value="${planData.mem_id}">
            
            <label>Plan Name</label>
            <input type="text" name="plan_name" value="${planData.plan_name}" required>
            
            <label>Diet Type</label>
            <select name="diet_type" required onchange="changeDietTemplate(this)">
                <option value="Veg" ${planData.diet_type == 'Veg' ? 'selected' : ''}>Veg</option>
                <option value="Non-Veg" ${planData.diet_type == 'Non-Veg' ? 'selected' : ''}>Non-Veg</option>
                </select>
            
            <label>Diet Instructions</label>
            <textarea name="description" rows="6">${safeDescription}</textarea>
            
            <button class="btn" type="submit" name="update_plan">
                ${planData.plan_id > 0 ? 'Save Changes' : 'Create Plan for Member'}
            </button>
        </form>
    </div>`;
}

// Auto-close other details in Predefined Plans
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('details.plan-item').forEach(d => {
        d.addEventListener('click', function(e){
            document.querySelectorAll('details.plan-item').forEach(dd => {
                if(dd!==d) dd.removeAttribute('open');
            });
        });
    });

    // Set active section based on hash or default
    setActiveSection('dashboard');

    // Flash fade out after 4 seconds
    const flash = document.getElementById('flash');
    if(flash){
        setTimeout(()=>{ flash.style.opacity = 0; setTimeout(()=>flash.remove(),500); }, 4000);
    }
});

// Handle hash changes
window.addEventListener('hashchange', () => {
    setActiveSection('dashboard');
});
</script>
</body>
</html>