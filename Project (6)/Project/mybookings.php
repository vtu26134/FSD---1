<?php
session_start();
include "connection.php";

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];

// Using Prepared Statements for security
$stmt = $conn->prepare("
    SELECT b.*, e.title, e.banner_image, e.event_date, e.event_time, e.location
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE b.user_email = ?
    ORDER BY b.created_at DESC
");

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Eventify</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ================= PROFESSIONAL THEME ================= */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #0f172a;
            color: #ffffff;
        }

        .page-wrapper {
            max-width: 1200px;
            margin: auto;
            padding: 120px 5% 80px;
        }

        .header-section {
            margin-bottom: 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-section h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -1px;
        }

        /* ================= BOOKING GRID ================= */
        .booking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        /* ================= ENHANCED CARD ================= */
        .booking-card {
            background: #1e293b;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .booking-card:hover {
            transform: translateY(-8px);
            border-color: #3b82f6;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
        }

        .img-container {
            position: relative;
            width: 100%;
            height: 180px;
        }

        .booking-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Status Badge Logic */
        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            backdrop-filter: blur(8px);
        }

        .upcoming {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .past {
            background: rgba(148, 163, 184, 0.2);
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .card-body {
            padding: 24px;
            flex-grow: 1;
        }

        .card-body h3 {
            margin: 0 0 15px;
            font-size: 19px;
            font-weight: 600;
            line-height: 1.4;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 10px;
        }

        .info-row i {
            color: #3b82f6;
            width: 16px;
        }

        .card-footer {
            padding: 20px 24px;
            background: rgba(15, 23, 42, 0.3);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-paid {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
        }

        .view-btn {
            text-decoration: none;
            color: #3b82f6;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            transition: 0.2s;
        }

        .view-btn:hover {
            color: #ffffff;
        }

        /* ================= EMPTY STATE ================= */
        .no-booking {
            text-align: center;
            padding: 100px 20px;
            background: #1e293b;
            border-radius: 24px;
            border: 2px dashed rgba(255, 255, 255, 0.05);
        }

        .no-booking i {
            font-size: 50px;
            color: #3b82f6;
            margin-bottom: 20px;
        }

        .no-booking p {
            font-size: 18px;
            color: #94a3b8;
            margin-bottom: 25px;
        }

        .browse-btn {
            background: #3b82f6;
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .browse-btn:hover {
            background: #2563eb;
        }
    </style>
</head>

<body>

    <?php include "navbar.php"; ?>

    <div class="page-wrapper">

        <div class="header-section">
            <h1>🎟 My Bookings</h1>
            <p style="color: #94a3b8; font-size: 14px;">Review your upcoming and past experiences</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="booking-grid">

                <?php while ($row = $result->fetch_assoc()):
                    // Determine status based on current date
                    $eventDate = strtotime($row['event_date']);
                    $today = strtotime(date("Y-m-d"));
                    $isUpcoming = ($eventDate >= $today);
                    ?>

                    <div class="booking-card">
                        <div class="img-container">
                            <img src="uploads/events/<?= htmlspecialchars($row['banner_image']); ?>" alt="Banner">
                            <div class="status-badge <?= $isUpcoming ? 'upcoming' : 'past' ?>">
                                <?= $isUpcoming ? 'Upcoming' : 'Past Event' ?>
                            </div>
                        </div>

                        <div class="card-body">
                            <h3><?= htmlspecialchars($row['title']); ?></h3>

                            <div class="info-row">
                                <i class="fa-regular fa-calendar"></i>
                                <?= date("D, d M Y", strtotime($row['event_date'])); ?>
                            </div>

                            <div class="info-row">
                                <i class="fa-regular fa-clock"></i>
                                <?= date("h:i A", strtotime($row['event_time'])); ?>
                            </div>

                            <div class="info-row">
                                <i class="fa-solid fa-location-dot"></i>
                                <?= htmlspecialchars($row['location']); ?>
                            </div>

                            <div class="info-row" style="margin-top: 15px; color: #f8fafc;">
                                <i class="fa-solid fa-ticket"></i>
                                Tickets Booked: <?= $row['quantity']; ?>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="total-paid">₹<?= number_format($row['total_price']); ?></div>
                            <a href="view_ticket.php?id=<?= $row['id']; ?>" class="view-btn">View Ticket <i
                                    class="fa-solid fa-chevron-right"></i></a>
                        </div>
                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="no-booking">
                <i class="fa-solid fa-calendar-xmark"></i>
                <p>You haven't booked any experiences yet.</p>
                <a href="Home.php" class="browse-btn">Explore Events</a>
            </div>

        <?php endif; ?>

    </div>

</body>

</html>