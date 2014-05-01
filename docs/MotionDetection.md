# Motion detection.

You want motion detection for your home-automation setup. There are many ways of implementing motion detection. I have used "ISpy" (http://www.ispyconnect.com/) together with the Raspberry camera.

This is my setup:

- Machine 1 ( raspberry ): My raspberry got the camera module, it uses v4l to send the stream to "Machine 2".
- Machine 2 ( virtual machine-linux ): This is just a linux machine that runs Crtmpserver.
- Machine 3 ( virtual machine-windows ): I got a virtual windows machine that runs iSpy http://www.ispyconnect.com/. This connects to the CRTMP server (Machine 2). Machine 3 sends a JSON request to "THE PHP SCRIPT", which you can see below.




## THE PHP SCRIPT

This runs whenever there are motion in my lounge. Easy.  just adds a row into the "evenqueue" table and "settings". We can then configure homeautomation-config.php to check for ->event("motion:Lounge") or see last movement with settings("motion:Lounge").!

```php
require __DIR__. "/vendor/autoload.php";

$event = "motion:Lounge";
$pdo = new \PDO("mysql:dbname=aibot;host=localhost", "aibot", "aibot");

$query = $pdo->prepare("SELECT * FROM settings WHERE `key`='$event'");

$query->execute();

if ($query->rowCount()) {
    echo "update";
    $query = $pdo->prepare("UPDATE settings SET value='".time()."' WHERE `key`='$event'");
    $query->execute();
} else {
    echo "insert";
    $query = $pdo->prepare("INSERT INTO settings VALUES ('$event', '".time()."')");
    $query->execute();
}

$query = $pdo->prepare("INSERT INTO eventqueue (event, args) VALUES (:event, :args)");
$query->execute(array(
':event' => $event,
':args' => json_encode(array("time" => time()))
));
```