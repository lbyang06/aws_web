
<?php
include 'base.php';




if (is_post()) {
    $email    = req('email');
    $password = req('password');

    if ($email == '') {
        $_err['email'] = 'Please Enter';
    }
    else if (!is_email($email)){
        $_err['email'] = 'Invalid!';
    }

    if ($password == '') {
        $_err['password'] = 'Please Enter';
    }

    if (!$_err) {
        $stm = $_db->prepare('
        SELECT * FROM user
        WHERE email = ? AND password = SHA1(?)
        ');
        $stm->execute([$email, $password]);
        $u = $stm->fetch();

        if ($u) {
            login($u);
        }
        else {
            $_err['password'] = 'Invalid!';
        }
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

        <div class="Logboxed">


                <a href="ValidationPage.php">
                    <img class="lback" src="/image/arrow.png">
                </a>

                <P class="LoginText">Log in</P>

                <form method="post" class="Sign">
                    
                    <label for="email">Email</label>
                    <?= html_email('email', 'maxlength="100"') ?>
                    <?= err('email') ?>

                    <label for="password">Password</label>
                    <?= html_pass('password', 'maxlength="100"') ?>
                    <?= err('password')?>

                    <section>
                    <button class="cont-btn">Continue</button>
                    </section>
                    
                </form>

                <div class="short-cut">
                <p>don't have an account?</p>
                <a href="SignUp.php">sign up</a>
                </div>
        </div>

    

</body>