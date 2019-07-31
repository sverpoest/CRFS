<?php
include 'database.php';

class Session
{
    public $tiles = [];
    public $board = [];
    public $tileSet = [];
    public $players = [];
    public $player = [];
    public $walkSettings = [];
    public $dirtyPlayers = [];
    public $currentTileId = 0;
    public $currentPlayer = 0;
    public $currentSequence = 0;
    public $playerCount = 0;
    public $sessionId = 0;
    
    public $walkIndex = 0;
    public $walked = [];
    public $walkedSummary = [];
    
    public $closeTrue = false;
    public $closeMeeple = [];
    public $closePieces = [];
    public $closeXOrigin = 0;
    public $closeYOrigin = 0;
    
    public $database;

    function __construct($sessionId)
    {
        $this->database = new Database();
        
        $this->sessionId        = $sessionId;
        
        $this->walkSettings = array('checkClose'=>0);
    }
    
    function __destruct()
    {
        foreach ($this->dirtyPlayers as $key => $value)
            $this->updatePlayer($key);
    }
    
    function initTiles()
    {   
        $this->tiles[1] = array(array(0,0,0,0),
                        array(0),
                        array(9,1,2,3,4,5,6,7,8));
        $this->tiles[2] = array(array(0,0,1,0),
                        array(0),
                        array(1,3),
                        array(9,1,2,3,4,5,6,7,8));
        $this->tiles[3] = array(array(2,2,2,2),
                        array(4,1,2,3,4,5,6,7,8));
        $this->tiles[4] = array(array(2,2,0,2),
                        array(3,1,2,3,4,7,8),
                        array(9,5,6));
        $this->tiles[5] = array(array(2,2,0,2),
                        array(4,1,2,3,4,7,8),
                        array(9,5,6));
        $this->tiles[6] = array(array(2,2,1,2),
                        array(3,1,2,3,4,7,8),
                        array(1,3),
                        array(9,5),
                        array(9,6));
        $this->tiles[7] = array(array(2,2,1,2),
                        array(4,1,2,3,4,7,8),
                        array(1,3),
                        array(9,5),
                        array(9,6));
        $this->tiles[8] = array(array(2,0,0,2),
                        array(3,1,2,7,8),
                        array(9,3,4,5,6));
        $this->tiles[9] = array(array(2,0,0,2),
                        array(4,1,2,7,8),
                        array(9,3,4,5,6));
        $this->tiles[10] = array(array(2,1,1,2),
                        array(3,1,2,7,8),
                        array(1,2,3),
                        array(9,3,6),
                        array(9,4,5));
        $this->tiles[11] = array(array(2,1,1,2),
                        array(4,1,2,7,8),
                        array(1,2,3),
                        array(9,3,6),
                        array(9,4,5));
        $this->tiles[12] = array(array(0,2,0,2),
                        array(3,3,4,7,8),
                        array(9,1,2),
                        array(9,5,6));
        $this->tiles[13] = array(array(0,2,0,2),
                        array(4,3,4,7,8),
                        array(9,1,2),
                        array(9,5,6));
        $this->tiles[14] = array(array(2,0,0,2),
                        array(3,1,2),
                        array(3,7,8),
                        array(9,3,4,5,6));
        $this->tiles[15] = array(array(0,2,0,2),
                        array(3,7,8),
                        array(3,3,4),
                        array(9,1,2,5,6));
        $this->tiles[16] = array(array(2,0,0,0),
                        array(3,1,2),
                        array(9,3,4,5,6,7,8));
        $this->tiles[17] = array(array(2,0,1,1),
                        array(3,1,2),
                        array(1,3,4),
                        array(9,3,4,5,8),
                        array(9,6,7));
        $this->tiles[18] = array(array(2,1,1,0),
                        array(3,1,2),
                       array(1,2,3),
                       array(9,3,6,7,8),
                       array(9,4,5));
        $this->tiles[19] = array(array(2,1,1,1),
                        array(3,1,2),
                        array(1,4),
                        array(1,2),
                        array(1,3),
                        array(9,3,8),
                        array(9,4,5),
                        array(9,6,7));
        $this->tiles[20] = array(array(2,1,0,1),
                        array(3,1,2),
                        array(1,2,4),
                        array(9,3,8),
                        array(9,4,5,6,7));
        $this->tiles[21] = array(array(0,1,0,1),
                        array(1,2,4),
                        array(9,1,2,3,8),
                        array(9,4,5,6,7));
        $this->tiles[22] = array(array(0,0,1,1),
                        array(1,3,4),
                        array(9,1,2,3,4,5,8),
                        array(9,6,7));
        $this->tiles[23] = array(array(0,1,1,1),
                        array(1,4),
                        array(1,2),
                        array(1,3),
                        array(9,1,2,3,8),
                        array(9,4,5),
                        array(9,6,7));
        $this->tiles[24] = array(array(1,1,1,1),
                        array(1,1),
                        array(1,2),
                        array(1,3),
                        array(1,4),
                        array(9,8,1),
                        array(9,2,3),
                        array(9,4,5),
                        array(9,6,7));
        $this->tiles[25] = array(array(0,1,0,1),
                        array(0),
                        array(1,4),
                        array(1,2),
                        array(9,1,2,3,8),
                        array(9,4,5,6,7));
        $this->tiles[26] = array(array(2,2,2,2),
                        array(8,1,2,3,4,5,6,7,8));
        $this->tiles[27] = array(array(2,2,2,2),
                        array(3,1,2),
                        array(3,7,8),
                        array(3,3,4),
                        array(3,5,6),
                        array(9));
        $this->tiles[28] = array(array(2,2,0,2),
                        array(3,1,2),
                        array(3,7,8),
                        array(3,3,4),
                        array(9,5,6));
        $this->tiles[29] = array(array(1,2,1,2),
                        array(3,7,8),
                        array(3,3,4),
                        array(1,1),
                        array(1),
                        array(1,3),
                        array(9,1),
                        array(9,2),
                        array(9,6),
                        array(9,5));
        $this->tiles[30] = array(array(2,2,0,2),
                        array(4,1,2,3,4),
                        array(4,7,8),
                        array(9,5,6));
        $this->tiles[31] = array(array(0,0,0,2),
                        array(3,7,8),
                        array(9,1,2),
                        array(9,3,4,5,6));
        $this->tiles[32] = array(array(2,0,1,0),
                        array(3,1,2),
                        array(1,3),
                        array(9,6,7,8),
                        array(9,3,4,5));
        // 0: field, 1: road, 2: city, 3: river
        // 0: church, 1: road, 2: iroad, 3: city, 4: kcity, 5: scity, 6: gcity, 7: bcity, 8: ccity, 9: farm

        $this->tiles[33] = array(array(2,2,1,0),
                        array(3,1,2,3,4),
                        array(1,3),
                        array(9,6,7,8),
                        array(9,5));
        $this->tiles[34] = array(array(1,2,1,2),
                        array(4,3,4,7,8),
                        array(1,1),
                        array(1,3),
                        array(9,1),
                        array(9,2),
                        array(9,6),
                        array(9,5));
        $this->tiles[35] = array(array(2,0,1,2),
                        array(3,1,2,7,8),
                        array(2,3),
                        array(9,3,4,5),
                        array(9,6));
        $this->tiles[36] = array(array(2,0,1,1),
                        array(3,1,2),
                        array(2,3,4),
                        array(9,3,4,5,8),
                        array(9,6,7));
        $this->tiles[37] = array(array(2,1,1,2),
                        array(4,1,2,7,8),
                        array(2,2,3),
                        array(9,3,6),
                        array(9,4,5));
        $this->tiles[38] = array(array(0,1,1,1),
                        array(1,4),
                        array(2,2),
                        array(1,3),
                        array(9,1,2,3,8),
                        array(9,4,5),
                        array(9,6,7));
        $this->tiles[39] = array(array(0,1,0,1),
                        array(2,2,4),
                        array(9,1,2,3,8),
                        array(9,4,5,6,7));
        $this->tiles[40] = array(array(0,0,1,1),
                        array(2,3,4),
                        array(9,1,2,3,4,5,8),
                        array(9,6,7));
        $this->tiles[41] = array(array(1,1,1,1),
                        array(1,1,4),
                        array(1,2,3),
                        array(9,1,9),
                        array(9,2,3,6,7),
                        array(9,4,5));
        $this->tiles[42] = array(array(),
                        array(),
                        array());
        $this->tiles[43] = array(array(),
                        array(),
                        array());
        $this->tiles[44] = array(array(),
                        array(),
                        array());
        $this->tiles[45] = array(array(),
                        array(),
                        array());
        $this->tiles[46] = array(array(),
                        array(),
                        array());
        $this->tiles[47] = array(array(),
                        array(),
                        array());
        $this->tiles[48] = array(array(),
                        array(),
                        array());
    }

    function querySession()
    {
        
        $sql = "SELECT s.cardsPlayed, s.cardsRemaining, p.playerId, s.sequence, s.players FROM crfs_session s "
                . "LEFT JOIN crfs_player p ON p.sessionId = s.sessionId AND s.sequence = p.sequence "
                . "WHERE s.sessionId = '$this->sessionId'";
        $result = $this->database->query($sql);
        
        if ($result->num_rows != 1) {
            throw new Exception('Couldn\'t fetch session.');
        }
        
        return $result->fetch_assoc();
    }
    
    function populateSession($printBoardOnly = false)
    {
        $row = $this->querySession();
        
        if($printBoardOnly)
        {
            print($row['cardsPlayed']);
            return;
        }
        
        $this->board            = json_decode($row['cardsPlayed'], true);
        $this->tileSet          = json_decode($row['cardsRemaining'], true);
        
        $this->currentTileId    = $this->tileSet[0];
        $this->currentPlayer    = $row['playerId'];
        $this->currentSequence  = $row['sequence'];
        $this->playerCount      = $row['players'];
    }
    
    function updatePlayer($sequence)
    {
        $sql = "UPDATE crfs_player SET status = " . $this->players[$sequence]['status'] . " WHERE id='" . $this->players[$sequence]['id'] . "'";
        $this->database->query($sql);
    }
    
    function populatePlayers($currentOnly = false)
    {
        $sql = "SELECT * FROM crfs_player WHERE sessionId = '$this->sessionId' ";
        if ($currentOnly) {
            $sql .= "AND playerId = '$this->currentPlayer'";
        }

        $result = $this->database->query($sql);
        if (($currentOnly && $result->num_rows != 1) || (!$currentOnly && $result->num_rows == 0)) {
            throw new Exception('Couldn\'t fetch session.');
        }
        
        while($row = $result->fetch_assoc())
        {
            $this->players[$row['sequence']] = $row;
            if($row['playerId'] == $this->currentPlayer)
                $this->player = &$this->players[$row['sequence']];
        }
    }
    
    function updateSession()
    {
        $sql = "UPDATE crfs_session SET sequence = $this->currentSequence, "
                . "                     cardsRemaining = '" . json_encode($this->tileSet) . "', "
                . "                     cardsPlayed = '" . json_encode($this->board) . "' "
                . "WHERE sessionId = '$this->sessionId'";
        $this->database->query($sql);
    }
    
    function positionExists($x, $y, &$set)
    {
        return array_key_exists($y, $set) && array_key_exists($x, $set[$y]);
    }
    
    function tileExists($x, $y)
    {
        return $this->positionExists($x, $y, $this->board);
    }
    
    function positionValid($x, $y, $rotation)
    {
        if($this->tileExists($x + 1, $y))
        {
            $tile =& $this->board[$y][$x+1];
            if($this->tiles[$tile[0]][0][(3 + $tile[1])%4] != $this->tiles[$this->currentTileId][0][(1 + $rotation)%4])
                return false;
        }
        if($this->tileExists($x, $y + 1))
        {
            $tile =& $this->board[$y+1][$x];
            if($this->tiles[$tile[0]][0][(0 + $tile[1])%4] != $this->tiles[$this->currentTileId][0][(2 + $rotation)%4])
                return false;
        }
        if($this->tileExists($x - 1, $y))
        {
            $tile =& $this->board[$y][$x-1];
            if($this->tiles[$tile[0]][0][(1 + $tile[1])%4] != $this->tiles[$this->currentTileId][0][(3 + $rotation)%4])
                return false;
        }
        if($this->tileExists($x, $y - 1))
        {
            $tile =& $this->board[$y-1][$x];
            if($this->tiles[$tile[0]][0][(2 + $tile[1])%4] != $this->tiles[$this->currentTileId][0][(0 + $rotation)%4])
                return false;
        }
        return true;
    }

    function translatePosition($position, $rotation, $road, $reverse = false)
    {
        if($road)
        {
            if($reverse)
                return (($position + 4 - $rotation) % 4);
            else
                return (($position + $rotation) % 4);
        }
        else
        {
            if($reverse)
                return (($position + 8 - $rotation * 2) % 8);
            else
                return (($position + $rotation * 2) % 8);
        }
    }

    function oppositePosition($position, $road = false)
    {
        if($road)
        {
            switch ($position)
            {
                case 0: return 2;
                case 1: return 3;
                case 2: return 0;
                case 3: return 1;
            }
        }
        else
        {
            switch ($position)
            {
                case 0: return 5;
                case 1: return 4;
                case 2: return 7;
                case 3: return 6;
                case 4: return 1;
                case 5: return 0;
                case 6: return 3;
                case 7: return 2;
            }
        }
    }

    function xFromPosition($x, $position, $road = false)
    {
        if($road)
        {
            switch($position)
            {
                case 0:
                case 2:
                    return $x;
                case 1:
                    return $x + 1;
                case 3:
                    return $x - 1;
            }
        }
        else
        {
            switch($position)
            {
                case 0:
                case 1:
                case 4:
                case 5:
                    return $x;
                case 7:
                case 6:
                    return $x - 1;
                default :
                    return $x + 1;
            }
        }
    }

    function yFromPosition($y, $position, $road = false)
    {
        if($road)
        {
            switch($position)
            {
                case 1:
                case 3:
                    return $y;
                case 2:
                    return $y + 1;
                case 0:
                    return $y - 1;
            }
        }
        else
        {
            switch($position)
            {
                case 2:
                case 3:
                case 6:
                case 7:
                    return $y;
                case 0:
                case 1:
                    return $y - 1;
                default :
                    return $y + 1;
            }
        }
    }

    function setWalked($x, $y, $position, $atTile, $tile, $road, &$idx)
    {
        $break = false;
        for($idx = 1; $idx < count($tile); $idx++)
        {
            $tilePosition = &$tile[$idx];
            if (($tilePosition[0] == 1 || $tilePosition[0] == 2) != $road) {
                continue;
            }

            for($i = 1; $i < count($tilePosition) && !$break; $i++)
                if($tilePosition[$i] == ($position + 1))
                    $break = true;
            
            if($break)
                break;
        }
        if($idx >= count($tile))
        {
            $idx = -1;
            return -1;
        }
        $idx--;
        
        if($this->positionExists($x, $y, $this->walked))
        {
            if(array_key_exists($idx, $this->walked[$y][$x]))
            {
                return $this->walked[$y][$x][$idx];
            }
            else
            {
                $this->walked[$y][$x][$idx] = $this->walkIndex;
            }
        }
        else
        {
            $this->walked[$y][$x][$idx] = $this->walkIndex;
        }
        return -1;
    }
    
    function isCloseable($type)
    {
        // 0: church, 1: road, 2: iroad, 3: city, 4: kcity, 5: scity, 6: gcity, 7: bcity, 8: ccity, 9: farm
        return $type > 0 && $type < 9;
    }
    
    function isFarm($type)
    {
        if($type == 9)
            return true;
        else
            return false;
    }
    
    function walk($x, $y, $position, $road = false, $end = false)
    {
        if (!$this->tileExists($x, $y))
        {
            if($x != $this->closeXOrigin || $y != $this->closeYOrigin)
            {
                $this->walkSettings['checkClose'] = false;
                $this->closeTrue = false;
            }
            return -1;
        }
        
        $atTile = &$this->board[$y][$x];
        $position = $this->translatePosition($position, $atTile[1], $road);
        $tile = &$this->tiles[$atTile[0]];
        $idx = 0;
        
        if(($walk = $this->setWalked($x, $y, $position, $atTile, $tile, $road, $idx)) >= 0)
        {
            if($walk == $this->walkIndex)
                return -1;
            else
            {
                //$this->closeMeeple = $this->walkedSummary[$walk][0];
                //$this->closePieces = $this->walkedSummary[$walk][1];
                return $this->walkedSummary[$walk][2];
            }
        }
        if($idx < 0)
            return -1;
        
        $tilePosition = &$tile[$idx + 1];
        if($this->isFarm($tilePosition[0]) && !$end)
            $this->walkSettings['checkClose'] = false;
        
        $this->walkSettings['checkClose'] = $this->walkSettings['checkClose'] && $this->isCloseable($tilePosition[0]);
        if($this->walkSettings['checkClose'])
        {
            $this->closePieces[$x][$y] = max($tilePosition[0], $this->closePieces[$x][$y]);
        }

        $returnOverall = -1;
        if($atTile[2] > 0)
        {
            // Do we have something in the current position
            if($atTile[2] >> 7 == ($idx))
            {
                $returnOverall = $atTile[2] & 7;
                
                array_push($this->closeMeeple, array($x, $y));
            }
        }
        for($j = 1; $j < count($tilePosition); $j++)
        {
            if($tilePosition[$j] == ($position + 1))
                continue;
            $currentPosition = $this->translatePosition($tilePosition[$j] - 1, $atTile[1], $road, true);
            $return = $this->walk($this->xFromPosition($x, $currentPosition, $road), $this->yFromPosition($y, $currentPosition, $road), $this->oppositePosition($currentPosition, $road), $road, $end);
            if($return >= 0)
            {
                if($returnOverall != $this->currentSequence)
                    $returnOverall = $return;
            }
        }

        return $returnOverall;
    }
    
    function flattenArray($array)
    {
        $tmp = [];
        foreach ($array as $y => $row)
            foreach($row as $x => $value)
                array_push($tmp, $value);
        
        return $tmp;
    }

    function borderMeeples(&$border, $x, $y, &$closers = NULL, $end = false)
    {
        if($x == -10 && $y == 3)
        {
            $test = 1;
            $test = $test + 1;
        }
        // Closers will assume that each side of the tile can 
        if($closers !== null)
        {
            $this->closeXOrigin = $x;
            $this->closeYOrigin = $y;
        }
        
        $this->walkSettings['checkClose'] = $closers !== NULL;
        for($i = 0; $i < 12; $i++)
        {
            $this->walkSettings['checkClose'] = $closers !== NULL;
            if($this->walkSettings['checkClose'])
                $this->closeTrue = true;
            $border[$i] = $this->walk($this->xFromPosition($x, $i % 8, $i > 7), $this->yFromPosition($y, $i % 8, $i > 7), $this->oppositePosition($i % 8, $i > 7), $i > 7, $end);
            if($closers !== null)
                $closers[$i] = array($this->walkSettings['checkClose'] && $this->closeTrue, $this->closeMeeple, $this->flattenArray($this->closePieces));
            $this->walkedSummary[$this->walkIndex++] = array($this->closeMeeple, $this->closePieces, $border[$i]);
            if($closers !== NULL)
            {
                $this->closePieces = [];
                $this->closeMeeple = [];
            }
        }
    }

    function positionMeeple($x, $y, $rotation, $border)
    {
        $tile =& $this->tiles[$this->currentTileId];
        
        $value = 0;
        for($i = 1; $i < count($tile); $i++)
        {
            $currentPosition =& $tile[$i];
            $road = $currentPosition[0] == 1 || $currentPosition[0] == 2;
            $curVal = 0;
            // 0: nothing
            // 1: meeple
            // 2: meepmeep
            // 3: farmer
            // 4: piggy
            // 5: builder
            // 6: farmerfarmer
            
            $found = -1;
            for($j = 1; $j < count($currentPosition) && $found < 0; $j++)
            {
                $position = $this->translatePosition($currentPosition[$j] - 1, $rotation, $road, true);
                if($road)
                    $found = $border[8 + $position];
                else
                    $found = $border[$position];
            }
            if($found == -1)
            {
                $curVal = ($currentPosition[0]==9)?3:1;
            }
            else if($found == $this->currentSequence)
            {
                switch($currentPosition[0])
                {
                    case 0: $curVal = 0; break;
                    case 9: $curVal = 4; break;
                    default: $curVal = 5; break;
                }
            }
            $value += ($curVal << (($i-1) * 3));
        }
        return $value;
    }

}