<?php
session_start();
include "connection.php";

$message = "";

if (isset($_POST['submit'])) {
    $loginInput = trim($_POST['login_input']);
    $pass = $_POST['p1'] . $_POST['p2'] . $_POST['p3'] . $_POST['p4'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ? OR email = ?");
    $stmt->bind_param("ss", $loginInput, $loginInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            header("Location: Home.php");
            exit();
        } else {
            $message = "Incorrect security PIN";
        }
    } else {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $phone = $isEmail ? "" : $loginInput;
        $email = $isEmail ? $loginInput : "";

        $insert = $conn->prepare("INSERT INTO users (phone, email, password) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $phone, $email, $hashed);

        if ($insert->execute()) {
            $_SESSION['user_email'] = $loginInput;
            header("Location: profile.php");
            exit();
        } else {
            $message = "Registration failed. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Login Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --input-bg: rgba(51, 65, 85, 0.5);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top left, #1e1b4b, #0f172a);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #f8fafc;
            overflow: hidden;
        }

        /* Ambient background glow */
        body::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            background: var(--primary);
            filter: blur(150px);
            top: 10%;
            left: 10%;
            opacity: 0.15;
            z-index: -1;
        }

        .login-box {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 24px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        h2 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        p.subtitle {
            color: #94a3b8;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-field {
            width: 100%;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            background: var(--input-bg);
            color: white;
            font-size: 15px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(51, 65, 85, 0.8);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
        }

        .toggle-link {
            color: var(--primary);
            cursor: pointer;
            font-size: 12px;
            display: inline-block;
            margin-top: 8px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .toggle-link:hover {
            color: #818cf8;
            text-decoration: underline;
        }

        .password-container {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 25px 0;
        }

        .password-container input {
            width: 100%;
            height: 60px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: var(--input-bg);
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
        }

        .password-container input:focus {
            outline: none;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3);
        }

        button {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            border: none;
            color: white;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
        }

        button:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(99, 102, 241, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .message {
            color: #fb7185;
            margin-top: 15px;
            font-size: 13px;
            min-height: 20px;
        }
    </style>
</head>

<body>

    <div class="login-box">
        <h2>Welcome back</h2>
        <p class="subtitle">Please enter your details to continue</p>

        <form method="POST" id="loginForm">
            <div class="input-group">
                <input type="text" name="login_input" id="loginInput" class="input-field" placeholder="Phone Number"
                    required>
                <span class="toggle-link" id="toggleType">Use email instead</span>
            </div>

            <div class="password-container" id="otp-inputs">
                <input type="password" maxlength="1" name="p1" autocomplete="off" required>
                <input type="password" maxlength="1" name="p2" autocomplete="off" required>
                <input type="password" maxlength="1" name="p3" autocomplete="off" required>
                <input type="password" maxlength="1" name="p4" autocomplete="off" required>
            </div>

            <button type="submit" name="submit">Continue</button>
            <div class="message"><?php echo $message; ?></div>
        </form>
    </div>

    <script>
        const input = document.getElementById("loginInput");
        const toggle = document.getElementById("toggleType");
        let isPhone = true;

        toggle.addEventListener("click", function () {
            if (isPhone) {
                input.type = "email";
                input.placeholder = "Email Address";
                toggle.innerText = "Use phone instead";
            } else {
                input.type = "text";
                input.placeholder = "Phone Number";
                toggle.innerText = "Use email instead";
            }
            isPhone = !isPhone;
        });

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