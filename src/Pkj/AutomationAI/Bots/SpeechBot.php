<?php
namespace Pkj\AutomationAI\Bots;


use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;


/**
 * Text-to-speech, uses GOOGLE API's.
 * 
 * TODO add support for more providers. - support the 'provider' setting.
 * @author peec
 *
 */
class SpeechBot extends Bot {
	

	public function run (array $args) {

		switch($args['provider']) {
			case "google":
				$process = new Process(sprintf($args['command'], urlencode($args['message'])));
				break;
			default:
				throw new \Exception("SpeechBot does not have google as provider.");
				break;
		}
		
		$process->setTimeout(3600);
		$process->run();
		if (!$process->isSuccessful()) {
			throw new RuntimeException($process->getErrorOutput());
		}
		
		return $process->getOutput();
	}
	
}