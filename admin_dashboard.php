<?php
session_start();
include "db.php";

if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle ADD / EDIT / DELETE actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add / Edit Member
    if (isset($_POST['save_member'])) {
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'];
        $email = $_POST['email'];
        $age = $_POST['age'];
        $height = $_POST['height'];
        $weight = $_POST['weight'];
        $goal = $_POST['goal'];

        if ($id) {
            mysqli_query($conn, "UPDATE member_registration SET 
                Mem_name='$name', Mem_email='$email', Mem_age='$age', Height='$height', 
                Weight='$weight', Goal_type='$goal' WHERE Mem_id='$id'");
        } else {
            mysqli_query($conn, "INSERT INTO member_registration 
                (Mem_name, Mem_email, Mem_age, Height, Weight, Goal_type)
                VALUES ('$name','$email','$age','$height','$weight','$goal')");
        }
    }

    // Add / Edit Trainer
    if (isset($_POST['save_trainer'])) {
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $gender = $_POST['gender'];
        $speciality = $_POST['speciality'];
        $status = $_POST['status'];

        if ($id) {
            mysqli_query($conn, "UPDATE trainer SET 
                Trainer_name='$name', Trainer_phno='$phone', Trainer_gender='$gender', 
                Speciality='$speciality', Trainer_status='$status' WHERE Trainer_id='$id'");
        } else {
            mysqli_query($conn, "INSERT INTO trainer 
                (Trainer_name, Trainer_phno, Trainer_gender, Speciality, Trainer_status)
                VALUES ('$name','$phone','$gender','$speciality','$status')");
        }
    }

    // Add / Edit Dietician
    if (isset($_POST['save_dietician'])) {
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $status = $_POST['status'];

        if ($id) {
            mysqli_query($conn, "UPDATE dietician SET 
                Dietician_name='$name', Dietician_phno='$phone', Dietician_status='$status' 
                WHERE Dietician_id='$id'");
        } else {
            mysqli_query($conn, "INSERT INTO dietician 
                (Dietician_name, Dietician_phno, Dietician_status)
                VALUES ('$name','$phone','$status')");
        }
    }

    header("Location: admin_dashboard.php");
    exit();
}

// Delete actions
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = intval($_GET['delete']);
    $type = $_GET['type'];

    if ($type === 'member') {
        mysqli_query($conn, "DELETE FROM member_registration WHERE Mem_id='$id'");
    } elseif ($type === 'trainer') {
        mysqli_query($conn, "DELETE FROM trainer WHERE Trainer_id='$id'");
    } elseif ($type === 'dietician') {
        mysqli_query($conn, "DELETE FROM dietician WHERE Dietician_id='$id'");
    }

    header("Location: admin_dashboard.php");
    exit();
}

// Fetch data
$members_res = mysqli_query($conn, "SELECT * FROM member_registration");
$trainers_res = mysqli_query($conn, "SELECT * FROM trainer");
$dieticians_res = mysqli_query($conn, "SELECT * FROM dietician");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="#" onclick="showSection('members')">Members</a>
    <a href="#" onclick="showSection('trainers')">Trainers</a>
    <a href="#" onclick="showSection('dieticians')">Dieticians</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="content">
    <h1>Welcome, Admin</h1>

    <!-- Members -->
    <div id="members" class="section">
        <h2>Members</h2>
        <button onclick="openModal('memberModal')">+ Add Member</button>
        <table>
            <tr>
                <th>Name</th><th>Email</th><th>Age</th><th>Height</th><th>Weight</th><th>Goal</th><th>Actions</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($members_res)): ?>
            <tr>
                <td><?= $row['Mem_name'] ?></td>
                <td><?= $row['Mem_email'] ?></td>
                <td><?= $row['Mem_age'] ?></td>
                <td><?= $row['Height'] ?></td>
                <td><?= $row['Weight'] ?></td>
                <td><?= $row['Goal_type'] ?></td>
                <td>
                    <button onclick='editMember(<?= json_encode($row) ?>)'>Edit</button>
                    <a href="?delete=<?= $row['Mem_id'] ?>&type=member" onclick="return confirm('Delete this member?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Trainers -->
    <div id="trainers" class="section" style="display:none;">
        <h2>Trainers</h2>
        <button onclick="openModal('trainerModal')">+ Add Trainer</button>
        <table>
            <tr>
                <th>Name</th><th>Phone</th><th>Gender</th><th>Speciality</th><th>Status</th><th>Actions</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($trainers_res)): ?>
            <tr>
                <td><?= $row['Trainer_name'] ?></td>
                <td><?= $row['Trainer_phno'] ?></td>
                <td><?= $row['Trainer_gender'] ?></td>
                <td><?= $row['Speciality'] ?></td>
                <td><?= $row['Trainer_status'] ?></td>
                <td>
                    <button onclick='editTrainer(<?= json_encode($row) ?>)'>Edit</button>
                    <a href="?delete=<?= $row['Trainer_id'] ?>&type=trainer" onclick="return confirm('Delete this trainer?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Dieticians -->
    <div id="dieticians" class="section" style="display:none;">
        <h2>Dieticians</h2>
        <button onclick="openModal('dieticianModal')">+ Add Dietician</button>
        <table>
            <tr>
                <th>Name</th><th>Phone</th><th>Status</th><th>Actions</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($dieticians_res)): ?>
            <tr>
                <td><?= $row['Dietician_name'] ?></td>
                <td><?= $row['Dietician_phno'] ?></td>
                <td><?= $row['Dietician_status'] ?></td>
                <td>
                    <button onclick='editDietician(<?= json_encode($row) ?>)'>Edit</button>
                    <a href="?delete=<?= $row['Dietician_id'] ?>&type=dietician" onclick="return confirm('Delete this dietician?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- Modals -->
<div id="memberModal" class="modal">
    <div class="modal-content">
        <h3>Member Form</h3>
        <form method="POST">
            <input type="hidden" name="id" id="member_id">
            <label>Name:</label><input type="text" name="name" id="member_name" required>
            <label>Email:</label><input type="email" name="email" id="member_email" required>
            <label>Age:</label><input type="number" name="age" id="member_age" required>
            <label>Height:</label><input type="text" name="height" id="member_height" required>
            <label>Weight:</label><input type="text" name="weight" id="member_weight" required>
            <label>Goal:</label><input type="text" name="goal" id="member_goal" required>
            <button type="submit" name="save_member">Save</button>
            <button type="button" onclick="closeModal('memberModal')">Cancel</button>
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

<script>
function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
    document.getElementById(sectionId).style.display = 'block';
}

function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function editMember(data) {
    document.getElementById('member_id').value = data.Mem_id;
    document.getElementById('member_name').value = data.Mem_name;
    document.getElementById('member_email').value = data.Mem_email;
    document.getElementById('member_age').value = data.Mem_age;
    document.getElementById('member_height').value = data.Height;
    document.getElementById('member_weight').value = data.Weight;
    document.getElementById('member_goal').value = data.Goal_type;
    openModal('memberModal');
}

function editTrainer(data) {
    document.getElementById('trainer_id').value = data.Trainer_id;
    document.getElementById('trainer_name').value = data.Trainer_name;
    document.getElementById('trainer_phone').value = data.Trainer_phno;
    document.getElementById('trainer_gender').value = data.Trainer_gender;
    document.getElementById('trainer_speciality').value = data.Speciality;
    document.getElementById('trainer_status').value = data.Trainer_status;
    openModal('trainerModal');
}

function editDietician(data) {
    document.getElementById('dietician_id').value = data.Dietician_id;
    document.getElementById('dietician_name').value = data.Dietician_name;
    document.getElementById('dietician_phone').value = data.Dietician_phno;
    document.getElementById('dietician_status').value = data.Dietician_status;
    openModal('dieticianModal');
}
</script>

</body>
</html>
