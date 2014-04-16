<?php
namespace Pkj\AutomationAI;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\RuntimeException;


/**
 * Shoots the application.
 * @author peec
 *
 */
class Application extends Command{
	
	
	
	private $options;
	
	public function __construct ($name, $options) {
		$this->options = $options;
		parent::__construct($name);
	}
	
	
	protected function configure() {
		$this->setName("ai:run")
		->setDescription("Starts the AI bot.")
		->setDefinition(array(
				new InputOption('logpath', 'lp', InputOption::VALUE_OPTIONAL, 'Where to put logs', $this->options['log_path']),
				new InputOption('loglevel', 'll', InputOption::VALUE_OPTIONAL, 'The log level (uses Monolog style) integer.', $this->options['log_level']),
				new InputOption('configfile', 'c', InputOption::VALUE_OPTIONAL, 'Where the config file that tells which bots to load.', $this->options['config_file']),
	
		))
		->setHelp(<<<EOT
	
Usage:
	
<info>php app/console.php ai:start</info>
	
EOT
		);
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$shutdown = false;
	
	
	
		$this->options['log_path'] = $input->getOption('logpath');
		$this->options['log_level'] = $input->getOption('loglevel');
		$this->options['configfile'] = $input->getOption('configfile');
	
		if (!$this->options['configfile'] || !file_exists($this->options['configfile'])) {
			throw new InvalidArgumentException("configfile ($cfgdir) does not exist.");
		}
	
		$this->options['conf'] = json_decode(file_get_contents($this->options['configfile']), true);
	
		$ts = isset($this->options['conf']['timezone']) ? $this->options['conf']['timezone'] : $this->options['timezone'];
		date_default_timezone_set($ts);
		
		if (!$ts) {
			$output->writeln("<error>Please define timezone settings.</error>");
			return false;
		}

		$output->writeln("Welcome To Automation Engine");
		$output->writeln("Using timezone: $ts");
		
		$ai = new AI($this->options, $output);
		$ai->run();
		
	}
	
}