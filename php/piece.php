<?php

include 'tiles.php';

abstract class MeepleType
{
    const None          = 0;
    const Meeple        = 1;
    const MeepMeep      = 2;
    const Farmer        = 3;
    const Piggy         = 4;
    const Builder       = 5;
    const FarmerFarmer  = 6;
    
    public static function typeCount($type)
    {
        switch($type)
        {
            case self::MeepMeep:
            case self::FarmerFarmer:
                return 2;
            case self::Builder:
            case self::Piggy:
            case self::None:
                return 0;
            default:
                return 1;
        }
    }
}

abstract class ConnectionType
{
    const Field = 0;
    const Road = 1;
    const City = 2;
    const River = 3;
}

abstract class PositionType
{
    const Church    = 0;
    const Road      = 1;
    const iRoad     = 2;    // inn
    const City      = 3;
    const kCity     = 4;    // knight
    const sCity     = 5;    // silk (resource
    const gCity     = 6;    // grain (resource)
    const bCity     = 7;    // barrel (resource)
    const cCity     = 8;    // cathedral
    const Farm      = 9;
    
    public static function isMultiplier($type)
    {
        return $type == self::cCity || $type == self::iRoad;
    }
}

class PositionsHelper
{
    public static function rotatePosition($pos, $rotation, $reverse = false)
    {
        if($pos > 7) // Road
            return 8 + (($pos - 4 - $rotation) % 4);
        else
            return (8 + $pos - $rotation * 2) % 8;
        /*if($pos > 7) // Road
            return 8 + (($pos - 8 + $rotation) % 4);
        else
            return ($pos + $rotation * 2) % 8;*/
    }
    
    public static function oppositeNESW($pos)
    {
        if($pos > 7) // Road
            return $pos - 8;
        else
            return floor($pos / 2);
    }
    
    public static function oppositePosition($position)
    {
        switch ($position)
        {
            // Normal positions
            case 0: return 5;
            case 1: return 4;
            case 2: return 7;
            case 3: return 6;
            case 4: return 1;
            case 5: return 0;
            case 6: return 3;
            case 7: return 2;
            // Road positions
            case 8: return 10;
            case 9: return 11;
            case 10: return 8;
            case 11: return 9;
        }
    }

    public static function xFromPosition($x, $position)
    {
        switch($position)
        {
            case 0:
            case 1:
            case 4:
            case 5:
            case 8:
            case 10:
                return $x;
            case 7:
            case 6:
            case 11:
                return $x - 1;
            default :
                return $x + 1;
        }
    }

    public static function yFromPosition($y, $position)
    {
        switch($position)
        {
            case 2:
            case 3:
            case 6:
            case 7:
            case 9:
            case 11:
                return $y;
            case 0:
            case 1:
            case 8:
                return $y - 1;
            default :
                return $y + 1;
        }
    }
}

class Play
{
    public $tileId = 0;
    public $rotation = 0;
    public $areas = array();
    public $meepleType = 0;
    public $meeplePosition = 0;
    public $sequence = 0;
    public $playerSequence = 0;
    
    function __construct($sequence, $playerSequence, $tileId, $rotation, $meepleType, $meeplePosition)
    {
        $this->sequence       = $sequence;
        $this->playerSequence       = $playerSequence;
        $this->rotation       = $rotation;
        $this->tileId         = $tileId;
        $this->meepleType     = $meepleType;
        $this->meeplePosition = $meeplePosition;
    }
    
    public function addArea($areaId)
    {
        array_push($this->areas, $areaId);
    }
    
    public function getArea(&$tileCache, $pos)
    {
        $positions =& $tileCache->getTile($this->tileId)->getPositions();
        
        for($i = 0, $count = count($positions); $i < $count; ++$i)
        {
            $position =& $positions[$i];
            
            // TODO SWITCH ROTATION TO $pos so it's only done once
            // Don't loop to the first position index since it's the type
            for($j = count($position) - 1; $j > 0; --$j)
                if(PositionsHelper::rotatePosition ($position[$j], $this->rotation) == $pos)
                    return $this->areas[$i];
        }
    }
    
    public function clearMeeple()
    {
        $this->meepleType = MeepleType::None;
        $this->meeplePosition = 0;
    }
    
    public function getPlayString($x, $y)
    {
        if($this->meepleType == MeepleType::None)
            return "" . $x . "," . $y . "," . (($this->tileId << 10) + ($this->rotation << 18));
        else 
            return "" . $x . "," . $y . "," . (max(0, $this->playerSequence) + ($this->meepleType << 4) + ($this->meeplePosition << 7) + ($this->tileId << 10) + ($this->rotation << 18));
    }
}

class Area
{
    public $areaId = 0;
    public $dirty = true;
    public $dirtyInsert = true;
    public $positionType = 0;
    public $score = 0;
    public $multiplier = false;
    // We give the area an initial count of 2 to take into account the
    // subtraction done in addTile.
    public $openCount = 2;
    public $meeple = array();
    public $barrels = 0;
    public $silk = 0;
    public $grain = 0;
    public $scoringPlayers = array();
    
    public $pieces = array();
    public $meeplePieces = array();
    
    public $delete = false;
    
    const meepleNone = 0;
    const meepleOther = 1;
    const meepleSame = 2;
    
    function __construct($areaId, $positionType)
    {
        $this->areaId = $areaId;
        $this->positionType = $positionType;
    }
    
    public function fromDbRow($row)
    {
        $dirty = false;
        $dirtyInsert = false;
    }
    
    function calculateOpenCount($position)
    {
        $openCount = 0;
        $N = $E = $S = $W = true;
        for($i = count($position) - 1; $i > 0; --$i)
        {
            switch($position[$i])
            {
                case 0: case 1: case 8: if($N) { $N = false; $openCount++; } break;
                case 2: case 3: case 9: if($E) { $E = false; $openCount++; } break;
                case 4: case 5: case 10: if($S) { $S = false; $openCount++; } break;
                case 6: case 7: case 11: if($W) { $W = false; $openCount++; } break;
            }
        }
        return $openCount;
    }
    
    public function meepleLevel($playerId)
    {
        if(count($this->meeple) == 0)
            return self::meepleNone;
        
        if(array_key_exists($playerId, $this->meeple))
            return self::meepleSame;
            
        return self::meepleOther;
    }
    
    public function close()
    {
        $this->openCount--;
    }
    
    public function addTile(&$position, $meepleType, $playerSequence, $x, $y)
    {
        $this->dirty = true;
        
        $positionType = $position[0];
        if($positionType == PositionType::kCity)
            $this->score += 2;
        else
            $this->score++;
        
        if($positionType == PositionType::cCity || $positionType == PositionType::iRoad)
            $this->multiplier = true;
        
        if($positionType == PositionType::bCity)
            $this->barrels++;
        if($positionType == PositionType::gCity)
            $this->grain++;
        if($positionType == PositionType::sCity)
            $this->silk++;
        
        if($positionType != PositionType::Farm)
            $this->openCount += $this->calculateOpenCount($position) - 2;
        
        if($meepleType != MeepleType::None)
        {
            if(!array_key_exists($playerSequence, $this->meeple))
                $this->meeple[$playerSequence] = array();
            
            if(array_key_exists($meepleType, $this->meeple[$playerSequence]))
                $this->meeple[$playerSequence][$meepleType] += 1;
            else
                $this->meeple[$playerSequence][$meepleType] = 1;
            
            array_push($this->meeplePieces, $x);
            array_push($this->meeplePieces, $y);
        }
        else
        {
            array_push($this->pieces, $x);
            array_push($this->pieces, $y);
        }
    }
    
    public function mergeArea(&$area)
    {
        $this->dirty = true;
        $area->dirty = true;
        
        if($area->areaId == $this->areaId)
        {
            if($this->positionType != PositionType::Farm)
                $this->openCount -= 2;
        }
        else
        {
            $area->delete       = true;
            
            $this->score       += $area->score;
            $this->multiplier  |= $area->multiplier;
            $this->openCount   += $area->openCount - 2;
            
            $this->barrels     += $area->barrels;
            $this->silk        += $area->silk;
            $this->grain       += $area->grain;

            $this->meeple = array_merge($this->meeple, $area->meeple);
            
            $area->score                     = 0;
            $area->meeple                    = null;
            
            $this->pieces       = array_merge($this->pieces, $area->pieces);
            $this->meeplePieces = array_merge($this->meeplePieces, $area->meeplePieces);
        }
    }
    
    function closeArea(&$players, $currentPlayerSequence, $final = false)
    {
        if($final && $this->multiplier)
            return false;
        if($this->openCount > 0 || count($this->meeple) == 0)
            return false;
        
        $playerSet = array();
        $maxValue = 0;
        foreach($this->meeple as $playerSequence => $meepleSet)
        {
            $player =& $players[$playerSequence];
            foreach($meepleSet as $meepleType => $amount)
            {
                if(array_key_exists($playerSequence, $playerSet))
                    $playerSet[$playerSequence] += MeepleType::typeCount($meepleType) * $amount;
                else
                    $playerSet[$playerSequence] = MeepleType::typeCount($meepleType) * $amount;
                $player->addMeeple($meepleType, $amount);
            }
            
            $maxValue = max($maxValue, $playerSet[$playerSequence]);
        }
        
        if($this->positionType == PositionType::Road || $this->positionType == PositionType::iRoad)
            $this->score = $this->score * ($this->multiplier?2:1);
        else
            $this->score = $this->score * ($this->multiplier?3:2);
        
        foreach($playerSet as $playerSequence => $value)
        {
            if($playerSequence != $currentPlayerSequence && $value < $maxValue)
                continue;
            
            $player =& $players[$playerSequence];
            if($playerSequence == $currentPlayerSequence)
            {
                $player->barrels += $this->barrels;
                $player->silk    += $this->silk;
                $player->grain   += $this->grain;
            }
            
            if($value == $maxValue)
            {
                array_push($this->scoringPlayers, $playerSequence);

                $player->score += $this->score;
                $player->dirty = true;
            }
        }
        
        $this->delete = true;
        
        return true;
    }
}

class Player
{
    public $playerSequence = 0;
    public $meeple = 7;
    public $double = true;
    public $builder = false;
    public $piggy = false;
    public $score = 0;
    public $barrel = 0;
    public $silk = 0;
    public $grain = 0;
    
    public $dirty = false;
    
    function __construct($playerSequence, $traders = 0)
    {
        $this->playerSequence = $playerSequence;
        if($traders > 0)
        {
            $this->piggy = true;
            $this->builder = true;
        }
    }
    
    public function useMeeple($meepleType)
    {
        switch($meepleType)
        {
            case MeepleType::FarmerFarmer:
            case MeepleType::MeepMeep:    
                if(!$this->double)
                    return false;
                $this->double  = false; 
                break;
            case MeepleType::Builder:  
                if(!$this->builder)
                    return false;    
                $this->builder = false; 
                break;
            case MeepleType::Piggy:   
                if(!$this->builder)
                    return false;         
                $this->piggy   = false; 
                break;
            case MeepleType::Farmer:
            case MeepleType::Meeple:  
                if($this->meeple <= 0)
                    return false;        
                $this->meeple--;
        }
        
        $this->dirty = true;
        
        return true;
    }
    
    public function addMeeple($meepleType, $amount)
    {
        switch($meepleType)
        {
            case MeepleType::FarmerFarmer:
            case MeepleType::MeepMeep:     $this->double  = true; break;
            case MeepleType::Builder:      $this->builder = true; break;
            case MeepleType::Piggy:        $this->piggy   = true; break;
            case MeepleType::Farmer:
            case MeepleType::Meeple:       $this->meeple += $amount;
        }
        
        $this->dirty = true;
    }
    
    public function setStatus($status)
    {
        $this->score    = ($status & 0x7FFF);
        $this->meeple   = (($status >> 15) & 0xF);
        $this->double = ((($status >> 19) & 0x1) > 0);
        $this->pig      = ((($status >> 20) & 0x1) > 0);
        $this->builder  = ((($status >> 21) & 0x1) > 0);
        $this->barrel   = (($status >> 22) & 0xF);
        $this->wheat    = (($status >> 26) & 0x7);
        $this->silk     = (($status >> 29) & 0x7);
        
        $this->dirty = false;
    }
    
    public function getStatus()
    {
        $status  = ($this->score & 0x7FFF);
        $status += (($this->meeple & 0xF) << 15);
        $status += (($this->double?1:0) << 19);
        $status += (($this->pig?1:0) << 20);
        $status += (($this->builder?1:0) << 21);
        $status += (($this->barrel & 0xF) << 22);
        $status += (($this->wheat & 0x7) << 26);
        $status += (($this->silk & 0x7) << 29);
        
        return $status;
    }
}
/*
$piece = new Piece(ConnectionType::Field, ConnectionType::Field, ConnectionType::Field, ConnectionType::Field);
$piece->addPositions(array(0));
$piece->addPositions(array(9,1,2,3,4,5,6,7,8));
$tiles[1] = $piece;
$piece = new Piece(ConnectionType::Field, ConnectionType::Field, ConnectionType::Road, ConnectionType::Field);
$piece->addPositions(array(0));
$piece->addPositions(array(1,3));
$piece->addPositions(array(9,1,2,3,4,5,6,7,8));
$tiles[2] = $piece;
$tiles[3] = $piece;

echo var_dump($tiles[4]);
$tmp = serialize($tiles);
echo var_dump($tmp);
$tmp = unserialize($tmp);
echo var_dump($tmp);
*/