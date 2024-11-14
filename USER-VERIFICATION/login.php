<?php require_once 'controllers/authController.php'; 

if (isset($_SESSION['id'])) {
    header('Location: index.php'); 
    exit();
} 

$lockout_time = isset($user['lockout_time']) ? strtotime($user['lockout_time']) : null;
$lockout_active = $lockout_time && $lockout_time > time();
$remaining_time_in_seconds = $lockout_active ? $lockout_time - time() : 0; // Remaining time in seconds
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
            background: url(../WEB/images/backbg.png) no-repeat center center;
            background-size: cover;
        }

        .split-container {
            display: flex;
            width: 60%;
            height: 60vh;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .left-panel, .right-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #fff;
        }

        .left-panel {
            background: #580000;
            flex-direction: column;
            text-align: center;
        }

        .right-panel {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
        }

        .container {
            max-width: 400px;
            width: 100%;
        }

        .input-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            overflow: hidden;
        }

        .input-group i {
            padding: 10px;
        }

        .input-group input {
            border: none;
            outline: none;
            padding: 10px;
            flex: 1;
            background: transparent;
        }

        #togglePassword,
        #togglePasswordSlash {
            cursor: pointer;
            padding: 10px;
        }

        .error-message {
            color: #9a3b3b;
            background: #FDBABA;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: none;
        }

        .btn {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
            background-color: #580000;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #Dc4a4a;
            color: white;
        }
        
        h2, .left-panel p{
            font-weight: bold;
        }

        h3 {
            font-size: 28px;
            color: #580000;
            margin-bottom: 20px;
        }

        .forgot-pass, .back-to-home {
            display: block;
            margin-top: 20px;
            font-size: 0.9em;
            text-align: center;
            color: #580000;
            text-decoration: none;
        }
        /* Header styling */
        header {
            width: 100%;
            position: fixed;
            left: 0;
            top: 0;
            padding: 5px 2%;
            display: flex;
            justify-content: space-between;
            z-index: 10;
            background-color: white;
        }
        .logoContent {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 5rem;
            padding-left: 5px;
            padding-top: 10px;
        }

    </style>
</head>
<body>
<header>
        <div class="logoContent">
            <a href="home.html" class="logo"><img src="../WEB/images/logo.png" alt="Serving Hearts Logo"></a>
        </div>
    </header>
    <div class="split-container">
        <div class="left-panel">
            <h2>Welcome Back!</h2>
            <p>Enter your credentials to access your account.</p>
        </div>
        <div class="right-panel">
            <div class="container">
                <form action="login.php" method="post">
                    <h3 class="text-center">Login</h3>

                    <?php if(count($errors) > 0): ?>
                        <div class="alert alert-danger">
                            <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" id="login-username" placeholder="Username or Email" required>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password"  id="login-password" placeholder="Password" autocomplete="off" required>
                        <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
                    </div>

                    <button type="submit" name="login-btn" class="btn" id="loginBtn" <?php echo $lockout_active ? 'disabled' : ''; ?>>
                        <?php echo $lockout_active ? 'Locked Out: <span id="remainingTime"></span>' : 'Login'; ?>
                    </button>

                    <p class="text-center">Don't have an account yet? <a href="signup.php">Sign Up</a></p>
                    <a href="forgot_password.php" class="forgot-pass">Forgot your Password?</a>
                    <a href="../WEB/home.html" class="back-to-home">Back to Home</a>
                
                </form>
            </div>
        </div>
    </div>

    <script>
    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('login-password');

    togglePassword.addEventListener('click', function() {
        const isPasswordVisible = passwordField.type === 'text';
        passwordField.type = isPasswordVisible ? 'password' : 'text';
        togglePassword.classList.toggle('fa-eye', !isPasswordVisible);
        togglePassword.classList.toggle('fa-eye-slash', isPasswordVisible);
    });

    // Update the icon based on the input field state
    passwordField.addEventListener('input', function() {
        if (passwordField.type === 'text') {
            togglePassword.classList.remove('fa-eye-slash');
            togglePassword.classList.add('fa-eye');
        } else {
            togglePassword.classList.remove('fa-eye');
            togglePassword.classList.add('fa-eye-slash');
        }
    });
</script>

</body>
</html>
