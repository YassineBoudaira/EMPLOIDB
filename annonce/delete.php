<?php include '../include/session.php'; ?>
<?php
$id= $_GET['id'];
include '../include/connexion.php';
$req = $bd->prepare('delete from annonces where id=?');
$req->execute([$id]);
header('location: /emploiDB/annonce/index.php?msg=deleted');