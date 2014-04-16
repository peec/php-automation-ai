<?php
namespace Pkj\AutomationAI\QueryLanguage;

class QueryBuilderStore {
	
	private $data = array();
	
	public function get ($key) {
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}
	
	public function set($key, $value) {
		$this->data[$key] = $value;
	}
}