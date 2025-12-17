<?php
include '../base.php';

auth('Admin');

// ----------------------------------------------------------------------------

if (is_post()) {
    $name = req('name');
    $email = req('email');
    $password = req('password');
    $confirm = req('confirm');
    $role = req('role');
    
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

    if (!$role) {
        $_err['role'] = 'Required';
    }
    else if ($role != 'Member') {
        $_err['role'] = 'Invalid (example: Member)';
    }


    if (!$_err) {

        $photo = 'user.png';

        $stm = $_db->prepare('
        INSERT INTO user (name, email, password, photo, role)
        VALUES (?, ?, SHA1(?), ?, ?)
        ');

    $stm->execute([$name, $email, $password, $photo, $role]);
    redirect('all_user.php');
    
    }
    
}

?>





<style>

label > * {
    vertical-align: text-top;
}

.form {
    display: grid;
    grid-template-columns: 200px 300px 300px; 
    gap: 15px; 
    padding: 20px; 
    place-content: start;
    place-items: center start;
    font-size: 18px; 
    margin-left: 200px;
}

.form > label:not(:has(*)) {
    place-self: stretch;
    background: #ccc;
    font-weight: bold;
    padding: 10px;
    font-size: 18px;
}


.form input, 
.form select, 
.form textarea {
    width: 100%;
    padding: 8px 12px;
    font-size: 18px;
    border: 1px solid #999;
    border-radius: 5px;
}



.form > section {
    grid-column: 1 / -1;
}

.err {
    color: red;
}

label.upload img {
    display: block;
    border: 1px solid #333;
    width: 150px;
    height: 200px;
    object-fit: cover;
    cursor: pointer;
}

.gback {
    position: absolute;
    width: 35px;
    height: 35px;
    top: 30px;
    left: 150px;
}

</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/image/logo2.png">
    <link rel="stylesheet" href="../css/app.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/app.js"></script>

    <title>BY BOOK</title>
</head>



>

    <a href="profile.php">
            <img class="gback" src="/image/arrow.png">
        </a>



        <form method="post" class="form" enctype="multipart/form-data">
                    
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

                    <label for="role">Role</label>
                    <?= html_name('role', 'maxlength="100"') ?>
                    <?= err('role') ?>


                    <section>
                    <button class="cont-btn">Insert</button>
                    </section>
                    
                </form>



