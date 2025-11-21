<?php
namespace DerelictIreland\Controllers;

use DerelictIreland\Controllers\Controller;

class SitemapController extends Controller
{


    // Per Page
    //private $pages;

    private $url;

    public function __construct()
    {
        //  $this->id = $id;
        parent::__construct();
        $this->url = 'http://'.$_SERVER['HTTP_HOST'];
    }

    /*
    *
    */

    public function sitemap()
    {
        header('Content-type: text/xml');
        header('Pragma: public');
        header('Cache-control: private');
        header('Expires: -1');
        date_default_timezone_set('UTC');
        define('m', 'monthly');
        define('a', 'always');
        define('w', 'weekly');
        define('d', 'daily');

        $this->head();
        $this->feed($this->url, m);
        $this->pages();

        $this->players();
        $this->invaders();

        //// TODO:
        //$this->tags()


        $this->foot();
    }

    private function head()
    {
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        //echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"; xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"; xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
    }

    private function feed($url, $freq)
    {
        echo '
        <url>
        <loc>'.$url.'</loc>
        <changefreq>'.$freq.'</changefreq>
        </url>
        ';
    }

    private function players()
    {
        $sql = "SELECT `username`
            FROM ".$_ENV['DB_USER_TABLE'];


        $stmt  = $this->dbconn->prepare($sql);

        $stmt->execute();

        $res = $stmt ->fetchAll(\PDO::FETCH_ASSOC);

        if ($res) {
            foreach ($res as $r) {
                $link = $this->url.'/contributor/@'.$this::clean($r['username']);
                $this->feed($link, a);
            }
        }
    }


    private function invaders()
    {
        $sql = "SELECT `id`
            FROM ".$_ENV['DB_TWEET_TABLE'];

        $stmt  = $this->dbconn->prepare($sql);

        $stmt->execute();

        $res = $stmt ->fetchAll(\PDO::FETCH_ASSOC);

        if ($res) {
            foreach ($res as $r) {
                $link = $this->url.'/post/'.$this::clean($r['id']);
                $this->feed($link, m);
            }
        }
    }

    private function foot()
    {
        echo '</urlset>';
    }

    private function clean($string)
    {
        $string = strtolower(preg_replace('@[\W_]+@', '-', $string));
        $string = rtrim($string, '-');
        $string = strtolower($string);
        return $string;
    }


    public function pages()
    {
        $pages = 'players, invaders, leader-board, get-involved,';
        $allpage = explode(',', $pages);
        foreach ($allpage as $page) {
            $link = 'http://'.$_SERVER['HTTP_HOST'].'/'.trim($page);
            $this->feed($link, m);
        }
    }
}
