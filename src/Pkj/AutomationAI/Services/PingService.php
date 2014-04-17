<?php
namespace Pkj\AutomationAI\Services;


use Symfony\Component\Process\Exception\RuntimeException;

class PingService extends ServiceThread {
	
	
	public function loop () {
		$ping = trim($this->ping($this->config['host'], isset($this->config['timeout']) ? $this->config['timeout'] : 1));
		if ($ping) {
			$ping = 1;
			$this->db->updateSetting("ping:{$this->config['device_name']}:lastping", time());
		} else {
			$ping = 0;
		}
		$res = $this->db->updateSetting("ping:{$this->config['device_name']}:status", $ping, $this->logger);

	}

	protected function ping ($host, $timeout = 1) {
		/* ICMP ping packet with a pre-calculated checksum */
		$package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
		$socket = @socket_create(AF_INET, SOCK_RAW, 1);
		if (!$socket) {
			throw new RuntimeException("Could not ping $host , insufficient permissions to create socket ping packet. DID YOU RUN AS ROOT? (required).");
		}
		socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
		socket_connect($socket, $host, null);

		$ts = microtime(true);
		socket_send($socket, $package, strLen($package), 0);
		if (socket_read($socket, 255)) {
			$result = microtime(true) - $ts;
		} else {
			$result = false;
		}
		socket_close($socket);

		return $result;
	}

}