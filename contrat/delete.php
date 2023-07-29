<?php include '../include/session.php'; ?>
<?php
$id= $_GET['id'];
include '../include/connexion.php';
$req = $bd->prepare('delete from contrats where id=?');
$req->execute([$id]);
header('location: /emploiDB/contrat/index.php?msg=deleted');
