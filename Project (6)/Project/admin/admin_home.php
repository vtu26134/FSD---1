<?php
session_start();
include "../connection.php";

/* ===============================
    ADMIN ACCESS CHECK
================================= */
if(!isset($_SESSION['admin_email'])){
    header("Location: adminlogin.php");
    exit();
}

/* ===============================
    SECURE DASHBOARD DATA
================================= */
$totalEvents = $conn->query("SELECT COUNT(*) as total FROM events")->fetch_assoc()['total'];
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

$bookingResult = $conn->query("SELECT COUNT(*) as total FROM bookings");
$totalBookings = ($bookingResult) ? $bookingResult->fetch_assoc()['total'] : 0;

$recentEvents = $conn->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Eventify</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="../css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
            background-image: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.08) 0%, transparent 40%);
            color: white;
            margin: 0;
        }

        .dashboard-container {
            max-width: 1300px;
            margin: auto;
            padding: 120px 5% 60px;
        }

        .welcome-header {
            margin-bottom: 50px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .welcome-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -1px;
        }

        /* --- STAT CARDS --- */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: var(--bg-card);
            padding: 35px;
            border-radius: 24px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 25px;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .stat-icon {
            width: 65px;
            height: 65px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-info h2 { font-size: 32px; margin: 0; font-weight: 700; }
        .stat-info p { color: var(--text-muted); margin: 5px 0 0; font-size: 14px; font-weight: 500; }

        .add-event-btn {
            background: var(--primary);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .add-event-btn:hover { background: #2563eb; transform: translateY(-3px); }

        /* --- TABLE STYLING --- */
        .table-section {
            background: var(--bg-card);
            border-radius: 24px;
            border: 1px solid var(--border);
            overflow: hidden;
            margin-top: 40px;
        }

        .section-title {
            padding: 25px 35px;
            border-bottom: 1px solid var(--border);
            font-size: 18px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title a {
            font-size: 12px;
            color: var(--primary);
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        table { width: 100%; border-collapse: collapse; }

        table th {
            text-align: left;
            padding: 18px 35px;
            background: rgba(15, 23, 42, 0.4);
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        table td {
            padding: 20px 35px;
            font-size: 14px;
            border-bottom: 1px solid var(--border);
        }

        /* Hover & Pointer for navigation */
        table tr.clickable-row {
            cursor: pointer;
            transition: 0.2s;
        }

        table tr.clickable-row:hover td {
            background: rgba(59, 130, 246, 0.05);
        }

        .price-badge { color: var(--primary); font-weight: 700; }

        .action-icon {
            color: var(--text-muted);
            cursor: pointer;
            transition: 0.2s;
            margin-left: 15px;
            position: relative;
            z-index: 10;
        }

        .action-icon:hover { color: white; }
    </style>
</head>

<body>

    <nav class="nav">
        <div class="navbar">
            <ul>
                <li><a href="admin_home.php" class="active">Dashboard</a></li>
                <li><a href="addevent.php">Add Event</a></li>
                <li><a href="manage_events.php">Manage Events</a></li>
                <li><a href="adminprofile.php">My Profile</a></li>
            </ul>
        </div>
        <div class="icons">
            <a href="adminprofile.php"><i class="fa-solid fa-circle-user" style="color: var(--primary);"></i></a>
            <i class="fa-solid fa-bell"></i>
        </div>
    </nav>

    <div class="dashboard-container">

        <div class="welcome-header">
            <div>
                <h1>Admin Dashboard</h1>
                <p style="color: var(--text-muted); margin-top: 5px;">Platform overview and real-time statistics</p>
            </div>
            <a href="addevent.php" class="add-event-btn">
                <i class="fa-solid fa-plus-circle"></i> Create New Event
            </a>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-calendar-star"></i></div>
                <div class="stat-info">
                    <h2><?php echo number_format($totalEvents); ?></h2>
                    <p>Published Events</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-users-gear"></i></div>
                <div class="stat-info">
                    <h2><?php echo number_format($totalUsers); ?></h2>
                    <p>Registered Users</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-ticket-airline"></i></div>
                <div class="stat-info">
                    <h2><?php echo number_format($totalBookings); ?></h2>
                    <p>Total Sales / Bookings</p>
                </div>
            </div>
        </div>

        <div class="table-section">
            <div class="section-title">
                <span><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary); margin-right: 10px;"></i> Recently Added Events</span>
                <a href="manage_events.php">View All Listings <i class="fa-solid fa-arrow-right-long"></i></a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Date & Time</th>
                        <th>Venue Location</th>
                        <th>Ticket Price</th>
                        <th style="text-align: right;">Quick Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($recentEvents && $recentEvents->num_rows > 0){
                        while($row = $recentEvents->fetch_assoc()){
                    ?>
                    <tr class="clickable-row" onclick="window.location='manage_events.php'">
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($row['title']); ?></td>
                        <td>
                            <div style="font-size: 13px;"><?php echo date("d M Y", strtotime($row['event_date'])); ?></div>
                            <div style="color: var(--text-muted); font-size: 12px;"><?php echo date("h:i A", strtotime($row['event_time'])); ?></div>
                        </td>
                        <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['location']); ?></td>
                        <td class="price-badge">₹<?php echo number_format($row['price']); ?></td>
                        <td style="text-align: right;">
                            <a href="#" class="action-icon" title="Edit" onclick="event.stopPropagation();"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a href="#" class="action-icon" title="Delete" style="color: #ef4444;" onclick="event.stopPropagation();"><i class="fa-solid fa-trash-can"></i></a>
                        </td>
                    </tr>
                    <?php }} else { ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">No events found in the database.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>

    <footer style="text-align: center; padding: 40px; color: var(--text-muted); font-size: 13px; border-top: 1px solid var(--border); margin-top: 60px;">
        &copy; 2026 Eventify Admin Portal. Built for high-performance management.
    </footer>

</body>
</html>