<?php
session_start();
session_destroy();
header('location: /emploiDB/login.php');