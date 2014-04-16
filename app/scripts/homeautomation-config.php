<?php
use Pkj\AutomationAI\BotAI;
use Pkj\AutomationAI\QueryLanguage\Query;

// Define your logic here ( this is PHP, but our API makes it easy to configure..


$do(function (BotAI $botai) {
	
	$botai->run("Pkj.AutomationAI.Bots.SpeechBot", array(
			"message" => "Good morning Peter, how are you today?"
	));
	
	$botai->run("Pkj.AutomationAI.Bots.LoggerBot", array(
		"message" => "Hello there dude... this is a message.."		
	));
	
})
->when(function (Query $q) {
	return
	// We must have gotten motion in the lounge.
	$q->event("motion:Lounge") &&
	$q->onceEvery("day") &&
	date('H') > 4;
});