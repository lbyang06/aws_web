<?php
include '../base.php';

// ----------------------------------------------------------------------------



if (is_post()) {
    $password     = req('password');
    $new_password = req('new_password');
    $confirm      = req('confirm');

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    }
    else if (strlen($password) < 8) {
        $_err['password'] = 'At least 8 characters';
    }
    else if (strlen($password) > 100) {
        $_err['password'] = 'Maximum 100 characters';
    }
    else {
        $stm = $_db->prepare('
            SELECT COUNT(*) FROM user
            WHERE password = SHA1(?) AND id = ?
        ');
        $stm->execute([$password, $_user->id]);
        
        if ($stm->fetchColumn() == 0) {
            $_err['password'] = 'Invalid';
        }
    }

    // Validate: new_password
    if ($new_password == '') {
        $_err['new_password'] = 'Required';
    }
    else if (strlen($password) < 8) {
        $_err['password'] = 'At least 8 characters';
    }
    else if (strlen($password) > 100) {
        $_err['password'] = 'Maximum 100 characters';
    }

    // Validate: confirm
    if (!$confirm) {
        $_err['confirm'] = 'Required';
    }
    else if (strlen($password) < 8) {
        $_err['password'] = 'At least 8 characters';
    }
    else if (strlen($password) > 100) {
        $_err['password'] = 'Maximum 100 characters';
    }
    else if ($confirm != $new_password) {
        $_err['confirm'] = 'Not matched';
    }

    // DB operation
    if (!$_err) {
        

        // Update user (password)
        // TODO
        $stm = $_db->prepare('
            UPDATE user
            SET password = SHA1(?)
            WHERE id = ?
        ');
        $stm->execute([$new_password, $_user->id]);

        temp('info', 'Password updated');
        redirect('/user/profile.php');
    }
}

?>


<style>
    .change-pass {
    position: absolute;
    top: 150px;
    right: 180px;
    padding: 140px 100px;
    
    width: 250px;
    border-radius: 20px;
    
    
    opacity: 0.9;
    background-color: #f1f1f1;
    }

    .change-pass-form {
    position: absolute;
    display: grid;
    grid: auto auto auto / auto auto auto;
    gap: 15px;
    top: 70px;
    left: 20px;
    }

    .change-pass-form label {
        font-size: 18px;
    }

    .change-pass-form input {
        padding: 3px;
        font-size: 16px;
        border: 1px solid;
        border-radius: 4px;
        outline: none;
    }

    .sv-btn {
    position: absolute;
    padding: 5px 140px;
    border-radius: 30px;
    cursor: pointer;
    background-color: black;
    color: white;
    border: 1px solid white;
    font-size: 20px;
    top: 150px;
    left: 35px;
    transition: all 0.1s;
    }

    .sv-btn:hover {
        transform: scale(1.1);
    }

    .cpword {
    position: absolute;
    right: 150px;
    top: 0px;
    font-family:'Times New Roman';
    font-size: 25px;
    }

    .arrow {
        position: absolute;
        height: 30px;
        width: 30px;
        top: 15px;
        left: 15px;
    }

</style>

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

<div>
    <img class="signImg" src="../image/signback.jpg">
</div>

<div>
    <img class="BookImg" src="../image/BookImg.png">
</div>

<div class="change-pass">

<a href="/user/profile.php">
    <img class="arrow" src="/image/arrow.png">
</a>

<P class="cpword">Change Password</P>
    <form method="post" class="change-pass-form">
        <label for="password">Password</label>
        <?= html_pass('password', 'maxlength="100"') ?>
        <?= err('password') ?>

        <label for="new_password">New Password</label>
        <?= html_pass('new_password', 'maxlength="100"') ?>
        <?= err('new_password') ?>

        <label for="confirm">Confirm</label>
        <?= html_pass('confirm', 'maxlength="100"') ?>
        <?= err('confirm') ?>

        <section>
            <button class="sv-btn" >Save</button>
        </section>
    </form>
</div>
