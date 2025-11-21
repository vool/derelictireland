<?php
function dd($a)
{
    var_dump($a);
    exit;
}

function largeAvatar($avatar)
{
    return str_replace('_normal', '', $avatar);
}


function isPlayer($cxn, $id)
{
    //return falue;

    $sql = "SELECT `id`
            FROM `users` where `id` = :id";

    $stmt = $cxn->prepare($sql);

    $stmt->execute(["id" => $id]);

    if (count($stmt ->fetchAll())) {
        return true;
    } else {
        return false;
    }
}

function isTweet($cxn, $id)
{
    //return falue;

    $sql = "SELECT `id`
            FROM `tweets` where `id` = :id";

    $stmt = $cxn->prepare($sql);

    $stmt->execute(["id" => $id]);

    if (count($stmt ->fetchAll())) {
        return true;
    } else {
        return false;
    }
}

function isYoutubeVideo($url)
{
    $regex_pattern = "/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/";
    $match;

    if (preg_match($regex_pattern, $url)) {
        return true;
    } else {
        return false;
    }
}

//
// <ul class="">
//   <li class="page-item disabled">
//     <a class="page-link" href="#" tabindex="-1">
//       <span class="bi bi-chevron-left"></span>
//     </a>
//   </li>
//   <li class="page-item">
//     <a class="page-link" href="#">1</a>
//   </li>
//   <li class="page-item active">
//     <a class="page-link" href="#">2</a>
//   </li>
//   <li class="page-item next">
//     <a class="page-link" href="#">
//       <span class="bi bi-chevron-right"></span>
//     </a>
//   </li>
// </ul>



function generatePageLinks($pn, $total_pages, $base_url = '/')
{
    $pagLink =  '<ul class="pagination justify-content-end">';
    // K is assumed to be the middle index.
    $k = (($pn+4>$total_pages)?$total_pages-4:(($pn-4<1)?5:$pn));

    // Show prev and first-page links.
    if ($pn>=2) {
        $pagLink .="<li class='page-item'><a href='".$base_url."1' class='page-link' > << </a></li>";
        $pagLink .= "<li class='page-item previous'><a href='".$base_url .($pn-1)."' ' class='page-link'> < </a></li>";
    }

    // Show sequential links.
    for ($i=-4; $i<=4; $i++) {
        if ($k+$i==$pn) {
            $active = ' active';
        } else {
            $active = '';
        }
        // why hack ?
        if ($k+$i > 0) {
            //$pagLink .= "<li class='list-inline-item'><a href='".$base_url.($k+$i)."'>".($k+$i)."</a></li>";
            $pagLink .= "<li class='page-item".$active."'><a href='".$base_url.($k+$i)."' class='page-link'>".($k+$i)."</a></li>";
        }
    };


    // Show next and last-page links.
    if ($pn<$total_pages) {
        $pagLink .= "<li class='page-item'><a href='".$base_url.($pn+1)."' class='page-link'> > </a></li>";
        $pagLink .= "<li class='page-item'><a href='".$base_url.$total_pages."' class='page-link'> >> </a></li>";
    }

    $pagLink .= '</ul>';

    return $pagLink;
}

function linkify($text)
{
    // do username
    $text = preg_replace('#@(\w+)#', '<a href="/contributor/@$1">$0</a>', $text);
    // do hashtag
    $text = preg_replace('/#(\w+)/', '<a href="/tag/$1">$0</a>', $text);
    //do url

    echo $text;
}


/*
Link generation helpers
*/

function t_link_user($username, $full=true)
{
    $url = "https://twitter.com/$username";

    if ($full) {
        echo  "<a href=\"$url\" target='_BLANK'>View on Twitter</a>";
    } else {
        echo $url;
    }
}


function t_link_tweet($id, $full=true)
{
    $url = "https://twitter.com/user/status/$id";

    if ($full) {
        echo  "<a href=\"$url\" target='_BLANK'>View on Twitter</a>";
    } else {
        echo $url;
    }
}

function t_like($id, $full=true)
{
    $url = "https://twitter.com/intent/like?tweet_id=$id&related=tweetphelan";

    if ($full) {
        echo  "<a href=\"$url\" target='_BLANK'>Like</a>";
    } else {
        echo $url;
    }
}

function t_retweet($id, $full=true)
{
    $url = "https://twitter.com/intent/retweet?tweet_id=$id&related=tweetphelan";

    if ($full) {
        echo  "<a href=\"$url\" target='_BLANK'>Retweet</a>";
    } else {
        echo $url;
    }
}

function t_reply($id, $full=true)
{
    $url = "https://twitter.com/intent/tweet?in_reply_to=$id&related=tweetphelan";

    if ($full) {
        echo  "<a href=\"$url\" target='_BLANK'>Reply</a>";
    } else {
        echo $url;
    }
}

function t_text($text, $link='', $full=true)
{
    $url = "https://twitter.com/intent/tweet?text=$text&url=$link&related=tweetphelan";

    if ($full) {
        echo  "<a href=\"$url\" target='_BLANK'>Reply</a>";
    } else {
        echo $url;
    }
}

function t_follow($id, $full=true)
{
    $url = "https://twitter.com/intent/follow?user_id=$id&related=tweetphelan";

    if ($full) {
        echo  "<a href=\"$url\" target='_BLANK'>Follow</a>";
    } else {
        echo $url;
    }
}
