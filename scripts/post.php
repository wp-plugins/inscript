<?php

function inscript_post_get ($args)
{
  $id = null;

  if (isset ($args['url']))
    $id = url_to_postid ($args['url']);
	else if (isset ($args['random']))
	{
		global $wpdb;
		$id = $wpdb->get_var ("SELECT ID FROM {$wpdb->posts} ORDER BY RAND() LIMIT 0,1");
	}
  else if (isset ($args['post']))
  {
    if ($args['post'] == "all")
    {
      global $posts;
      $id = $posts;
    }
    else
      $id = $args['post'];
  }
  else
  {
    global $post;
    if ($post->ID)
      $id = $post->ID;

    if (is_single () || is_page ())
    {
      global $posts;
      $id = $posts[0]->ID;
    }
  }

  if (!is_array ($id))
    return array (get_post ($id));
  return $id;
}

function inscript_post_helper ($field, $args)
{
  $join   = " ";
  if (isset ($args['join']))
    $join = $args['join'];

  $mypost = inscript_post_get ($args);

  for ($x = 0; $x < count ($mypost); $x++)
  {
    $str .= $mypost[$x]->$field;
    if ($x + 1 != count ($mypost))
      $str .= $join;
  }

  return $str;
}

function inscript_func_post_page ($args)
{
  global $page;
  if ($page)
    return $page;
  return 0;
}

function inscript_func_post_custom ($args)
{
  if (isset ($args['key']))
  {
    $mypost = inscript_post_get ($args);
    $join   = " ";
    if (isset ($args['join']))
      $join = $args['join'];

    $str = "";
    for ($x = 0; $x < count ($mypost); $x++)
    {
      $meta = get_post_meta ($mypost[$x]->ID, $args['key']);
      if (count ($meta) > 0)
      {
        $str .= implode (isset ($args['merge']) ? $args['merge'] : "", $meta);
        if ($x + 1 != count ($mypost))
          $str .= $join;
      }
    }
    return $str;
  }
  return;
}

function inscript_func_post_id ($args)
{
  return inscript_post_helper ("ID", $args);
}

function inscript_func_post_date ($args)
{
  $mypost = inscript_post_get ($args);
  $join   = " ";
  if (isset ($args['join']))
    $join = $args['join'];

  $str = "";
  for ($x = 0; $x < count ($mypost); $x++)
  {
    if (isset ($args['format']))
      $str .= date ($args['format'], strtotime ($mypost[$x]->post_date));
    else
      $str .= $mypost[$x]->post_date;

    if ($x + 1 != count ($mypost))
      $str .= $join;
  }
  return $str;
}

function inscript_func_post_modified ($args)
{
  $mypost = inscript_post_get ($args);
  $join   = " ";
  if (isset ($args['join']))
    $join = $args['join'];

  $str = "";
  for ($x = 0; $x < count ($mypost); $x++)
  {
    if (isset ($args['format']))
      $str .= date ($args['format'], strtotime ($mypost[$x]->post_modified));
    else
      $str .= $mypost[$x]->post_modified;

    if ($x + 1 != count ($mypost))
      $str .= $join;
  }
  return $str;
}

function inscript_func_post_content ($args)
{
  return inscript_post_helper ("content", $args);
}

function inscript_func_post_title ($args)
{
  return inscript_post_helper ("post_title", $args);
}

function inscript_func_post_excerpt ($args)
{
  return inscript_post_helper ("post_excerpt", $args);
}

function inscript_func_post_slug ($args)
{
  return inscript_post_helper ("post_name", $args);
}

function inscript_func_post_guid ($args)
{
  return inscript_post_helper ("guide", $args);
}

function inscript_func_post_categories ($args)
{
  $mypost = inscript_post_get ($args);
  $join   = " ";
  if (isset ($args['join']))
    $join = $args['join'];

  $format = '$name';
  if (isset ($args['format']))
    $format = $args['format'];

  $str = "";
  for ($x = 0; $x < count ($mypost); $x++)
  {
    $cats = get_the_category ($mypost[$x]->ID);
    for ($y = 0; $y < count ($cats); $y++)
    {
      $text = str_replace ('$name', $cats[$y]->cat_name, $format);
      $text = str_replace ('$id',   $cats[$y]->cat_ID, $text);
      $text = str_replace ('$desc', $cats[$y]->category_description, $text);
      $text = str_replace ('$nice', $cats[$y]->category_nicename, $text);
      $str .= $text;

      if ($y + 1 < count ($cats))
      {
        if (isset ($args['merge']))
          $str .= $args['merge'];
        else
          $str .= ", ";
      }
    }

    if ($x + 1 != count ($mypost))
      $str .= $join;
  }
  return $str;
}

function inscript_func_post_total ($args)
{
  global $wpdb;
  return $wpdb->get_var ("SELECT count(*) FROM $wpdb->posts");
}

function inscript_func_post_c2c_recent ($args)
{
  $num      = inscript_helper_get ($args, 'num_posts', 5);
  $format   = inscript_helper_get ($args, 'format', '<li>%post_date%: %post_URL%</li>');
  $cat      = inscript_helper_get ($args, 'categories', '');
  $orderby  = inscript_helper_get ($args, 'orderby', 'date');
  $order    = inscript_helper_get ($args, 'order',  'DESC');
  $offset   = inscript_helper_get ($args, 'offset', 0);
  $date     = inscript_helper_get ($args, 'date_format', 'm/d/Y');
  $authors  = inscript_helper_get ($args, 'authors', '');
  $password = inscript_helper_get ($args, 'include_passworded_posts', false);

  ob_start ();
  c2c_get_recent_posts ($num, $format, $cat, $orderby, $order, $offset, $date, $authors, $password);
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}

function inscript_func_post_c2c_random ($args)
{
  $num      = inscript_helper_get ($args, 'num_posts', 5);
  $format   = inscript_helper_get ($args, 'format', '<li>%post_date%: %post_URL%</li>');
  $cat      = inscript_helper_get ($args, 'categories', '');
  $order    = inscript_helper_get ($args, 'order',  'DESC');
  $offset   = inscript_helper_get ($args, 'offset', 0);
  $date     = inscript_helper_get ($args, 'date_format', 'm/d/Y');
  $authors  = inscript_helper_get ($args, 'authors', '');
  $password = inscript_helper_get ($args, 'include_passworded_posts', false);

  ob_start ();
  c2c_get_random_posts ($num, $format, $cat, $orderby, $order, $offset, $date, $authors, $password);
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}

function inscript_func_post_c2c_recently_commented ($args)
{
  $num      = inscript_helper_get ($args, 'num_posts', 5);
  $format   = inscript_helper_get ($args, 'format', '<li>%post_date%: %post_URL%</li>');
  $cat      = inscript_helper_get ($args, 'categories', '');
  $order    = inscript_helper_get ($args, 'order',  'DESC');
  $offset   = inscript_helper_get ($args, 'offset', 0);
  $date     = inscript_helper_get ($args, 'date_format', 'm/d/Y');
  $authors  = inscript_helper_get ($args, 'authors', '');
  $password = inscript_helper_get ($args, 'include_passworded_posts', false);

  ob_start ();
  c2c_get_recently_commented ($num, $format, $cat, $orderby, $order, $offset, $date, $authors, $password);
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}

function inscript_func_post_c2c_recently_modified ($args)
{
  $num      = inscript_helper_get ($args, 'num_posts', 5);
  $format   = inscript_helper_get ($args, 'format', '<li>%post_date%: %post_URL%</li>');
  $cat      = inscript_helper_get ($args, 'categories', '');
  $order    = inscript_helper_get ($args, 'order',  'DESC');
  $offset   = inscript_helper_get ($args, 'offset', 0);
  $date     = inscript_helper_get ($args, 'date_format', 'm/d/Y');
  $authors  = inscript_helper_get ($args, 'authors', '');
  $password = inscript_helper_get ($args, 'include_passworded_posts', false);

  ob_start ();
  c2c_get_recently_modified ($num, $format, $cat, $orderby, $order, $offset, $date, $authors, $password);
  $result = ob_get_contents ();
  ob_end_clean ();
  return $result;
}
?>
