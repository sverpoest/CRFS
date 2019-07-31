<?php

include 'piece.php';

function errHandle($errNo, $errStr, $errFile, $errLine) {
    $msg = "$errStr in $errFile on line $errLine";
    if ($errNo == E_NOTICE || $errNo == E_WARNING) {
        throw new ErrorException($msg, $errNo);
    } else {
        echo $msg;
    }
}

set_error_handler('errHandle');

 class CRFS
{
    public $cardsPlayed = array();
    public $possiblePlays = array();
    private $dummyPlay = null;
    
    private $tileCount = 0;
    public $currentTileId = 0;
    private $currentTile = null;
    
    
    public $players = array();
    public $currentPlayerSequence = -1;
    
    public $areas = array();
    public $areaLinks = array();
    private $nextAreaId = 0;

    public $tileCache = null;
    
    public $tilesRefresh = array();
    public $tilesHit = array();
    public $scoring = array();
    
    function __construct() 
    {
        $this->tileCache = new TileCache();
    }
    
    public function playPiece($x, $y, $rotation, $meepleType, $meeplePosition)
    {
        if(!$this->verifyPlay($x, $y, $rotation, $meepleType, $meeplePosition))
            throw new Exception("Position invalid.");
        
        if($meepleType != MeepleType::None &&
           $this->currentPlayerSequence >= 0 &&
           !$this->players[$this->currentPlayerSequence]->useMeeple($meepleType))
            throw new Exception("Meeple invalid.");
        
        $play = new Play($this->tileCount++, $this->currentPlayerSequence, $this->currentTileId, $rotation, $meepleType, $meeplePosition);
        $this->processPositions($this->currentTile->getPositions(), $play, $x, $y);
        
        $this->cardsPlayed[$x][$y] = $play;
    }
    
    function pushTiles(&$array, $x, $y)
    {
        if(!$array)
            $array = array($x, $y);
        else
        {
            array_push($array, $x);
            array_push($array, $y);
        }
    }
    
    public function setCurrentTile($tileId)
    {
        $this->currentTileId = $tileId;
        $this->currentTile = $this->tileCache->getTile($tileId);
    }
    
    function verifyPlay($x, $y, $rotation, $meepleType, $meeplePosition)
    {
        return true;
    }
    
    function finish()
    {
        
    }
    
    public function progress()
    {
        $this->currentPlayerSequence = (($this->currentPlayerSequence + 1) % max(2,  count($this->players)));
        
        $this->setPlays();
    }
    
    function setPlays()
    {
        $this->possiblePlays = array();
        
        foreach($this->cardsPlayed as $x => $col)
        {
            foreach($col as $y => $cell)
            {
                $this->addPlays($x - 1, $y);
                $this->addPlays($x, $y - 1);
                $this->addPlays($x + 1, $y);
                $this->addPlays($x, $y + 1);
            }
        }
    }
    
    function &getPlay($x, $y)
    {
        if(!array_key_exists($x, $this->cardsPlayed))
            return $this->dummyPlay;
        
        if(!array_key_exists($y, $this->cardsPlayed[$x]))
            return $this->dummyPlay;
        
        return $this->cardsPlayed[$x][$y];
    }
    
    function addPlays($x, $y)
    {
        if ($this->tileExists($x, $y) || $this->positionExists($x, $y, $this->possiblePlays))
            return;
        
        $playsR = [];
        
        $N = $this->getPlay($x, $y - 1);
        $E = $this->getPlay($x + 1, $y);
        $S = $this->getPlay($x, $y + 1);
        $W = $this->getPlay($x - 1, $y);
        for($i = 0; $i < 4; ++$i)
        {
            if (!$this->positionValid($N, $E, $S, $W, $i))
                continue;

            array_push($playsR, $this->positionMeeple($i, $N, $E, $S, $W) + ($i << 29));
        }

        if (count($playsR) > 0)
            $this->possiblePlays[$x][$y] = $playsR;
    }
    
    function positionMeeple($rotation, &$N, &$E, &$S, &$W)
    {
        $positions =& $this->currentTile->getPositions();
        $value = 0;
        for($i = 0, $count = count($positions); $i < $count; ++$i)
        {
            $position =& $positions[$i];
            
            $maxLevel = Area::meepleNone;
            for($j = count($position) - 1; $j > 0 && $maxLevel != Area::meepleSame; --$j)
            {
                $pos  = PositionsHelper::rotatePosition($position[$j], $rotation);
                $nPos = PositionsHelper::oppositePosition($pos);
                
                $areaId = 0;
                switch(PositionsHelper::oppositeNESW($pos))
                {
                    case 0: $areaId = ($N==$this->dummyPlay?-1:$N->getArea($this->tileCache, $nPos)); break;
                    case 1: $areaId = ($E==$this->dummyPlay?-1:$E->getArea($this->tileCache, $nPos)); break;
                    case 2: $areaId = ($S==$this->dummyPlay?-1:$S->getArea($this->tileCache, $nPos)); break;
                    case 3: $areaId = ($W==$this->dummyPlay?-1:$W->getArea($this->tileCache, $nPos)); break;
                }
                
                if($areaId >= 0)
                {
                    $areaId = $this->getPrimaryAreaId($areaId);
                    $level = $this->areas[$areaId]->meepleLevel($this->currentPlayerSequence);
                    $maxLevel = ($level>$maxLevel?$level:$maxLevel);
                }
            }
            $curVal = 0;
            switch($maxLevel)
            {
                case Area::meepleNone:
                    $curVal = ($position[0]== PositionType::Farm)?MeepleType::Farmer:MeepleType::Meeple;
                    break;
                case Area::meepleSame:
                    switch($position[0])
                    {
                        case PositionType::Church: $curVal = MeepleType::None; break;
                        case PositionType::Farm:   $curVal = MeepleType::Piggy; break;
                        default:                   $curVal = MeepleType::Builder; break;
                    }
                    break;
            }
            $value += ($curVal << ($i * 3));
        }
        return $value;
    }
    
    function positionValid(&$N, &$E, &$S, &$W, $rotation)
    {
        if($N != $this->dummyPlay)
        {
            $a = $this->currentTile->getNESW(0, $rotation);
            $b = $this->tileCache->getTile($N->tileId)->getNESW(2, $N->rotation);
            if($this->currentTile->getNESW(0, $rotation) != $this->tileCache->getTile($N->tileId)->getNESW(2, $N->rotation))
                return false;
        }
        if($E != $this->dummyPlay)
            if($this->currentTile->getNESW(1, $rotation) != $this->tileCache->getTile($E->tileId)->getNESW(3, $E->rotation))
                return false;
        if($S != $this->dummyPlay)
            if($this->currentTile->getNESW(2, $rotation) != $this->tileCache->getTile($S->tileId)->getNESW(0, $S->rotation))
                return false;
        if($W != $this->dummyPlay)
            if($this->currentTile->getNESW(3, $rotation) != $this->tileCache->getTile($W->tileId)->getNESW(1, $W->rotation))
                return false;
        return true;
    }
    
    function positionExists($x, $y, &$set)
    {
        return array_key_exists($x, $set) && array_key_exists($y, $set[$x]);
    }
    
    function tileExists($x, $y)
    {
        return $this->positionExists($x, $y, $this->cardsPlayed);
    }
    
    function getPrimaryAreaId($areaId)
    {
        if(array_key_exists($areaId, $this->areaLinks))
            return $this->getPrimaryAreaId($this->areaLinks[$areaId]);
        else
            return $areaId;
    }
    
    function processPositions(&$positions, &$play, $x, $y)
    {
        $area = null;
        for($i = 0, $count = count($positions); $i < $count; ++$i)
        {
            $position =& $positions[$i];
            
            unset($area);
            $area = null;
            
            // Don't loop to the first position index since it's the type
            for($j = count($position) - 1; $j > 0; --$j)
            {
                $pos   = PositionsHelper::rotatePosition($position[$j], $play->rotation);
                
                $nX     = PositionsHelper::xFromPosition($x, $pos);
                $nY     = PositionsHelper::yFromPosition($y, $pos);
                $nPos   = PositionsHelper::oppositePosition($pos);
                if($this->tileExists($nX, $nY))
                {
                    $areaId = $this->getPrimaryAreaId($this->cardsPlayed[$nX][$nY]->getArea($this->tileCache, $nPos));
                    if($area)
                    {
                        $area->mergeArea($this->areas[$areaId]);
                        if($areaId != $area->areaId)
                            $this->areaLinks[$areaId] = $area->areaId;
                    }
                    else
                    {
                        $area =& $this->areas[$areaId];
                        $area->addTile($position, $play->meeplePosition==$i?$play->meepleType:MeepleType::None, $this->currentPlayerSequence, $x, $y);
                    }
                }
            }
            
            if(!$area)
            {
                $area = new Area($this->nextAreaId, $position[0]);
                $area->addTile($position, $play->meeplePosition==$i?$play->meepleType:MeepleType::None, $this->currentPlayerSequence, $x, $y);
                
                $this->areas[$this->nextAreaId++] = $area;
            
                $play->addArea($area->areaId);
            }
            else 
            {
                $play->addArea($area->areaId);
                if($area->closeArea($this->players, $this->currentPlayerSequence, false))
                   $this->clearPieces($area, $play);
            }
        }
    }
    
    function clearPieces(&$area, &$play)
    {
        for($i = 0, $count = count($area->meeplePieces); $i < $count; ++$i)
        {
            $x = $area->meeplePieces[$i++];
            $y = $area->meeplePieces[$i];
            
            if($this->tileExists($x, $y))
                $this->clearMeeple($this->cardsPlayed[$x][$y], $area->areaId);
            else
                $this->clearMeeple($play, $area->areaId);
        }
        
        if(!$this->tilesHit)
            $this->tilesHit = array($area->pieces);
        else
            $this->tilesHit[count($this->tilesHit)] = $area->pieces;
        if(!$this->tilesRefresh)
            $this->tilesRefresh = array($area->meeplePieces);
        else
            $this->tilesRefresh[count($this->tilesRefresh)] = $area->meeplePieces;
        
        if(!$this->scoring)
            $this->scoring = array(array_merge(array($area->score, $area->barrels, $area->silk, $area->grain), $area->scoringPlayers));
        else
            $this->scoring[count($this->scoring)] = array_merge(array($area->score, $area->barrels, $area->silk, $area->grain), $area->scoringPlayers);
    }
    
    function clearMeeple(&$play, $areaIdComp)
    {
        $areaId = $this->getPrimaryAreaId($play->areas[$play->meeplePosition]);
        if($areaIdComp == $areaId)
            $play->clearMeeple();
    }
}