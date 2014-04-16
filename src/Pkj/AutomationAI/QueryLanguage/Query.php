<?php
namespace Pkj\AutomationAI\QueryLanguage;

use Pkj\AutomationAI\Store;

class Query {
	private $store;
	
	
	public function __construct (Store $store) {
		$this->store = $store;
	}
	
	public function event ($eventName) {
		return isset($this->store->eventChanges[$eventName]) ?: null;
	}
	
	public function setting ($settingName) {
		return isset($this->store->settings[$settingName]) ?: null;	
	}
	
	public function settingChange ($settingName) {
		return isset($this->store->settingChanges[$settingName]) ?: null;
	}
	
	
	
	
}