<?php
namespace Pkj\AutomationAI\Bots;

use Forecast\Forecast;
use Symfony\Component\Process\Exception\InvalidArgumentException;


/**
 * Class WeatherBot
 * @package Pkj\Raspberry\SpeechDaemon\Bots
 */
class WeatherBot extends Bot{

    private $apiKey;

    /**
     * @var \Forecast\Forecast
     */
    public $forecast;



    /**
     * set FORECAST_API_KEY as env var if you dont want to add it to
     */
    public function setup () {

        $envKey = getenv('FORECAST_API_KEY');
        if ($envKey) {
            $this->apiKey = $envKey;
        } else {
            $this->apiKey = $this->cfgRequire('apikey');
        }

        if (!$this->apiKey) {
            throw new InvalidArgumentException("Could not find APIKEY. Please configure bots.json or set FORECAST_API_KEY ENV VARIABLE to your key from http://forecast.io.");
        }
        $this->output->writeln("Using Forecast.IO API key {$this->apiKey}.");

        $forecast = new Forecast($this->apiKey);
        $this->forecast = $forecast;


    }
    
    public function say($msg) {
    	$this->bots->run("Pkj.AutomationAI.Bots.SpeechBot",array('message' => $msg));
    }

	public function run (array $config) {
        $this->config = array_merge($this->config, $config);

        $forecast = $this->forecast->get($this->config['latitude'], $this->config['longitude'], null, array(
            'units' => $this->cfgRequire('units'),
            'exclude' => 'flags'
        ));

        $daily = $forecast->daily;



        if(isset($forecast->currently)) {
            $currently = $forecast->currently;
            if ($currently->temperature < 1) {
                $this->say("Currently, the temprature are below: 1, degree. Please drive carefully.");
            } else {
                $rounded = round($currently->temperature);
                $this->say("Currently, the temprature outside is: $rounded, degree.");
            }
        }

        if (isset($daily->summary)) {
            $summary = $daily->summary;
            $summary = preg_replace('/(\d+)°[A-Za-z]/', ': ${1}, degree', $summary);

            $this->say($summary);
        }
	}

} 