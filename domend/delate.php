<?php include '../include/session.php'; ?>
<?php
$id= $_GET['id'];
include '../include/connexion.php';
$req = $bd->prepare('delete from postulation where id=?');
$req->execute([$id]);
header('location: /emploiDB/domand/domandes.php?msg=deleted');