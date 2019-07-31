<?php
include 'pieces.php';

$sessionId  = $_GET['session'];

$session = new Session($sessionId);
$row = $session->querySession();

echo $row['sequence'];