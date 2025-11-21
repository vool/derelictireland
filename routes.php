<?php

// Custom 404 Handler
$router->set404(function () {
    header('HTTP/1.1 404 Not Found');
    echo '404, route not found!';
});

//Before Router Middleware
$router->before('GET', '/.*', function () {
    header('X-Powered-By: Blood, sweat and gears');
});

$router->get('/', 'HomeController@home');

  $router->get('/(\d+)', function ($page) use ($router) {
      call_user_func_array([new DerelictIreland\Controllers\HomeController,'Home'], [$page]);
  });

// Static route: /hello
// $router->get('/leader-board(/\w+)?', 'LeaderBoardController@allTime');//function () use ($tpl) {);

/*
Posts
*/

$router->get('/tag(/\w+)?(/\d+)?', 'PostController@tag');

$router->get('/posts(/\d+)?(/\w+)?', 'PostController@index');

$router->get('/post(/\d+)?', 'PostController@show');

/*
Players
*/
$router->get('/contributors(/\d+)?', 'PlayerController@index');

$router->get('/contributor/(@\w+)(/\d+)?', function ($username, $page) {
    call_user_func_array([new DerelictIreland\Controllers\PlayerController,'Show'], [$username, is_null($page) ? 1 : $page]);
});

/*
Map
*/
// $router->get('/map', function () {
//     echo '<h1>bsdsdder</h1><p>Visit <code>/hello/<em>name</em></code> to get your Hello World mojo on!</p>';
// });

// Static route: /hello
//$router->get('/free-the-what-now', 'PageController@what');

// $router->get('/get-involved', 'PageController@getInvolved');

 $router->get('/collage', 'PageController@collage');

/*
Sitemap
*/
$router->get('/sitemap.xml', 'SitemapController@sitemap');


/*
Actions
*/
$router->mount('/actions', function () use ($router) {
    $router->get('/update', 'ActionsController@update');

    //$router->get('/import', 'ActionsController@import');

    //$router->get('/init', 'DbSetupController@initDB');

    $router->get('/update-players', 'ActionsController@updatePlayers');
});
