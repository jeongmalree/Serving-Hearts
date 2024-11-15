<?php require_once 'controllers/authController.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="style.css">
    <title>Reset Password</title>
</head>
<body>
    
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form-div login">
                <form action="reset_password.php" method="post">
                    <h3 class="text-center">Reset you Password</h3>

                    <?php if(count($errors) > 0): ?>
                        <div class="alert alert-danger">
                            <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label for="password">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg">
                    </div>

                    <div class="form-group mb-3">
                        <label for="password">Confirm Password</label>
                        <input type="password" name="passwordConf" class="form-control form-control-lg">
                    </div>

                    <div class="form-group mb-3">
                        <button type="submit" name="reset-password-btn" class="btn btn-primary btn-lg w-100">
                            Reset Password
                        </button>
                    </div>
                </form> 
            </div>
        </div>
    </div>
</body>
</html>