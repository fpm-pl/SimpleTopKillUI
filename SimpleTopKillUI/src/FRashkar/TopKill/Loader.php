<?php

namespace FRashkar\TopKill;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as T;
use pocketmine\Server;
use jojoe77777\FormAPI\SimpleForm;

class Loader extends PluginBase implements Listener {

    public $n;
	private static Loader $loader;
    public Config $kill_record;
    
    public function onEnable(): void {
        $this->getServer()->getPluginManager()->getPlugin("FormAPI");
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->kill_record = new Config($this->getDataFolder() . "/kills.yml", Config::YAML);
    	self::$loader = $this;
    }
    
    public function onJoin(PlayerJoinEvent $ev){
    	$p = $ev->getPlayer();
        if(!$this->kill_record->get($p->getName())){
        	$this->kill_record->set($p->getName(), 0);
            $this->kill_record->save();
        }
    }
    
    public function onPlayerDeath(PlayerDeathEvent $ev){
    	$p = $ev->getPlayer();
        $cause = $p->getLastDamageCause();
        if($cause instanceof EntityDamageByEntityEvent){
        	$killer = $cause->getDamager();
        	if($killer instanceof Player){
        		$this->addKill($killer);
            }
        }
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
    	switch($command->getName()) {
        	case "topkill":
                if($command == "topkill"){
            		if(!$sender instanceof Player){
                		$sender->sendMessage("Use it in-game please!");
                	}elseif($sender->hasPermission("topkill.use")){
                		$this->openUI($sender);
                	}else{
                		$sender->sendMessage(T::RED . "You don't have permission to use this command!");
               		}
                }
                break;
        }
        return true;
    }
    
    public function openUI(Player $p){
        $r = "";
        $data = $this->kill_record->getAll();
        
        if(count($data) > 0){
            arsort($data);
            $n = 1;
            
        	foreach($data as $name => $kill){
                $r .= "§a» §fTop (" . $n . ")§e " . $name . "§f, " . $kill . " kills" . "\n";
                
                if($n >= 10){
                    break;
                }
                ++$n;
        	}
    	}
    	$form = new SimpleForm(function(Player $player, int $r = null){
        	if($r === null){
            	return;
            }
            switch($r){
                case "0":
                    break;
            }
        });
        
        $form->setTitle("§8»§c Top Kills UI §8«§r");
        $form->setContent("". $r);
        $form->addButton(T::RED . "EXIT", 0, "textures/ui/cancel");
        
        $p->sendForm($form);
    }
    
    public function addKill(Player $player){
    	$kr = $this->kill_record;
        $kr->set($player->getName(), $kr->get($player->getName()) +1);
        $kr->save();
        
        $array = [];
        foreach($kr->getAll() as $key => $value){
        	$array[$key] = $value;
            $kr->remove($key);
        }
        
        arsort($array);
        foreach($array as $key => $value){
        	$kr->set($key, $value);
            $kr->save();
        }
    }
    
    public static function getInstance(): Loader{
    	$this->loader = $loader;
    }
}