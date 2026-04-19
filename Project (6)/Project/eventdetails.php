<?php
session_start();
include "connection.php";

if (!isset($_GET['id'])) {
    die("Event not found");
}

$id = intval($_GET['id']);
// Using Prepared Statements for security
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows == 0) {
    die("Event not found");
}

$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']); ?> | Eventify</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #0f172a;
            color: white;
        }

        .hero {
            position: relative;
            height: 500px;
            background: url("uploads/events/<?= htmlspecialchars($event['banner_image']); ?>") center/cover no-repeat;
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, #0f172a 10%, rgba(0, 0, 0, 0.4));
        }

        .hero-content {
            position: absolute;
            bottom: 40px;
            left: 5%;
            z-index: 2;
        }

        .hero-content h1 {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-wrapper {
            max-width: 1200px;
            margin: auto;
            padding: 60px 5% 80px;
            display: grid;
            grid-template-columns: 1.8fr 1fr;
            gap: 40px;
        }

        .about-section h3 {
            color: #3b82f6;
            font-size: 22px;
            margin-bottom: 15px;
        }

        .about-section p {
            color: #94a3b8;
            line-height: 1.8;
        }

        .booking-sidebar {
            background: #1e293b;
            padding: 35px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            height: fit-content;
            position: sticky;
            top: 110px;
        }

        .price-tag {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 25px;
        }

        .meta-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #94a3b8;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .meta-info i {
            color: #3b82f6;
            width: 18px;
        }

        .book-btn {
            width: 100%;
            padding: 18px;
            background: #3b82f6;
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: 0.3s;
        }

        .book-btn:hover {
            background: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>

<body>
    <?php include "navbar.php"; ?>

    <div class="hero">
        <div class="hero-content">
            <h1><?= htmlspecialchars($event['title']); ?></h1>
            <div class="meta-info" style="color: white;">
                <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($event['location']); ?>
            </div>
        </div>
    </div>

    <main class="page-wrapper">
        <div class="about-section">
            <h3><i class="fa-solid fa-circle-info"></i> About The Event</h3>
            <p><?= nl2br(htmlspecialchars($event['description'])); ?></p>
        </div>

        <aside class="sidebar-wrapper">
            <div class="booking-sidebar">
                <div class="meta-info"><i class="fa-regular fa-calendar-days"></i>
                    <?= date("D, d M Y", strtotime($event['event_date'])); ?></div>
                <div class="meta-info"><i class="fa-regular fa-clock"></i>
                    <?= date("h:i A", strtotime($event['event_time'])); ?></div>

                <div style="color: #94a3b8; font-size: 13px; margin-top: 20px;">Tickets starting from</div>
                <div class="price-tag">₹<?= number_format($event['price']); ?></div>

                <a href="booking.php?id=<?= $id ?>" class="book-btn">
                    <i class="fa-solid fa-ticket"></i> BOOK TICKETS
                </a>
            </div>
        </aside>
    </main>
</body>

</html>