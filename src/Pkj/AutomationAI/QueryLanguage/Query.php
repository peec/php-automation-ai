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
		return isset($this->store->eventChanges[$eventName]) ?: null;
	}
	
	public function setting ($settingName) {
		return isset($this->store->settings[$settingName]) ?: null;	
	}
	
	public function settingChange ($settingName) {
		return isset($this->store->settingChanges[$settingName]) ?: null;
	}
	
	
	/**
	 * Can be given in format such as:
	 * @param string $times Mon@12:00|23:00,Tue@12:00,Wed@15:00|16:00|17:00
	 */
	public function matchScheme($times) {
		$self = $this;
		
		if (!is_array($times)) {
			$times = explode(',', $times);
		}
		
		$createScheme = function () use ($self, $times) {			
			$nextTimes = array();
			foreach($times as $t) {
				$nextTimes = array_merge($nextTimes, $self->getTimeForSchema($t));
			}
			$nextTime = min($nextTimes);
			
			return $nextTime;
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
	
	public function getTimeForSchema ($schema, $checkToday=true) {
		
		list($day, $clocks) = explode('@', $schema);
		$clocks = explode('|', $clocks);
		
		if ($checkToday && strtolower(date('D')) == strtolower($day)) {
			$nearestDay = strtotime("today midnight");
		} else {
			$nearestDay = strtotime("next $day midnight");	
		}
		sort($clocks);
		
		
		$times = array();
		foreach ($clocks as $clock) {
			list($hour, $minute) = explode(':', $clock);
		
			// Next @:
			$ts = $nearestDay + ($hour * 60 * 60) + ($minute * 60);
			if (time() < $ts) {
				$times[] = $ts;
			}
		}
		
		if (empty($times)) {
			$times = $this->getTimeForSchema($schema, false);
		}
		
		return $times;
	}
	
	
	/**
	 * 
	 * @param string $unit day,hour,minute or week
	 * @throws \Exception
	 */
	public function onceEvery ($unit) {
		
		$dateParam = '';
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
		
		$this->tasks['onceEvery'] = function () use ($self, $dateParam) {
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