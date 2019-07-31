<?php
include 'database.php';

$main = array_key_exists('main', $_GET)?min(1, intval($_GET['main'])):1;
$river = array_key_exists('river', $_GET)?max(1, intval($_GET['river'])):0;
$inns = array_key_exists('inns', $_GET)?intval($_GET['inns']):0;
$traders = array_key_exists('traders', $_GET)?intval($_GET['traders']):0;

$tileset = array();
$x = 0;

$cardsPlayed = '';
for ($i = 0; $i < $main; $i++) 
{
    array_push($tileset, 1);
    array_push($tileset, 1);
    array_push($tileset, 1);
    array_push($tileset, 1);
    array_push($tileset, 2);
    array_push($tileset, 2);
    array_push($tileset, 3);
    array_push($tileset, 4);
    array_push($tileset, 4);
    array_push($tileset, 4);
    array_push($tileset, 5);
    array_push($tileset, 6);
    array_push($tileset, 7);
    array_push($tileset, 7);
    array_push($tileset, 8);
    array_push($tileset, 8);
    array_push($tileset, 8);
    array_push($tileset, 9);
    array_push($tileset, 9);
    array_push($tileset, 10);
    array_push($tileset, 10);
    array_push($tileset, 10);
    array_push($tileset, 11);
    array_push($tileset, 11);
    array_push($tileset, 12);
    array_push($tileset, 13);
    array_push($tileset, 13);
    array_push($tileset, 14);
    array_push($tileset, 14);
    array_push($tileset, 15);
    array_push($tileset, 15);
    array_push($tileset, 15);
    array_push($tileset, 16);
    array_push($tileset, 16);
    array_push($tileset, 16);
    array_push($tileset, 16);
    array_push($tileset, 16);
    array_push($tileset, 17);
    array_push($tileset, 17);
    array_push($tileset, 17);
    array_push($tileset, 18);
    array_push($tileset, 18);
    array_push($tileset, 18);
    array_push($tileset, 19);
    array_push($tileset, 19);
    array_push($tileset, 19);
    array_push($tileset, 20);
    array_push($tileset, 20);
    array_push($tileset, 20);
    array_push($tileset, 21);
    array_push($tileset, 21);
    array_push($tileset, 21);
    array_push($tileset, 21);
    array_push($tileset, 21);
    array_push($tileset, 21);
    array_push($tileset, 21);
    array_push($tileset, 21);
    array_push($tileset, 22);
    array_push($tileset, 22);
    array_push($tileset, 22);
    array_push($tileset, 22);
    array_push($tileset, 22);
    array_push($tileset, 22);
    array_push($tileset, 22);
    array_push($tileset, 22);
    array_push($tileset, 22);
    array_push($tileset, 23);
    array_push($tileset, 23);
    array_push($tileset, 23);
    array_push($tileset, 23);
    array_push($tileset, 24);
}
for ($i = 0; $i < $inns; $i++) 
{
    array_push($tileset, 25);
    array_push($tileset, 26);
    array_push($tileset, 26);
    array_push($tileset, 27);
    array_push($tileset, 28);
    array_push($tileset, 29);
    array_push($tileset, 30);
    array_push($tileset, 31);
    array_push($tileset, 32);
    array_push($tileset, 33);
    array_push($tileset, 34);
    array_push($tileset, 35);
    array_push($tileset, 36);
    array_push($tileset, 37);
    array_push($tileset, 38);
    array_push($tileset, 39);
    array_push($tileset, 40);
    array_push($tileset, 41);
}
for ($i = 0; $i < $traders; $i++) 
{
    array_push($tileset, 42);
    array_push($tileset, 43);
    array_push($tileset, 44);
    array_push($tileset, 45);
    array_push($tileset, 46);
    array_push($tileset, 47);
    array_push($tileset, 48);
    array_push($tileset, 49);
    array_push($tileset, 50);
    array_push($tileset, 51);
    array_push($tileset, 52);
    array_push($tileset, 53);
    array_push($tileset, 54);
    array_push($tileset, 55);
    array_push($tileset, 56);
    array_push($tileset, 57);
    array_push($tileset, 58);
    array_push($tileset, 59);
    array_push($tileset, 60);
    array_push($tileset, 61);
    array_push($tileset, 62);
    array_push($tileset, 63);
    array_push($tileset, 64);
    array_push($tileset, 65);
}
shuffle($tileset);

array_push($tileset, 20);

if($river > 0)
{
    $x = 0;
    $result = array();
    array_push($result, 67);
    array_push($result, 68);
    array_push($result, 69);
    array_push($result, 70);
    array_push($result, 71);
    array_push($result, 72);
    array_push($result, 73);
    array_push($result, 74);
    array_push($result, 75);
    array_push($result, 76);
    
    shuffle($result);
    
    array_push($tileset, 77);
    for($i = 0; $i < 12; $i++)
        array_push($tileset, $result[$i]);
    
    $cardsPlayed = array(20,0,0,0,0);
}
else
    $cardsPlayed = array(66,0,0,0,0);

$tileset = array_reverse($tileset);
$test = json_encode($tileset);

$tileset = json_decode($test);
print_r($tileset);

$players = explode(',', $_GET['players']);
$name = array_key_exists('name', $_GET)?intval($_GET['name']):'';

$sessionId = uniqid();
$sql = "INSERT INTO crfs_session(sessionId, sequence, name, cardsRemaining, cardsPlayed) "
        . "VALUES('$sessionId'," . random_int(0,count($players) - 1) . ",'$name','" . json_encode($tileset) . "','" . json_encode($cardsPlayed) . "')";
$result = $conn->query($sql);
$conn->close();

echo $sessionId;
?>