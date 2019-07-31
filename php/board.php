<?php
include 'database.php';
include 'CRFS.php';

class Board
{
    public $gameId = "";
    private $game = null;
    private $database;
    private $players = null;
    private $defaultStatus = 0;
    private $currentTileId = 0;
            
    function __construct($gameId, $name = '', $sessionId = '', $main = 1, $inns = 0, $traders = 0, $river = 0)
    {
        $this->database = new Database();
        
        if($gameId == 0)
        {
            $this->game = new CRFS();
            
            if($river > 0)
                $this->game->setCurrentTile(66);
            else
                $this->game->setCurrentTile(20);
            
            $this->defaultStatus = ($traders>0?338000:0xB8000);
            $this->insertGame($name, $sessionId);
            $this->insertCardSet($main, $inns, $traders, $river);
        }
        else 
            $this->gameId = $gameId;
        
        
    }
    
    public function startGame()
    {
        $this->initPlayers();
        $this->playPiece(-1, 0, 0);
        $this->game->currentPlayerSequence = 0;
    }
    
    function initPlayers()
    {
        $sql = "SELECT s.playerId FROM crfs_game_player s "
                . "WHERE s.gameId = $this->gameId";
        $result = $this->database->query($sql);
        
        if ($result->num_rows == 0) 
            throw new Exception('Couldn\'t fetch players.');
        
        $sequences = array();
        for($i = $result->num_rows - 1; $i >= 0; --$i)
            $sequences[$i] = $i;
        shuffle($sequences);
        
        $stmt = $this->database->prepare("UPDATE crfs_game_player SET sequence = ?, status = $this->defaultStatus "
                                        . "WHERE gameId = $this->gameId AND playerId = ?");
        $stmt->bind_param('ii', $sequence, $playerId);
        
        $i = 0;
        while($row = $result->fetch_array(MYSQLI_NUM))
        {
            $sequence = $sequences[$i++];
            $playerId = $row[0];
            $stmt->execute();
        }
    }
    
    function insertGame($name, $sessionId)
    {
        $stmt = $this->database->prepare("INSERT INTO crfs_game(sequence, currentTileId, name, sessionId, game, defaultStatus) "
                                        . "VALUES(-1, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sissi', $this->game->currentTileId, $name, $sessionId, $serial, $this->defaultStatus);
        
        $serial = serialize($this->game);
        
        $stmt->execute();
        
        $this->gameId = $this->database->getInsertedId();
    }
    
    function getGame()
    {
        $sql = "SELECT s.game, s.gameId FROM crfs_game s "
                . "WHERE s.gameId = '$this->gameId'";
        $result = $this->database->query($sql);
        
        if ($result->num_rows != 1) 
            throw new Exception('Couldn\'t fetch session.');
        
        $row = $result->fetch_assoc();
        $this->game        = unserialize($row['game']);
        $this->gameId      = $row['gameId'];
        
        $this->getPlayers();
        $this->getAreas();
    }
    
    function updateGame()
    {
        $stmt = $this->database->prepare("UPDATE crfs_game "
                                       . "SET "
                                       . "  possiblePlays = ?, "
                                       . "  game = ?, "
                                       . "  currentTileId = ?, "
                                       . "  sequence = ?, "
                                       . "  refreshes = ? "
                                       . "WHERE gameId = ?");
        $stmt->bind_param('ssiisi', $serialPP, $serialG, $this->game->currentTileId, $this->game->currentPlayerSequence, $refreshes, $this->gameId);
        
        $serialPP = serialize($this->game->possiblePlays);
        $this->game->possiblePlays = null;
        
        $this->updatePlayers();
        $this->game->players       = null;
        
        $this->updateAreas();
        $this->game->areas         = null;
        
        $this->updatePlays();
        
        $this->game->tileCache     = new TileCache();
        
        $refreshes = $this->getRefresh();
        $this->game->tilesHit      = null;
        $this->game->tilesRefresh  = null;
        $this->game->scoring       = null;
        
        $serialG = serialize($this->game);
        
        $stmt->execute();
    }
    
    public function getRefresh()
    {
        $str = "";
        if($this->game->tilesRefresh)
        {
            for($i = 0, $count = count($this->game->tilesRefresh); $i < $count; ++$i)
            {
                if($i > 0)
                    $str .= ';';
                $str .= implode(',', $this->game->tilesRefresh[$i]);
                $str .= ';';
                $str .= implode(',', $this->game->tilesHit[$i]);
                $str .= ';';
                $str .= implode(',', $this->game->scoring[$i]);
            }
        }
        return $str;
    }
    
    public function getSequence()
    {
        $sql = "SELECT s.sequence FROM crfs_game s WHERE s.gameId = $this->gameId";
        $result = $this->database->query($sql);
        
        if ($result->num_rows != 1) 
            throw new Exception('Couldn\'t fetch session.');
        
        $row = $result->fetch_array(MYSQLI_NUM);
        return $row[0];
    }
    
    function updateAreas()
    {
        $stmt = $this->database->prepare("UPDATE crfs_game_area SET openCount = ?, serial = ? WHERE gameId = ? AND areaId = ?");
        $stmt->bind_param('issi', $openCount, $serial, $this->gameId, $id);
        $stmti = $this->database->prepare("INSERT INTO crfs_game_area(gameId, areaId, openCount, serial) VALUES(?,?,?,?)");
        $stmti->bind_param('iiss', $this->gameId, $id, $openCount, $serial);
        
        foreach($this->game->areas as $id => $area)
        {
            if(!$area->dirty)
                continue;
            
            $insert = $area->dirtyInsert;
            
            $area->dirty = false;
            $area->dirtyInsert = false;
            
            $openCount = $area->openCount;
            $serial    = serialize($area);
            
            if($insert)
                $stmti->execute();
            else
                $stmt->execute();
        }
        
        $this->game->areas = null;
    }
    
    function getAreas()
    {
        $sql = "SELECT s.areaId, s.serial FROM crfs_game_area s "
                . "WHERE s.openCount > 0 AND s.gameId = $this->gameId";
        $result = $this->database->query($sql);
        
        if (!$result) 
            throw new Exception('Couldn\'t fetch session.');
        
        while($row = $result->fetch_array(MYSQLI_NUM))
            $this->game->areas[$row[0]] = unserialize($row[1]);
    }
    
    function updatePlayers()
    {
        $stmt = null;
        $status = 0;
        $sequence = 0;
        
        for($i = count($this->game->players) - 1; $i >= 0; --$i)
        {
            $player =& $this->game->players[$i];
            if(!$player->dirty)
                continue;
            
            if(!$stmt)
            {
                $stmt = $this->database->prepare("UPDATE crfs_game_player SET status = ? WHERE gameId = ? AND sequence = ? ");
                $stmt->bind_param('iii', $status, $this->gameId, $sequence);
            }
            
            $status = $player->getStatus();
            $sequence = $player->playerSequence;
            
            $stmt->execute();
        }
    }
    
    public function insertPlayer($playerId, $color)
    {
        $stmt = $this->database->prepare("INSERT INTO crfs_game_player(gameId, playerId, color) VALUES(?,?,?) ");
        $stmt->bind_param('iii', $this->gameId, $playerId, $color);
        $stmt->execute();
    }
    
    function getFlatPlayers()
    {
        $sql = "SELECT gp.status, gp.color, p.icon "
                . "FROM crfs_game_player gp "
                . "LEFT JOIN crfs_player p ON p.playerId = gp.playerId "
                . "WHERE gameId = $this->gameId "
                . "ORDER BY sequence ";
        $result = $this->database->query($sql);
        
        $string = '';
        while($row = $result->fetch_array(MYSQLI_NUM))
            $string .= $row[0] . ',' . $row[1] . ',' . $row[2] . ',';
        
        return substr($string, 0, -1);
    }
    
    function getPlayers()
    {
        $sql = "SELECT gp.sequence, gp.status "
                . "FROM crfs_game_player gp "
                . "WHERE gp.gameId = $this->gameId "
                . "ORDER BY gp.sequence ";
        $result = $this->database->query($sql);
        
        if($this->game->players === null)
            $this->game->players = array();
        
        while($row = $result->fetch_array(MYSQLI_NUM))
        {
            $player = new Player($row[0]);
            $player->setStatus($row[1]);
            
            array_push($this->game->players, $player);
        }
    }
    
    function popCard()
    {
        return 23;
        $sql = "SELECT tileId, sequence FROM crfs_game_cards "
                . "WHERE gameId = $this->gameId "
                . "ORDER BY sequence LIMIT 1";
        $result = $this->database->query($sql);
        if ($result->num_rows != 1) 
            return -1;
        
        $row = $result->fetch_array(MYSQLI_NUM );
        $sql = "DELETE FROM crfs_game_cards "
                . "WHERE gameId = $this->gameId AND sequence = " + $row[1];
        $this->database->query($sql);
        
        return $row[0];
    }
    
    function insertCardSet($main, $inns, $traders, $river)
    {
        $cardsLeft = array();
        for ($i = 0; $i < $main; $i++) 
        {
            array_push($cardsLeft, 1);
            array_push($cardsLeft, 1);
            array_push($cardsLeft, 1);
            array_push($cardsLeft, 1);
            array_push($cardsLeft, 2);
            array_push($cardsLeft, 2);
            array_push($cardsLeft, 3);
            array_push($cardsLeft, 4);
            array_push($cardsLeft, 4);
            array_push($cardsLeft, 4);
            array_push($cardsLeft, 5);
            array_push($cardsLeft, 6);
            array_push($cardsLeft, 7);
            array_push($cardsLeft, 7);
            array_push($cardsLeft, 8);
            array_push($cardsLeft, 8);
            array_push($cardsLeft, 8);
            array_push($cardsLeft, 9);
            array_push($cardsLeft, 9);
            array_push($cardsLeft, 10);
            array_push($cardsLeft, 10);
            array_push($cardsLeft, 10);
            array_push($cardsLeft, 11);
            array_push($cardsLeft, 11);
            array_push($cardsLeft, 12);
            array_push($cardsLeft, 13);
            array_push($cardsLeft, 13);
            array_push($cardsLeft, 14);
            array_push($cardsLeft, 14);
            array_push($cardsLeft, 15);
            array_push($cardsLeft, 15);
            array_push($cardsLeft, 15);
            array_push($cardsLeft, 16);
            array_push($cardsLeft, 16);
            array_push($cardsLeft, 16);
            array_push($cardsLeft, 16);
            array_push($cardsLeft, 16);
            array_push($cardsLeft, 17);
            array_push($cardsLeft, 17);
            array_push($cardsLeft, 17);
            array_push($cardsLeft, 18);
            array_push($cardsLeft, 18);
            array_push($cardsLeft, 18);
            array_push($cardsLeft, 19);
            array_push($cardsLeft, 19);
            array_push($cardsLeft, 19);
            array_push($cardsLeft, 20);
            array_push($cardsLeft, 20);
            array_push($cardsLeft, 20);
            array_push($cardsLeft, 21);
            array_push($cardsLeft, 21);
            array_push($cardsLeft, 21);
            array_push($cardsLeft, 21);
            array_push($cardsLeft, 21);
            array_push($cardsLeft, 21);
            array_push($cardsLeft, 21);
            array_push($cardsLeft, 21);
            array_push($cardsLeft, 22);
            array_push($cardsLeft, 22);
            array_push($cardsLeft, 22);
            array_push($cardsLeft, 22);
            array_push($cardsLeft, 22);
            array_push($cardsLeft, 22);
            array_push($cardsLeft, 22);
            array_push($cardsLeft, 22);
            array_push($cardsLeft, 22);
            array_push($cardsLeft, 23);
            array_push($cardsLeft, 23);
            array_push($cardsLeft, 23);
            array_push($cardsLeft, 23);
            array_push($cardsLeft, 24);
        }
        for ($i = 0; $i < $inns; $i++) 
        {
            array_push($cardsLeft, 25);
            array_push($cardsLeft, 26);
            array_push($cardsLeft, 26);
            array_push($cardsLeft, 27);
            array_push($cardsLeft, 28);
            array_push($cardsLeft, 29);
            array_push($cardsLeft, 30);
            array_push($cardsLeft, 31);
            array_push($cardsLeft, 32);
            array_push($cardsLeft, 33);
            array_push($cardsLeft, 34);
            array_push($cardsLeft, 35);
            array_push($cardsLeft, 36);
            array_push($cardsLeft, 37);
            array_push($cardsLeft, 38);
            array_push($cardsLeft, 39);
            array_push($cardsLeft, 40);
            array_push($cardsLeft, 41);
        }
        for ($i = 0; $i < $traders; $i++) 
        {
            array_push($cardsLeft, 42);
            array_push($cardsLeft, 43);
            array_push($cardsLeft, 44);
            array_push($cardsLeft, 45);
            array_push($cardsLeft, 46);
            array_push($cardsLeft, 47);
            array_push($cardsLeft, 48);
            array_push($cardsLeft, 49);
            array_push($cardsLeft, 50);
            array_push($cardsLeft, 51);
            array_push($cardsLeft, 52);
            array_push($cardsLeft, 53);
            array_push($cardsLeft, 54);
            array_push($cardsLeft, 55);
            array_push($cardsLeft, 56);
            array_push($cardsLeft, 57);
            array_push($cardsLeft, 58);
            array_push($cardsLeft, 59);
            array_push($cardsLeft, 60);
            array_push($cardsLeft, 61);
            array_push($cardsLeft, 62);
            array_push($cardsLeft, 63);
            array_push($cardsLeft, 64);
            array_push($cardsLeft, 65);
        }
        
        shuffle($cardsLeft);

        array_push($cardsLeft, 20);

        if($river > 0)
        {
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

            array_push($cardsLeft, 77);
            for($i = 0; $i < 12; $i++)
                array_push($cardsLeft, $result[$i]);
        }
        
        $stmt = $this->database->prepare("INSERT INTO crfs_game_cards(gameId, sequence, tileId) VALUES(?,?,?) ");
        $stmt->bind_param('iis', $this->gameId, $i, $tileId);
        
        for($i = 0, $count = count($cardsLeft); $i < $count; ++$i)
        {
            $tileId = $cardsLeft[$i];
            $stmt->execute();
        }
    }
    
    function insertPlay($x, $y)
    {
        $play =& $this->game->cardsPlayed[$x][$y];

        $stmt = $this->database->prepare("INSERT INTO crfs_game_plays(gameId, sequence, play) VALUES(?,?,?) ");
        $stmt->bind_param('iis', $this->gameId, $sequence, $playString);

        $playString = $play->getPlayString($x, $y);
        $sequence   = $play->sequence;
        
        $stmt->execute();
    }
    
    function updatePlays()
    {
        $stmt = null;
        $sequence = 0;
        $play = 0;
        
        for($i = 0, $count = count($this->game->tilesRefresh); $i < $count; ++$i)
        {
            for($j = 0, $countJ = count($this->game->tilesRefresh[$i]); $j < $countJ;)
            {
                if(!$stmt)
                {
                    $stmt = $this->database->prepare("UPDATE crfs_game_plays SET play = ? WHERE gameId = ? AND sequence = ? ");
                    $stmt->bind_param('sii', $playString, $this->gameId, $sequence);
                }
                $x = $this->game->tilesRefresh[$i][$j++];
                $y = $this->game->tilesRefresh[$i][$j++];

                $play =& $this->game->cardsPlayed[$x][$y];

                $playString = $play->getPlayString($x, $y);
                $sequence   = $play->sequence;

                $stmt->execute();
            }
        }
    }
    
    public function playPiece($playerSequence, $x, $y, $rotation = 0, $meepleType = 0, $meeplePosition = 0)
    {
        $this->getGame();
        
        if($playerSequence != $this->game->currentPlayerSequence)
            throw new Exception('Incorrect player');
        
        $this->game->playPiece($x, $y, $rotation, $meepleType, $meeplePosition);
        
        $this->insertPlay($x, $y);
        
        $nextTileId = $this->popCard();
        if($nextTileId < 0)
            $this->game->finish();
        else
        {
            $this->game->setCurrentTile($nextTileId);
            $this->game->progress();
        }
        
        $this->updateGame();
    }
    
    public function getFlatBoard($sequence = -1)
    {
        $sql = "SELECT GROUP_CONCAT(c.play), currentTileId, g.refreshes FROM crfs_game_plays c "
                . "LEFT JOIN crfs_game g ON g.gameId = c.gameId "
                . "WHERE g.gameId = $this->gameId AND c.sequence > $sequence "
                . "ORDER BY c.sequence ";
        $result = $this->database->query($sql);
        if ($result->num_rows != 1) 
            return;
        
        $row = $result->fetch_array(MYSQLI_NUM);
        
        $this->currentTileId = $row[1];
        return $row[2] . '#' . $row[0];
    }
    
    public function getCurrentTileId()
    {
        return $this->currentTileId;
    }
    
    public function getFlatPlays()
    {
        $sql = "SELECT g.possiblePlays FROM crfs_game g WHERE g.gameId = $this->gameId";
        $result = $this->database->query($sql);
        if ($result->num_rows != 1) 
            return;
        
        $row = $result->fetch_array(MYSQLI_NUM);
        return json_encode(unserialize($row[0]));
    }
}
/*
$test = new Board('58ae4e6d3d578');
echo $test->getBoard();
 
*/