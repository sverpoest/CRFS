<?php
session_start();

include 'board.php';

$main = $_GET['main'];
if($main <= 0)
    $main = 1;
$inns = $_GET['inns'];
$traders = $_GET['traders'];
$river = $_GET['river'];
if($river > 1)
    $river = 1;

$db = new Database();

$sql = "DELETE FROM `crfs_game_cards`";
$db->query($sql);
$sql = "DELETE FROM `crfs_game_player`";
$db->query($sql);
$sql = "DELETE FROM `crfs_game_plays`";
$db->query($sql);
$sql = "DELETE FROM `crfs_game_area`";
$db->query($sql);
$sql = "DELETE FROM `crfs_game_area_link`";
$db->query($sql);
$sql = "DELETE FROM `crfs_game`";
$db->query($sql);

$board = new Board("", "HelloGame", uniqid(), $main, $inns, $traders, $river);
$_SESSION['crfs_gameId'] = $board->gameId;

$board->insertPlayer(0, 0);
$board->insertPlayer(1, 1);

$board->startGame();

echo '0';