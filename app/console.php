<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Application;
use Monolog\Logger;

set_time_limit(0);


$options = array(
	'app_path' => __DIR__,
    'log_path' => __DIR__ . '/logs/ai.log',
    'log_level' => Logger::DEBUG,
    'config_file' => __DIR__ . '/config/bots.json'
);

$app = new Application("Automation AI", "0.0.1");
$app->add(new Pkj\AutomationAI\Application("Daemon Command",$options));

$app->run();