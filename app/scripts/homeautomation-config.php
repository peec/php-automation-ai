<?php
use Pkj\AutomationAI\BotAI;
use Pkj\AutomationAI\QueryLanguage\Query;

// Define your logic here ( this is PHP, but our API makes it easy to configure..


$do(function (BotAI $botai) {
	
	$botai->run("Pkj.AutomationAI.Bots.SpeechBot", array(
			"message" => "Good morning Peter, how are you today?"
	));
	
	$botai->run("Pkj.AutomationAI.Bots.ZwayRazberryBot", array(
		"commands" => array(
			"HALL-LIGHTS=on"
		)
	));
})
->when(function (Query $q) {
	return
	$q->event("motion:Lounge") && // Motion in the Lounge.
	$q->onceEvery("day"); // && // Once every day.
	date('H') >= 4; // Clock must be more than 04:00

});

// Weather cast every hour.
$do(function (BotAI $botai) {
    $botai->run("Pkj.AutomationAI.Bots.WeatherBot", array());
})
    ->when(function (Query $q) {
        return
            $q->onceEvery("hour") &&
            date('H') > 6 &&
            date('H') < 20;
    });


// Stop listening to bad music.
$do(function (BotAI $botai) {
    $botai->run("Pkj.AutomationAI.Bots.SpeechBot", array(
        "message" => "Please stop listening to bad music, Beaver feaver out."
    ));
})
    ->when(function (Query $q) {
        $currentMusic = $q->event("songchange");
        if ($currentMusic) {
            if ($currentMusic['data']['artist']=='Justin Bieber') {
                return true;
            }
        }
        return false;
    });

