# PHP Automation AI

This tool makes it possible to do automated tasking, supports Events and settings. Useful for e.g. Home automation.
Automation AI is a more advanced type of CRON, the Automation AI is built to handle many useful conditions. 


Sample config:

```php

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
	$q->event("motion:Lounge") && // We must have gotten motion in the lounge.
	$q->onceEvery("day") && // Once every day.
	date('H') >= 4; // Clock must be more than 04:00 
});
``` 

