<?php

namespace SignShop\Manager;

use SignShop\SignShop;
use pocketmine\plugin\Plugin;

class MoneyManager {
    /** @var Plugin */
    private $PocketMoney = false, $EconomyS = false, $MassiveEconomy = false, $BedrockEconomy = false;

    public function __construct(SignShop $SignShop) {
        if ($SignShop->getServer()->getPluginManager()->getPlugin("PocketMoney") instanceof Plugin) {
            // Existing PocketMoney logic...
        } elseif ($SignShop->getServer()->getPluginManager()->getPlugin("EconomyAPI") instanceof Plugin) {
            // Existing EconomyAPI logic...
        } elseif ($SignShop->getServer()->getPluginManager()->getPlugin("MassiveEconomy") instanceof Plugin) {
            // Existing MassiveEconomy logic...
        } elseif ($SignShop->getServer()->getPluginManager()->getPlugin("BedrockEconomy") instanceof Plugin) {
            $this->BedrockEconomy = $SignShop->getServer()->getPluginManager()->getPlugin("BedrockEconomy");
        } else {
            // Existing plugin missing error handling...
            $SignShop->getLogger()->critical("This plugin to work needs the plugin PocketMoney, EconomyS, MassiveEconomy, or BedrockEconomy.");
            $SignShop->getServer()->shutdown();
        }
    }

    /**
     * @return string
     */
    public function getValue() {
        if ($this->PocketMoney) return "pm";
        if ($this->EconomyS) return "$";
        if ($this->MassiveEconomy) return $this->MassiveEconomy->getMoneySymbol();
        if ($this->BedrockEconomy) return $this->BedrockEconomy->getMoneySymbol();
        return "?";
    }

    /**
     * @param string $player
     * @return int
     */
    public function getMoney($player) {
        if ($this->PocketMoney) return $this->PocketMoney->getMoney($player);
        if ($this->EconomyS) return $this->EconomyS->myMoney($player);
        if ($this->MassiveEconomy) return $this->MassiveEconomy->getMoney($player);
        if ($this->BedrockEconomy) return $this->BedrockEconomy->getMoney($player);
        return 0;
    }

    /**
     * @param string $player
     * @param int $value
     * @return bool
     */
    public function addMoney($player, $value) {
        if ($this->PocketMoney) {
            // Existing PocketMoney logic...
        } elseif ($this->EconomyS) {
            // Existing EconomyAPI logic...
        } elseif ($this->MassiveEconomy) {
            // Existing MassiveEconomy logic...
        } elseif ($this->BedrockEconomy) {
            return $this->BedrockEconomy->addToBalance($player, $value);
        }
        return false;
    }

    /**
     * @param string $player
     * @return bool
     */
    public function isExists($player) {
        if ($this->PocketMoney) return $this->PocketMoney->isRegistered($player);
        elseif ($this->EconomyS) return $this->EconomyS->accountExists($player);
        elseif ($this->MassiveEconomy) return $this->MassiveEconomy->isPlayerRegistered($player);
        elseif ($this->BedrockEconomy) return $this->BedrockEconomy->hasAccount($player);
        return false;
    }
}
