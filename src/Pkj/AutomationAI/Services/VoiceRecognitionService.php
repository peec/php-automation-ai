<?php
namespace Pkj\AutomationAI\Services;

use Symfony\Component\Process\Process;

use Pkj\AutomationAI\Services\ServiceThread;


class VoiceRecognitionService extends ServiceThread {

	public $matcherRules = array();
	
	
	public function setup() {
		$this->config['keywords'] = explode(',', $this->config['keywords']);
		$this->config['keywords'] = array_map('strtolower', $this->config['keywords']);
	}
	
	public $commandActive = false;
	
	public $stopListen = false;
	
	public function sendMessage ($msg) {
		if ($this->stopListen)return;
		
		$this->stopListen = true;
		if (in_array(strtolower($msg), $this->config['keywords'])) {
			$this->bots->run("Pkj.AutomationAI.Bots.SpeechBot", array(
				"message" => "What can I do for you?"
			));
			$this->commandActive = true;
		} else if ($this->commandActive) {
			
			$matched = false;
			foreach($this->matcherRules as $rule) {
				if (preg_match($rule->rule(), $msg, $matches)) {
					$rule->match();
					// on next main loop , match this..
					$matched = true;
				}
			}
			if ($matched) {
				$this->commandActive = false;
				
			} else {
				$this->bots->run("Pkj.AutomationAI.Bots.SpeechBot", array(
						"message" => "I could not quite catch what you said, please try again."
				));
			}
		}
		sleep(1);
		$this->stopListen = false;
	}
	
	
	public function loop () {
		$self = $this;
		
		$appPath = $this->bots->appPath;
		$args = str_replace('${APP_PATH}', $appPath, $this->config['arguments']);
		$cmd = "cd " . $this->config['pocketsphinx_binary'] . " && ./pocketsphinx_continuous $args";
		
		
		$process = new Process($cmd);
		$process->setTimeout(0);
		$process->run(function ($type, $buffer) use ($self) {
			if ('err' === $type) {
				// $this->output( 'ERR > '.$buffer);
			} else {
				
			}
			$line = trim((string)$buffer);
			if (stristr($line, 'READY')) {
				$self->output($line);
			}
			
			
			$matches = array();
			if (preg_match("/(\d{9}): (.*?)\n/m", $line, $matches)) {
				$msg = trim($matches[2]);
				$self->sendMessage($msg);		
			}
		});

		
		$this->exit = true; // Exit now.
	}
	
	
}