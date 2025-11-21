<?php


namespace DerelictIreland\Controllers;

use DerelictIreland\Controllers\Controller;

class PageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    public function what()
    {
        echo $this->tpl->render('what');
    }


    public function getInvolved()
    {
        $rcpg_ids = array(1971268730, 3095701085, 2330741400, 209496062, 836395631337943041, 570213090, 312784768, 53996964, 1054079618431569923, 127925602, 1106908585555042304, 954808357591900160);

        $sql = "SELECT * FROM `".$_ENV['DB_USER_TABLE']."` WHERE `id` IN (" . implode(',', $rcpg_ids).")";

        $stmt  = $this->dbconn->prepare($sql);

        $stmt->execute();

        $rcpgs =  $stmt ->fetchAll();

        echo $this->tpl->render('get-involved', [ 'rcpgs' =>   $rcpgs ]);
    }

    public function credit()
    {
        echo $this->tpl->render('get-involved');
    }

    public function collage()
    {

      //$sql = "SELECT id, media FROM ".$_ENV['DB_TWEET_TABLE']." LIMIT 10";

      $sql = "SELECT id, media FROM ".$_ENV['DB_TWEET_TABLE'];

      $stmt  = $this->dbconn->prepare($sql);

      $stmt->execute();

      $res = $stmt ->fetchAll(\PDO::FETCH_ASSOC);

      $pics = [];

      foreach($res as $r){

        $media = JSON_decode($r['media']);

        foreach($media as $m){

          $pics[] = [$m->media_url_https.':small', $r['id']];


          //var_dump($m->media_url_https);
        }
      }

      shuffle($pics);

      // Preassign data to the layout
      $this->tpl->addData(['title' => ' Collage', 'description' => '', 'layout']);

      // Render a template
      echo $this->tpl->render('collage', ['name' => 'Collage', 'pics' => $pics]);


      //echo $this->tpl->render('collage');
    }
}
