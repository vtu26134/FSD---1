<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="nav">
    <div class="navbar">
        <ul>
            <li><a href="Home.php" class="<?= ($currentPage == 'Home.php') ? 'active' : '' ?>">Discover</a></li>
            <li><a href="liveevent.php" class="<?= ($currentPage == 'liveevent.php') ? 'active' : '' ?>">Live Events</a>
            </li>
            <li><a href="dining.php" class="<?= ($currentPage == 'dining.php') ? 'active' : '' ?>">Dining</a></li>
            <li><a href="mybookings.php" class="<?= ($currentPage == 'mybookings.php') ? 'active' : '' ?>">My
                    Tickets</a></li>
            <li><a href="#"
                    class="<?= ($currentPage == 'activities.php') ? 'active' : '' ?>">Activities</a></li>
        </ul>
    </div>

    <div class="icons">
        <div class="search">
            <input type="text" id="showsearch" placeholder="Search experiences...">
            <i id="showicon" class="fa-solid fa-magnifying-glass"></i>
        </div>

        <?php if (isset($_SESSION['user_email'])): ?>
            <a href="profile.php"><i class="fa-solid fa-circle-user"
                    style="color: var(--primary); font-size: 24px;"></i></a>
        <?php else: ?>
            <a href="login.php"><i class="fa-solid fa-user-circle"></i></a>
        <?php endif; ?>

        <div style="position: relative;">
            <i class="fa-solid fa-bell"></i>
            <span
                style="position: absolute; top: -5px; right: -5px; width: 8px; height: 8px; background: var(--primary); border-radius: 50%; border: 2px solid var(--bg-dark);"></span>
        </div>
    </div>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const showicon = document.getElementById("showicon");
        const showsearch = document.getElementById("showsearch");

        if (showicon && showsearch) {
            showicon.addEventListener("click", function (e) {
                e.stopPropagation();
                showsearch.classList.toggle("active");
                if (showsearch.classList.contains("active")) showsearch.focus();
            });

            document.addEventListener("click", function (e) {
                if (!showsearch.contains(e.target) && !showicon.contains(e.target)) {
                    showsearch.classList.remove("active");
                }
            });
        }
    });
</script>