<?php
namespace DerelictIreland\Controllers;

use DerelictIreland\Controllers\Controller;

class LeaderBoardController extends Controller
{

  // Per Page
    private $ppage;

    private $limit;

    public function __construct()
    {
        parent::__construct();
    }


    public function allTime($mode = null)
    {

//         $sql = "SELECT users.id, count( tweets.user_id ) AS tweet_count
        // FROM tweets
        // LEFT JOIN users ON users.id = tweets.user_id
        // GROUP BY tweets.user_id
        // ORDER BY tweet_count DESC
        // LIMIT 0 , 30";



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


//
        // $range = 'WHERE YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
        // AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)';
//
        // $range = 'WHERE DATE(created_at) > Date_add(Now(), interval - 12 MONTH) ';


        // by tweets
//         $sql = "SELECT *
        // FROM users
        // RIGHT JOIN (
        // 	SELECT user_id, COUNT(*) AS tweet_count
        // 	FROM tweets
        //   $range
        // 	GROUP BY user_id
        // ) tweet_counts ON tweet_counts.user_id = users.id
        // ORDER BY tweet_count DESC
        // LIMIT 20";


        // by score
        $sql = "SELECT *
        FROM users
        RIGHT JOIN (
        SELECT user_id,
        COUNT(*) AS tweet_count,
        SUM(score) AS total_score

        FROM tweets
        $range
        GROUP BY user_id
        ) tweet_counts ON tweet_counts.user_id = users.id
        ORDER BY total_score DESC
        LIMIT 20";

        $stmt = $this->dbconn->prepare($sql);
        $stmt->execute();

        $res = $stmt ->fetchAll();

        $total = count($res);

        // Preassign data to the layout
        $this->tpl->addData(['title' => 'Leader Board', 'description' => 'Game Leader board', 'layout']);
        ;
        // Render a template
        echo $this->tpl->render('leader-board', [ 'users' => $res, 'total' => $total, 'mode' => $mode]);
    }
}
