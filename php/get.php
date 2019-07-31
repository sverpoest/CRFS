<?php
session_start();
include 'board.php';

$gameId = (array_key_exists('crfs_gameId', $_SESSION)?$_SESSION['crfs_gameId']:null);
if(!$gameId)
    throw new Exception('No game id present.');

$action = (array_key_exists('a', $_GET)?$_GET['a']:0);

$board = new Board($gameId);

$retString = '';

switch($action)
{
    // Tick
    case 0:
        $retString = $board->getSequence();
        break;
    // Get board with plays
    case 1:
        $retString  = $board->getFlatPlays();
        $retString .= '#';
    // Get board without plays
    case 2:
        $sequence = (array_key_exists('sequence', $_GET)?$_GET['sequence']:-1);
        $retString .= $board->getFlatBoard($sequence);
        $retString .= '#';
        $retString .= $board->getCurrentTileId();
        $retString .= '#';
        $retString .= $board->getFlatPlayers();
        break;
}
    

// Send processing ok
echo '0';
// Send data
echo $retString;