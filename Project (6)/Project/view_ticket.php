<?php
session_start();
include "connection.php"; // Database connection

/* ===============================
    USER ACCESS & SECURITY CHECK (Logic Untouched)
================================= */
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Error: Ticket identifier missing.");
}

$booking_id = intval($_GET['id']);
$user_email = $_SESSION['user_email'];

$stmt = $conn->prepare("
    SELECT b.*, e.title, e.banner_image, e.event_date, e.event_time, e.location, e.price
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE b.id = ? AND b.user_email = ?
");

$stmt->bind_param("is", $booking_id, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Unauthorized access: You do not have permission to view this ticket.");
}

$ticket = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket | <?= htmlspecialchars($ticket['title']); ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        /* ================= PREMIUM THEME UPGRADE ================= */
        :root {
            --bg-dark: #0f172a;
            --primary: #3b82f6;
            --slate: #1e293b;
            --text-muted: #94a3b8;
            --white: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            color: white;
            background-image: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.12) 0%, transparent 40%);
            overflow-x: hidden;
        }

        /* --- ENTRY ANIMATION --- */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- NAVIGATION HEADER --- */
        .ticket-header {
            max-width: 850px;
            margin: 40px auto 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .back-link {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .back-link:hover {
            color: white;
            transform: translateX(-5px);
        }

        .action-btns {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-print {
            background: var(--slate);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-download {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        .btn:hover {
            transform: translateY(-3px);
            filter: brightness(1.1);
        }

        /* --- TICKET ARCHITECTURE --- */
        .ticket-container {
            max-width: 850px;
            margin: 0 auto 60px;
            background: var(--white);
            color: #0f172a;
            border-radius: 32px;
            overflow: visible;
            /* Required for punch-holes */
            display: flex;
            flex-direction: column;
            box-shadow: 0 40px 80px -15px rgba(0, 0, 0, 0.7);
            position: relative;
            animation: slideUp 0.8s ease-out;
        }

        /* TICKET PUNCH HOLES (Premium Visual Effect) */
        .ticket-container::before,
        .ticket-container::after {
            content: "";
            position: absolute;
            width: 40px;
            height: 40px;
            background: var(--bg-dark);
            border-radius: 50%;
            top: 67.5%;
            /* Aligns with the stub divider */
            z-index: 5;
        }

        .ticket-container::before {
            left: -20px;
        }

        .ticket-container::after {
            right: -20px;
        }

        .ticket-top {
            background: #111827;
            padding: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            border-top-left-radius: 32px;
            border-top-right-radius: 32px;
        }

        .ticket-top h2 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .ticket-id {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .ticket-main {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            padding: 50px;
            gap: 40px;
        }

        .event-info h1 {
            font-size: 32px;
            margin: 0 0 25px;
            color: #000;
            font-weight: 800;
            line-height: 1.1;
        }

        .detail-group {
            margin-bottom: 25px;
        }

        .detail-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 17px;
            font-weight: 600;
            color: #1e293b;
        }

        .qr-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-left: 2px dashed #e2e8f0;
            padding-left: 45px;
            text-align: center;
        }

        .barcode-font {
            font-family: 'Libre Barcode 128', cursive;
            font-size: 85px;
            margin: 15px 0;
            color: #000;
            transition: 0.3s;
        }

        .barcode-font:hover {
            color: var(--primary);
            transform: scale(1.05);
        }

        .stub-divider {
            height: 2px;
            background-image: linear-gradient(to right, #cbd5e1 50%, transparent 50%);
            background-size: 15px 2px;
            border: none;
            margin: 0 45px;
        }

        .ticket-footer {
            padding: 40px 50px;
            background: #f8fafc;
            border-bottom-left-radius: 32px;
            border-bottom-right-radius: 32px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .instructions {
            font-size: 11px;
            color: #64748b;
            line-height: 1.6;
            max-width: 450px;
        }

        .ticket-logo {
            height: 45px;
            transition: 0.3s;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }

        /* --- PRINT OPTIMIZATION --- */
        @media print {
            body {
                background: white !important;
            }

            .ticket-header {
                display: none !important;
            }

            .ticket-container {
                box-shadow: none;
                border: 1px solid #eee;
                margin: 0;
            }

            .ticket-container::before,
            .ticket-container::after {
                display: none;
            }

            .ticket-top {
                background: #000 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="ticket-header">
        <a href="mybookings.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to My Bookings
        </a>
        <div class="action-btns">
            <button class="btn btn-print" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print Ticket
            </button>
            <button class="btn btn-download" onclick="downloadPDF()">
                <i class="fa-solid fa-file-arrow-down"></i> Save as PDF
            </button>
        </div>
    </div>

    <div class="ticket-container" id="ticketContent">
        <div class="ticket-top">
            <div>
                <div class="ticket-id">Transaction Ref: #EVT-<?= str_pad($ticket['id'], 6, '0', STR_PAD_LEFT); ?></div>
                <h2>Eventify E-Ticket</h2>
            </div>
            <div style="text-align: right;">
                <div class="detail-label" style="color: rgba(255,255,255,0.5);">Verified Status</div>
                <div style="color: #4ade80; font-weight: 700; font-size: 14px;"><i class="fa-solid fa-certificate"></i>
                    CONFIRMED</div>
            </div>
        </div>

        <div class="ticket-main">
            <div class="event-info">
                <h1><?= htmlspecialchars($ticket['title']); ?></h1>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div class="detail-group">
                        <div class="detail-label">Schedule Date</div>
                        <div class="detail-value"><?= date("l, d M Y", strtotime($ticket['event_date'])); ?></div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Check-in Time</div>
                        <div class="detail-value"><?= date("h:i A", strtotime($ticket['event_time'])); ?></div>
                    </div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Primary Location</div>
                    <div class="detail-value"><?= htmlspecialchars($ticket['location']); ?></div>
                </div>

                <div class="detail-group"
                    style="background: #f1f5f9; padding: 25px; border-radius: 20px; border-left: 5px solid var(--primary);">
                    <div class="detail-label">Admitted Attendee</div>
                    <div class="detail-value" style="color: var(--primary); font-size: 19px;">
                        <?= htmlspecialchars($ticket['full_name']); ?></div>
                </div>
            </div>

            <div class="qr-area">
                <div class="detail-label">Electronic Passcode</div>
                <div class="barcode-font"><?= str_pad($ticket['id'], 8, '0', STR_PAD_LEFT); ?></div>
                <div style="font-weight: 800; font-size: 16px;">QUANTITY: <?= $ticket['quantity']; ?> ADULT(S)</div>
                <div style="color: #64748b; font-size: 13px; margin-top: 5px;">Paid:
                    ₹<?= number_format($ticket['total_price']); ?></div>
            </div>
        </div>

        <hr class="stub-divider">

        <div class="ticket-footer">
            <div class="instructions">
                <strong>Entry Policy:</strong><br>
                Please present this digital or printed ticket at the entrance. Entrance is subject to verification of a
                valid government ID matching the attendee name. Unauthorized duplication of this ticket is strictly
                prohibited.
            </div>
            <div style="text-align: right;">
                <img src="./images/logo.png" class="ticket-logo" alt="Eventify">
                <div
                    style="font-size: 10px; color: #94a3b8; margin-top: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                    Securely Powered by Eventify
                </div>
            </div>
        </div>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('ticketContent');
            const fileName = 'Ticket_<?= str_replace(" ", "_", $ticket['title']); ?>.pdf';

            // Visual State Update
            const btn = document.querySelector('.btn-download');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
            btn.style.opacity = '0.7';
            btn.style.pointerEvents = 'none';

            const options = {
                margin: 0.4,
                filename: fileName,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 3, useCORS: true, letterRendering: true },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            html2pdf().set(options).from(element).save().then(() => {
                btn.innerHTML = originalText;
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
            });
        }
    </script>

</body>

</html>