<?php
namespace Pkj\AutomationAI\QueryLanguage;


use Pkj\AutomationAI\BotAI;

class QueryBuilder {

	private $do;
	private $when;
	
	
	private $botAi;
	private $query;
	
	public function __construct (callable $doCallback) {
		$this->do = $doCallback;
		
	}
	
	public function configure (BotAI $botAi, Query $query) {
		$this->botAi = $botAi;
		$this->query = $query;
	}
	
	
	public function when (callable $conditionCallback) {
		$this->when = $conditionCallback;
		return $this;
	}
	
	
	public function run () {
		$run = true;
		
		if ($this->when) {
			$run = false;
			
			$when = $this->when->bindTo($this->query);		
			$run = $when($this->query);
			
			
		}
		
		if ($run) {
			$do = $this->do->bindTo($this->botAi);
			$do(); // Now run it.
		}
		
	}
	
	
}