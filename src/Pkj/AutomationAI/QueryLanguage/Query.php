<?php
namespace Pkj\AutomationAI\QueryLanguage;

use Pkj\AutomationAI\TimeUtils;

use Monolog\Logger;

use Pkj\AutomationAI\Store;

class Query {
	public $store;
	public $qbs;
	
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
	

	
	

}