<?php
use Pkj\AutomationAI\BotAI;
use Pkj\AutomationAI\QueryLanguage\Query;


// Define your logic here ( this is PHP, but our API makes it easy to configure..
// Quick guide:
// Each $do statement is separated logic. So you can safety remove all $do except one forexample.
// $do(CALLBACK($botai))->when(CALLBACK($q));



// See includes for examples of "when" rules.
require __DIR__ . '/includes.php';




$do(function (BotAI $botai) {
	$botai->run("Pkj.AutomationAI.Bots.SpeechBot", array(
			"message" => "Good morning Peter, have a nice day!"
	));
})
->when($WAKE_UP_TIME);



// When there are motion in lounge, turn on lights in hallway-
$do(function (BotAI $botai) {
    $botai->run("Pkj.AutomationAI.Bots.ZwayRazberryBot", array(
        "commands" => array(
            "HALL-LIGHTS=on"
        )
    ));
})->when($AT_HOME);



$do(function (BotAI $botai) {
    $botai->run("Pkj.AutomationAI.Bots.ZwayRazberryBot", array(
        "commands" => array(
            "HALL-LIGHTS=on"
        )
    ));
})->when($NOT_AT_HOME);





// Weather cast every hour.
$do(function (BotAI $botai) {
    $botai->run("Pkj.AutomationAI.Bots.WeatherBot", array());
})->when($EVERY_HOUR_WHEN_AWAKE);



// Stop listening to bad music.
$do(function (BotAI $botai) {
    $botai->run("Pkj.AutomationAI.Bots.SpeechBot", array(
        "message" => "Please stop listening to bad music, Beaver feaver out."
    ));
})->when($LISTEN_TO_STUPID_MUSIC);

