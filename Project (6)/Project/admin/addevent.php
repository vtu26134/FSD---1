<?php
session_start();
include "../connection.php";

/* ===============================
    ADMIN ACCESS CHECK
================================= */
if (!isset($_SESSION['admin_email'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$msgType = "";

if (isset($_POST['add_event'])) {

    // Secure handling with Prepared Statements
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $date = $_POST['event_date'];
    $time = $_POST['event_time'];
    $location = trim($_POST['location']);
    $price = $_POST['price'];
    $seats = $_POST['total_seats'];
    $description = trim($_POST['description']);

    $imageName = "";
    $uploadDir = "../uploads/events/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!empty($_FILES['banner']['name'])) {
        $imageName = time() . "_" . basename($_FILES['banner']['name']);
        $targetPath = $uploadDir . $imageName;
        move_uploaded_file($_FILES['banner']['tmp_name'], $targetPath);
    }

    // Corrected SQL statement with 9 placeholders
    $stmt = $conn->prepare("INSERT INTO events 
        (title, category, event_date, event_time, location, price, total_seats, description, banner_image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // FIX: Changed "sssssdis s" (10 chars) to "sssssdiss" (9 chars) to match the number of variables
    $stmt->bind_param("sssssdiss", $title, $category, $date, $time, $location, $price, $seats, $description, $imageName);

    if ($stmt->execute()) {
        $message = "Event Published Successfully!";
        $msgType = "success";
    } else {
        $message = "Error: Could not save event.";
        $msgType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | Add New Event</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        /* ================= PREMIUM THEME ================= */
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --primary: #3b82f6;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            color: white;
            margin: 0;
        }

        /* NAVBAR */
        .nav {
            background: var(--bg-dark);
            padding: 18px 5%;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
            margin: 0;
            padding: 0;
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

        /* CONTAINER */
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: var(--bg-card);
            padding: 50px;
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        header {
            text-align: center;
            margin-bottom: 40px;
        }

        header h2 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }

        header p {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 5px;
        }

        /* BANNER UPLOAD */
        .banner-dropzone {
            width: 100%;
            height: 320px;
            border-radius: 20px;
            background: #0f172a;
            border: 2px dashed rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 30px;
            cursor: pointer;
            position: relative;
            transition: 0.3s;
        }

        .banner-dropzone:hover {
            border-color: var(--primary);
            background: #161e31;
        }

        .banner-dropzone img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-placeholder {
            text-align: center;
            color: var(--text-muted);
        }

        .upload-placeholder i {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
            color: var(--primary);
        }

        /* FORM */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .input-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: #334155;
            color: white;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
        }

        textarea {
            height: 120px;
            resize: none;
        }

        .submit-btn {
            width: 100%;
            padding: 18px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        /* ALERTS */
        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-top: 25px;
            text-align: center;
            font-weight: 600;
        }

        .alert.success {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.2);
        }

        .alert.error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(248, 113, 113, 0.2);
        }
    </style>
</head>

<body>

    <nav class="nav">
        <div style="font-weight: 700; font-size: 20px; color: var(--primary);">Admin<span>Panel</span></div>
        <ul>
            <li><a href="admin_home.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
            <li><a href="addevent.php" style="color: white;"><i class="fa-solid fa-plus-circle"></i> Add Event</a></li>
            <li><a href="manage_events.php"><i class="fa-solid fa-list-check"></i> Manage</a></li>
            <li><a href="adminprofile.php"><i class="fa-solid fa-user-shield"></i> Profile</a></li>
        </ul>
    </nav>

    <div class="container">

        <header>
            <h2><i class="fa-solid fa-calendar-plus"></i> Create New Event</h2>
            <p>Fill in the details below to publish an experience on the platform.</p>
        </header>

        <form method="POST" enctype="multipart/form-data">

            <div class="banner-dropzone" onclick="document.getElementById('bannerInput').click();">
                <div class="upload-placeholder" id="placeholder">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p>Click to Upload Event Banner</p>
                    <small>Recommended: 1200 x 600 pixels</small>
                </div>
                <img id="bannerImage" style="display:none;">
            </div>

            <input type="file" name="banner" id="bannerInput" hidden required>

            <div class="form-row">
                <div class="input-group">
                    <label>Event Title</label>
                    <input type="text" name="title" placeholder="Ex: Summer Music Festival" required>
                </div>
                <div class="input-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option>Live Event</option>
                        <option>Dining</option>
                        <option>Activity</option>
                        <option>Workshop</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>Event Date</label>
                    <input type="date" name="event_date" required>
                </div>
                <div class="input-group">
                    <label>Event Time</label>
                    <input type="time" name="event_time" required>
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>Venue Location</label>
                    <input type="text" name="location" placeholder="City, Country" required>
                </div>
                <div class="input-group">
                    <label>Price (INR)</label>
                    <input type="number" name="price" placeholder="0.00" required>
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>Total Availability</label>
                    <input type="number" name="total_seats" placeholder="Max Tickets" required>
                </div>
            </div>

            <div class="input-group">
                <label>Detailed Description</label>
                <textarea name="description" placeholder="Tell users about this experience..." required></textarea>
            </div>

            <button type="submit" name="add_event" class="submit-btn">
                <i class="fa-solid fa-paper-plane"></i> Publish Event
            </button>

            <?php if ($message): ?>
                <div class="alert <?= $msgType ?>">
                    <i class="fa-solid <?= $msgType == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

        </form>

    </div>

    <script>
        // Image Preview Interaction
        const bannerInput = document.getElementById("bannerInput");
        const bannerImage = document.getElementById("bannerImage");
        const placeholder = document.getElementById("placeholder");

        bannerInput.onchange = function () {
            const reader = new FileReader();
            reader.onload = function (e) {
                bannerImage.src = e.target.result;
                bannerImage.style.display = "block";
                placeholder.style.display = "none";
            }
            reader.readAsDataURL(this.files[0]);
        }
    </script>

</body>

</html>