<?php
session_start();
include "../connection.php";

/* ===============================
    ADMIN ACCESS CHECK
================================= */
if (!isset($_SESSION['admin_email'])) {
    header("Location: adminlogin.php");
    exit();
}

$email = $_SESSION['admin_email'];

// Fetching admin details securely with Prepared Statements
$stmt = $conn->prepare("SELECT * FROM admins WHERE email=? OR phone=?");
$stmt->bind_param("ss", $email, $email);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

/* DEFAULT PROFILE IMAGE LOGIC */
$profileImage = (!empty($admin['profile_image']) && file_exists("../uploads/admins/" . $admin['profile_image']))
    ? "../uploads/admins/" . $admin['profile_image']
    : "../images/profile.png";

/* UPDATE PROFILE LOGIC */
if (isset($_POST['save'])) {

    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $email_input = trim($_POST['email']);
    $location = trim($_POST['location']);
    $address = trim($_POST['address']);

    $imageName = $admin['profile_image'];

    // Handle Image Upload
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $uploadPath = "../uploads/admins/" . $imageName;

        if (!is_dir("../uploads/admins/")) {
            mkdir("../uploads/admins/", 0777, true);
        }

        move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
    }

    // Secure Update with Prepared Statements
    $updateStmt = $conn->prepare("UPDATE admins SET first_name=?, last_name=?, phone=?, email=?, location=?, address=?, profile_image=? WHERE id=?");
    $updateStmt->bind_param("sssssssi", $first, $last, $phone, $email_input, $location, $address, $imageName, $admin['id']);
    $updateStmt->execute();

    header("Location: adminprofile.php");
    exit();
}

/* LOGOUT */
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: adminlogin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | Eventify Portal</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        /* ================= PREMIUM THEME ================= */
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --bg-input: #334155;
            --primary: #3b82f6;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            color: white;
            margin: 0;
            overflow-x: hidden;
        }

        /* NAVBAR */
        .nav {
            background: var(--bg-dark);
            padding: 18px 5%;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
            margin: 0;
        }

        .nav ul li a {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s;
        }

        .nav ul li a:hover {
            color: var(--primary);
        }

        /* CONTAINER WRAPPER */
        .profile-wrapper {
            max-width: 850px;
            margin: 120px auto 60px;
            background: var(--bg-card);
            padding: 50px;
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* --- PROFILE IMAGE SECTION --- */
        .img-container {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }

        .img-container img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            cursor: pointer;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .img-container img:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }

        .cam-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--primary);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid var(--bg-card);
            font-size: 14px;
        }

        /* --- FORM GRID --- */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .input-group label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: var(--bg-input);
            color: white;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .save-btn {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .save-btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        /* --- QUICK ACTION CARDS --- */
        .quick-actions {
            margin-top: 50px;
        }

        .grid-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .action-card {
            background: #f8fafc;
            color: #0f172a;
            padding: 22px 25px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-decoration: none;
            transition: 0.3s;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .card-left {
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
            font-size: 14px;
        }

        .card-left i {
            font-size: 20px;
            color: var(--primary);
            width: 25px;
            text-align: center;
        }

        /* --- LOGOUT --- */
        .logout-wrapper {
            margin-top: 60px;
            text-align: center;
            border-top: 1px solid var(--border);
            padding-top: 40px;
        }

        .logout-btn {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 14px 45px;
            border-radius: 50px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: #ef4444;
            color: white;
        }

        @media (max-width: 768px) {

            .form-grid,
            .grid-cards {
                grid-template-columns: 1fr;
            }

            .profile-wrapper {
                width: 90%;
                padding: 30px;
            }
        }
    </style>
</head>

<body>

    <nav class="nav">
        <div style="font-weight: 700; color: var(--primary); font-size: 20px;">Admin<span>Portal</span></div>
        <ul>
            <li><a href="admin_home.php">Dashboard</a></li>
            <li><a href="addevent.php">Add Event</a></li>
            <li><a href="manage_events.php">Manage Events</a></li>
            <li><a href="adminprofile.php" style="color: white;">Profile</a></li>
        </ul>
    </nav>

    <div class="profile-wrapper">

        <div class="img-container">
            <img src="<?php echo $profileImage; ?>" id="imgTrigger" alt="Admin">
            <div class="cam-badge"><i class="fa-solid fa-camera-rotate"></i></div>
            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <input type="file" name="image" id="fileInput" hidden>
            </form>
        </div>

        <form method="POST" enctype="multipart/form-data" id="updateForm" form="profileForm">
            <div class="form-grid">
                <div class="input-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($admin['first_name']); ?>"
                        placeholder="First Name">
                </div>
                <div class="input-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($admin['last_name']); ?>"
                        placeholder="Last Name">
                </div>
                <div class="input-group">
                    <label>Admin Contact</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>"
                        placeholder="Phone">
                </div>
                <div class="input-group">
                    <label>Verified Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>"
                        placeholder="Email">
                </div>
                <div class="input-group">
                    <label>Primary Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($admin['location']); ?>"
                        placeholder="City">
                </div>
                <div class="input-group">
                    <label>Office Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($admin['address']); ?>"
                        placeholder="Full Address">
                </div>
            </div>

            <button type="submit" name="save" form="profileForm" class="save-btn">
                <i class="fa-solid fa-user-check"></i> Update Administrative Records
            </button>
        </form>

        <div class="quick-actions">
            <h3
                style="font-size: 16px; font-weight: 600; margin-bottom: 25px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">
                System Access</h3>
            <div class="grid-cards">
                <a href="admin_home.php" class="action-card">
                    <div class="card-left"><i class="fa-solid fa-chart-pie"></i><span>System Dashboard</span></div>
                    <i class="fa-solid fa-chevron-right" style="color: #94a3b8; font-size: 12px;"></i>
                </a>
                <a href="manage_events.php" class="action-card">
                    <div class="card-left"><i class="fa-solid fa-calendar-check"></i><span>Manage Events</span></div>
                    <i class="fa-solid fa-chevron-right" style="color: #94a3b8; font-size: 12px;"></i>
                </a>
                <a href="#" class="action-card">
                    <div class="card-left"><i class="fa-solid fa-user-group"></i><span>User Analytics</span></div>
                    <i class="fa-solid fa-chevron-right" style="color: #94a3b8; font-size: 12px;"></i>
                </a>
                <a href="#" class="action-card">
                    <div class="card-left"><i class="fa-solid fa-gears"></i><span>System Settings</span></div>
                    <i class="fa-solid fa-chevron-right" style="color: #94a3b8; font-size: 12px;"></i>
                </a>
            </div>
        </div>

        <div class="logout-wrapper">
            <form method="POST">
                <button type="submit" name="logout" class="logout-btn">
                    <i class="fa-solid fa-power-off"></i> Secure Logout
                </button>
            </form>
        </div>

    </div>

    <script>
        // Image Upload Interactive logic
        const trigger = document.getElementById("imgTrigger");
        const input = document.getElementById("fileInput");

        trigger.onclick = () => input.click();

        input.onchange = function () {
            const reader = new FileReader();
            reader.onload = function (e) {
                trigger.src = e.target.result;
            }
            reader.readAsDataURL(this.files[0]);
        }
    </script>

</body>

</html>