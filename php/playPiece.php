<?php
include 'pieces.php';

class Play extends Session
{
    public $skip = false;
    
    function __construct($sessionId) {
        parent::__construct($sessionId);
        
        $this->initTiles();
        $this->populateSession();
        $this->populatePlayers();
    }
    
    function updateStatusMurple($type, $add = false, $sequence = -1)
    {
        if($sequence < 0)
            $player = &$this->player;
        else
            $player = &$this->players[$sequence];
        
        $error = false;
        switch($type)
        {
            case 1:
            case 3:
                if($add)
                    $player['status'] += 0x8000;
                else if((($player['status'] & 0x78000) >> 15) > 0)
                    $player['status'] -= 0x8000;
                else
                    $error = true;
                break;
            case 2:
            case 6:
                if($add)
                    $player['status'] += 0x80000;
                else if(($player['status'] & 0x80000) > 0)
                    $player['status'] -= 0x80000;
                else
                    $error = true;
                break;
            case 4:
                if($add)
                    $player['status'] += 0x100000;
                else if(($player['status'] & 0x100000) > 0)
                    $this->player['status'] -= 0x100000;
                else
                    $error = true;
                break;
            case 5:
                if($add)
                    $player['status'] += 0x200000;
                else if(($player['status'] & 0x200000) > 0)
                    $player['status'] -= 0x200000;
                else
                    $error = true;
                break;

        }
        if ($error)
            throw new Exception('Don\'t have the murples');
    }
    
    function meepTypeToCount($type)
    {
        switch($type)
        {
            case 1: return 1;
            case 2: return 2;
            case 3: return 1;
            case 6: return 2;
        }
        return 0;
    }
    
    function meepTypeToMultiplier($type)
    {
        if($type == 4)
            return 1;
        else
            return 0;
    }
    
    function processMeeple(&$meeple, &$sum, $x, $y, $close = true)
    {
        $max = 0;
        for($i = 0; $i < count($meeple); $i++)
        {
            $tileMeeple = &$this->board[$meeple[$i][1]][$meeple[$i][0]][2];
            $player = ($tileMeeple & 0xF);
            $type = ($tileMeeple & 0x70) >> 4;
            if($close)
            {
                $tileMeeple = 0;
                $this->updateStatusMurple($type, true, $player);
                $this->dirtyPlayers[$player] = true;
            }
            
            if(array_key_exists($player, $sum))
            {
                $sum[$player][0] += $this->meepTypeToCount($type);
                $sum[$player][1] += $this->meepTypeToMultiplier($type);
            }
            else
                $sum[$player] = array($this->meepTypeToCount($type), $this->meepTypeToMultiplier($type));
            $max = max($max, $sum[$player][0]);
            
            if($player == $this->currentSequence && $type == 5 && ($meeple[$i][0] != $x || $meeple[$i][1] != $y))
                $this->skip = true;
        }
        return $max;
    }
    
    function checkSkip(&$meeple, $x, $y)
    {
        for($i = 0; $i < count($meeple); $i++)
        {
            $tileMeeple = &$this->board[$meeple[$i][1]][$meeple[$i][0]][2];
            $player = ($tileMeeple & 0xF);
            $type = ($tileMeeple & 0x70) >> 4;
            
            if($player == $this->currentSequence && $type == 5 && ($meeple[$i][0] != $x || $meeple[$i][1] != $y))
                $this->skip = true;
        }
    }
    
    function tilesTypeToCount($type)
    {
        $trade = 0;
        if($type == 5)
            $trade = 1 << 29;
        else if($type == 6)
            $trade = 1 << 26;
        else if($type == 7)
            $trade = 1 << 22;
        if($type == 4)
            return 2 + $trade;
        else
            return 1 + $trade;
    }
    
    function tilesTypeToMultiplier($type)
    {
        if($type == 8 || $type == 2)
            return 1;
        else
            return 0;
    }
    
    function isRoad($type)
    {
        if($type == 1 || $type == 2)
            return true;
        else
            return false;
    }
    
    function processTiles(&$tiles, $closed = true)
    {
        $sum = 0;
        $multiplier = 0;
        for($i = 0; $i < count($tiles); $i++)
        {
            $sum += $this->tilesTypeToCount($tiles[$i]);
            $multiplier += $this->tilesTypeToMultiplier($tiles[$i]);
        }
        if($this->isRoad($tiles[0]))
        {
            if($closed)
                $sum += ($sum & 0xFFFF) * ($multiplier>0?1:0);
            else
                $sum += ($sum & 0xFFFF) * ($multiplier>0?-1:0);
        }
        else
        {
            if($closed)
                $sum += ($sum & 0xFFFF) * ($multiplier>0?2:1);
            else
                $sum += ($sum & 0xFFFF) * ($multiplier>0?-1:0);
        }
        
        return $sum;
    }
    
    function applyStatus($processedMeeple, $max, $status)
    {
        foreach ($processedMeeple as $key => $value)
        {
            if($max == $value[0])
            {
                $score = ($status & 0xFFFF) * ($value[1]>0?2:1);
                
                if($this->players[$key]['sequence'] == $key)
                {
                    $this->players[$key]['status'] += $score;
                    $this->dirtyPlayers[$key] = true;
                    break;
                }
            }
        }
        foreach ($this->players as $key => $value)
        {
            if($key == $this->currentSequence)
            {
                $this->players[$key]['status'] += $score & 0xFFFF0000;
                $this->dirtyPlayers[$key] = true;
                break;
            }
        }
    }
    
    function checkComplete($x, $y, $rotation, &$closers, $murple)
    {
        $tile =& $this->tiles[$this->currentTileId];
        $type = (($murple & 0x70) >> 4) ;
        $idx = (($murple & 0x380) >> 7) + 1;
        for($i = 1; $i < count($tile); $i++)
        {
            $currentPosition =& $tile[$i];
            $road = $currentPosition[0] == 1 || $currentPosition[0] == 2;
            
            $closed = true;
            $meeple = [];
            $tiles = [];
            for($j = 1; $j < count($currentPosition) && $closed; $j++)
            {
                $position = $this->translatePosition($currentPosition[$j] - 1, $rotation, $road, true);
                $arrIdx = $position + ($road?8:0);
                if(!array_key_exists($arrIdx, $closers) || !$closers[$arrIdx][0])
                {
                    $closed = false;
                    if(($this->status >> 21) & 0x1)
                        break;
                }
                
                $meeple = array_merge($meeple, $closers[$arrIdx][1]);
                $tiles = array_merge($tiles, $closers[$arrIdx][2]);
            }
            
            if($closed)
            {
                $processedMeeple = [];
                if($murple > 0 && $i ==  $idx)
                    array_push($meeple, array($x, $y));
                $max = $this->processMeeple($meeple, $processedMeeple, $x, $y);
                /*
                if($murple > 0 && $i == $idx && ($type == 1 || $type == 2 || $type == 3 || $type == 6))
                {
                    $processedMeeple[$this->currentSequence] = 1;
                    $max = 1;
                }
                 */
                $status = $this->processTiles($tiles);
                
                $this->applyStatus($processedMeeple, $max, $status);
            }
            else
                $this->checkSkip($meeple, $x, $y);
        }
        
        // Check churches
    }
    
    function play($playerId, $x, $y, $rotation, $murple)
    {
        if ($playerId != $this->currentPlayer)
            throw new Exception('Player id mismatch.');

        if(!$this->positionValid($x, $y, $rotation) || $this->tileExists($x, $y))
            throw new Exception("Position invalid.");

        // Add tile to the board
        $this->board[$y][$x] = array($this->currentTileId, $rotation, 0);
        
        $border = [];
        $closers = [];
        $this->borderMeeples($border, $x, $y, $closers);
        if($murple > 0)
        {
            $type = (($murple & 0x70) >> 4) ;
            $idx = (($murple & 0x380) >> 7);
            
            $typeAllowed = ($this->positionMeeple($x, $y, $rotation, $border) >> ($idx * 3)) & 0x7;
            $secondaryType = 0;
            if($typeAllowed == 1)
                $secondaryType = ($this->player['status']&0x80000?2:0);
            else if($typeAllowed == 3)
                $secondaryType = ($this->player['status']&0x80000?6:0);
            
            if(($murple & 0xF) != $this->currentSequence || ($type != $typeAllowed && $type != $secondaryType))
                throw new Exception('Bad murple position.');
            
            $this->updateStatusMurple($type);
                
            $this->dirtyPlayers[$this->currentSequence] = true;
        }
        
        $this->board[$y][$x][2] = $murple;
        
        $this->checkComplete($x, $y, $rotation, $closers, $murple);
        
        // Remove tile from tileSet
        array_splice($this->tileSet, 0, 1);
        
        if(!$this->skip)
            $this->currentSequence = ($this->currentSequence + 1) % $this->playerCount;
        
        $this->updateSession();
    }
}

$sessionId  = $_GET['session'];
$playerId   = $_GET['playerId'];
$x          = $_GET['x'];
$y          = $_GET['y'];
$rotation   = $_GET['rotation'];
$murple     = $_GET['murple'];

$session = new Play($sessionId);
$session->play($playerId, $x, $y, $rotation, $murple);