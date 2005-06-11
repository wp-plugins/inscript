<?php

function inscript_func_theme_name ($args)
{
  $theme = get_theme_data (ABSPATH."/wp-content/themes/".get_settings ('stylesheet')."/style.css");
  return trim ($theme['Name']);
}

function inscript_func_theme_description ($args)
{
  $theme = get_theme_data (ABSPATH."/wp-content/themes/".get_settings ('stylesheet')."/style.css");
  return trim ($theme['Description']);
}

function inscript_func_theme_author ($args)
{
  $theme = get_theme_data (ABSPATH."/wp-content/themes/".get_settings ('stylesheet')."/style.css");
  return trim ($theme['Author']);
}

function inscript_func_theme_version ($args)
{
  $theme = get_theme_data (ABSPATH."/wp-content/themes/".get_settings ('stylesheet')."/style.css");
  return trim ($theme['Version']);
}




function inscript_func_http_host ($args)
{
  return $_SERVER['HTTP_HOST'];
}

function inscript_func_http_ip ($args)
{
  return $_SERVER['REMOTE_ADDR'];
}

function inscript_func_http_agent ($args)
{
  return $_SERVER['HTTP_USER_AGENT'];
}

function inscript_func_http_referer ($args)
{
  return $_SERVER['HTTP_REFERER'];
}



function inscript_time_since ($count, $word)
{
  if ($count == 1)
    return "$count $word";
  return "$count $word".__("s");
}

function inscript_func_time_since ($args)
{
  if (isset ($args['time']))
  {
    $thentime = strtotime ($args['time']);
    $nowtime  = time ();
    $diff     = $nowtime - $thentime;
    
    if ($diff < 0)
      $direction = __("in the future");
    else
      $direction = __("ago");

    $diff = abs ($diff);
    
    // Convert different into two units: days and minutes
    $days = floor ($diff / (60 * 60 * 24));
    $mins = floor (($diff % (60 * 60 * 24)) / 60);  

    if ($days == 0)
      return inscript_time_since (floor ($mins / 60), __("hour"))." ".inscript_time_since (floor ($mins % 60), "minute")." $direction";
    else if ($days > 365)
      return inscript_time_since (floor ($days / 365), __("year"))." ".inscript_time_since (floor (($days % 365) / 30), "month")." $direction";
    else if ($days > 30)
      return inscript_time_since (floor ($days / 30), __("month"))." ".inscript_time_since (floor ($days % 30), "day")." $direction";
    else
      return inscript_time_since ($days, __("day"))." ".inscript_time_since (floor ($mins / 60), __("hour"))." $direction";
  }
  
  return "";
}

// Adapted from Phu Ly's 'Time Of Day' plugin (http://www.ifelse.co.uk)
function inscript_func_time_fuzzy ($args)
{
  if (isset ($args['time']))
  {
    $fuzzy = array
    (
      2 =>  'in the wee hours',
      6 =>  'terribly early in the morning',
      9 =>  'in the early morning',
      10 => 'mid-morning',
      11 => 'just before lunchtime',
      13 => 'around lunchtime',
      14 => 'in the early afternoon',
      16 => 'in the late afternoon',
      18 => 'in the early evening',
      21 => 'at around evening time',
      22 => 'in the late evening',
      23 => 'late at night'
    );
    
    $thetime = strtotime (str_replace ("<br />", "" , $args['time']));
    $hour    = date ('H', $thetime);

    foreach ($fuzzy AS $fhour => $ftext)
    {
      if ($hour <= $fhour)
        return $ftext;
    }
  }
}





function inscript_func_wp_getcalendar ($args)
{
  $daylen = 1;
  if (isset ($args['daylen']))
    $daylen = $args['daylen'];
    
  ob_start ();
  get_calendar ($daylen);
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}


function inscript_func_wp_register ($args)
{
  $before = inscript_helper_get ($args, 'before', '<li>');
  $after = inscript_helper_get ($args, 'after', '</li>');
  
  ob_start ();
  wp_register ($before, $after);
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}

function inscript_func_wp_loginout ($args)
{
  ob_start ();
  wp_loginout ();
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}

function inscript_func_wp_get_archives ($args)
{
  ob_start ();
  wp_get_archives (implode ('&', $args));
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}

function inscript_func_wp_link_pages ($args)
{
  ob_start ();
  wp_link_pages (implode ('&', $args));
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}

function inscript_func_wp_list_pages ($args)
{
  ob_start ();
  wp_list_pages (implode ('&', $args));
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}

function inscript_func_wp_get_recent_posts ($args)
{
  ob_start ();
  wp_get_recent_posts (implode ('&', $args));
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}

function inscript_func_wp_get_links ($args)
{
  ob_start ();
  wp_get_links (implode ('&', $args));
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}

function inscript_func_wp_list_authors ($args)
{
  ob_start ();
  wp_list_authors (implode ('&', $args));
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}

function inscript_func_wp_list_cats ($args)
{
  ob_start ();
  wp_list_cats (implode ('&', $args));
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}


function inscript_func_comments_list ($args)
{
  global $wpdb;

  $count  = 5;
  $limit  = 100;
  $before = "<ul>";
  $after  = "</ul>";
  $format = '<li><a title="Visit the author" href="$authorurl">$author</a> commented on <a title="View the post" href="$post">$title</a><br/><em>$comment</em></li>';

  if (isset ($args['before']))
    $before = $args['before'];
  if (isset ($args['after']))
    $after = $args['after'];
  if (isset ($args['format']))
    $format = $args['format'];
  if (isset ($args['count']))
    $count = (int)$args['count'];
  if (isset ($args['limit']))
    $limit = (int)$args['limit'];

  $clause = "comment_type = '' ORDER BY comment_date";
  if (isset ($args['type']) && $args['type'] == "random")
    $clause = "comment_type = '' ORDER BY RAND()";
  
  $comments = $wpdb->get_results ("SELECT comment_ID, comment_author, comment_author_url, comment_post_ID, LEFT(comment_content, $limit) AS comment_content, post_title FROM $wpdb->comments LEFT JOIN $wpdb->posts ON $wpdb->posts.ID=$wpdb->comments.comment_post_ID WHERE $clause DESC LIMIT $count");
  if ($comments)
  {
    foreach ($comments as $comment)
    {
      $text = $format;
      $text = str_replace ('$authorurl', $comment->comment_author_url,              $text);
      $text = str_replace ('$post',      get_permalink ($comment->comment_post_ID), $text);
      $text = str_replace ('$author',    $comment->comment_author,                  $text);
      $text = str_replace ('$title',     $comment->post_title,                      $text);
      $text = str_replace ('$comment',   str_replace ("<br />", "", strip_tags ($comment->comment_content)),    $text);
      $str .= $text;
    }
  }
  
  return $before.$str.$after;
}

function inscript_func_comments_total ($args)
{
  global $wpdb;
  if (isset ($args['post']))
    return $wpdb->get_var ("SELECT count(*) FROM $wpdb->comments WHERE comment_post_ID = '".$args['post']."'");
  else
    return $wpdb->get_var ("SELECT count(*) FROM $wpdb->comments");
}



function inscript_func_jumpto ($args)
{
  if (isset ($args['list']))
  {
    global $page;

    // list will contain all the jump elements on seperate lines
    $elements = explode ("\r\n", $args['list']);
    $str = '<select id="jumpto" name="jumpto" size="1" onchange="document.location.href=this.options[selectedIndex].value">';
    foreach ($elements AS $item)
    {
      $split = explode ('=', trim ($item));
      if (count ($split) == 2)
      {
        $sel = "";
        if ($page == $split[0])
          $sel = 'selected="selected"';
          
        if ('' == get_settings('permalink_structure'))
          $str .= "<option $sel value=\"".get_permalink ()."&amp;page=$split[0]\">".htmlspecialchars ($split[1])."</option>";
        else
          $str .= "<option $sel value=\"".get_permalink ()."$split[0]\">".htmlspecialchars ($split[1])."</option>";
      }
    }
    
    $str .= '</select>';
    return $str;
  }
  return '';
}

function inscript_func_list ($args)
{
  if (isset ($args['list']))
  {
    // list will contain all the jump elements on seperate lines
    $elements = explode ("\r\n", $args['list']);
    $str = '<ul>';
    foreach ($elements AS $item)
      $str .= "<li>$item</li";
    
    $str .= '</ul>';
    return $str;
  }
  return '';
}

function inscript_func_linklist ($args)
{
  if (isset ($args['list']))
  {
    global $page;

    // list will contain all the jump elements on seperate lines
    $elements = explode ("\r\n", $args['list']);
    $str = '<ul>';
    foreach ($elements AS $item)
    {
      $split = explode ('=', trim ($item));
      if (count ($split) == 2)
          $str .= '<li><a href="'.$split[1].'">'.htmlspecialchars ($split[0]).'</a></li>';
    }
    
    $str .= '</ul>';
    return $str;
  }
  return '';
}

?>
