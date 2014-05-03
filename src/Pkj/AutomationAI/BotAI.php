<?php
namespace Pkj\AutomationAI;

use Symfony\Component\Console\Output\OutputInterface;

class BotAI {
	
	private $bots = array();
	public $appPath;

    public $output;


	public function __construct ($appPath, OutputInterface $output) {
		$this->appPath = $appPath;	
	    $this->output = $output;
    }
	public function setBots (array $bots) {
		$this->bots = $bots;
	}

	
	public function run ($bot, array $args, array $runConditions = array()) {
        $b = $this->bots[$bot];
        $args = array_merge($b['args'], $args);
        return call_user_func_array(array($b['object'], 'run'), array($args));
	}
	
}