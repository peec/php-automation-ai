<?php
namespace Pkj\AutomationAI\QueryLanguage;

use Pkj\AutomationAI\TimeUtils;

use Monolog\Logger;

use Pkj\AutomationAI\Store;

class Query {
	public $store;
	public $qbs;
	public $tasks=array();
	
	public $logger;
	
	
	public function __construct (Store $store, QueryBuilderStore $qbs, Logger $logger) {
		$this->store = $store;
		$this->qbs = $qbs;
		$this->logger = $logger;
	}
	
	
	public function event ($eventName) {
		return isset($this->store->eventChanges[$eventName]) ? $this->store->eventChanges[$eventName] : null;
	}
	
	public function setting ($settingName) {
		return isset($this->store->settings[$settingName]) ? $this->store->settings[$settingName] : null;
	}
	
	public function settingChange ($settingName) {
		return isset($this->store->settingChanges[$settingName]) ? $this->store->settingChanges[$settingName] : null;
	}
	
	
	/**
	 * Can be given in format such as:
	 * @param string $times Mon@12:00|23:00,Tue@12:00,Wed@15:00|16:00|17:00
	 */
	public function matchScheme($times) {
		$self = $this;
		
		$createScheme = function () use ($self, $times) {			
			return TimeUtils::getNextTime($times);
		};
		
		// Run when DO is done.
		$this->tasks['schemaSetNewDate'] = function () use ($self, $createScheme) {
			$time = $createScheme();
			$self->qbs->set('schemaset', $time);
			$self->logger->addDebug("Scheduled new time for Query > $time . In " . TimeUtils::timespanToReadable($time - time()) . '.');
		};
		
		if (!$this->qbs->get("schemaset")) {
			$this->tasks['schemaSetNewDate']();
		}
		
		return time() > $this->qbs->get("schemaset");
	}
	
	

}