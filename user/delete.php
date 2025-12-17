<?php
include '../base.php';

auth('Admin');

// ----------------------------------------------------------------------------
if (is_post()) {
    $id = req('id');


    $stm = $_db->prepare('SELECT photo FROM user WHERE id = ?');
    $stm->execute([$id]);
    $photo = $stm->fetchColumn();

 
    if ($photo) {
        unlink("../user_photos/$photo");
    }

    $stm = $_db->prepare('DELETE FROM user WHERE id = ?');
    $stm->execute([$id]);

    temp('info', 'User deleted');
}

redirect('all_user.php');
?>
