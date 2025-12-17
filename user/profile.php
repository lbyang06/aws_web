<?php 
require '../base.php';



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

        temp('info', 'Profile Updated');
        redirect('/user/profile.php');

    }
}


?>




<style>
    input:focus {
        border: 2px solid black;
    }

   .blur {
        filter: blur(1px);
    }

    header {

        height: 200px;
        background-color: #8e5039 ;

       
    }



    .profile {
        position: absolute;
        display: flex;
        justify-content: center;
        align-items: center ;
        background-color:rgb(117, 153, 116);
        height: 200px;
        width: 250px;
        left: 40%;
        border-radius: 30px;

        

    }

    .profile-img {
        display: block;
        width: 100px;
        height: 100px;
        cursor: pointer;
        border-radius: 20px;
        border: 1px solid black;
    }

    .profile-img:hover {
        transform: scale(1.1);
    }

    .Update-form {
       display: block;
       background-color: rgb(117, 153, 116);
       position: fixed;
       border-radius: 20px;
       left: 25%;
       bottom: 20px;
       height: 500px;
       width: 50%;
       z-index: 2;
    }

    .upload img {
    display: block;
    border: 2px solid #333;
    width: 200px;
    height: 200px;
    object-fit: cover;
    cursor: pointer;
}   

.Update-info {
    position: absolute;
    display: grid;
    grid: auto auto auto / auto auto auto;
    gap: 15px;
    top: 100px;
    left: 20px;
}

.Update-info label {
    font-size: 18px;
    color: black;
}

.Update-info input {
    padding: 3px;
    font-size: 16px;
    border: 1px solid;
    border-radius: 4px;
    outline: none;
}
    .user-name {
        display: flex;
        position: absolute;
        top: 150px;
        text-align: center;
        color: black;
    }

    .c-t-D {
        display: flex;
        position: absolute;
        top: 155px;
        text-align: center;
    }

    .u-cancel-btn {
    position: absolute;
    padding: 5px 140px;
    border-radius: 30px;
    cursor: pointer;
    background-color: white;
    color: black
    ;
    font-size: 20px;
    bottom: 33px;
    right: 25px;
    transition: all 0.1s;
    }

    .u-cancel-btn:hover {
        transform: scale(1.1);
    }

    .save-btn {
    position: absolute;
    padding: 5px 140px;
    border-radius: 30px;
    cursor: pointer;
    background-color: black;
    color: white;
    border: 1px solid white;
    font-size: 20px;
    top: 330px;
    left: 10px;
    transition: all 0.1s;
    }

    .save-btn:hover {
        transform: scale(1.1);
    }
    


    .home-btn {
        position: absolute;
        top: 210px;
        left: 10px;
        height: 50px;
        width: 120px;
        background-color:rgb(141, 196, 139);
        border-radius: 18px;
        border: 1px solid black; 
    }

    .home-p {
        position: absolute;
        height: 50px;
        width: 50px;
        background-color:rgb(141, 196, 139);
        border-radius: 18px;
        border: 1px solid black; 
    }

    .home-btn p {
        text-decoration: none;
        color: black;
        right: 15px;
        position: absolute;
        font-size: 18px;
    }

    .go-profile {
        position: absolute;
        background-color:rgb(176, 227, 174);
        border-radius: 15px;
        height: 30px;
        width: 100px;
        top: 10px;
        left: 10px;
        text-align: center;
        font-size: 20px;
        cursor: pointer;
    }

    .go-profile:hover {
        background-color: #64af62;
    }

    .go-password {
        position: absolute;
        background-color:rgb(176, 227, 174);
        border-radius: 15px;
        height: 30px;
        width: 100px;
        top: 10px;
        text-align: center;
        font-size: 20px;
        left: 120px;
        cursor: pointer;
        text-decoration: none;
        color: black;
        border: 1px solid black;
    }


    .go-password:hover {
        background-color: #64af62;
    }

    .err {
        color: red;
    }

    .change-pass {
       display: block;
       background-color: rgb(117, 153, 116);
       position: fixed;
       border-radius: 20px;
       left: 25%;
       bottom: 20px;
       height: 500px;
       width: 50%;
       z-index: 2;
    }

    .new-pass-form {
    position: absolute;
    display: grid;
    grid: auto auto auto / auto auto auto;
    gap: 15px;
    top: 100px;
    left: 20px;
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

.obox {
    justify-content: center;
    align-items: center;
    text-align: center;
}

.myorder {
    position: absolute;
    display: block;
    background-color: #64af62;
    left: 40%;
    top: 250px;
    border-radius: 10px;
    border: 1px solid black;
    padding: 30px 30px;
    text-align: center;
    justify-content: center;
    text-decoration: none;
    color: black;
   

}

.myorder:hover {
    transform: scale(1.1);
}

.probox {
    justify-content: center;
    align-items: center;
    text-align: center;
}

.mypro {
    position: absolute;
    display: block;
    background-color: #64af62;
    left: 50%;
    top: 250px;
    border-radius: 10px;
    border: 1px solid black;
    padding: 30px 30px;
    text-align: center;
    justify-content: center;
    text-decoration: none;
    color: black;
   

}

.mypro:hover {
    transform: scale(1.1);
}

.inbox {
    justify-content: center;
    align-items: center;
    text-align: center;

}

.in {
    position: absolute;
    display: block;
    background-color: #64af62;
    left: 50%;
    top: 350px;
    border-radius: 10px;
    border: 1px solid black;
    padding: 30px 30px;
    text-align: center;
    justify-content: center;
    text-decoration: none;
    color: black;
}

.in:hover {
    transform: scale(1.1);
}

.usbox {
    justify-content: center;
    align-items: center;
    text-align: center;
}

.us {
    position: absolute;
    display: block;
    background-color: #64af62;
    left: 40%;
    top: 350px;
    border-radius: 10px;
    border: 1px solid black;
    padding: 30px 30px;
    text-align: center;
    justify-content: center;
    text-decoration: none;
    color: black;
}

.us:hover {
    transform: scale(1.1);
}

.ins_u {
    justify-content: center;
    align-items: center;
    text-align: center;
}

.is {
    position: absolute;
    display: block;
    background-color: #64af62;
    left: 40%;
    top: 450px;
    border-radius: 10px;
    border: 1px solid black;
    padding: 30px 30px;
    text-align: center;
    justify-content: center;
    text-decoration: none;
    color: black;
}

</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
</head>



<header>

    <div id="info"><?= temp('info') ?></div>
    <div class="profile">
       <?php if ($_user): ?>

        
        <img class="profile-img" src="../user_photos/<?= $_user->photo ?>">

        <div class="user-name">
            <?= $_user->name ?>
            
        </div>

        <div class="c-t-d">
            <P>  click to edit</P>
        </div>

        <?php endif ?>
    </div>

</header>


<body>



    <a href="/">
    <div class="home-btn">
        
            <p>Home</p>
            <img class="home-p" src="/image/house.png">
      
    </div>
    </a>

<div class="Update-form">

            <button class="go-profile">Profile</button>
            <a href="password.php" class="go-Password">Password</a>
            

            <form method="post" class="Update-info" enctype="multipart/form-data">
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


                <section>
                    <button class="save-btn">Save</button>
                </section>
            </form>

         <button class="u-cancel-btn">Cancel</button>

</div>


<?php if ($_user->role == 'Member'): ?>
<div class="obox">
    <a class="myorder" href="/order/history.php">
        My Orders
    </a>
</div>
<?php elseif ($_user->role == 'Admin'): ?>
<div class="obox">
    <a class="myorder" href="/order/history.php">
        All Order
    </a>
</div>


<div class="probox">
    <a class="mypro" href="/product/all_product.php">
        Products
    </a>
</div>

<div class="inbox">
    <a class="in" href="/product/insert.php">Insert Book</a>
</div>

<div class="usbox">
    <a class="us" href="/user/all_user.php">User List</a>
</div>

<div class="ins_u">
    <a class="is" href="/user/insert.php">Add user</a>
</div>

<?php endif; ?>


</body>

<?php if ($_err): ?>
    <script>
        window.updateFormError = true;
    </script>
<?php endif ?>


<script>

    // update-form
      $('.Update-form').hide();

      $('.profile-img').on('click', e =>{
        $('.Update-form').slideDown();

      });

      $('.u-cancel-btn').on('click', e =>{
        $('.Update-form').slideUp();
        $('.change-pass').slideUp();

      });

      $(function() {
    if (window.updateFormError) {
        $('.Update-form').show();

    }
    });

    $('.go-profile').on('click', e =>{
        $('.change-pass').hide();
        $('.Update-form').show();
    });





     

</script>