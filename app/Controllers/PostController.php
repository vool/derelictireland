<?php
namespace DerelictIreland\Controllers;

use DerelictIreland\Controllers\Controller;

class PostController extends Controller
{

  // Per Page
    private $ppage;

    public function __construct()
    {
        parent::__construct();

        $this->ppage = 9;
    }

    public function index($page = 1, $mode = null)//: string
    {

      //why is this not been set as defaut fann_clear_scaling_params
        if (!$page) {
            $page = 1;
        }


        switch ($mode) {
        case 'day':
            $range  = 'WHERE created_at >= DATE_SUB(DATE(NOW()), INTERVAL 1 DAY) ';
          break;
          case 'week':
            $range  = 'WHERE created_at >= DATE_SUB(DATE(NOW()), INTERVAL 7 DAY) ';
          break;
          case 'month':
            $range = 'WHERE created_at >= DATE_SUB(NOW(),INTERVAL 1 MONTH)';
          break;
          case 'year':
            $range = 'WHERE created_at >= DATE_SUB(NOW(),INTERVAL 1 YEAR)';
          break;

        default:
          $range ='';
          break;
      }

        /* Begin Paging Info */

        $sqlcount = "select count(*) as total_records from ".$_ENV['DB_TWEET_TABLE'] .' '.$range;

        $stmt = $this->dbconn->prepare($sqlcount);
        $stmt->execute();
        $row = $stmt->fetch();
        $total_records= $row['total_records'];

        $total_pages=ceil($total_records/$this->ppage);

        $offset=($page-1)*$this->ppage;

        /* End Paging Info */


        $this->dbconn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

        //$sql="SELECT `tweet` FROM `ftcl` ORDER BY `timestamp` ASC LIMIT :offset, :limit";

        $sql = "SELECT *, t.id, t.created_at
                FROM ".$_ENV['DB_TWEET_TABLE']." t
                LEFT JOIN ".$_ENV['DB_USER_TABLE']." u
                ON t.user_id=u.id
                $range
                ORDER BY t.created_at DESC
                LIMIT :offset, :limit";

        $stmt  = $this->dbconn->prepare($sql);

        $stmt->execute([":limit" => $this->ppage, ":offset" => $offset]);

        $res = $stmt ->fetchAll(\PDO::FETCH_ASSOC);

        // If out of bounds, send em home
        if ($page > 1 && count($res) == 0) {
            header("location: /");

            //exit; si this needed
        }

        //// TEMP:
        //$mode = null;

        // Preassign data to the layout
        $this->tpl->addData(['title' => ' ????', 'description' => '??.', 'layout']);

        // Render a template
        echo $this->tpl->render('posts', ['name' => '???', 'tweets' => $res, 'total' => $total_records, 'page' => $page, 'total_pages' => $total_pages, 'mode' => $mode]);
    }

    public function tag($tag, $page = 1)//: string
    {

        //why is this not been set as defaut fann_clear_scaling_params
        if (!$page) {
            $page = 1;
        }

        //tump;

        $range = '';

        /* Begin Paging Info */
        $sqlcount = "select count(*) as total_records from ".$_ENV['DB_TWEET_TABLE'] ." t WHERE t.hashtags LIKE '%$tag%' $range ";

        $stmt = $this->dbconn->prepare($sqlcount);
        $stmt->execute();
        $row = $stmt->fetch();
        $total_records= $row['total_records'];

        $total_pages=ceil($total_records/$this->ppage);

        $offset=($page-1)*$this->ppage;

        /* End Paging Info */

        $this->dbconn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

        //$sql="SELECT `tweet` FROM `ftcl` ORDER BY `timestamp` ASC LIMIT :offset, :limit";

        $sql = "SELECT *, t.id, t.created_at
                FROM ".$_ENV['DB_TWEET_TABLE']." t
                LEFT JOIN ".$_ENV['DB_USER_TABLE']." u
                ON t.user_id=u.id
                WHERE t.hashtags LIKE '%$tag%'
                $range
                ORDER BY t.created_at DESC
                LIMIT :offset, :limit";

        $stmt  = $this->dbconn->prepare($sql);

        $stmt->execute([":limit" => $this->ppage, ":offset" => $offset]);

        $res = $stmt ->fetchAll(\PDO::FETCH_ASSOC);

        // If out of bounds, send em home
        if ($page > 1 && count($res) == 0) {
            header("location: /");

            //exit; si this needed
        }


        // Preassign data to the layout
        $this->tpl->addData(['title' => ' ????', 'description' => $total_records.' posts for #'.$tag, 'layout']);

        // Render a template
        echo $this->tpl->render('posts_tag', [ 'tweets' => $res, 'total' => $total_records, 'page' => $page, 'total_pages' => $total_pages, 'tag' => $tag]);
    }



    public function show($id)
    {

      // get tweet
        $sqltweet = "SELECT *, t.id,  t.created_at
        FROM `".$_ENV['DB_TWEET_TABLE']. "` t
        JOIN `".$_ENV['DB_USER_TABLE']. "` u ON u.id = t.user_id
        WHERE t.id = :id
        LIMIT 1";

        $stmt = $this->dbconn->prepare($sqltweet);

        $stmt->execute(['id' => $id]);

        $res = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($res) { // if invader exists

            // Preassign data to the layout
            $this->tpl->addData(['title' => ' ????', 'description' => '??.', 'layout']);

            // Render a template
            echo $this->tpl->render('post', ['name' => 'jjJonathan', 'data' => $res ]);
        } else {
            // Render a template
            echo $this->tpl->render('errors::404', ['error' => 'Invader not found :(']);
        }
    }
}
