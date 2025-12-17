
<?php
include 'base.php';

if (is_post()) {
    $name = req('name');
    $email = req('email');
    $password = req('password');
    $confirm = req('confirm');
    
    
    if (!$name) {
        $_err['name'] = 'Required';
    }
    else if (strlen('name') > 100) {
        $_err['name'] = 'Maximum 100 characters';
    }

//validation email
    if (!$email) {
        $_err['email'] = 'Required';
    }
    else if (strlen($email) > 100) {
        $_err['email'] = 'Maximum 100 characters';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }
    else if (!is_unique($email, 'user', 'email')) {
        $_err['email'] = 'Duplicated';
    }

// Validation Password
    if (!$password) {
        $_err['password'] = 'Required';
    }
    else if (strlen($password) < 8) {
        $_err['password'] = 'At least 8 characters';
    }
    else if (strlen($password) > 100) {
        $_err['password'] = 'Maximum 100 characters';
    }

    if (!$confirm) {
        $_err['confirm'] = 'Required';
    }
    else if (strlen($confirm) < 8) {
        $_err['confirm'] = 'At least 8 characters';
    }
    else if (strlen($confirm) > 100) {
        $_err['confirm'] = 'Maximum 100 characters';
    }
    else if ($confirm != $password) {
        $_err['confirm'] = 'not matched';
    }



    if (!$_err) {

        $photo = 'user.png';
        $role = 'Member';
        $stm = $_db->prepare('
        INSERT INTO user (name, email, password, photo, role)
        VALUES (?, ?, SHA1(?), ?, ?)
        ');

    $stm->execute([$name, $email, $password, $photo, $role]);
    redirect('login.php');
    
    }
    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
</head>

<body>
    <div>
        <img class="signImg" src="../image/signback.jpg">
    </div>

    <div>
        <img class="BookImg" src="../image/BookImg.png">
    </div>

        <div class="Signboxed">
                <a href="ValidationPage.php">
                    <img class="lback" src="/image/arrow.png">
                </a>
                <P class="LoginText">Sign Up</P>

                <form method="post" class="Sign" enctype="multipart/form-data">
                    
                    <label for="name">Name</label>
                    <?= html_name('name', 'maxlength="100"') ?>
                    <?= err('name') ?>

                    <label for="email">Email</label>
                    <?= html_email('email', 'maxlength="100"') ?>
                    <?= err('email') ?>

                    <label for="password">Password</label>
                    <?= html_pass('password', 'maxlength="100"') ?>
                    <?= err('password')?>

                    <label for="confirm">Confirm</label>
                    <?= html_pass('confirm', 'maxlength="100"') ?>
                    <?= err('confirm')?>


                    <section>
                    <button class="cont-btn">Continue</button>
                    </section>
                    
                </form>

                <div class="short-cut-s">
                <p>already have account?</p>
                <a href="login.php">Log in</a>
                </div>
        </div>

    

</body>
