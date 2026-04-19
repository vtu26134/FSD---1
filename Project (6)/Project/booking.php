<?php
session_start();
include "connection.php";

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Event not found");
}

$id = intval($_GET['id']);
// Using Prepared Statements for real-time security
$stmt = $conn->prepare("SELECT * FROM events WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows == 0) {
    die("Event not found");
}

$event = $result->fetch_assoc();
$success = "";

if (isset($_POST['confirm_booking'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']); // Now captures the edited email
    $quantity = intval($_POST['quantity']);
    $total = $quantity * $event['price'];

    // Secure database insertion with Prepared Statements
    $book = $conn->prepare("INSERT INTO bookings (user_email, event_id, full_name, phone, quantity, total_price) VALUES (?,?,?,?,?,?)");
    $book->bind_param("sissid", $email, $id, $full_name, $phone, $quantity, $total);

    if ($book->execute()) {
        $success = "Booking Successful! View your tickets in 'My Bookings'.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | Eventify</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #0f172a;
            color: white;
        }

        .page-wrapper {
            max-width: 1100px;
            margin: auto;
            padding: 120px 5% 80px;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 40px;
        }

        /* EVENT SUMMARY CARD */
        .summary-card {
            background: #1e293b;
            padding: 30px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            height: fit-content;
        }

        .summary-card img {
            width: 100%;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .summary-card h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #94a3b8;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .info-row i {
            color: #3b82f6;
            width: 18px;
        }

        /* FORM CARD */
        .booking-card {
            background: #1e293b;
            padding: 40px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .booking-card h3 {
            margin-bottom: 25px;
            font-size: 22px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 12px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: #0f172a;
            color: white;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .total-box {
            padding: 20px;
            background: rgba(59, 130, 246, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(59, 130, 246, 0.2);
            margin: 25px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-box span {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
        }

        .confirm-btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            background: #3b82f6;
            color: white;
            cursor: pointer;
            transition: 0.3s;
            font-size: 16px;
        }

        .confirm-btn:hover {
            background: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .success-alert {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid rgba(74, 222, 128, 0.2);
            margin-bottom: 20px;
            text-align: center;
        }

        @media(max-width:850px) {
            .page-wrapper {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include "navbar.php"; ?>

    <div class="page-wrapper">
        <div class="summary-card">
            <img src="uploads/events/<?= htmlspecialchars($event['banner_image']); ?>" alt="Banner">
            <h2><?= htmlspecialchars($event['title']); ?></h2>
            <div class="info-row"><i class="fa-regular fa-calendar-days"></i>
                <?= date("D, d M Y", strtotime($event['event_date'])); ?></div>
            <div class="info-row"><i class="fa-regular fa-clock"></i>
                <?= date("h:i A", strtotime($event['event_time'])); ?></div>
            <div class="info-row"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($event['location']); ?>
            </div>
            <div class="info-row"><i class="fa-solid fa-indian-rupee-sign"></i> ₹<?= number_format($event['price']); ?>
                per ticket</div>
        </div>

        <div class="booking-card">
            <h3><i class="fa-solid fa-shield-halved" style="color: #3b82f6;"></i> Secure Checkout</h3>

            <?php if ($success): ?>
                <div class="success-alert"><i class="fa-solid fa-circle-check"></i> <?= $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Enter your full name" required>
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="Ex: +91 9876543210" required>
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['user_email']); ?>"
                        placeholder="Enter email address" required>
                </div>
                <div class="input-group">
                    <label>Number of Tickets</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" required>
                </div>

                <div class="total-box">
                    <div style="font-size: 13px; color: #94a3b8; font-weight: 600;">TOTAL AMOUNT</div>
                    <span>₹<span id="totalPrice"><?= number_format($event['price']); ?></span></span>
                </div>

                <button type="submit" name="confirm_booking" class="confirm-btn">
                    CONFIRM & BOOK NOW
                </button>
            </form>
        </div>
    </div>

    <script>
        const price = <?= $event['price']; ?>;
        const qtyInput = document.getElementById("quantity");
        const totalDisplay = document.getElementById("totalPrice");

        qtyInput.addEventListener("input", function () {
            let qty = parseInt(this.value) || 1;
            if (qty < 1) qty = 1;
            totalDisplay.textContent = (qty * price).toLocaleString();
        });
    </script>
</body>

</html>