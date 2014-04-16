<?php
namespace Pkj\AutomationAI;

class BotAI {
	
	private $bots = array();
	
	
	public function setBots (array $bots) {
		$this->bots = $bots;
	}
	
	public function run ($bot, array $args) {
		$b = $this->bots[$bot];
		$args = array_merge($b['args'], $args);
		return call_user_func_array(array($b['object'], 'run'), array($args));
	}
	
}