<?php


function inscript_func_head_title ($args)
{
  if (isset ($args['title']))
    return "<title>".$args['title']."</title>";
  return "";
}

function inscript_func_head_style ($args)
{
  if (isset ($args['url']))
    return "<link type=\"text/css\" rel=\"stylesheet\" href=\"".$args['url']."\"/>\r\n";
  return "";
}

function inscript_func_head_import ($args)
{
  if (isset ($args['url']))
  {
    $media = "all";
    if (isset ($args['media']))
      $media = $args['media'];
    
    return "<import type=\"text/css\" media=\"$media\">\r\n  @import url (".$args['url'].";\r\n</style>\r\n";
  }
  return '';
}

function inscript_func_head_keywords ($args)
{
  if (isset ($args['keywords']) && $args['keywords'])
    return '<meta name="keywords" content="'.$args['keywords'].'"/>'."\r\n";
  else if (isset ($args['alt']))
    return '<meta name="keywords" content="'.$args['alt'].'"/>'."\r\n";
  return '';
}

function inscript_func_head_description ($args)
{
  if (isset ($args['desc']) && $args['desc'])
    return '<meta name="description" content="'.$args['desc'].'"/>'."\r\n";
  else if (isset ($args['alt']))
    return '<meta name="description" content="'.$args['alt'].'"/>'."\r\n";
  return '';
}

function inscript_func_head_meta ($args)
{
  if (isset ($args['name']) && isset ($args['content']))
  {
    $str = '<meta name="'.$args['name'].'" content="'.$args['content'].'"';
    if (isset ($args['equiv']))
      $str .= ' equiv="'.$args['equiv'].'"';
    $str .= "/>\r\n";
    return $str;
  }
  return '';
}

?>
