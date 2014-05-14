<?php
use Pkj\AutomationAI\BotAI;
use Pkj\AutomationAI\QueryLanguage\Query;
use Pkj\AutomationAI\QueryLanguage\QueryBuilder;




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
->when($WAKE_UP_TIME)
->runOnceBasedOn('time', function (QueryBuilder $b) {
    return $b->timeBased("day");
});


// When there are motion in lounge, turn on lights in hallway-
$do(function (BotAI $botai) {
    $botai->run("Pkj.AutomationAI.Bots.ZwayRazberryBot", array(
        "commands" => array(
            "HALL-LIGHTS=on"
        )
    ));
})->when($AT_HOME)->runOnceBasedOn('when');



$do(function (BotAI $botai) {
    $botai->run("Pkj.AutomationAI.Bots.ZwayRazberryBot", array(
        "commands" => array(
            "HALL-LIGHTS=off"
        )
    ));
})->when($NOT_AT_HOME)->runOnceBasedOn('when');





// Weather cast every hour.

$do(function (BotAI $botai) {
    $botai->run("Pkj.AutomationAI.Bots.WeatherBot", array());
})
->when($I_AM_AWAKE)
->runOnceBasedOn('time', function (QueryBuilder $b) {
    return $b->timeBased("hour");
});



// Stop listening to bad music.
$do(function (BotAI $botai) {
    $botai->run("Pkj.AutomationAI.Bots.SpeechBot", array(
        "message" => "Please stop listening to bad music, Beaver feaver out."
    ));
})->when($LISTEN_TO_STUPID_MUSIC);




/*
Very annoying but is here for sample:
Says your phone is on only once per success ping, if fail than success again it says it again:
$do(function (BotAI $botai) {
    $botai->output->writeln("This runs only once based on the when conditions (saves resources)..");
    $botai->run("Pkj.AutomationAI.Bots.SpeechBot", array(
        "message" => "Your phone is on."
    ));
})->when(function (Query $q) {
    return $q->setting("ping:my-phone:status");
})->runOnceBasedOn('time', function (QueryBuilder $b) {
        return $b->timeBased("minute");
});
*/

