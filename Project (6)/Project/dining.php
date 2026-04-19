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
    <title>Dining</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ================= BODY ================= */

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: white;
        }

        /* ================= PAGE WRAPPER ================= */

        .page-wrapper {
            max-width: 1200px;
            margin: auto;
            padding: 120px 40px 80px;
        }

        /* ================= PAGE HEADER ================= */

        .page-header {
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        /* ================= GRID ================= */

        .top-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 24px;
        }

        /* ================= CARD ================= */

        .blog-contain {
            background: rgba(28, 36, 52, 0.9);
            border-radius: 14px;
            overflow: hidden;
            transition: 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(10px);
        }

        .blog-contain img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            transition: 0.4s ease;
        }

        .blog-contain:hover img {
            transform: scale(1.08);
        }

        .blog-contain:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        .details {
            padding: 16px;
        }

        .details h3 {
            font-size: 15px;
            font-weight: 600;
            margin: 6px 0;
        }

        .details h5 {
            font-size: 12px;
            color: #cbd5e1;
            margin: 0;
        }

        .details span {
            font-size: 14px;
            font-weight: 600;
            color: #22d3ee;
            display: block;
            margin-top: 6px;
        }

        /* ================= FOOTER ================= */

        .footer {
            background: linear-gradient(180deg, #0f0f1a, #1a1a2e);
            padding: 80px 0 30px;
            margin-top: 80px;
        }

        .footer-container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
        }

        .footer-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .footer-col {
            width: 23%;
            min-width: 220px;
            margin-bottom: 40px;
        }

        .footer-col h4 {
            color: #ffffff;
            font-size: 18px;
            margin-bottom: 25px;
            position: relative;
            font-weight: 500;
        }

        .footer-col h4::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, #a78bfa, #6c5ce7);
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
            color: #aaa;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .footer-col ul li a:hover {
            color: #ffffff;
            padding-left: 8px;
        }

        .footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            width: 40px;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .footer-social a:hover {
            background: linear-gradient(90deg, #a78bfa, #6c5ce7);
            transform: translateY(-5px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-bottom p {
            color: #888;
            font-size: 14px;
        }

        /* ================= RESPONSIVE ================= */

        @media (max-width:768px) {
            .page-wrapper {
                padding: 110px 20px 60px;
            }
        }
    </style>
</head>

<body>

    <!-- COMMON NAVBAR -->
    <?php include "navbar.php"; ?>

    <div class="page-wrapper">

        <div class="page-header">
            <h1>🍽 DINING</h1>
        </div>

        <div class="top-container">

            <?php
            $stmt = $conn->prepare("SELECT * FROM events WHERE category=? ORDER BY created_at DESC");
            $category = "Dining";
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {

                    $image = !empty($row['banner_image'])
                        ? "uploads/events/" . $row['banner_image']
                        : "./images/food1.jpeg";
                    ?>

                    <a href="eventdetails.php?id=<?php echo $row['id']; ?>" style="text-decoration:none;color:inherit;">
                        <div class="blog-contain">
                            <img src="<?php echo $image; ?>" alt="Dining">
                            <div class="details">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <h5><?php echo htmlspecialchars($row['location']); ?></h5>
                                <span>₹<?php echo htmlspecialchars($row['price']); ?></span>
                            </div>
                        </div>
                    </a>

                    <?php
                }
            } else {
                echo "<p>No Dining Events Available</p>";
            }
            ?>

        </div>

    </div>

    <!-- FOOTER -->
    <footer class="footer" id="footer">
        <div class="footer-container">
            <div class="footer-row">

                <div class="footer-col">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Our Services</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Get Help</h4>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Booking Status</a></li>
                        <li><a href="#">Payment Options</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="liveevent.php">Live Events</a></li>
                        <li><a href="dining.php">Dining</a></li>
                        <li><a href="booking.php">Bookings</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Follow Us</h4>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>

            </div>

            <div class="footer-bottom">
                <p>© 2026 Event Platform. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

</body>

</html>