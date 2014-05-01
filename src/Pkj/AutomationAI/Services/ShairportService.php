<?php
namespace Pkj\AutomationAI\Services;


use Symfony\Component\Process\Exception\RuntimeException;

class ShairportService extends ServiceThread {

    public $previous;

    public function loop () {
        $play_file = $this->config['now_playing_file'];

        $announceNewSong = false;

        if (file_exists($play_file)) {
            $current = file_get_contents($play_file);
            if ($current) {
                $entry = new ShairportEntry($current);
                if (!$this->previous) {
                    $this->previous = $entry;
                    $announceNewSong = true;
                }

                if (!$this->previous->equals($entry)) {
                    $announceNewSong = true;
                }

                if ($announceNewSong) {
                    $this->newSong($entry);
                }



                $this->previous = $entry;
            } else {
                $this->logger->addAlert("$play_file is empty.");
            }
        } else {
            $this->logger->addAlert("Could not open file $play_file . Does not exist. Is Shairport on ? It should be.");
        }



    }

    public  function newSong (ShairportEntry $e) {
        $this->db->addEvent("songchange", array('data' => $e));

        $this->db->updateSetting("currentsong", json_encode(array('data' => $e)));
    }


}

class ShairportEntry implements \Serializable{
    public $artist;
    public $title;
    public $album;
    public $artwork;
    public $genre;
    public $comment;
    public $time;

    public $str;

    public function __construct ($data) {
        $this->decode($data);
    }

    public function decode ($data) {
        $this->str = $data;
        $e = explode("\n", $data);
        $this->artist = $e[0];
        $this->title = $e[1];
        $this->album = $e[2];
        $this->artwork = $e[3];
        $this->genre = $e[4];
        $this->comment = $e[5];
        $this->time = time();
    }

    public function serialize() {
        return serialize($this->str);
    }
    public function unserialize($data) {
        $this->decode(unserialize($data));
    }


    public function equals(ShairportEntry $e) {
        return
            $this->artist == $e->artist &&
            $this->title == $e->title &&
            $this->album == $e->album &&
            $this->artwork == $e->artwork &&
            $this->genre == $e->genre &&
            $this->comment == $e->comment;
    }
}