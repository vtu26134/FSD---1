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

/* ===============================
    SECURE DELETE LOGIC
================================= */
$message = "";
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $message = "Listing successfully removed from inventory.";
    }
}

/* ===============================
    FETCH EVENTS & ANALYTICS
================================= */
$events = $conn->query("
    SELECT e.*, IFNULL(SUM(b.quantity), 0) as booked_seats 
    FROM events e 
    LEFT JOIN bookings b ON e.id = b.event_id 
    GROUP BY e.id 
    ORDER BY e.event_date ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | Eventify Admin</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ================= PREMIUM THEME ================= */
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --primary: #3b82f6;
            --primary-glow: rgba(59, 130, 246, 0.15);
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.05);
            --danger: #ef4444;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            background-image: radial-gradient(circle at 0% 0%, var(--primary-glow) 0%, transparent 40%);
            color: white;
            margin: 0;
            overflow-x: hidden;
        }

        /* --- NAVBAR --- */
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 5%;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--border);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-sizing: border-box;
        }
        .nav h2 { font-size: 20px; font-weight: 700; color: var(--primary); margin:0; }
        .nav h2 span { color: white; }
        .nav ul { display: flex; list-style: none; gap: 30px; margin: 0; }
        .nav a { color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 500; transition: 0.3s; }
        .nav a:hover { color: white; }

        /* --- MAIN CONTENT --- */
        .wrapper {
            max-width: 1300px;
            margin: 120px auto 60px;
            padding: 0 5%;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 40px;
        }

        .action-bar h1 { font-size: 32px; font-weight: 700; margin: 0; letter-spacing: -1px; }

        .add-btn {
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }
        .add-btn:hover { background: #2563eb; transform: translateY(-3px); box-shadow: 0 15px 30px rgba(59, 130, 246, 0.3); }

        /* --- TABLE ARCHITECTURE --- */
        .table-container {
            background: var(--bg-card);
            border-radius: 24px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        table { width: 100%; border-collapse: collapse; }

        th {
            text-align: left;
            padding: 20px 30px;
            background: rgba(15, 23, 42, 0.5);
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
        }

        td {
            padding: 22px 30px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(59, 130, 246, 0.03); }

        /* --- DATA STYLING --- */
        .event-cell { display: flex; align-items: center; gap: 18px; }
        .event-cell img {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.05);
        }

        .inventory-box { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); }

        /* Seat Progress */
        .progress-wrapper { width: 120px; margin-top: 8px; }
        .progress-bar { height: 6px; background: #334155; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--primary); box-shadow: 0 0 10px var(--primary); }

        .price-tag { font-weight: 700; font-size: 16px; color: white; }

        .del-link {
            color: #64748b;
            font-size: 18px;
            transition: 0.2s;
            padding: 8px;
        }
        .del-link:hover { color: var(--danger); transform: scale(1.2); }

        .msg-alert {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            padding: 15px 25px;
            border-radius: 12px;
            border: 1px solid rgba(34, 197, 94, 0.2);
            margin-bottom: 30px;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <nav class="nav">
        <h2>Admin<span>Portal</span></h2>
        <ul>
            <li><a href="admin_home.php">Dashboard</a></li>
            <li><a href="addevent.php">Add Event</a></li>
            <li><a href="manage_events.php" style="color: white;">Manage Inventory</a></li>
            <li><a href="adminprofile.php">Profile</a></li>
        </ul>
    </nav>

    <div class="wrapper">

        <?php if ($message): ?>
            <div class="msg-alert"><i class="fa-solid fa-circle-check"></i> <?= $message ?></div>
        <?php endif; ?>

        <div class="action-bar">
            <div>
                <h1>Live Inventory</h1>
                <p style="color: var(--text-muted); margin-top: 5px; font-size: 14px;">Review analytics by selecting an event row.</p>
            </div>
            <a href="addevent.php" class="add-btn">
                <i class="fa-solid fa-plus"></i> Publish New Event
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Event Listing</th>
                        <th>Occupancy</th>
                        <th>Schedule</th>
                        <th>Category</th>
                        <th>Ticket Price</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($events->num_rows > 0): ?>
                        <?php while ($row = $events->fetch_assoc()): 
                            $percent = ($row['booked_seats'] / $row['total_seats']) * 100;
                        ?>
                        <tr onclick="window.location='event_dashboard.php?id=<?= $row['id'] ?>'">
                            <td>
                                <div class="event-cell">
                                    <img src="../uploads/events/<?= $row['banner_image'] ?>" alt="">
                                    <div>
                                        <div style="font-weight: 600;"><?= htmlspecialchars($row['title']) ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted);"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($row['location']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="inventory-box" style="color: <?= $percent > 85 ? 'var(--danger)' : '#4ade80' ?>;">
                                    <?= $row['booked_seats'] ?> / <?= $row['total_seats'] ?> Sold
                                </div>
                                <div class="progress-wrapper">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $percent ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?= date("d M Y", strtotime($row['event_date'])) ?></div>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= date("h:i A", strtotime($row['event_time'])) ?></div>
                            </td>
                            <td><span style="background: rgba(255,255,255,0.05); padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 600;"><?= $row['category'] ?></span></td>
                            <td class="price-tag">₹<?= number_format($row['price']) ?></td>
                            <td style="text-align: right;">
                                <a href="?delete_id=<?= $row['id'] ?>" class="del-link" 
                                   onclick="event.stopPropagation(); return confirm('Archive this listing permanently?');">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 60px; color: var(--text-muted);">No active events found in the database.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>