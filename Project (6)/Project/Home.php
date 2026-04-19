<?php
session_start();
include "connection.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>

    <?php include "navbar.php"; ?>

    <div class="image-container">
        <div class="images">
            <img src="./images/homepage1.jpg" alt="Slide 1">
            <video src="./images/homepagevideo.mp4" autoplay muted loop playsinline></video>
            <img src="./images/homepage.jpg" alt="Slide 2">
            <video src="./images/homepagevideo2.mp4" autoplay muted loop playsinline></video>
        </div>
        <div class="dots"></div>
    </div>

    <section class="top" id="foryou">
        <h1>Trending Now</h1>

        <div class="top-container">
            <?php
            // Original logic for fetching events
            $stmt = $conn->prepare("SELECT * FROM events ORDER BY created_at DESC LIMIT 4");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {

                    $image = !empty($row['banner_image'])
                        ? "uploads/events/" . $row['banner_image']
                        : "./images/homepage1.jpg";
                    ?>
                    <a href="eventdetails.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; color:inherit;">
                        <div class="blog-contain">
                            <img src="<?php echo $image; ?>" alt="event image">
                            <div class="details">
                                <i>
                                    <i class="fa-regular fa-calendar-days"></i>
                                    <?php
                                    echo date("d M Y", strtotime($row['event_date'])) .
                                        " | " .
                                        date("h:i A", strtotime($row['event_time']));
                                    ?>
                                </i>
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <h5>
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?php echo htmlspecialchars($row['location']); ?>
                                </h5>
                                <span>₹<?php echo number_format($row['price']); ?></span>
                            </div>
                        </div>
                    </a>
                    <?php
                }
            } else {
                echo "<p style='color:white;'>No Events Available</p>";
            }
            ?>
        </div>
    </section>

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

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const slides = document.querySelector(".images");
            const images = document.querySelectorAll(".images img, .images video");
            const dotsContainer = document.querySelector(".dots");

            if (!slides || images.length === 0 || !dotsContainer) return;

            let index = 0;

            images.forEach((_, i) => {
                const dot = document.createElement("span");
                dot.classList.add("dot");
                if (i === 0) dot.classList.add("active");
                dot.addEventListener("click", () => {
                    index = i;
                    updateSlider();
                });
                dotsContainer.appendChild(dot);
            });

            const dots = document.querySelectorAll(".dot");

            function updateSlider() {
                slides.style.transform = `translateX(-${index * 100}%)`;
                dots.forEach(dot => dot.classList.remove("active"));
                if (dots[index]) dots[index].classList.add("active");
            }

            function autoSlide() {
                const currentSlide = slides.children[index];
                updateSlider();
                if (currentSlide && currentSlide.tagName === "VIDEO") {
                    currentSlide.currentTime = 0;
                    currentSlide.play();
                    currentSlide.onended = nextSlide;
                } else {
                    setTimeout(nextSlide, 3000);
                }
            }

            function nextSlide() {
                index = (index + 1) % slides.children.length;
                autoSlide();
            }

            autoSlide();
        });
    </script>

</body>

</html>