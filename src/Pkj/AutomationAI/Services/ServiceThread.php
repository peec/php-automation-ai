<?php
namespace Pkj\AutomationAI\Services;

use Pkj\AutomationAI\Bots\Bot;
use Monolog\Logger;

abstract class ServiceThread extends Bot{

    private $t;
    protected $exit = false;
    
    abstract public function loop ();

    
    public function initService () {    	
    	$t = new Thread(array($this, 'runLoop'));
    	$this->t =$t;
    }

    public function start () {
    	$this->logger->addDebug("Starting service " . get_class($this));
        $this->t->start();
    }

    public function __destruct (){
        $this->t->stop();
    }

    public function runLoop () {
    	while(!$this->exit) {
    		$return = $this->loop();
    	
    		sleep(5);
    	}
    }

    public function run (array $args) {
    	
    }


} 