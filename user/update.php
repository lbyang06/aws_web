<?php
include '../base.php';

auth('Admin');

// ----------------------------------------------------------------------------

if (is_get()) {

    $stm = $_db->prepare('SELECT *FROM user WHERE id = ?');
    $stm->execute([$_user->id]);
    $u = $stm->fetch();

    if (!$u) {
        redirect('/');
    }

    extract((array)$u);
    $_SESSION['photo'] = $u->photo;   
}


if (is_post()) {

$email = req('email');
$name  = req('name');
$photo = $_SESSION['photo'];
$f = get_file('photo');
$role = req('role');

// Validate: email
if ($email == '') {
    $_err['email'] = 'Required';
}
else if (strlen($email) > 100) {
    $_err['email'] = 'Maximum 100 characters';
}
else if (!is_email($email)) {
    $_err['email'] = 'Invalid email';
}
else {

    $stm = $_db->prepare('
        SELECT COUNT(*) FROM user
        WHERE email = ? AND id != ?
    ');
    $stm->execute([$email, $_user->id]);

    if ($stm->fetchColumn() > 0) {
        $_err['email'] = 'email already use';
    }
}

// Validate: name
if ($name == '') {
    $_err['name'] = "can't empty";
}
else if (strlen($name) > 100) {
    $_err['name'] = 'Maximum 100 characters';
}



if ($f) {
    if (!str_starts_with($f->type, 'image/')) {
        $_err['photo'] = 'Must be image';
    }
    else if ($f->size > 1 * 1024 * 1024) {
        $_err['photo'] = 'Maximum 1MB';
    }
}



if (!$_err) {



    if ($f) {
         // (1) Delete and save photo --> optional
         if ($photo !== 'user.png'){
            unlink("../user_photos/$photo");
            }
        $photo = save_photo($f, '../user_photos');
    }

    // (2) Update user (email, name, photo)
    // TODO
    $stm = $_db->prepare('
        UPDATE user
        SET email = ?, name = ?, photo = ?
        WHERE id = ?
    ');
    $stm->execute([$email, $name, $photo, $_user->id]);

    // (3) Update global user object
    $_user->email = $email;
    $_user->name  = $name;
    $_user->photo = $photo;

    temp('info', 'User Updated');
    redirect('/user/profile.php');

}
}


?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="../js/app.js"></script>

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

.upd {
    padding: 10px 20px;
    border-radius: 10px;
    border: 1px solid black;
    font-size: 18px;
}
.upd:hover {
    transform: scale(1.1);
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/app.js"></script>

    <title>BY BOOK</title>
</head>

<a href="all_user.php">
            <img class="gback" src="/image/arrow.png">
        </a>






<form method="post" class="form" enctype="multipart/form-data">
    <label for="email">Email</label>
    <?= html_email('email', 'maxlength="100"') ?>
    <?= err('email') ?>

    <label for="name">Name</label>
    <?= html_name('name', 'maxlength="100"') ?>
    <?= err('name') ?>



    <label for="photo">Photo</label>
    <label class="upload" tabindex="0">
    <?= html_file('photo', 'image/*', 'hidden') ?>
    <img src="/user_photos/<?= $photo ?>">
    </label>
    <?= err('photo') ?>

    <label for="name">Role</label>
    <?= html_name('role', 'maxlength="100"') ?>
    <?= err('role') ?>


    <section>
        <button class="save-btn">Update</button>
    </section>
</form>





