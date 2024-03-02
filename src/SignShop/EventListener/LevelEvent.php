<?php

namespace SignShop\EventListener;

use SignShop\SignShop;
use pocketmine\event\Listener;
use pocketmine\event\world\WorldLoadEvent;

class WorldEvent implements Listener {
    private $SignShop;
    
    public function __construct(SignShop $SignShop) {
        $this->SignShop = $SignShop;        
    }
    
    public function worldLoad(WorldLoadEvent $event) {
        $this->SignShop->getSignManager()->reload($event->getWorld());
    }    
}
