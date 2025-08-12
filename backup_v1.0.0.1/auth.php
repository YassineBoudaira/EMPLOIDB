<?php 
// include 'auth.php';
include 'include/sess.php';
// include 'include/session.php';
include 'include/connexion.php';

// echo $_SESSION['id'];
// echo $res;
echo $_SESSION['role'];
echo $_SESSION['user_id'];
echo $_SESSION['user'];





?>
<?php
if($_SESSION['role']=="admin" || $_SESSION['role']=="user" ):
// if($_SESSION['role']=='user' & $_SESSION['role']=='admin'):
        
?>
<ul>
    <li>text 01</li>
    <li>text 02</li>
    <li>text 03</li>
    <li>text 04</li>
    <?php
    if($_SESSION['role']=="admin"):
    ?>
    <li>text 05</li>
    <li>text 06</li>
    <li>text 07</li>
    <li>text 08</li>
    <li>text 09</li>
    <li>text 10</li>
    <?php
    endif;
    ?>
</ul>
<?php
endif;
?>