<?php
namespace Pkj\AutomationAI\QueryLanguage;


use Pkj\AutomationAI\BotAI;

class QueryBuilder {

	private $do;
	private $when;
	private $store;
	
	
	private $botAi;
	private $query;
	
	public function __construct (callable $doCallback, QueryBuilderStore $store) {
		$this->do = $doCallback;
		$this->store = $store;
		
	}
	
	public function configure (BotAI $botAi, Query $query) {
		$this->botAi = $botAi;
		$this->query = $query;
		$this->query->queryStore = $this->store;
	}
	
	
	public function when (callable $conditionCallback) {
		$this->when = $conditionCallback;
		return $this;
	}
	
	
	public function getStore () {
		return $this->store;
	}
	
	
	public function run () {
		$run = true;
		
		if ($this->when) {
			$run = false;
			
			$when = $this->when->bindTo($this);		
			$run = $when($this->query);
			
			
		}
		
		if ($run) {
			$do = $this->do->bindTo($this);
			$do($this->botAi); // Now run it.
			
			$this->query->afterRun();
		}
		
	}
	
	
}