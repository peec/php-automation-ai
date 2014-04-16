<?php
namespace Pkj\AutomationAI;

class TimeUtils {

	static public function timespanToReadable($secs) {
		$units = array(
				"week"   => 7*24*3600,
				"day"    =>   24*3600,
				"hour"   =>      3600,
				"minute" =>        60,
				"second" =>         1,
		);

		// specifically handle zero
		if ( $secs == 0 ) return "0 seconds";

		$s = "";

		foreach ( $units as $name => $divisor ) {
			if ( $quot = intval($secs / $divisor) ) {
				$s .= "$quot $name";
				$s .= (abs($quot) > 1 ? "s" : "") . ", ";
				$secs -= $quot * $divisor;
			}
		}

		return substr($s, 0, -2);
	}
	
	static public function getNextTime ($times) {
		
		if (!is_array($times)) {
			$times = explode(',', $times);
		}
		
		$nextTimes = array();
		foreach($times as $t) {
			$nextTimes = array_merge($nextTimes, self::getTimeForSchema($t));
		}
		$nextTime = min($nextTimes);
			
		return $nextTime;
	}
	
	static public function getTimeForSchema ($schema, $checkToday=true) {
	
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
			$times = self::getTimeForSchema($schema, false);
		}
	
		return $times;
	}

}