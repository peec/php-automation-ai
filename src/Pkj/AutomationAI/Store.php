<?php
namespace Pkj\AutomationAI;

class Store {
	
	public $eventChanges;
	public $settingChanges;
	public $settings;
	
	
	public function reset () {
		$this->eventChanges = array();
		$this->settingChanges = array();
		$this->settings = array();
	}
	
	
}