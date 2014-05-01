<?php
namespace Pkj\AutomationAI;

use Pkj\AutomationAI\QueryLanguage\QueryBuilderStore;

use Pkj\AutomationAI\QueryLanguage\Query;
use Pkj\AutomationAI\QueryLanguage\QueryBuilder;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\RuntimeException;

class AI {

	private $config;
	private $output;
	private $logger;
	private $loggerStream;
	private $exit;
	
	
	private $todos = array();
	
	/**
	 * 
	 * @var DB
	 */
	private $db;
	
	
	private $botAi;
	private $store;
	
	public function __construct (array $config, OutputInterface $output) {
		$this->config = $config;
		$this->output = $output;
		$this->constructLogger();
	    $this->db = $this->constructDB();
		$this->constructBotAI();
		$this->configureScripts();
		$this->initServices();
	}
	
	private function constructLogger () {
		$log = new Logger('ai.core');
		$loggerStream = new StreamHandler($this->config['log_path'], $this->config['log_level']);
		$this->loggerStream = $loggerStream;
		$log->pushHandler($loggerStream);
		$this->logger = $log;
	}
	
	private function constructDB () {
		$cfg = $this->config['conf']['database'];

        foreach($cfg as $k => $v) {
            if ($val = getenv("DB_$k")) {
                $cfg[$k] = $val;
            }
        }

		return new DB($cfg['dsn'], $cfg['username'], $cfg['password'], $cfg['driver_options'], $this->createSubLogger('ai.db'));
	}
	
	
	/**
	 * Creates a new sublogger given a name. eg. ai.core is one.
	 * @param unknown_type $name
	 */
	public function createSubLogger ($name) {
		$log = new Logger($name);
		$log->pushHandler($this->loggerStream);
		return $log;
	}
	
	
	public function getCompiledBotConfig () {
		$bots = $this->config['conf']['bots'];
		$ret = array();
		foreach($bots as $botns => $args) {
			$c = str_replace('.', '\\', $botns);
			$b = new $c($this->createSubLogger($botns), $this->db, $this->output, $args, $this->botAi);
			$b->setup();
			$ret[$botns] = array(
				'object' => $b,
				'class' => $c,
				'args' => $args
			);
			
		}
		return $ret;
	}
	
	public function initServices () {
		$services = isset($this->config['conf']['services']) ? $this->config['conf']['services'] : array();
		
		foreach($services as $service) {
			$class = array_keys($service)[0];
			$args = $service[$class];
			$c = str_replace('.', '\\', $class);
			$s = new $c($this->createSubLogger($class), $this->constructDB(), $this->output, $args, $this->botAi);
			$s->initService();
			$s->setup();
			$s->start();
		}
		
	}
	
	
	public function constructBotAI () {
		$this->botAi = new BotAI($this->config['app_path']);
		$this->botAi->setBots($this->getCompiledBotConfig());
		$this->store = new Store();
	}
	
	public function configureScripts () {
		foreach($this->config['conf']['scripts'] as $script) {
			$p = $this->config['app_path'] . DIRECTORY_SEPARATOR . $script;
			$this->output->writeln("Using configuration script: $p");
			$this->runConfigScript($p);
		}
	}
	
	public function runConfigScript ($path) {
		$botAi = $this->botAi;
		$store = $this->store;
		$self = $this;
		$do = function ($callback) use ($store, $botAi, $self) {
			$qbs = new QueryBuilderStore();
			$qb = new QueryBuilder($callback, $qbs);
			$loggerName = 'ai.query';
			$qb->configure($botAi, new Query($store, $qbs, $self->createSubLogger($loggerName)));
			$self->todos[] = $qb;
			return $qb;
		};
		
		include $path;
	}
	
	
	public function checkTodos () {
		foreach($this->todos as $todo) {
			$todo->run();
		}
	}
	
	public function run () {
		$self = $this;
		$this->exit = false;
		
		
		
		$settingCallback = function ($setting, $args) use ($self) {
			$self->store->settingChanges[$setting] = $args;
		};
		
		$eventCallback = function ($ev, $args) use ($self) {
			$self->store->eventChanges[$ev] = $args;
		};
		
		
		$this->logger->addInfo("Bot started.");
		
		while (!$this->exit) {
			$this->store->reset();
			$this->db->checkQueue($settingCallback, $eventCallback);
			$this->store->settings = $this->db->settings;
			// checkQueue callback has now gotten the setting / event change events... now..
			
			$this->checkTodos();
			sleep(2);
		}
		
	}
	
	
	
}