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
        if (stripos($trim, "BMI < 18.5") !== false) {
            $current_block = "under";
        } elseif (stripos($trim, "BMI 18.5-24.9") !== false) {
            $current_block = "normal";
        } elseif (stripos($trim, "BMI ≥ 25") !== false || stripos($trim, "BMI >=") !== false) {
            $current_block = "over";
        }
        if (($current_block == "under" && $bmi < 18.5) ||
            ($current_block == "normal" && $bmi >= 18.5 && $bmi < 25) ||
            ($current_block == "over" && $bmi >= 25)) {
            $selected[] = $trim;
        }
    }
    return implode("\n", $selected);
}

// Handle assign action
if (isset($_POST['assign_plan'])) {
    $member_id = (int)$_POST['member_id'];
    $template_id = (int)$_POST['template_id'];

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
    <link rel="stylesheet" href="dietician.css">
    <style>
        /* Feedback table style (same as trainer) */
        #feedback table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        #feedback th, #feedback td {
            border: 1px solid #444;
            padding: 10px;
            text-align: left;
        }
        #feedback th {
            background-color: #111;
            color: orange;
        }
        #feedback tr:nth-child(even) {
            background-color: #222;
        }
    </style>
    <script>
        function showSection(id) {
            let sections = document.querySelectorAll(".section");
            sections.forEach(sec => sec.style.display = "none");
            document.getElementById(id).style.display = "block";
        }
        window.onload = () => showSection("dashboard");
    </script>
</head>
<body>
<div class="sidebar">
    <h2>Dietician Panel</h2>
    <a href="#" onclick="showSection('dashboard')">Dashboard</a>
    <a href="#" onclick="showSection('members')">Members</a>
    <a href="#" onclick="showSection('assign')">Assign Plans</a>
    <a href="#" onclick="showSection('edit')">Edit Plans</a>
    <a href="#" onclick="showSection('templates')">Predefined Plans</a>
    <a href="#" onclick="showSection('feedback')">View Feedback</a>
    <a class="logout" href="logout.php">Logout</a>
</div>

<div class="main">
    <?php if ($flash): ?><div class="flash"><?php echo $flash; ?></div><?php endif; ?>

    <!-- DASHBOARD -->
    <div class="section" id="dashboard">
        <h1>Welcome, <?php echo $dietician['Dietician_name']; ?>!</h1>
        <div class="box">
            <h2>Your Profile</h2>
            <p><b>Email:</b> <?php echo $dietician['Email']; ?></p>
            <p><b>Phone:</b> <?php echo $dietician['Dietician_phno']; ?></p>
            <p><b>Status:</b> <?php echo $dietician['Dietician_status']; ?></p>
        </div>
    </div>

    <!-- MEMBERS -->
    <div class="section" id="members">
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
    <div class="section" id="assign">
        <h2>Assign Diet Plan</h2>
        <form method="post">
            <label>Member:</label>
            <select id="memberSelect" name="member_id" required onchange="filterPlans()">
                <option value="">-- Select Member --</option>
                <?php foreach ($members as $m): 
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

            <button type="submit" name="assign_plan">Assign</button>
        </form>
    </div>

    <!-- EDIT PLANS -->
    <div class="section" id="edit">
        <h2>Edit Member Plans</h2>
        <?php foreach ($members as $row): ?>
            <?php if ($row['Diet_plan_id']): ?>
            <div class="box">
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
                    <button class="btn" type="submit" name="update_plan">Save</button>
                </form>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- PREDEFINED -->
    <div class="section" id="templates">
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
    <div class="section" id="feedback">
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
function filterPlans() {
    let member = document.querySelector("#memberSelect option:checked");
    if (!member.value) return;

    let goal = member.dataset.goal;
    let bmi = member.dataset.bmi;
    let age = member.dataset.age;

    document.querySelectorAll("#planSelect option").forEach(opt => {
        if (!opt.value) return;
        let matches = (opt.dataset.goal === goal &&
                       opt.dataset.bmi === bmi &&
                       opt.dataset.age === age);
        opt.style.display = matches ? "block" : "none";
    });

    document.getElementById("planSelect").value = "";
}
</script>
</body>
</html>
