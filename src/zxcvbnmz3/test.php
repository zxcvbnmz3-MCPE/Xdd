<?php

namespace zxcvbnmz3;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

class test extends PluginBase implements Listener {

    private $validCodes = [];
    private $usedCodes = [];
    private $dataFile;

    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->dataFile = new Config($this->getDataFolder() . "used_codes.yml", Config::YAML);
        $this->usedCodes = $this->dataFile->getAll();

        // Add some initial valid codes
        $this->addPromoCode("WELCOME10", 10);
        $this->addPromoCode("NEWPLAYER", 50);
    }

    public function onDisable() {
        @mkdir($this->getDataFolder());
        $this->dataFile->setAll($this->usedCodes);
        $this->dataFile->save();
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if ($command->getName() === "promocode" || $command->getName() === "pcode") {
            if ($sender instanceof Player) { // Check if the sender is a player
                if (isset($args[0])) {
                    $code = strtoupper($args[0]);
                    if (isset($this->validCodes[$code]) && (!isset($this->usedCodes[$sender->getName()]) || !in_array($code, $this->usedCodes[$sender->getName()]))) {
                        $amount = $this->validCodes[$code];
                        $this->claimReward($sender, $amount);
                        if (!isset($this->usedCodes[$sender->getName()])) {
                            $this->usedCodes[$sender->getName()] = [];
                        }
                        $this->usedCodes[$sender->getName()][] = $code;
                        $sender->sendMessage(TextFormat::GREEN . "You have claimed a reward of " . TextFormat::GOLD . "$amount" . TextFormat::GREEN . " coins!");
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Invalid promo code or you have already used this code.");
                    }
                } else {
                    $sender->sendMessage(TextFormat::YELLOW . "Usage: /promocode <code>");
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "This command can only be used by players.");
            }
            return true;
        }
        return false;
    }

    public function claimReward(Player $player, int $amount) {
        $economyAPI = EconomyAPI::getInstance();
        $economyAPI->addMoney($player, $amount);
    }

    private function addPromoCode(string $code, int $amount) {
        $this->validCodes[strtoupper($code)] = $amount;
    }
