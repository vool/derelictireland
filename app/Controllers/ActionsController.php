<?php


namespace DerelictIreland\Controllers;

use Carbon\Carbon;
use DG\Twitter\Twitter;
use DG\Twitter\Exception;
use DerelictIreland\Controllers\Controller;

class ActionsController extends Controller
{
    private $twitter;

    private $towns;

    private $mentions;


    public function __construct()
    {
        parent::__construct();
        // new twitter
        $this->twitter = new Twitter($_ENV['TWITTER_CONSUMER_KEY'], $_ENV['TWITTER_CONSUMER_SECRET'], $_ENV['TWITTER_ACCESS_TOKEN'], $_ENV['TWITTER_ACCESS_SECRET']);

        //$this->towns = [];

        // for the secret score
        $this->mentions = ['gardatraffic', 'gardainfo'];
    }


    public function import()
    {
        $this->logger->info("Doing import");

        function in_arrayi($needle, $haystack)
        {
            return in_array(strtolower($needle), array_map('strtolower', $haystack));
        }


        $array = array_map('str_getcsv', file('../tweet_urls.csv'));

        $chunks = array_chunk($array, 50);

        $i =0;

        foreach ($chunks as $chunk) {
            $t_ids = array();

            foreach ($chunk as $a) {
                $t_ids[] = preg_replace("/^.*\//","",$a[0]);
            }

            $tweet_ids = implode(',', $t_ids);

            echo 'processing chunk - '. $tweet_ids. '<br>';

            //if($i>5){

            //$tweets = $this->twitter->request('statuses/lookup', 'GET', ['id' => "$tweet_ids", 'include_entities'=>true, 'tweet_mode' => 'extended']);

            //$this->ingest($tweets);

            //}


            //   if($i > 10){
            //   dd('done');
            // }
            $i++;
        }


        // $tweets = $this->twitter->request('statuses/lookup', 'GET', ['id' => $tweet_ids, 'include_entities'=>true, 'tweet_mode' => 'extended']);
        //
        // header('Content-Type: application/json');
        // //
        // echo JSON_encode($tweets);
        // exit;
        //
        //
        // $this->ingest($tweets);
    }




    public function import_twitterscraper()
    {
        $this->logger->info("Doing import");

        function in_arrayi($needle, $haystack)
        {
            return in_array(strtolower($needle), array_map('strtolower', $haystack));
        }

        $strJsonFileContents = file_get_contents("tweets.json");

        $array = json_decode($strJsonFileContents, true);

        // sort it owt maaate
        usort($array, function ($a, $b) {
            return $a['timestamp_epochs'] <=> $b['timestamp_epochs'];
        });

        $chunks = array_chunk($array, 50);

        $i =0;

        foreach ($chunks as $chunk) {
            $t_ids = array();

            foreach ($chunk as $a) {

            // //if(strpos(strtolower($a['text']), '#freethecyclelanes')){
                // if(in_arrayi('freethecyclelanes',$a['hashtags'] )){
                //   echo 1;
                // }else{
                //
                //   echo $a['text'];
                //   //echo 0;
                //   //print_r($a['hashtags']);
                //   echo '<br>';
                // }

                $t_ids[] = $a['tweet_id'];
            }

            $tweet_ids = implode(',', $t_ids);

            echo 'processing chunk - '. $tweet_ids. '<br>';

            //if($i>5){

            $tweets = $this->twitter->request('statuses/lookup', 'GET', ['id' => "$tweet_ids", 'include_entities'=>true, 'tweet_mode' => 'extended']);

            $this->ingest($tweets);

            //}


            //   if($i > 10){
            //   dd('done');
            // }
            $i++;
        }


        // $tweets = $this->twitter->request('statuses/lookup', 'GET', ['id' => $tweet_ids, 'include_entities'=>true, 'tweet_mode' => 'extended']);
        //
        // header('Content-Type: application/json');
        // //
        // echo JSON_encode($tweets);
        // exit;
        //
        //
        // $this->ingest($tweets);
    }


    public function update()
    {
        $this->logger->info("Doing update");
        // set options
        $opts=array('q'=>$_ENV['HASHTAG']."+exclude:retweets",
            'result_type'=>'mixed',
            'include_entities'=>true,
            'tweet_mode' => 'extended',
            'count'=>1000
            );

        // set since tweet
        $opts['since_id'] = $this->getLatestStatusId(); // todo this is the last tweet in the db (ie last tweet with media) rather thans the last tweet parsed
        //  $opts['since_id'] = 610341092790259712;//1218635639794688000; //$this->getLatestStatusId();// $previous_tweet_id;

        try{

                $tweets=$this->twitter->search($opts);

              } catch (Exception $e) {

        echo "Error: ", $e->getMessage();

        }


        $this->ingest($tweets);
    }


    private function ingest($tweets, $welcome = false)
    {
        $this->logger->info("Doing ingest");


      // get the town list
        $sql = 'SELECT LOWER(`townName`) FROM `towns`';

        $stmt  = $this->dbconn->prepare($sql);

        $stmt->execute();

        $this->towns = $stmt ->fetchAll(\PDO::FETCH_COLUMN, 0);

        //process each twat

        foreach ($tweets as $tweet) {

                // check the urls for youtube
            $youtube = null;

            if ($tweet->entities && $tweet->entities->urls) {
                foreach ($tweet->entities->urls as $url) {
                    //echo "<h1>".$url->expanded_url.'</h1>';

                    if (isYoutubeVideo($url->expanded_url)) {
                        $youtube = $url->expanded_url;
                    }
                }
            }


            //check if tweet has media - if so we are good to go
            if ($youtube || (isset($tweet->extended_entities) && isset($tweet->extended_entities->media))) {
                if (isset($tweet->extended_entities) && isset($tweet->extended_entities->media)) {

                        // chcek if user exists, if not add them
                    if (!isPlayer($this->dbconn, $tweet->user->id)) {

                          // add user
                        if ($this->addPlayer($tweet->user->id, $tweet->user->name, $tweet->user->screen_name, $tweet->user->profile_image_url_https, $tweet->user->location, $tweet->user->description, $tweet->user->url, Carbon::parse($tweet->created_at)->toDateTimeString())) {
                            echo "User ".$tweet->user->name." addded successfully !<br>";
                            $this->logger->info("User ".$tweet->user->name." addded successfully !");
                            if ($welcome) {
                                $this->logger->info("Welcoming user ".$tweet->user->name);
                                //TODO
                            }
                        }
                    }

                    // chcek if tweet exists, if not add them
                    if (!isTweet($this->dbconn, $tweet->id_str)) {

                        //TODO save the checks ie, geotag, location etc.

                        $score = $this->calcScore($tweet);

                        if ($this->addTweet($tweet->id_str, $tweet->user->id_str, $tweet->full_text, JSON_encode($tweet->extended_entities->media), $youtube, JSON_encode(array_column($tweet->entities->hashtags, 'text')), JSON_encode($tweet->coordinates), JSON_encode($tweet->place), $score, Carbon::parse($tweet->created_at)->toDateTimeString())) {
                            echo "Tweet ".$tweet->id_str." addded successfully !<br>";
                            $this->logger->info("Tweet ".$tweet->id_str." addded successfully !");
                        }
                    }
                }
            }

            echo "<hr>";
        }

        return true;
    }

    private function addPlayer($id, $screen_name, $username, $avatar, $location, $description, $url, $created_at)
    {
        $sql = "INSERT INTO ".$_ENV['DB_USER_TABLE']." (`username`, `screenname`, `location`, `description`, `url`, `avatar`, `id`, `created_at`) VALUES (:username, :screenname, :location, :description, :url, :avatar, :id, :created_at)";

        $stmt = $this->dbconn->prepare($sql);

        try {
            $stmt->execute(
                 ['username' => $username,
                   'screenname' => $screen_name,
                   'location' => $location,
                   'description' => $description,
                   'url' => $url,
                   'avatar' => $avatar,
                   'id' => $id,
                   'created_at' => $created_at
                 ]
             );
        } catch (Exception $e) {
            echo "Error adding user : ".$e->getMessage();

            return false;
        }

        return true;
    }

    private function updatePlayer($id, $screen_name, $username, $avatar, $location, $description, $url)
    {
        $sql = "UPDATE ".$_ENV['DB_USER_TABLE']." SET username=:username, screenname=:screenname, location=:location, description=:description, url=:url, avatar=:avatar WHERE id=:id";


        $stmt = $this->dbconn->prepare($sql);

        try {
            $stmt->execute(
                 ['id' => $id,
                   'username' => $username,
                   'screenname' => $screen_name,
                   'location' => $location,
                   'description' => $description,
                   'url' => $url,
                   'avatar' => $avatar
                 ]
             );
        } catch (Exception $e) {
            echo "Error updating user : ".$e->getMessage();

            return false;
        }

        return true;
    }

    public function updatePlayers()
    {
        $this->logger->info("Doing update players");

        echo "Doing update players";

        $this->dbconn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

        $sql = "SELECT * FROM ".$_ENV['DB_USER_TABLE'];

        $stmt  = $this->dbconn->prepare($sql);

        $stmt->execute();

        $res = $stmt ->fetchAll();

        foreach($res as $player){
          echo "<hr>";
          echo "Updating @".$player['username'] ." | ". $player['screenname']." | ". $player['id'];

          //$player['id'] = 1210263422778118147;

          try {

              $user = $this->twitter->request('users/show', 'GET', ['user_id' => $player['id']]);

              try {

                $this->updateplayer($player['id'], $user->name, $user->screen_name, $user->profile_image_url, $user->location, $user->description, $user->url);

                } catch (Exception $e) {
                    echo "Error updating  player : ".$e->getMessage();

                    return false;
                }

            } catch (Exception $e) {

                echo "Error getting player info : ".$e->getMessage();

                //return false;
            }


        }

        //dd(count($res));

            return true;
      }


    private function addTweet($id, $user_id, $text, $media, $youtube, $hashtags, $coordinates, $place, $score, $created_at)
    {
        $sql = "INSERT INTO `tweets` (`id`, `user_id`, `text`, `media`, `youtube`, `hashtags`, `coordinates`, `place`, `score`, `created_at`) VALUES (:id, :user_id, :text, :media, :youtube, :hashtags, :coordinates, :place, :score, :created_at)";

        $stmt = $this->dbconn->prepare($sql);

        try {
            $stmt->execute(
                 ["id" => $id,
                   'user_id' => $user_id,
                   'text' => $text,
                   'media' => $media,
                   'youtube' => $youtube,
                   'hashtags' => $hashtags,
                   'coordinates' => $coordinates,
                   'place' => $place,
                   'score' => $score,
                   'created_at' => $created_at
                 ]
             );
        } catch (Exception $e) {
            echo "Error adding tweet : ".$e->getMessage();

            return false;
        }

        return true;
    }

    private function getLatestStatusId()
    {
        // get the latest twwet entry
        $sql="SELECT `id` FROM `tweets` ORDER BY `created_at` DESC LIMIT 0, 1";

        $stmt  = $this->dbconn->prepare($sql);

        $stmt->execute();

        $res = $stmt ->fetch();

        //$opts = array('include_entities' => true, 'q' => '#freethecyclelanes');

        return $res['id'];
    }

    private function calcScore($tweet)
    {

      // The fault
        $s = 10;

        if ($tweet->coordinates) {
            $s += 5;
            echo "Score coords <br>";
        }

        // if town in hashtags
        // TODO Countys and othe area ie Rathmines
        if (array_uintersect(array_column($tweet->entities->hashtags, 'text'), $this->towns, "strcasecmp")) {
            $s += 5;
            echo "Score #town<br>";
        };

        //ssh - if AGS are taged
        if (array_uintersect(array_column($tweet->entities->user_mentions, 'screen_name'), $this->mentions, "strcasecmp")) {
            $s += 3;
            echo "Score AGS mentioned<br>";
        };

        return $s;
    }
}
