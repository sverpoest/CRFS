<?php
session_start();
include 'board.php';

$gameId = (array_key_exists('crfs_gameId', $_SESSION)?$_SESSION['crfs_gameId']:null);
if(!$gameId)
    throw new Exception('No game id present.');
$sequence = (array_key_exists('playerSequence', $_SESSION)?$_SESSION['playerSequence']:null);
if($sequence === null)
    throw new Exception('No sequence present.');

$action = (array_key_exists('a', $_GET)?$_GET['a']:0);

$board = new Board($gameId);

$retString = '';

switch($action)
{
    // Play tile
    case 1:
        if(!array_key_exists('x', $_GET) ||
           !array_key_exists('y', $_GET) ||
           !array_key_exists('rotation', $_GET) ||
           !array_key_exists('meepleType', $_GET) ||
           !array_key_exists('meeplePosition', $_GET))
                throw new Exception('Incorrect parameters.'); 
        $retString = $board->playPiece($_SESSION['playerSequence'], 
                                        $_GET['x'], $_GET['y'], $_GET['rotation'], 
                                        $_GET['meepleType'], $_GET['meeplePosition']);
        break;
    // Get players
    case 2:
        $retString = $board->getFlatPlayers();
        break;
}
 

// Send processing ok
echo '0';
// Send data
echo $retString;