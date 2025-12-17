<?php
include '../base.php';

auth('Admin');

// ----------------------------------------------------------------------------

$arr = $_db->query('SELECT * FROM user')->fetchAll();

// ----------------------------------------------------------------------------


?>

<style>

.pbox {
    margin-left: 200px;
    
}

.up {
    text-decoration: none;
    color: black;
    padding: 5px 10px;
    background-color: rgb(167, 215, 167);
    border-radius: 10px;
    border: 1px solid black;
}

.de {
    margin-top: 10px;
    text-decoration: none;
    color: black;
    padding: 8px 18px;
    background-color: rgb(215, 167, 167);
    border-radius: 10px;
    border: 1px solid black;

}

.up:hover {
    transform: scale(1.1);
}

.de:hover {
    transform: scale(1.1);
}

.table {
    font-size: 18px;
    margin-top: 150px;
}

.table th, .table td {
    border: 1px solid #333;
    padding: 10px 50px;
}

.table th {
    color: #fff;
    background: #666;
}

.table tr:hover td {
    background: #ccc;
}

.table td:has(.popup) {
    position: relative;
}

.table .popup {
    position: absolute;
    top: 50%;
    left: 100%;
    translate: 5px -50%;
    z-index: 1;
    border: 1px solid #333;
    display: none;
}

.table tr:hover .popup {
    display: block;
}

.detail th {
    text-align: left;
}

    .popup {
        width: 100px;
        height: 100px;
    }


.co {
   
    font-size: 25px;
}

#info {
    position: fixed;
    color: black;
    background:rgb(175, 194, 174);
    border: 1px solid #333;
    border-radius: 10px;
    padding: 10px 20px;
    left: 50%;
    translate: -50% 0;
    z-index: 999;

    top: -100px;
    opacity: 0;
}

#info:not(:empty) {
    animation: fade 2s;
}

@keyframes fade {
    0% { top: -100px; opacity: 0; }
   10% { top:  100px; opacity: 1; }
   90% { top:  100px; opacity: 1; }
  100% { top: -100px; opacity: 0; }
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

<a href="/user/profile.php">
            <img class="gback" src="/image/arrow.png">
        </a>

<div class="pbox">

<div id="info"><?= temp('info') ?></div>



<p class="co"><?= count($arr) ?> Record user(s)</p>

<table class="table">
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th></th>
    </tr>

    <?php foreach ($arr as $p): ?>
    <tr>
        <td><?= $p->id ?></td>
        <td><?= $p->name ?></td>
        <td><?= $p->email ?></td>
        <td><?= $p->role ?></td>
        <td>


            <?php if ($p->role == "Member"): ?>

            <a class="up" href="update.php?id=<?= $p->id ?>">Update</a>
            <form method="post" action="delete.php">

            <?= html_hidden('id', $p->id) ?>
                <section>
                    <button class="de">Delete</button>
                </section>
            </form>
            <?php endif; ?>
            <img src="/user_photos/<?= $p->photo ?>" class="popup">
        </td>
    </tr>
    <?php endforeach ?>
</table>

</div>