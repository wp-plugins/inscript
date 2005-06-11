<?php


function inscript_func_echo ($args)
{
  if (isset ($args['text']))
    return $args['text'];
  return '';
}

function inscript_func_str_toupper ($args)
{
  if (isset ($args['text']))
    return strtoupper ($args['text']);
  return "";
}

function inscript_func_str_tolower ($args)
{
  if (isset ($args['text']))
    return strtolower ($args['text']);
  return "";
}

function inscript_func_str_firstupper ($args)
{
  if (isset ($args['text']))
    return ucfirst ($args['text']);
  return "";
}

function inscript_func_str_allupper ($args)
{
  if (isset ($args['text']))
    return ucwords ($args['text']);
  return "";
}

function inscript_func_str_isbn ($args)
{
  if (isset ($args['isbn']))
    return 'http://www.amazon.com/exec/obidos/ISBN='.$args['isbn'];
  return '';
}

function inscript_func_str_rot13 ($args)
{
  if (isset ($args['text']))
    return str_rot13 ($args['text']);
  return '';
}

function inscript_func_str_shuffle ($args)
{
  if (isset ($args['text']))
    return str_shuffle ($args['text']);
  return '';
}

function inscript_func_str_wordwrap ($args)
{
  if (isset ($args['text']))
    return wordwrap ($args['text'], isset ($args['len']) ? $args['len'] : 75, isset ($args['break']) ? $args['break'] : "\n", isset ($args['force']) ? true : false);
  return '';
}

function inscript_func_str_striptags ($args)
{
  if (isset ($args['text']))
    return strip_tags ($args['text']);
  return '';
}

function inscript_func_str_reverse ($args)
{
  if (isset ($args['text']))
    return strrev ($args['text']);
  return '';
}

function inscript_func_md5 ($args)
{
  if (isset ($args['text']))
    return md5 ($args['text']);
  else if (isset ($args['file']))
    return md5_file (str_replace ("//", "/", ABSPATH.$args['file']));
  return '';
}

function inscript_func_str_obscure_email ($args)
{
  if (isset ($args['email']))
  {
    $text = str_replace ('@', ' at ', $args['email']);
    $text = str_replace ('.', ' dot ', $text);
    return $text;
  }
  return '';
}

function inscript_func_str_leet ($args)
{
  // Please please don't shoot me for creating this...
  if (isset ($args['text']))
  {
    // Put all single-chars first
    $leet = array
    (
      "e" => "3",
      "o" => "0",
      "t" => "7",
      "i" => "1",
      "a" => "4",
      
      "th3" => "teh",
    );
    
    $text = $args['text'];
    foreach ($leet AS $before => $after)
      $text = str_replace ($before, $after, $text);
    
    return $text;
  } 
}

function inscript_func_str_wordcount ($args)
{
  if (isset ($args['text']))
    return str_word_count ($args['text']);
}

function inscript_func_str_repeat ($args)
{
  if (isset ($args['text']))
    return str_repeat ($args['text'], isset ($args['count']) ? $args['count'] : 1);
}

function inscript_func_str_bytes ($args)
{
  if (isset ($args['bytes']) && $args['bytes'])
  {
    if ($args['bytes'] < 1024)
      return $args['bytes']." bytes";
    else if ($args['bytes'] < (1024 * 1024))
      return round (($args['bytes'] / 1024), 2)." KB";
    else if ($args['bytes'] < (1024 * 1024 * 1024))
      return round (($args['bytes'] / (1024 * 1024)), 2). " MB";
    else
      return round (($args['bytes'] / (1024 * 1024 * 1024)), 2)." GB";
  }
  return '0';
}

?>
