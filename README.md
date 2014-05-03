# PHP Automation AI

This tool makes it possible to do automated tasking, supports Events and settings. Useful for e.g. Home automation.
Automation AI is a more advanced type of CRON, the Automation AI is built to handle many useful conditions. 

![PHP Automation AI Diagram](/docs/diagram/diagram.phpautomationai.png?raw=true "PHP Automation AI Diagram")


## Features

- Uses extensive logging as it's really important for automation. (PSR-0).
- Supports custom bots.
- Included bots are many: Weather forecast, Z-way razberry integration, Google speech bot.
- Tuned for performance to perform well on small devices such as the Raspberry PI.
- Services such as a Ping service is also included.



## Example configuration

Here is a typical configuration for home-automation, using the `SpeechBot` and `ZwayRazberryBot`.

```php
// app/scripts/homeautomation-config.php
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
	$q->onceEvery("hour");
});
``` 


So configuration for the bot is simple PHP scripts. It's clean built with `$do`->CALLBACK->`when`->CALLBACK.




## INSTALL

#### Setup



**Install dependencies**

```bash
apt-get install mysql-server php5-cli
```

**Install the application**

```bash
git clone https://github.com/peec/php-automation-ai.git ~/php-automation-ai
cd ~/php-automation-ai
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

**Install MySQL db**

```bash
mysql -u root -p < app/sql/createdb-mysql.sql
mysql -u root -p aibot < app/sql/mysql.sql
```

#### Run

1. Configure the `app/config/bots.json` file for your needs.
2. Run command with `sudo php app/console.php ai:run`

Tip: Run the command as root - the ping service requires root access.

Tip: use "Screen" to run the app in background and i.e. at startup .

 


## The Query API

The when callback has an argument and it's an instance of `Pkj\AutomationAI\QueryLanguage\Query`.



#### setting($name)

Returns the given setting. 

Forexample. the ping service sets ping:my-device to 0 or 1.


#### event($name)

Returns true if event has been fired since the last loop. Only once, after run the event is deleted.


#### onceEvery($timetype)

Returns true when a `$timetype` has passed since last run. 

- $timetype can be: `minute`, `hour`, `day` or `week`.


#### matchScheme($times)

matchScheme will return true if the bot has reached that specific time or more. There might be some seconds delay because of sleep / wait times.

**Example**

```php
matchScheme("Mon@12:00,Tue@14:00,Wed@09:12,Thu@23:00,Fri@02:00|05:00|09:00,Sat@22:00|15:00,Sun@12:00");
``` 
- A comma separated list or array of days with one or many timestamps.
- Can be many clock definitions with a `|` separator.
- Days use 3 letter format. Mon,Tue,Wed .. etc.
- Format of a timestamp: DAY@CLOCK|CLOCK|...





