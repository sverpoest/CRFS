<?php

class Tile
{
    public $N = 0;
    public $E = 0;
    public $S = 0;
    public $W = 0;
    
    public $p = array();
    
    
    public function set($N, $E, $S, $W)
    {
        $this->N = $N;
        $this->E = $E;
        $this->S = $S;
        $this->W = $W;
    }
    
    public function addPosition($position)
    {
        if($position[0] != 1 && $position[0] != 2)
        {
            for($i = count($position) - 1; $i > 0; --$i)
                $position[$i] = $position[$i] - 1;
        }
        array_push($this->p, $position);
    }
    
    public function & getPositions()
    {
        return $this->p;
    }
    
    public function getNESW($position, $rotation)
    {
        switch(($position + $rotation) % 4)
        {
            case 0: return $this->N;
            case 1: return $this->E;
            case 2: return $this->S;
            case 3: return $this->W;
        }
    }
}

class TileCache
{
    protected $tiles = array();
    
    public function &getTile($tileId)
    {
        if(!array_key_exists($tileId, $this->tiles))
        {
            $tile = new Tile();
            switch ($tileId)
            {
                case 1:
                    $tile->set(0,0,0,0);
                    $tile->addPosition(array(0));
                    $tile->addPosition(array(9,1,2,3,4,5,6,7,8));
                    break;
                case 2:
                    $tile->set(0,0,1,0);
                    $tile->addPosition(array(0));
                    $tile->addPosition(array(1,10));
                    $tile->addPosition(array(9,1,2,3,4,5,6,7,8));
                    break;
                case 3:
                    $tile->set(2,2,2,2);
                    $tile->addPosition(array(4,1,2,3,4,5,6,7,8));
                    break;
                case 4:
                    $tile->set(2,2,0,2);
                    $tile->addPosition(array(3,1,2,3,4,7,8));
                    $tile->addPosition(array(9,5,6));
                    break;
                case 5:
                    $tile->set(2,2,0,2);
                    $tile->addPosition(array(4,1,2,3,4,7,8));
                    $tile->addPosition(array(9,5,6));
                    break;
                case 6:
                    $tile->set(2,2,1,2);
                    $tile->addPosition(array(3,1,2,3,4,7,8));
                    $tile->addPosition(array(1,10));
                    $tile->addPosition(array(9,5));
                    $tile->addPosition(array(9,6));
                    break;
                case 7:
                    $tile->set(2,2,1,2);
                    $tile->addPosition(array(4,1,2,3,4,7,8));
                    $tile->addPosition(array(1,10));
                    $tile->addPosition(array(9,5));
                    $tile->addPosition(array(9,6));
                    break;
                case 8:
                    $tile->set(2,0,0,2);
                    $tile->addPosition(array(3,1,2,7,8));
                    $tile->addPosition(array(9,3,4,5,6));
                    break;
                case 9:
                    $tile->set(2,0,0,2);
                    $tile->addPosition(array(4,1,2,7,8));
                    $tile->addPosition(array(9,3,4,5,6));
                    break;
                case 10:
                    $tile->set(2,1,1,2);
                    $tile->addPosition(array(3,1,2,7,8));
                    $tile->addPosition(array(1,9,10));
                    $tile->addPosition(array(9,3,6));
                    $tile->addPosition(array(9,4,5));
                    break;
                case 11:
                    $tile->set(2,1,1,2);
                    $tile->addPosition(array(4,1,2,7,8));
                    $tile->addPosition(array(1,9,10));
                    $tile->addPosition(array(9,3,6));
                    $tile->addPosition(array(9,4,5));
                    break;
                case 12:
                    $tile->set(0,2,0,2);
                    $tile->addPosition(array(3,3,4,7,8));
                    $tile->addPosition(array(9,1,2));
                    $tile->addPosition(array(9,5,6));
                    break;
                case 13:
                    $tile->set(0,2,0,2);
                    $tile->addPosition(array(4,3,4,7,8));
                    $tile->addPosition(array(9,1,2));
                    $tile->addPosition(array(9,5,6));
                    break;
                case 14:
                    $tile->set(2,0,0,2);
                    $tile->addPosition(array(3,1,2));
                    $tile->addPosition(array(3,7,8));
                    $tile->addPosition(array(9,3,4,5,6));
                    break;
                case 15:
                    $tile->set(0,2,0,2);
                    $tile->addPosition(array(3,7,8));
                    $tile->addPosition(array(3,3,4));
                    $tile->addPosition(array(9,1,2,5,6));
                    break;
                case 16:
                    $tile->set(2,0,0,0);
                    $tile->addPosition(array(3,1,2));
                    $tile->addPosition(array(9,3,4,5,6,7,8));
                    break;
                case 17:
                    $tile->set(2,0,1,1);
                    $tile->addPosition(array(3,1,2));
                    $tile->addPosition(array(1,10,11));
                    $tile->addPosition(array(9,3,4,5,8));
                    $tile->addPosition(array(9,6,7));
                    break;
                case 18:
                    $tile->set(2,1,1,0);
                    $tile->addPosition(array(3,1,2));
                       $tile->addPosition(array(1,9,10));
                       $tile->addPosition(array(9,3,6,7,8));
                       $tile->addPosition(array(9,4,5));
                    break;
                case 19:
                    $tile->set(2,1,1,1);
                    $tile->addPosition(array(3,1,2));
                    $tile->addPosition(array(1,11));
                    $tile->addPosition(array(1,9));
                    $tile->addPosition(array(1,10));
                    $tile->addPosition(array(9,3,8));
                    $tile->addPosition(array(9,4,5));
                    $tile->addPosition(array(9,6,7));
                    break;
                case 20:
                    $tile->set(2,1,0,1);
                    $tile->addPosition(array(3,1,2));
                    $tile->addPosition(array(1,9,11));
                    $tile->addPosition(array(9,3,8));
                    $tile->addPosition(array(9,4,5,6,7));
                    break;
                case 21:
                    $tile->set(0,1,0,1);
                    $tile->addPosition(array(1,9,11));
                    $tile->addPosition(array(9,1,2,3,8));
                    $tile->addPosition(array(9,4,5,6,7));
                    break;
                case 22:
                    $tile->set(0,0,1,1);
                    $tile->addPosition(array(1,10,11));
                    $tile->addPosition(array(9,1,2,3,4,5,8));
                    $tile->addPosition(array(9,6,7));
                    break;
                case 23:
                    $tile->set(0,1,1,1);
                    $tile->addPosition(array(1,11));
                    $tile->addPosition(array(1,9));
                    $tile->addPosition(array(1,10));
                    $tile->addPosition(array(9,1,2,3,8));
                    $tile->addPosition(array(9,6,7));
                    $tile->addPosition(array(9,4,5));
                    break;
                case 24:
                    $tile->set(1,1,1,1);
                    $tile->addPosition(array(1,8));
                    $tile->addPosition(array(1,9));
                    $tile->addPosition(array(1,10));
                    $tile->addPosition(array(1,11));
                    $tile->addPosition(array(9,8,1));
                    $tile->addPosition(array(9,2,3));
                    $tile->addPosition(array(9,4,5));
                    $tile->addPosition(array(9,6,7));
                    break;
                case 25:
                    $tile->set(0,1,0,1);
                    $tile->addPosition(array(0));
                    $tile->addPosition(array(1,11));
                    $tile->addPosition(array(1,9));
                    $tile->addPosition(array(9,1,2,3,8));
                    $tile->addPosition(array(9,4,5,6,7));
                    break;
                case 26:
                    $tile->set(2,2,2,2);
                    $tile->addPosition(array(8,1,2,3,4,5,6,7,8));
                    break;
                case 27:
                    $tile->set(2,2,2,2);
                    $tile->addPosition(array(3,1,2));
                    $tile->addPosition(array(3,7,8));
                    $tile->addPosition(array(3,3,4));
                    $tile->addPosition(array(3,5,6));
                    $tile->addPosition(array(9));
                    break;
                case 28:
                    $tile->set(2,2,0,2);
                    $tile->addPosition(array(3,1,2));
                    $tile->addPosition(array(3,7,8));
                    $tile->addPosition(array(3,3,4));
                    $tile->addPosition(array(9,5,6));
                    break;
                case 29:
                    $tile->set(1,2,1,2);
                    $tile->addPosition(array(3,7,8));
                    $tile->addPosition(array(3,3,4));
                    $tile->addPosition(array(1,8));
                    $tile->addPosition(array(1));
                    $tile->addPosition(array(1,10));
                    $tile->addPosition(array(9,1));
                    $tile->addPosition(array(9,2));
                    $tile->addPosition(array(9,6));
                    $tile->addPosition(array(9,5));
                    break;
                case 30:
                    $tile->set(2,2,0,2);
                    $tile->addPosition(array(4,1,2,3,4));
                    $tile->addPosition(array(4,7,8));
                    $tile->addPosition(array(9,5,6));
                    break;
                case 31:
                    $tile->set(0,0,0,2);
                    $tile->addPosition(array(3,7,8));
                    $tile->addPosition(array(9,1,2));
                    $tile->addPosition(array(9,3,4,5,6));
                    break;
                case 32:
                    $tile->set(2,0,1,0);
                    $tile->addPosition(array(3,1,2));
                    $tile->addPosition(array(1,10));
                    $tile->addPosition(array(9,6,7,8));
                    $tile->addPosition(array(9,3,4,5));
        // 0: field, 1: road, 2: city, 3: river
        // 0: church, 1: road, 2: iroad, 3: city, 4: kcity, 5: scity, 6: gcity, 7: bcity, 8: ccity, 9: farm

                    break;
                case 33:
                    $tile->set(2,2,1,0);
                    $tile->addPosition(array(3,1,2,3,4));
                    $tile->addPosition(array(1,10));
                    $tile->addPosition(array(9,6,7,8));
                    $tile->addPosition(array(9,5));
                    break;
                case 34:
                    $tile->set(1,2,1,2);
                    $tile->addPosition(array(4,3,4,7,8));
                    $tile->addPosition(array(1,8));
                    $tile->addPosition(array(1,10));
                    $tile->addPosition(array(9,1));
                    $tile->addPosition(array(9,2));
                    $tile->addPosition(array(9,6));
                    $tile->addPosition(array(9,5));
                    break;
                case 35:
                    $tile->set(2,0,1,2);
                    $tile->addPosition(array(3,1,2,7,8));
                    $tile->addPosition(array(2,10));
                    $tile->addPosition(array(9,3,4,5));
                    $tile->addPosition(array(9,6));
                    break;
                case 36:
                    $tile->set(2,0,1,1);
                    $tile->addPosition(array(3,1,2));
                    $tile->addPosition(array(2,10,11));
                    $tile->addPosition(array(9,3,4,5,8));
                    $tile->addPosition(array(9,6,7));
                    break;
                case 37:
                    $tile->set(2,1,1,2);
                    $tile->addPosition(array(4,1,2,7,8));
                    $tile->addPosition(array(2,9,10));
                    $tile->addPosition(array(9,3,6));
                    $tile->addPosition(array(9,4,5));
                    break;
                case 38:
                    $tile->set(0,1,1,1);
                    $tile->addPosition(array(1,11));
                    $tile->addPosition(array(2,9));
                    $tile->addPosition(array(1,10));
                    $tile->addPosition(array(9,1,2,3,8));
                    $tile->addPosition(array(9,4,5));
                    $tile->addPosition(array(9,6,7));
                    break;
                case 39:
                    $tile->set(0,1,0,1);
                    $tile->addPosition(array(2,9,11));
                    $tile->addPosition(array(9,1,2,3,8));
                    $tile->addPosition(array(9,4,5,6,7));
                    break;
                case 40:
                    $tile->set(0,0,1,1);
                    $tile->addPosition(array(2,10,11));
                    $tile->addPosition(array(9,1,2,3,4,5,8));
                    $tile->addPosition(array(9,6,7));
                    break;
                case 41:
                    $tile->set(1,1,1,1);
                    $tile->addPosition(array(1,8,11));
                    $tile->addPosition(array(1,9,10));
                    $tile->addPosition(array(9,1,9));
                    $tile->addPosition(array(9,2,3,6,7));
                    $tile->addPosition(array(9,4,5));
            }
            $this->tiles[$tileId] = $tile;
        }
            
        return $this->tiles[$tileId];
    }
}