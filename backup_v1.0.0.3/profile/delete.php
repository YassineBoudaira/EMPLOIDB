<?php include '../include/session.php'; ?>
<?php
$id= $_GET['id'];
include '../include/connexion.php';
$req = $bd->prepare('delete from profiles where id=?');
$req->execute([$id]);
header('location: /profile/index.php?msg=deleted');