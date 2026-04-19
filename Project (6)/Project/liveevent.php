<?php
session_start();
include "connection.php";
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Events | Eventify</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ================= BODY & BACKGROUND ================= */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #0f172a;
            /* Subtle radial glow to simulate real-time depth */
            background-image: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.05) 0%, transparent 40%);
            color: #ffffff;
            overflow-x: hidden;
        }

        /* ================= PAGE WRAPPER ================= */
        .page-wrapper {
            max-width: 1300px;
            margin: auto;
            padding: 120px 5% 80px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
            border-left: 5px solid #3b82f6;
            padding-left: 20px;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }

        /* ================= GRID SYSTEM ================= */
        .top-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        /* ================= PREMIUM CARDS ================= */
        .blog-contain {
            background: #1e293b;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
        }

        .blog-contain:hover {
            transform: translateY(-12px);
            border-color: #3b82f6;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .img-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .blog-contain img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.6s ease;
        }

        .blog-contain:hover img {
            transform: scale(1.1);
        }

        /* Premium Date Tag */
        .date-tag {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(5px);
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            color: #3b82f6;
            text-transform: uppercase;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .details {
            padding: 24px;
        }

        .details h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 10px 0;
            color: #f8fafc;
            line-height: 1.4;
        }

        .details h5 {
            font-size: 13px;
            color: #94a3b8;
            margin: 0 0 20px 0;
            font-weight: 400;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .details .price-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 15px;
        }

        .details span.price {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
        }

        .book-link {
            font-size: 12px;
            font-weight: 700;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .closed-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #ef4444;
            color: white;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 8px;
        }

        /* ================= FOOTER ================= */
        .footer {
            margin-top: 100px;
            background: #0f172a;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding: 80px 0 40px;
        }

        .footer-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
        }

        .footer-col h4 {
            color: #f8fafc;
            font-size: 16px;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-col ul {
            list-style: none;
            padding: 0;
        }

        .footer-col ul li {
            margin-bottom: 12px;
        }

        .footer-col ul li a {
            text-decoration: none;
            color: #94a3b8;
            font-size: 14px;
            transition: 0.3s;
        }

        .footer-col ul li a:hover {
            color: #3b82f6;
            padding-left: 5px;
        }

        .footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            width: 40px;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border-radius: 12px;
            margin-right: 12px;
            transition: 0.3s;
        }

        .footer-social a:hover {
            background: #3b82f6;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .footer-bottom {
            text-align: center;
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: #475569;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .page-wrapper {
                padding-top: 100px;
            }

            .page-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

    <?php include "navbar.php"; ?>

    <div class="page-wrapper">

        <div class="page-header">
            <h1>LIVE EVENTS</h1>
            <p style="color: #94a3b8; font-size: 14px; margin: 0;">Explore curated live experiences</p>
        </div>

        <div class="top-container">

            <?php
            // Using Prepared Statements for Real-Time Security
            $stmt = $conn->prepare("SELECT * FROM events WHERE category=? ORDER BY event_date ASC");
            $category = "Live Event";
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $events = $stmt->get_result();

            if ($events && $events->num_rows > 0) {
                while ($row = $events->fetch_assoc()) {

                    /* EVENT CLOSED CHECK */
                    $eventDateTime = strtotime($row['event_date'] . ' ' . $row['event_time']);
                    $isClosed = ($eventDateTime < time());

                    $image = !empty($row['banner_image'])
                        ? "uploads/events/" . $row['banner_image']
                        : "./images/homepage1.jpg";
                    ?>

                    <a href="eventdetails.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; color:inherit;">
                        <div class="blog-contain">
                            <div class="img-wrapper">
                                <img src="<?php echo $image; ?>" alt="event image">

                                <!-- CLOSED BADGE -->
                                <?php if ($isClosed): ?>
                                    <div class="closed-badge">CLOSED</div>
                                <?php endif; ?>
                                <div class="date-tag">
                                    <i class="fa-regular fa-calendar-days"></i>
                                    <?php echo date("d M", strtotime($row['event_date'])); ?>
                                </div>
                            </div>

                            <div class="details">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <h5><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($row['location']); ?>
                                </h5>

                                <div class="price-wrap">
                                    <span class="price">₹<?php echo number_format($row['price']); ?></span>
                                    <span class="book-link">Details <i class="fa-solid fa-chevron-right"></i></span>
                                </div>
                            </div>
                        </div>
                    </a>

                    <?php
                }
            } else {
                echo "<div style='grid-column: 1/-1; text-align: center; padding: 50px; color: #94a3b8;'>
                        <i class='fa-solid fa-calendar-xmark' style='font-size: 40px; margin-bottom: 20px;'></i>
                        <p>No Live Events scheduled at the moment. Check back later!</p>
                      </div>";
            }
            ?>

        </div>

    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-row">
                <div class="footer-col">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Booking Status</a></li>
                        <li><a href="#">Cancellation</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="liveevent.php">Live Events</a></li>
                        <li><a href="dining.php">Dining</a></li>
                        <li><a href="activities.php">Activities</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Connect</h4>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-x-twitter"></i></a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                &copy; 2026 Eventify Platform. Crafted for real-time experiences.
            </div>
        </div>
    </footer>

</body>

</html>