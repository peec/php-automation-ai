<?php
namespace Pkj\AutomationAI\QueryLanguage;


use Pkj\AutomationAI\BotAI;

class QueryBuilder {

	private $do;
	private $when;
	private $store;
	
	
	private $botAi;
	private $query;



    private $lastWhenResult;
    private $provenOtherwiseMap = array();
    private $runConditions = array();


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


    /**
     * Filter to add to $runConditions to BotAI::run .
     * Saves the value $value  in a store, where $key is the key. If $value has changed, it returns true.
     * @param $key
     * @param $value
     * @return bool
     */
    public function runOnceBasedOn ($key, callable $value = null) {
        $botAi = $this->botAi;
        $this->runConditions[] = function () use ($key, $value, $botAi) {

            switch($key) {
                case "when":
                    $value = $this->whenResult();
                    break;
                default:
                    $value = $value($botAi);
                    break;
            }


            $do = false;
            if (!isset($this->provenOtherwiseMap[$key])) {
                $do = true;
            } else {
                if ($this->provenOtherwiseMap[$key] !== $value) {
                    $do = true;
                }
            }
            $this->provenOtherwiseMap[$key] = $value;
            return $do;
        };
    }

    public function setLastWhenResult ($whenResult) {
        $this->lastWhenResult = $whenResult;
    }

    /**
     * Can be used with runOnceBasedOn ex.:
     */
    public function whenResult () {
        return $this->lastWhenResult;
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
            $this->setLastWhenResult($run);

            $doCommand = true;
            foreach($this->runConditions as $do) {
                if (!$do()) {
                    $doCommand = false;
                    break;
                }
            }

            $run = $run && $doCommand;

		}
		
		if ($run) {
			$do = $this->do->bindTo($this);
			$do($this->botAi); // Now run it.
			
			$this->query->afterRun();
		}
		
	}
	
	
}