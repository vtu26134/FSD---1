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

// Get Event ID from URL
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

/* ===============================
    FETCH LIVE ANALYTICS
================================= */
// 1. Core Event Info
$event_query = $conn->query("SELECT * FROM events WHERE id = $event_id");
$event = $event_query->fetch_assoc();

if (!$event) {
    die("Event Data Not Found.");
}

// 2. Aggregate Booking Data
$stats_query = $conn->query("
    SELECT 
        SUM(total_price) as total_revenue, 
        SUM(quantity) as tickets_booked 
    FROM bookings 
    WHERE event_id = $event_id
");
$stats = $stats_query->fetch_assoc();

$revenue = $stats['total_revenue'] ?? 0;
$booked = $stats['tickets_booked'] ?? 0;
$capacity = $event['total_seats'];
$fill_percent = ($capacity > 0) ? round(($booked / $capacity) * 100) : 0;

// 3. Attendee Manifest
$attendees = $conn->query("
    SELECT b.*, u.first_name, u.last_name 
    FROM bookings b 
    JOIN users u ON b.user_email = u.email 
    WHERE b.event_id = $event_id 
    ORDER BY b.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($event['title']) ?> | Event Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        /* ================= PREMIUM THEME ================= */
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --primary: #3b82f6;
            --success: #22c55e;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            background-image: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.05) 0%, transparent 40%);
            color: white;
            margin: 0;
            padding: 40px 5%;
        }

        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .back-btn {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .back-btn:hover {
            color: white;
        }

        /* --- DASHBOARD CARDS --- */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--bg-card);
            padding: 30px;
            border-radius: 24px;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .stat-card h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin: 0 0 15px;
            letter-spacing: 1px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: white;
        }

        .stat-card .sub-text {
            font-size: 13px;
            color: var(--primary);
            margin-top: 8px;
            font-weight: 600;
        }

        /* Progress Bar */
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #334155;
            border-radius: 10px;
            margin-top: 15px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary);
            box-shadow: 0 0 15px var(--primary);
            transition: 1s ease-out;
        }

        /* --- ATTENDEE LIST --- */
        .data-section {
            background: var(--bg-card);
            border-radius: 24px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .table-header {
            padding: 25px 35px;
            border-bottom: 1px solid var(--border);
            background: rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 20px 35px;
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        td {
            padding: 20px 35px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(59, 130, 246, 0.02);
        }

        .price-badge {
            color: var(--success);
            font-weight: 700;
        }
    </style>
</head>

<body>

    <div class="header-nav">
        <a href="manage_events.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Return to Inventory</a>
        <div style="text-align: right;">
            <div style="font-weight: 700; color: var(--primary);">REAL-TIME ANALYTICS</div>
            <div style="font-size: 11px; color: var(--text-muted);">Syncing with Master Database</div>
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 25px; margin-bottom: 45px;">
        <img src="../uploads/events/<?= $event['banner_image'] ?>"
            style="width: 120px; height: 120px; border-radius: 20px; object-fit: cover; border: 4px solid var(--bg-card);">
        <div>
            <h1 style="margin: 0; font-size: 32px;"><?= htmlspecialchars($event['title']) ?></h1>
            <p style="color: var(--text-muted); margin-top: 5px;"><i class="fa-solid fa-location-dot"></i>
                <?= $event['location'] ?> • <?= date("d M Y", strtotime($event['event_date'])) ?></p>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <h3>Net Revenue</h3>
            <div class="value">₹<?= number_format($revenue) ?></div>
            <div class="sub-text"><i class="fa-solid fa-receipt"></i> From <?= $booked ?> tickets</div>
        </div>

        <div class="stat-card">
            <h3>Occupancy Status</h3>
            <div class="value"><?= $booked ?> <span style="font-size: 14px; color: var(--text-muted);">/
                    <?= $capacity ?> Seats</span></div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $fill_percent ?>%"></div>
            </div>
            <div class="sub-text" style="color: <?= ($fill_percent > 85) ? '#ef4444' : 'var(--primary)' ?>;">
                <?= $fill_percent ?>% Filled</div>
        </div>

        <div class="stat-card">
            <h3>Ticket Value</h3>
            <div class="value">₹<?= number_format($event['price']) ?></div>
            <div class="sub-text"><i class="fa-solid fa-tag"></i> Original Listing Price</div>
        </div>
    </div>

    <div class="data-section">
        <div class="table-header">
            <h2 style="font-size: 18px; margin: 0;"><i class="fa-solid fa-users"
                    style="margin-right: 10px; color: var(--primary);"></i> Attendee Manifest</h2>
            <button onclick="window.print()"
                style="background: none; border: none; color: var(--text-muted); cursor: pointer;"><i
                    class="fa-solid fa-print"></i></button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Reference Email</th>
                    <th>Booking Date</th>
                    <th>Tickets</th>
                    <th>Total Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($attendees->num_rows > 0): ?>
                    <?php while ($row = $attendees->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                            </td>
                            <td style="color: var(--text-muted);"><?= $row['user_email'] ?></td>
                            <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td class="price-badge">₹<?= number_format($row['total_price']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 50px; color: var(--text-muted);">No tickets have
                            been booked for this event yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>