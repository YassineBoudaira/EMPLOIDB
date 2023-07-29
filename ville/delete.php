<?php include '../include/session.php'; ?>
<?php
$id= $_GET['id'];
include '../include/connexion.php';
$req = $bd->prepare('delete from villes where id=?');
$req->execute([$id]);
header('location: /emploiDB/ville/index.php?msg=deleted');