<?php
use Pkj\AutomationAI\QueryLanguage\Query;

// Define your logic here ( this is PHP, but our API makes it easy to configure..


$do(function () {
	$this->run("Pkj.AutomationAI.Bots.LoggerBot", array(
		"message" => "Hello there dude... this is a message.."		
	));
})
->when(function (Query $q) {
	return
	$q->event("motion:Lounge");
});