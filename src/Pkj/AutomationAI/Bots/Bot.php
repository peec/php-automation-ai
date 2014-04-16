<?php
namespace Pkj\AutomationAI\Bots;

use Pkj\AutomationAI\BotAI;

use Symfony\Component\Console\Output\OutputInterface;
use Pkj\AutomationAI\DB;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Monolog\Logger;

abstract class Bot {
	
	protected $logger;
	protected $db;
	protected $output;
	protected $config;
	protected $bots;
	
	public function __construct (Logger $logger, DB $db, OutputInterface $output, array $config, BotAI $bots) {
		$this->logger = $logger;
		$this->db = $db;
		$this->output = $output;
		$this->config = $config;
		$this->bots = $bots;
	}
	
	public function output ($msg) {
		$this->output->writeln(get_class($this) . ": " . $msg);
	}
	
	public function setup () {
		
	}
	
	public function cfg($key, $default = null)
	{
		return isset($this->config[$key]) ? $this->config[$key] : $default;
	}
	
	public function cfgRequire($key)
	{
		if (!isset($this->config[$key])) {
			$this->logger->addCritical("Configuration $key is not set. Must be set.");
			throw new InvalidArgumentException("Configuration $key is required");
		}
		return $this->config[$key];
	}
	
	
	abstract public function run (array $args);
	
}