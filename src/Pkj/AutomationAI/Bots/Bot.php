<?php
namespace Pkj\AutomationAI\Bots;

use Symfony\Component\Console\Output\OutputInterface;

use Pkj\AutomationAI\DB;

use Monolog\Logger;

abstract class Bot {
	
	protected $logger;
	protected $db;
	protected $output;
	
	public function __construct (Logger $logger, DB $db, OutputInterface $output) {
		$this->logger = $logger;
		$this->db = $db;
		$this->output = $output;
	}
	
	public function output ($msg) {
		$this->output->writeln(get_class($this) . ": " . $msg);
	}
	
	public function setup () {
		
	}
	
	abstract public function run (array $args);
	
}