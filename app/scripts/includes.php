<?php
use Pkj\AutomationAI\QueryLanguage\Query;


$WAKE_UP_TIME = function (Query $q) {
    return
    $q->event("motion:Lounge") && // Motion in the Lounge.
    date('H') >= 4; // Clock must be more than 04:00
};



$I_AM_AWAKE = function (Query $q) {
    return
        date('H') > 6 &&
        date('H') < 20;
};

$LISTEN_TO_STUPID_MUSIC = function (Query $q) {
    $currentMusic = $q->event("songchange");
    return $currentMusic && $currentMusic['data']['artist']=='Justin Bieber';
};


$AT_HOME = function (Query $q) {
    return $q->event("motion:Lounge") ||
    time() - $q->setting("ping:my-phone:lastping") < 60*5 ||
    time() - $q->setting("motion:Lounge") < 3600;
};


$NOT_AT_HOME = function (Query $q) use($AT_HOME) {
    return !$AT_HOME($q);
};