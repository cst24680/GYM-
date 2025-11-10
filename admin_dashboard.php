<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

include "db.php";

if (!isset($conn) || !$conn) {
    die("<h1>Database Connection Error!</h1><p>Please check your db.php file.</p>");
}

if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Helper function
function clean_input($conn, $data) {
    return mysqli_real_escape_string($conn, $data);
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Add / Edit Member
    if (isset($_POST['save_member'])) {
        $id = $_POST['id'] ?? '';
        $name = clean_input($conn, $_POST['name']);
        $email = clean_input($conn, $_POST['email']);
        $age = (int)($_POST['age'] ?? 0);
        $height = clean_input($conn, $_POST['height']); // in cm
        $weight = clean_input($conn, $_POST['weight']); // in kg
        $goal = clean_input($conn, $_POST['goal']);
        // Whitelist goal to match register page options
        $allowedGoals = ['Weight Loss','Muscle Gain','General Fitness'];
        if (!in_array($goal, $allowedGoals, true)) { $goal = 'General Fitness'; }
        $phone = clean_input($conn, $_POST['phone'] ?? '');
        $gender = clean_input($conn, $_POST['gender'] ?? '');
        $dob = clean_input($conn, $_POST['dob'] ?? '');
        $status = clean_input($conn, $_POST['status'] ?? 'Active');
        $trainerId = clean_input($conn, $_POST['trainer_id'] ?? '');
        $dieticianId = clean_input($conn, $_POST['dietician_id'] ?? '');

        // Compute BMI if height/weight present (height in cm to m)
        $bmi = 0;
        if (is_numeric($height) && is_numeric($weight) && (float)$height > 0) {
            $h = ((float)$height) / 100.0;
            if ($h > 0) { $bmi = round(((float)$weight) / ($h * $h), 2); }
        }

        // Derive plan name from goal selection
        $planName = $goal === 'Weight Loss' ? 'Weight Loss Plan' : ($goal === 'Muscle Gain' ? 'Muscle Gain Plan' : 'General Fitness Plan');

        if ($id) {
            $sql = "UPDATE member_registration SET 
                Mem_name='$name', Mem_email='$email', Mem_age=$age, 
                Height='$height', Weight='$weight', Goal_type='$goal',
                Mem_phno='$phone', Gender='$gender', Mem_dob='$dob',
                BMI=" . ($bmi ? $bmi : "NULL") . ", Plan_name=" . ($planName ? "'$planName'" : "NULL") . ",
                Mem_status='$status', Trainer_id=" . ($trainerId !== '' ? "'$trainerId'" : "NULL") . ",
                Dietician_id=" . ($dieticianId !== '' ? "'$dieticianId'" : "NULL") .
                " WHERE Mem_id='$id'";
            mysqli_query($conn, $sql);
        } else {
            // Default login password is lowercase first-name + '123'
            $nameBase = strtolower(preg_split('/\s+/', trim($name))[0] ?? 'user');
            $password = password_hash($nameBase . '123', PASSWORD_DEFAULT);

            $sql = "INSERT INTO member_registration 
                (Mem_name, Mem_email, Mem_age, Height, Weight, Goal_type, Mem_phno, Gender, Mem_dob, BMI, Plan_name, Mem_status, Trainer_id, Dietician_id, Mem_pass)
                VALUES ('$name','$email',$age,'$height','$weight','$goal',
                '$phone','$gender','$dob'," . ($bmi ? $bmi : "NULL") . "," . ($planName ? "'$planName'" : "NULL") . ",
                '" . ($status ?: 'Active') . "'," . ($trainerId !== '' ? "'$trainerId'" : "NULL") . "," . ($dieticianId !== '' ? "'$dieticianId'" : "NULL") . ",
                '$password')";
            mysqli_query($conn, $sql);

            // Also create a row in login table (if your app uses it)
            mysqli_query($conn, "INSERT INTO login (Email, Password, User_type) VALUES ('$email','$password','member')");
        }
    }

    // Add / Edit Trainer
    if (isset($_POST['save_trainer'])) {
        $id = $_POST['id'] ?? '';
        $name = clean_input($conn, $_POST['name']);
        $phone = clean_input($conn, $_POST['phone']);
        $gender = clean_input($conn, $_POST['gender']);
        $speciality = clean_input($conn, $_POST['speciality']);
        $status = clean_input($conn, $_POST['status']);

        if ($id) {
            $sql = "UPDATE trainer SET 
                Trainer_name='$name', Trainer_phno='$phone', Trainer_gender='$gender', 
                Speciality='$speciality', Trainer_status='$status' WHERE Trainer_id='$id'";
            mysqli_query($conn, $sql);
        } else {
            $sql = "INSERT INTO trainer 
                (Trainer_name, Trainer_phno, Trainer_gender, Speciality, Trainer_status)
                VALUES ('$name','$phone','$gender','$speciality','$status')";
            mysqli_query($conn, $sql);

            // Create login with default password = lowercase first-name + 123
            $nameBase = strtolower(preg_split('/\s+/', trim($name))[0] ?? 'user');
            $password = password_hash($nameBase . '123', PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO login (Email, Password, User_type) VALUES ('$phone','$password','trainer')");
        }
    }

    // Add / Edit Dietician
    if (isset($_POST['save_dietician'])) {
        $id = $_POST['id'] ?? '';
        $name = clean_input($conn, $_POST['name']);
        $phone = clean_input($conn, $_POST['phone']);
        $status = clean_input($conn, $_POST['status']);

        if ($id) {
            $sql = "UPDATE dietician SET 
                Dietician_name='$name', Dietician_phno='$phone', Dietician_status='$status' 
                WHERE Dietician_id='$id'";
            mysqli_query($conn, $sql);
        } else {
            $sql = "INSERT INTO dietician 
                (Dietician_name, Dietician_phno, Dietician_status)
                VALUES ('$name','$phone','$status')";
            mysqli_query($conn, $sql);

            // Create login with default password = lowercase first-name + 123
            $nameBase = strtolower(preg_split('/\s+/', trim($name))[0] ?? 'user');
            $password = password_hash($nameBase . '123', PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO login (Email, Password, User_type) VALUES ('$phone','$password','dietician')");
        }
    }

    header("Location: admin_dashboard.php");
    exit();
}

// Delete actions
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && isset($_GET['type'])) {
    $id = (int)$_GET['id']; 
    $type = $_GET['type'];
    $table = "";
    $id_col = "";

    if ($type === 'member') {
        $table = "member_registration";
        $id_col = "Mem_id";
    }
    if ($type === 'trainer') {
        $table = "trainer";
        $id_col = "Trainer_id";
    } elseif ($type === 'dietician') {
        $table = "dietician";
        $id_col = "Dietician_id";
    }

    if ($table) {
        mysqli_query($conn, "DELETE FROM $table WHERE $id_col = $id");
    }
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch data
$members_res = mysqli_query($conn, "SELECT * FROM member_registration");
$trainers_res = mysqli_query($conn, "SELECT * FROM trainer");
$dieticians_res = mysqli_query($conn, "SELECT * FROM dietician");

// Fetch staff names for feedback
function fetch_target_names($conn) {
    $names = ['Trainer' => [], 'Dietician' => []];

    $res = mysqli_query($conn, "SELECT Trainer_id, Trainer_name FROM trainer");
    while ($row = mysqli_fetch_assoc($res)) {
        $names['Trainer'][$row['Trainer_id']] = $row['Trainer_name'];
    }

    $res = mysqli_query($conn, "SELECT Dietician_id, Dietician_name FROM dietician");
    while ($row = mysqli_fetch_assoc($res)) {
        $names['Dietician'][$row['Dietician_id']] = $row['Dietician_name'];
    }
    return $names;
}

$target_names = fetch_target_names($conn);

// Feedback
$feedback_sql = "
    SELECT f.*, m.Mem_name
    FROM feedback f
    LEFT JOIN member_registration m ON f.Mem_id = m.Mem_id
    ORDER BY f.created_at DESC
";
$feedback_res = mysqli_query($conn, $feedback_sql);

// Contact Messages
$contact_sql = "SELECT * FROM contact ORDER BY sent_at DESC";
$contact_res = mysqli_query($conn, $contact_sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body onload="showSection('members')">

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="#" onclick="showSection('members')">Members</a>
    <a href="#" onclick="showSection('trainers')">Trainers</a>
    <a href="#" onclick="showSection('dieticians')">Dieticians</a>
    <a href="#" onclick="showSection('feedback')">Feedback</a>
    <a href="#" onclick="showSection('contact')">Contact Messages</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="content">
    <h1>Welcome, Admin</h1>

    <!-- Members Section -->
    <div id="members" class="section">
        <h2>Members</h2>
        <button onclick="clearMemberForm(); openModal('memberModal')">+ Add Member</button>
        <table>
            <tr>
                <th>Name</th><th>Email</th><th>Age</th><th>Height</th><th>Weight</th><th>Goal</th><th>Actions</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($members_res)): ?>
            <tr>
                <td><?= htmlspecialchars($row['Mem_name']) ?></td>
                <td><?= htmlspecialchars($row['Mem_email']) ?></td>
                <td><?= htmlspecialchars($row['Mem_age']) ?></td>
                <td><?= htmlspecialchars($row['Height']) ?></td>
                <td><?= htmlspecialchars($row['Weight']) ?></td>
                <td><?= htmlspecialchars($row['Goal_type']) ?></td>
                <td>
                    <button onclick='editMember(<?= json_encode($row) ?>)'>Edit</button>
                    <button class="delete-btn" onclick="showCustomConfirm(<?= $row['Mem_id'] ?>, 'member')">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Trainers Section -->
    <div id="trainers" class="section" style="display:none;">
        <h2>Trainers</h2>
        <button onclick="clearTrainerForm(); openModal('trainerModal')">+ Add Trainer</button>
        <table>
            <tr>
                <th>Name</th><th>Phone</th><th>Gender</th><th>Speciality</th><th>Status</th><th>Actions</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($trainers_res)): ?>
            <tr>
                <td><?= htmlspecialchars($row['Trainer_name']) ?></td>
                <td><?= htmlspecialchars($row['Trainer_phno']) ?></td>
                <td><?= htmlspecialchars($row['Trainer_gender']) ?></td>
                <td><?= htmlspecialchars($row['Speciality']) ?></td>
                <td><?= htmlspecialchars($row['Trainer_status']) ?></td>
                <td>
                    <button onclick='editTrainer(<?= json_encode($row) ?>)'>Edit</button>
                    <button class="delete-btn" onclick="showCustomConfirm(<?= $row['Trainer_id'] ?>, 'trainer')">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Dieticians Section -->
    <div id="dieticians" class="section" style="display:none;">
        <h2>Dieticians</h2>
        <button onclick="clearDieticianForm(); openModal('dieticianModal')">+ Add Dietician</button>
        <table>
            <tr>
                <th>Name</th><th>Phone</th><th>Status</th><th>Actions</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($dieticians_res)): ?>
            <tr>
                <td><?= htmlspecialchars($row['Dietician_name']) ?></td>
                <td><?= htmlspecialchars($row['Dietician_phno']) ?></td>
                <td><?= htmlspecialchars($row['Dietician_status']) ?></td>
                <td>
                    <button onclick='editDietician(<?= json_encode($row) ?>)'>Edit</button>
                    <button class="delete-btn" onclick="showCustomConfirm(<?= $row['Dietician_id'] ?>, 'dietician')">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Feedback Section -->
    <div id="feedback" class="section" style="display:none;">
        <h2>All Member Feedback</h2>
        <?php if ($feedback_res && mysqli_num_rows($feedback_res) > 0): ?>
            <table>
                <tr>
                    <th>Target Type</th><th>Target Name</th><th>Rating</th><th>Comments</th><th>Member Name</th><th>Date</th>
                </tr>
                <?php while ($fb = mysqli_fetch_assoc($feedback_res)): 
                    $target_name = 'Gym'; 
                    $target_id = $fb['target_id'];
                    if ($fb['target_type'] === 'Trainer' && isset($target_names['Trainer'][$target_id])) {
                        $target_name = $target_names['Trainer'][$target_id];
                    } elseif ($fb['target_type'] === 'Dietician' && isset($target_names['Dietician'][$target_id])) {
                        $target_name = $target_names['Dietician'][$target_id];
                    }
                ?>
                <tr>
                    <td><?= htmlspecialchars($fb['target_type']) ?></td>
                    <td><?= htmlspecialchars($target_name) ?></td>
                    <td><?= htmlspecialchars($fb['rating']) ?>/5</td>
                    <td style="max-width:300px"><?= htmlspecialchars($fb['comments']) ?></td>
                    <td><?= htmlspecialchars($fb['Mem_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($fb['created_at']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No feedback records found yet.</p>
        <?php endif; ?>
    </div>

    <!-- Contact Messages Section -->
    <div id="contact" class="section" style="display:none;">
        <h2>Contact Messages</h2>
        <?php if ($contact_res && mysqli_num_rows($contact_res) > 0): ?>
            <table>
                <tr><th>Date</th><th>Name</th><th>Email</th><th>Message</th></tr>
                <?php while ($msg = mysqli_fetch_assoc($contact_res)): ?>
                <tr>
                    <td><?= htmlspecialchars($msg['sent_at']) ?></td>
                    <td><?= htmlspecialchars($msg['sender_name']) ?></td>
                    <td><?= htmlspecialchars($msg['sender_email']) ?></td>
                    <td style="max-width:400px"><?= htmlspecialchars($msg['message']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No contact messages found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modals -->
<div id="memberModal" class="modal">
    <div class="modal-content">
        <h3>Member Form</h3>
        <form method="POST" class="form-grid">
            <input type="hidden" name="id" id="member_id">

            <div class="form-group"><label>Name:</label><input type="text" name="name" id="member_name" required></div>
            <div class="form-group"><label>Email:</label><input type="email" name="email" id="member_email" required></div>

            <div class="form-group"><label>Age:</label><input type="number" name="age" id="member_age" min="0" required></div>
            <div class="form-group"><label>Phone:</label><input type="text" name="phone" id="member_phone"></div>

            <div class="form-group"><label>Gender:</label>
                <select name="gender" id="member_gender">
                    <option value="">Select</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <input type="hidden" name="dob" id="member_dob">

            <div class="form-group"><label>Height (cm):</label><input type="text" name="height" id="member_height" required></div>
            <div class="form-group"><label>Weight (kg):</label><input type="text" name="weight" id="member_weight" required></div>

            <div class="form-group"><label>Goal:</label>
                <select name="goal" id="member_goal" required>
                    <option value="Weight Loss">Weight Loss</option>
                    <option value="Muscle Gain">Muscle Gain</option>
                    <option value="General Fitness">General Fitness</option>
                </select>
            </div>
            <div class="form-group"><label>Status:</label>
                <select name="status" id="member_status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>

            <div class="form-group"><label>Trainer (optional):</label>
                <select name="trainer_id" id="member_trainer_id">
                    <option value="">None</option>
                    <?php foreach ($target_names['Trainer'] as $tid => $tname): ?>
                        <option value="<?= htmlspecialchars($tid) ?>"><?= htmlspecialchars($tname) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Dietician (optional):</label>
                <select name="dietician_id" id="member_dietician_id">
                    <option value="">None</option>
                    <?php foreach ($target_names['Dietician'] as $did => $dname): ?>
                        <option value="<?= htmlspecialchars($did) ?>"><?= htmlspecialchars($dname) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="button-group full-row">
                <button type="button" onclick="closeModal('memberModal')">Cancel</button>
                <button type="submit" name="save_member">Save</button>
            </div>
        </form>
    </div>
    </div>
<div id="trainerModal" class="modal">
    <div class="modal-content">
        <h3>Trainer Form</h3>
        <form method="POST">
            <input type="hidden" name="id" id="trainer_id">
            <label>Name:</label><input type="text" name="name" id="trainer_name" required>
            <label>Phone:</label><input type="text" name="phone" id="trainer_phone" required>
            <label>Gender:</label><input type="text" name="gender" id="trainer_gender" required>
            <label>Speciality:</label><input type="text" name="speciality" id="trainer_speciality" required>
            <label>Status:</label><input type="text" name="status" id="trainer_status" required>
            <button type="submit" name="save_trainer">Save</button>
            <button type="button" onclick="closeModal('trainerModal')">Cancel</button>
        </form>
    </div>
</div>

<div id="dieticianModal" class="modal">
    <div class="modal-content">
        <h3>Dietician Form</h3>
        <form method="POST">
            <input type="hidden" name="id" id="dietician_id">
            <label>Name:</label><input type="text" name="name" id="dietician_name" required>
            <label>Phone:</label><input type="text" name="phone" id="dietician_phone" required>
            <label>Status:</label><input type="text" name="status" id="dietician_status" required>
            <button type="submit" name="save_dietician">Save</button>
            <button type="button" onclick="closeModal('dieticianModal')">Cancel</button>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <h3 class="text-red">Confirm Deletion</h3>
        <p class="message" id="confirmMessage">Are you sure you want to delete this record?</p>
        <div class="button-group">
            <button onclick="closeModal('confirmModal')">Cancel</button>
            <button class="delete" id="confirmDeleteButton">Delete</button>
        </div>
    </div>
</div>

<script>
// Show section
function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(s => s.style.display='none');
    document.getElementById(sectionId).style.display='block';
    document.querySelectorAll('.sidebar a').forEach(a => a.classList.remove('active'));
    document.querySelector('.sidebar a[onclick*="' + sectionId + '"]').classList.add('active');
}

function openModal(id){ document.getElementById(id).style.display='flex'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }

function showCustomConfirm(id,type){
    document.getElementById('confirmMessage').innerText=`Are you sure you want to permanently delete this ${type} (ID: ${id})?`;
    document.getElementById('confirmDeleteButton').onclick=()=>{ window.location.href=`?action=delete&id=${id}&type=${type}`; };
    openModal('confirmModal');
}

function editTrainer(data){
    document.getElementById('trainer_id').value=data.Trainer_id;
    document.getElementById('trainer_name').value=data.Trainer_name;
    document.getElementById('trainer_phone').value=data.Trainer_phno;
    document.getElementById('trainer_gender').value=data.Trainer_gender;
    document.getElementById('trainer_speciality').value=data.Speciality;
    document.getElementById('trainer_status').value=data.Trainer_status;
    openModal('trainerModal');
}

function editDietician(data){
    document.getElementById('dietician_id').value=data.Dietician_id;
    document.getElementById('dietician_name').value=data.Dietician_name;
    document.getElementById('dietician_phone').value=data.Dietician_phno;
    document.getElementById('dietician_status').value=data.Dietician_status;
    openModal('dieticianModal');
}

function editMember(data){
    document.getElementById('member_id').value=data.Mem_id;
    document.getElementById('member_name').value=data.Mem_name;
    document.getElementById('member_email').value=data.Mem_email;
    document.getElementById('member_age').value=data.Mem_age;
    document.getElementById('member_phone').value=data.Mem_phno || '';
    document.getElementById('member_gender').value=data.Gender || '';
    document.getElementById('member_dob').value=(data.Mem_dob || '').substring(0,10);
    document.getElementById('member_height').value=data.Height;
    document.getElementById('member_weight').value=data.Weight;
    document.getElementById('member_goal').value=data.Goal_type;
    document.getElementById('member_status').value=data.Mem_status || 'Active';
    document.getElementById('member_trainer_id').value=data.Trainer_id || '';
    document.getElementById('member_dietician_id').value=data.Dietician_id || '';
    openModal('memberModal');
}

function clearMemberForm(){
    document.getElementById('member_id').value='';
    document.getElementById('member_name').value='';
    document.getElementById('member_email').value='';
    document.getElementById('member_age').value='';
    document.getElementById('member_phone').value='';
    document.getElementById('member_gender').value='';
    document.getElementById('member_dob').value='';
    document.getElementById('member_height').value='';
    document.getElementById('member_weight').value='';
    document.getElementById('member_goal').value='';
    document.getElementById('member_status').value='Active';
    document.getElementById('member_trainer_id').value='';
    document.getElementById('member_dietician_id').value='';
}

function clearTrainerForm(){
    document.getElementById('trainer_id').value='';
    document.getElementById('trainer_name').value='';
    document.getElementById('trainer_phone').value='';
    document.getElementById('trainer_gender').value='';
    document.getElementById('trainer_speciality').value='';
    document.getElementById('trainer_status').value='';
}

function clearDieticianForm(){
    document.getElementById('dietician_id').value='';
    document.getElementById('dietician_name').value='';
    document.getElementById('dietician_phone').value='';
    document.getElementById('dietician_status').value='';
}

document.addEventListener('DOMContentLoaded',()=>{
    const hash=window.location.hash.substring(1);
    if(hash){ showSection(hash); } else{ showSection('members'); }

    // Auto-fill DOB when Age is entered
    const ageInput = document.getElementById('member_age');
    const dobInput = document.getElementById('member_dob');
    if (ageInput && dobInput) {
        const updateDobFromAge = () => {
            const age = parseInt(ageInput.value, 10);
            if (!isNaN(age) && age > 0 && age < 120) {
                const today = new Date();
                const dob = new Date(today.getFullYear() - age, today.getMonth(), today.getDate());
                const yyyy = dob.getFullYear();
                const mm = String(dob.getMonth() + 1).padStart(2, '0');
                const dd = String(dob.getDate()).padStart(2, '0');
                dobInput.value = `${yyyy}-${mm}-${dd}`;
            }
        };
        ageInput.addEventListener('input', updateDobFromAge);
        ageInput.addEventListener('change', updateDobFromAge);
    }
});
</script>

</body>
</html>
