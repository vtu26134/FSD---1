<?php
session_start();
include "connection.php";

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];

// Using Prepared Statements for real-time security
$stmt = $conn->prepare("SELECT * FROM users WHERE email=? OR phone=?");
$stmt->bind_param("ss", $email, $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* Proper Default Profile Image Logic */
$profileImage = (!empty($user['profile_image']) && file_exists("uploads/" . $user['profile_image']))
    ? "uploads/" . $user['profile_image']
    : "images/profile.png";

if (isset($_POST['save'])) {
    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $email_input = trim($_POST['email']);
    $location = trim($_POST['location']);
    $address = trim($_POST['address']);

    $imageName = $user['profile_image'];

    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $imageName);
    }

    $updateStmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, email=?, location=?, address=?, profile_image=? WHERE id=?");
    $updateStmt->bind_param("sssssssi", $first, $last, $phone, $email_input, $location, $address, $imageName, $user['id']);
    $updateStmt->execute();

    header("Location: profile.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: Home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Eventify</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ================= PREMIUM PROFILE STYLING ================= */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #0f172a;
            color: #ffffff;
        }

        .container {
            max-width: 850px;
            margin: 120px auto 60px;
            background: #1e293b;
            padding: 50px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* --- Profile Image --- */
        .profile-img-section {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }

        .profile-img-section img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #3b82f6;
            cursor: pointer;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .profile-img-section img:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }

        .edit-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: #3b82f6;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #1e293b;
            font-size: 14px;
        }

        /* --- Form Elements --- */
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
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: #334155;
            color: white;
            font-family: inherit;
            font-size: 14px;
            transition: 0.3s;
            outline: none;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        .save-btn {
            width: 100%;
            padding: 16px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .save-btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        /* --- Quick Action Cards --- */
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin: 50px 0 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .action-card {
            background: #f8fafc;
            color: #0f172a;
            padding: 20px 25px;
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
            background: #ffffff;
        }

        .card-content {
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
            font-size: 14px;
        }

        .card-content i {
            font-size: 20px;
            color: #3b82f6;
            width: 25px;
            text-align: center;
        }

        .action-card span.arrow {
            font-size: 18px;
            color: #94a3b8;
        }

        /* --- Logout --- */
        .logout-section {
            margin-top: 60px;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 40px;
        }

        .logout-btn {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 14px 40px;
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
            .card-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 30px;
                margin-top: 100px;
                width: 90%;
            }
        }
    </style>
</head>

<body>

    <?php include "navbar.php"; ?>

    <div class="container">

        <div class="profile-img-section">
            <img src="<?php echo $profileImage; ?>" id="profileTrigger" alt="User Profile">
            <div class="edit-badge"><i class="fa-solid fa-camera"></i></div>
            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <input type="file" name="image" id="imageInput" hidden>
            </form>
        </div>

        <form method="POST" enctype="multipart/form-data" id="mainForm" form="profileForm">
            <div class="form-grid">
                <div class="input-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>"
                        placeholder="First Name">
                </div>
                <div class="input-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>"
                        placeholder="Last Name">
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"
                        placeholder="Ex: +91 9876543210">
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                        placeholder="email@example.com">
                </div>
                <div class="input-group">
                    <label>City / Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($user['location']); ?>"
                        placeholder="City">
                </div>
                <div class="input-group">
                    <label>Full Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>"
                        placeholder="Flat/House No, Area">
                </div>
            </div>

            <button type="submit" name="save" form="profileForm" class="save-btn">
                <i class="fa-solid fa-cloud-arrow-up"></i> Save Profile Changes
            </button>
        </form>

        <h3 class="section-title"><i class="fa-solid fa-bolt"></i> Quick Actions</h3>
        <div class="card-grid">
            <a href="mybookings.php" class="action-card">
                <div class="card-content">
                    <i class="fa-solid fa-box-archive"></i>
                    <span>Booking History</span>
                </div>
                <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
            </a>
            <a href="#" class="action-card">
                <div class="card-content">
                    <i class="fa-solid fa-crown"></i>
                    <span>Insider Access</span>
                </div>
                <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
            </a>
            <a href="#" class="action-card">
                <div class="card-content">
                    <i class="fa-solid fa-headset"></i>
                    <span>Customer Support</span>
                </div>
                <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
            </a>
            <a href="#" class="action-card">
                <div class="card-content">
                    <i class="fa-solid fa-ticket"></i>
                    <span>Active Coupons</span>
                </div>
                <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
            </a>
        </div>

        <div class="logout-section">
            <form method="POST">
                <button type="submit" name="logout" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i> Sign Out of Account
                </button>
            </form>
        </div>

    </div>

    <script>
        // Image Upload Interaction
        const trigger = document.getElementById("profileTrigger");
        const input = document.getElementById("imageInput");

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