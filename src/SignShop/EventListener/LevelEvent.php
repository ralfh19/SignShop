<?php

namespace SignShop\EventListener;

use SignShop\SignShop;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class LevelEvent implements Listener{
    private $SignShop;
    
    public function __construct(SignShop $SignShop) {
        $this->SignShop = $SignShop;        
    }
    
    public function onDataPacketReceive(DataPacketReceiveEvent $event){
        $packet = $event->getPacket();
        if($packet instanceof LevelSoundEventPacket && $packet->sound === LevelSoundEventPacket::SOUND_PLACE){
            $player = $event->getPlayer();
            $world = $player->getWorld();
            $this->SignShop->getSignManager()->reload($world);
        }
    }
}
