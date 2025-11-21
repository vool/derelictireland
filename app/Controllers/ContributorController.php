<?php
namespace DerelictIreland\Controllers;

use DerelictIreland\Controllers\Controller;

class PlayerController extends Controller
{


    // Per Page
    private $ppage;

    //  private $id;

    public function __construct()
    {
        //  $this->id = $id;
        parent::__construct();
        $this->ppage = 18;
    }

    /*
    *
    */

    public function index($page = 1)
    {
        //why is this not been set as defaut fann_clear_scaling_params
        if (!$page) {
            $page = 1;
        }

        /* Begin Paging Info */
        $sqlcount = "select count(*) as total_records from ".$_ENV['DB_USER_TABLE'];

        $stmt = $this->dbconn->prepare($sqlcount);
        $stmt->execute();
        $row = $stmt->fetch();
        $total_records= $row['total_records'];

        $total_pages=ceil($total_records/$this->ppage);

        $offset=($page-1)*$this->ppage;
        /* End Paging Info */

        $this->dbconn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

        $sql = "SELECT *
              FROM ".$_ENV['DB_USER_TABLE']." LIMIT :offset, :limit";

        // TODO:
        //ORDER BY u.created_at ASC

        $stmt  = $this->dbconn->prepare($sql);

        $stmt->execute([":limit" => $this->ppage, ":offset" => $offset]);

        //$stmt->execute();

        $res = $stmt ->fetchAll();

        // Preassign data to the layout
        $this->tpl->addData(['title' => ' ????', 'description' => '??.', 'layout']);

        // Render a template
        echo $this->tpl->render('contributors', ['name' => '??', 'contributors' => $res, 'total' => $total_records, 'page' => $page, 'total_pages' => $total_pages ]);
    }

    /*
    *
    */


    public function Show($username, $page = 1)
    {

      //echo pixelate("../public/img/test.jpg", "testing", 5,5);

        //why is this not been set as defaut fann_clear_scaling_params
        if (!$page) {
            $page = 1;
        }

        // strip off @
        $username = str_replace('@', '', $username);

        //TODO test for valid username

        // get user
        $sqluser = "select * from ".$_ENV['DB_USER_TABLE']. " WHERE `username` = :username  LIMIT 1";

        $stmt = $this->dbconn->prepare($sqluser);

        $stmt->execute([":username" => $username]);

        $user = $stmt->fetch();

        if (!$user) {

          // Render a template
            echo $this->tpl->render('errors::404_user', ['username' => $username]);

            return false;
        }

        /* Begin Paging Info */
        // & sum
        $sqlcount = "select count(*) as total_records, sum(score) as total_score from ".$_ENV['DB_TWEET_TABLE']. " WHERE `user_id` = :user_id";

        $stmt = $this->dbconn->prepare($sqlcount);
        $stmt->execute([":user_id" => $user['id']]);
        $row = $stmt->fetch();
        $total_records= $row['total_records'];

        $total_pages=ceil($total_records/$this->ppage);

        $offset=($page-1)*$this->ppage;

        /* End Paging Info */

        $score= $row['total_score'];

        $this->dbconn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

        $sql="SELECT * FROM `tweets`
              WHERE `user_id` = :user_id
              ORDER BY created_at DESC
              LIMIT :offset, :limit" ;

        $stmt  = $this->dbconn->prepare($sql);

        $stmt->execute([":limit" => $this->ppage, ":offset" => $offset, ":user_id" => $user['id']]);

        try {
            $stmt->execute();
        } catch (Exception $e) {

                //var_dump($stmt->debugDumpParams());
        }

        $tweets = $stmt ->fetchAll(\PDO::FETCH_ASSOC);

        // If out of bounds, send em home
                  if ($page > 0 && count($tweets) == 0) { // todo - this shod be for user info ?

                  header("location: /contributor/@".$username);

                      //exit; si this needed
                  }

        // get last active date
        $sqluser = "select created_at from ".$_ENV['DB_TWEET_TABLE']. "
                  WHERE `user_id` = :id
                  ORDER BY created_at DESC
                  LIMIT 1";

        $stmt = $this->dbconn->prepare($sqluser);

        $stmt->execute([":id" => $user['id']]);

        $res = $stmt->fetch();

        $last_active = $res['created_at'];

        // get user INFO
        //         $sql="SELECT * FROM `users` WHERE `id` = :id LIMIT 1" ;
        //
        //
        //         $stmt  = $this->dbconn->prepare($sql);
        //
        //         $stmt->execute([ ":id" => $id]);
        //
        // try{
        //
        //         $stmt->execute();
        //
        //       }catch(Exception $e){
        //
        //         //var_dump($stmt->debugDumpParams());
        //
        //         }
        //
        //         $user = $stmt ->fetch();



        //for sparkline
        //   $sql="SELECT COUNT(`tweet`), EXTRACT(YEAR_MONTH FROM `timestamp`) as `month`
        // FROM `ftcl`
        // WHERE `user_id` = :user_id
        // GROUP BY EXTRACT(YEAR_MONTH FROM `timestamp`)";
        //
        //   $stmt  = $this->dbconn->prepare($sql);
        //
        //   $stmt->execute([":user_id" => $id]);

        //$res = $stmt ->fetchAll();

        //var_dump($res);

        //$total = count($res);

        //echo $total;
        //exit;

//
//       SELECT COUNT(id)
        // FROM stats
        // GROUP BY EXTRACT(YEAR_MONTH FROM record_date)


        //$this->dbconn->setAttribute( \PDO::ATTR_EMULATE_PREPARES, false );
        //
        // $sql="SELECT `tweet` FROM `ftcl` WHERE `user_id` = :user_id";
        // //$sql="SELECT * FROM :table LIMIT :limit, :offset";
        //
        // $stmt  = $this->dbconn->prepare($sql);
        //
        // $stmt->execute([":user_id" => $id]);

        //$res = $stmt ->fetchAll();

        //$total = count($tweets);

        // Preassign data to the layout
        $this->tpl->addData(['title' => '@'.$user['username'].' is documenting #DerelictIreland !', 'description' => '@'.$user['username'].' is is documenting #DerelictIreland with '.$total_records.' post to date !']);

        // Render a template
        echo $this->tpl->render('contributor', ['tweets' => $tweets, 'total' => $total_records, 'page' => $page, 'total_pages' => $total_pages, 'user' => $user, 'last_active' => $last_active, 'score' => $score  ]);
    }
}
