<?php
namespace Pkj\AutomationAI\Bots;


class ZwayRazberryBot extends Bot{


    public function setup () {

    }

    public function lookup ($dev) {
        return $this->config['devices'][$dev];
    }
    
    public function run (array $config) {
        foreach ($config['commands'] as $device) {
            $device = explode("=", $device);
            $vdev = $this->lookup($device[0]);
            $cmd = $device[1];
            $url = "{$this->config['protocol']}://{$this->config['host']}:{$this->config['port']}/ZAutomation/api/v1/devices/{$vdev}/command/{$cmd}";

            $content = @file_get_contents($url);
            if ($content) {
                $this->logger->addInfo("Requested $url.", array("response" => $content));
            } else {
                $this->logger->addCritical("Could not open $url. Wrong configured, sure you got z-way installed  with Razberry device?", array("device" => $vdev, "devicename" => $device[0]));
            }
        }
    }
}