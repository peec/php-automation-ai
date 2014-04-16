<?php
namespace Pkj\AutomationAI\Bots;

class LoggerBot extends Bot{
	
	public function run (array $args) {
		$this->logger->addDebug("Logger bot announce.", array('message' => $args['message']));
		
		$this->output("Hello World, from the logger bot.");
	}
}