<?php
session_start();
include "../connection.php";

$message = "";

if(isset($_POST['submit'])){

    $loginInput = trim($_POST['login_input']);
    $pass = $_POST['p1'].$_POST['p2'].$_POST['p3'].$_POST['p4'];

    // Secure database handling with Prepared Statements
    $stmt = $conn->prepare("SELECT * FROM admins WHERE phone=? OR email=?");
    $stmt->bind_param("ss", $loginInput, $loginInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result && $result->num_rows > 0){
        $admin = $result->fetch_assoc();

        if(password_verify($pass, $admin['password'])){
            $_SESSION['admin_email'] = $loginInput;
            header("Location: admin_home.php");
            exit();
        } else {
            $message = "Incorrect Admin Security PIN";
        }
    } else {
        $message = "Admin credentials not recognized";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | Secure Login</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ================= PREMIUM THEME ================= */
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --bg-input: #334155;
            --primary: #3b82f6;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            /* Ambient radial glow for premium feel */
            background-image: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.1) 0%, transparent 40%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: white;
            overflow: hidden;
        }

        .login-box {
            background: var(--bg-card);
            padding: 50px;
            border-radius: 24px;
            width: 400px;
            text-align: center;
            border: 1px solid var(--border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .icon-header {
            width: 70px;
            height: 70px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 25px;
        }

        h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .input-group {
            position: relative;
            margin-bottom: 15px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .input-field {
            width: 100%;
            padding: 16px 20px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--bg-input);
            color: white;
            font-size: 15px;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }

        .input-field:focus { border-color: var(--primary); }

        .toggle-link {
            color: var(--primary);
            cursor: pointer;
            font-size: 12px;
            display: block;
            margin-top: 8px;
            font-weight: 600;
            transition: 0.3s;
        }
        .toggle-link:hover { color: white; }

        /* PASSCODE INPUTS */
        .password-grid {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 25px;
        }

        .password-grid input {
            width: 100%;
            height: 60px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--bg-input);
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: white;
            transition: 0.2s ease;
        }

        .password-grid input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        button {
            width: 100%;
            padding: 16px;
            margin-top: 35px;
            background: var(--primary);
            border: none;
            color: white;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        button:hover {
            background: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.3);
        }

        .message {
            color: #f87171;
            margin-top: 20px;
            font-size: 13px;
            font-weight: 500;
            background: rgba(248, 113, 113, 0.1);
            padding: 10px;
            border-radius: 8px;
            display: <?php echo $message ? 'block' : 'none'; ?>;
        }
    </style>
</head>

<body>

<div class="login-box">
    
    <div class="icon-header">
        <i class="fa-solid fa-user-shield"></i>
    </div>

    <h2>Admin Login</h2>
    <p class="subtitle">Enter your credentials to access the portal</p>

    <form method="POST">

        <div class="input-group">
            <label id="inputLabel">Admin Phone Number</label>
            <input type="text" 
                   name="login_input" 
                   id="loginInput"
                   class="input-field"
                   placeholder="Ex: +91 9876543210"
                   required>
            <span class="toggle-link" id="toggleType">Use Email Instead</span>
        </div>

        <div class="input-group" style="margin-top: 25px;">
            <label>Security PIN</label>
            <div class="password-grid" id="otp-inputs">
                <input type="password" maxlength="1" name="p1" autocomplete="off" required>
                <input type="password" maxlength="1" name="p2" autocomplete="off" required>
                <input type="password" maxlength="1" name="p3" autocomplete="off" required>
                <input type="password" maxlength="1" name="p4" autocomplete="off" required>
            </div>
        </div>

        <button type="submit" name="submit">
            Authorize & Sign In <i class="fa-solid fa-arrow-right" style="font-size: 12px; margin-left: 8px;"></i>
        </button>

        <div class="message">
            <i class="fa-solid fa-circle-exclamation" style="margin-right: 5px;"></i>
            <?php echo $message; ?>
        </div>

    </form>

</div>

<script>
// Toggle Phone/Email Logic
const input = document.getElementById("loginInput");
const toggle = document.getElementById("toggleType");
const label = document.getElementById("inputLabel");

let isPhone = true;

toggle.addEventListener("click", function(){
    if(isPhone){
        input.type = "email";
        input.placeholder = "admin@eventify.com";
        label.innerText = "Admin Email Address";
        toggle.innerText = "Use Phone Instead";
    } else {
        input.type = "text";
        input.placeholder = "Ex: +91 9876543210";
        label.innerText = "Admin Phone Number";
        toggle.innerText = "Use Email Instead";
    }
    isPhone = !isPhone;
});

// REAL-TIME UX: Auto-focus next input field
const inputs = document.querySelectorAll('#otp-inputs input');

inputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        if (e.target.value.length === 1 && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
            inputs[index - 1].focus();
        }
    });
});
</script>

</body>
</html>