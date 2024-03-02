<?php

namespace SignShop\Manager;

use SignShop\SignShop;
use pocketmine\Server;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\world\World;
use pocketmine\world\Position;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;

class SignManager{
    private $SignShop;
    private $signs = [];    
    
    public function __construct(SignShop $SignShop){
        $this->SignShop = $SignShop;
        if(count($SignShop->getProvider()->getAllSigns()) <= 0) 
            return;
        foreach($SignShop->getProvider()->getAllSigns() as $var => $c){
            $pos = explode(":", $var);
            $this->signs[$pos[3]][$pos[0].":".$pos[1].":".$pos[2]] = true;            
        }
        foreach($this->signs as $world => $sign)
            ksort($this->signs[$world]);
        
        $this->reload();  
    }
    
    public function removeSign(Position $pos){
        $pos = $this->getPos($pos);
        unset($this->signs[$pos->getWorld()->getFolderName()][$pos->getX().":".$pos->getY().":".$pos->getZ()]);
        
        $this->SignShop->getProvider()->removeSign($this->getTextPos($pos));
        $pos->getWorld()->setBlock($pos, Block::get(Block::AIR), true, true);
    } 
    
    public function existsSign(Position $pos){
        $pos = $this->getPos($pos);
        return isset($this->signs[$pos->getWorld()->getFolderName()][$pos->getX().":".$pos->getY().':'.$pos->getZ()]);        
    }
    
    public function setSign(Position $pos, array $get){
        $pos = $this->getPos($pos);
        if(!$this->existsSign($pos)){
            $this->signs[$pos->getWorld()->getFolderName()][$pos->getX().":".$pos->getY().':'.$pos->getZ()] = true;
            ksort($this->signs[$pos->getWorld()->getFolderName()]);    
        }
        
        $this->SignShop->getProvider()->setSign($this->getTextPos($pos), $get);
        $this->spawnSign($pos, $get);
    } 
    
    private function getWorld(World $world){
        return $world->getFolderName();
    }
    
    public function getSign(Position $pos){
        $pos = $this->getPos($pos);
        return $this->SignShop->getProvider()->getSign($this->getTextPos($pos));
    }
    
    public function spawnSign(Position $pos, $get = false){
        if(!$get || !isset($get))
            $get = $this->SignShop->getProvider()->getSign($this->getTextPos($pos));     
        
        if($pos->getWorld()->getBlockIdAt($pos->x, $pos->y, $pos->z) != Item::SIGN_POST && $pos->getWorld()->getBlockIdAt($pos->x, $pos->y, $pos->z) != Item::WALL_SIGN){
            if($pos->getWorld()->getBlockIdAt($pos->x, $pos->y - 1, $pos->z) != Item::AIR && $pos->getWorld()->getBlockIdAt($pos->x, $pos->y - 1, $pos->z) != Item::WALL_SIGN)
                $pos->getWorld()->setBlock($pos, Block::get(Item::SIGN_POST, $get["direction"]), true, true);
            else{
                $direction = 3;
                if($pos->getWorld()->getBlockIdAt($pos->x - 1 , $pos->y, $pos->z) != Item::AIR)
                    $direction = 5;
                elseif($pos->getWorld()->getBlockIdAt($pos->x + 1 , $pos->y, $pos->z) != Item::AIR)
                    $direction = 4;
                elseif($pos->getWorld()->getBlockIdAt($pos->x , $pos->y, $pos->z + 1) != Item::AIR)
                    $direction = 2;                      
                $pos->getWorld()->setBlock($pos, Block::get(Item::WALL_SIGN, $direction), true, true);    
            }            
        }            
        
        if($get["type"] == "sell"){
            if($get["need"] == -1)
                $get["need"] = "∞";
            $line = [TextFormat::GOLD."[SignSell]", 
                    TextFormat::ITALIC.str_replace(" ", "", Item::get($get["id"], $get["damage"])->getName()), 
                    $get["available"]."/".$get["need"], 
                    $get["cost"].$this->SignShop->getMoneyManager()->getValue().TextFormat::BLACK." for ".$get["amount"]
                ];
        }else{
            if($get["available"] != "unlimited" && $get["available"] - $get["amount"] <= 0)
                $get["cost"] = TextFormat::DARK_RED."Out Of Stock";
            else{
                if($get["cost"] == 0) 
                    $get["cost"] = "Price: "."FREE";
                else 
                    $get["cost"] = "Price: ".$get["cost"].$this->SignShop->getMoneyManager()->getValue();
            }   
            
            $line = [TextFormat::GOLD."[SignBuy]", 
                    TextFormat::ITALIC.str_replace(" ", "", Item::get($get["id"], $get["damage"])->getName()),
                    "Amount: x".$get["amount"],
                    $get["cost"]
                ];
        }
        
        $tile = $pos->getWorld()->getTile($pos); 
        if($tile instanceof Sign){            
            $tile->setText(... $line);
            return;
        }
        
        $sign = new Sign($pos->getChunk(), new CompoundTag("", [
            new IntTag("x", $pos->x),
            new IntTag("y", $pos->y),
            new IntTag("z", $pos->z),
            new StringTag("id", Tile::SIGN),
            new StringTag("Text1", $line[0]),
            new StringTag("Text2", $line[1]),
            new StringTag("Text3", $line[2]),
            new StringTag("Text4", $line[3])
            ]));               
    }   
    
    private function getTextPos(Position $pos){
        return $pos->getX().":".$pos->getY().":".$pos->getZ().":".$pos->getWorld()->getFolderName();
    } 
        
    private function getPos(Position $pos){        
        $pos->x = (int) $pos->getX();
        $pos->y = (int) $pos->getY();
        $pos->z = (int) $pos->getZ();
     
        return $pos;
    }
    
    public function reload($world = false){
        if(count($this->signs) <= 0) return false;

        if(empty($world)){
            foreach($this->signs as $world => $var){
                $world = Server::getInstance()->getWorldManager()->getWorldByName(str_replace("%", " ", $world));
                if($world instanceof World){
                    foreach($var as $pos => $c){
                        $t = explode(":", $pos);
                        $this->spawnSign(new Position($t[0], $t[1], $t[2], $world));                
                    }               
                }  
            }   
            return true;
        }else{            
            $world = Server::getInstance()->getWorldManager()->getWorldByName(str_replace("%", " ", trim($world)));
            foreach($this->signs[$world->getFolderName()] as $w => $var){                
                foreach($var as $pos => $c){
                    $t = explode(":", $pos);
                    $this->spawnSign(new Position($t[0], $t[1], $t[2], $world));                
                }              
                  
            }
        }
    }
    
    public function onDisable(){
        unset($this->signs);
    }
}
