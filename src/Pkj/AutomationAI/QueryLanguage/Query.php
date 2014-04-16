<?php
namespace Pkj\AutomationAI\QueryLanguage;

use Pkj\AutomationAI\Store;

class Query {
	public $store;
	public $qbs;
	public $tasks=array();
	
	
	public function __construct (Store $store, QueryBuilderStore $qbs) {
		$this->store = $store;
		$this->qbs = $qbs;
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
	
	/**
	 * 
	 * @param string $unit day,hour,minute or week
	 * @throws \Exception
	 */
	public function onceEvery ($unit) {
		
		switch($unit) {
			case "day":
				$dateParam = 'D';
				break;
			case "hour":
				$dateParam = 'H';
				break;
			case "minute":
				$dateParam = 'i';
				break;
			case "week":
				$dateParam = 'W';
				break;
			default:
				throw new \Exception("Valid time units for Query::onceEvery is day,hour,minute");
		}
		$self = $this;
		$previous = $this->qbs->get('onceEvery');
		
		$this->tasks['onceEvery'] = function () use ($self) {
			$this->qbs->set('onceEvery',date($dateParam));
		};
		return date($dateParam) != $previous;
	}
	
	
	public function afterRun () {
		foreach($this->tasks as $t) {
			$t();
		}
	}
}