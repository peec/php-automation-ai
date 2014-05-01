<?php
namespace Pkj\AutomationAI\Services;

use Pkj\AutomationAI\Bots\Bot;
use Monolog\Logger;
use Pkj\AutomationAI\DB;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ServiceThread{

    private $t;
    protected $exit = false;

    protected $db;
    protected $config;
    protected $output;
    protected $logger;


    abstract public function loop ();

    public function __construct (Logger $logger, DB $db, OutputInterface $output, array $args) {
        $this->db = $db;
        $this->config = $args;
        $this->output = $output;
        $this->logger = $logger;
    }

    public function setup () {

    }
    
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