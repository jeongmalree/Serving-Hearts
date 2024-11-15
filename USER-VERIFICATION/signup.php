<?php require_once 'controllers/authController.php';

if (isset($_SESSION['id'])) {
    header('Location: index.php'); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
        width: 50%; /* Decrease width of the whole panel */
        height: 80vh; /* Decrease height slightly */
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        margin-top: 50px;
    }

    .left-panel, .right-panel {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px; /* Decrease padding */
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
        max-width: 300px; /* Further decrease width */
        width: 100%;
    }

    .input-group {
        display: flex;
        align-items: center;
        margin-bottom: 10px; /* Decrease margin */
        border: 1px solid #ccc;
        border-radius: 5px;
        overflow: hidden;
    }
    .input-group label {
    margin-right: 10px; /* Space between label and select */
    font-size: 14px; /* Consistent font size */
    color: #333; /* Text color for label */
}

.input-group select {
    flex: 1; /* Allow the select to take available space */
    border: 1px solid #ccc; /* Border style for select */
    border-radius: 5px; /* Match the border radius */
    padding: 6px; /* Padding for select */
    outline: none; /* Remove outline */
    font-size: 14px; /* Consistent font size */
}


    .input-group i {
        padding: 6px; /* Decrease padding */
    }

    .input-group input {
        border: none;
        outline: none;
        padding: 6px; /* Decrease padding */
        flex: 1;
        background: transparent;
        font-size: 14px; /* Decrease font size */
    }

    .error-message {
        color: #9a3b3b;
        background: #FDBABA;
        padding: 8px; /* Decrease padding */
        margin-bottom: 15px; /* Decrease margin */
        border-radius: 5px;
        display: none;
    }
    .btn {
        width: 100%;
        padding: 6px;
        margin-bottom: 10px;
        border: none;
        border-radius: 5px;
        background-color: #8B0000; /* Dark red color */
        color: white;
        cursor: pointer;
        transition: background-color 0.3s;
        font-size: 15px; /* Decrease font size */
    }
    
    h2,.left-panel p{
        font-weight: bold;
    }

    .btn:hover {
        background-color: #DC143C;
        color: white;
    }

    h3 {
        font-size: 20px; /* Decrease heading font size */
        color: #580000;
        margin-bottom: 10px; /* Decrease margin */
    }

    .back-to-home {
        display: block;
        margin-top: 15px; /* Decrease margin */
        font-size: 0.8em; /* Decrease font size */
        text-align: center;
        color: #580000;
        text-decoration: none;
    }
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
            <h2>Welcome to Serving Hearts Inc.</h2>
            <p>Create your account to access all features.</p>
        </div>
        <div class="right-panel">
            <div class="container">
                <form action="signup.php" method="post">
                    <h3 class="text-center">Sign Up</h3>

                    <?php if(count($errors) > 0): ?>
                        <div class="alert alert-danger">
                            <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="fullname" placeholder="Full Name">
                    </div>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="phone" placeholder="Phone Number">
                    </div>
                    <div class="input-group">
                        <i class="fas fa-home"></i>
                        <input type="text" name="address" placeholder="Address">
                    </div>
                    <div class="input-group">
                        <i class="fas fa-calendar"></i>
                        <input type="date" name="dateofbirth">
                     </div>
                    <div class="input-group">
                    <i class="fas fa-venus-mars"></i>
                        <select name="gender" id="gender">
                            <option value="select">--Select Gender--</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>


                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="login-password" placeholder="Password" autocomplete="off" required>
                        <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="passwordConf" placeholder="Confirm Password" required>
                    </div>

                    <button type="submit" name="signup-btn" class="btn">Sign Up</button>

                    <p class="text-center">Already have an account? <a href="login.php">Login</a></p>
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
