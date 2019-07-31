<?php
include 'pieces.php';

class Progress extends Session
{
    public $status      = [];
    public $positions   = [];
    
    function __construct($sessionId) {
        parent::__construct($sessionId);
        
        $this->populateSession();
        
        array_push($this->status, $this->currentTileId);
        $this->populatePlayerStatus();
    }
    
    function populatePlayerStatus()
    {
        $sql = "SELECT icon, color, status FROM crfs_player WHERE sessionId = '$this->sessionId' ORDER BY sequence";
        $result = $this->database->query($sql);

        if ($result->num_rows == 0) {
            throw new Exception('Couldn\'t fetch player status.');
        }

        array_push($this->status, $result->num_rows);
        
        while($row = $result->fetch_assoc())
        {
            array_push($this->status, $row['icon']);
            array_push($this->status, $row['color']);
            array_push($this->status, $row['status']);
        }
    }
    
    function addPosition($x, $y)
    {

        if(count($this->tiles[22]) > 5)
            $a = 2;
        if($x == -4 && $y == -2)
        {
            $a = 2;
        }
        
        if ($this->tileExists($x, $y) || $this->positionExists($x, $y, $this->positions))
        {
            return;
        }
        $positionsR = [];
        $border = [];
        
        for($i = 0; $i < 4; ++$i)
        {
            if (!$this->positionValid($x, $y, $i))
            {
                continue;
            }
            if(count($border) == 0)
            {
               $this->borderMeeples($border, $x, $y);
            }

            array_push($positionsR, $this->positionMeeple($x, $y, $i, $border) + ($i << 29));
        }

        if (count($positionsR) > 0)
        {
            $this->positions[$y][$x] = $positionsR;
        }
    }
    
    function setPositions()
    {
        foreach($this->board as $y => $row)
        {
            foreach($row as $x => $cell)
            {
                $this->addPosition($x - 1, $y);
                $this->addPosition($x, $y - 1);
                $this->addPosition($x + 1, $y);
                $this->addPosition($x, $y + 1);
            }
        }
        array_push($this->status, $this->positions);
    }
    
    function printStatus()
    {
        print(json_encode($this->status));
    }
}

$sessionId = $_GET['session'];
$getPositions = array_key_exists('getPositions', $_GET)?$_GET['getPositions']:false;

$progress = new Progress($sessionId);
if ($getPositions)
{
    $progress->initTiles();
    $progress->setPositions();
}
$progress->printStatus();