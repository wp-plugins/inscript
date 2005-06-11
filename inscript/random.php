<?php

function inscript_func_random_file ($args)
{
  if (isset ($args['dir']))
  {
    $dir = opendir (ABSPATH.$args['dir']);
    if ($dir)
    {
      $files = array ();
      while (($item = readdir ($dir)))
      {
        if (is_file ($item))
          $files[] = $item;
      }

      closedir ($dir);
      
      // Now do the random part
      return $files[rand (0, count ($files))];
    }
  }
  return "";
}

function inscript_func_random_word ($args)
{
  if (isset ($args['file']))
    $text = trim (file_get_contents (ABSPATH.$args['file']));
  else if (isset ($args['text']))
    $text = trim ($args['text']);
    
  if ($text)
  {   
    // Choose random point in the text and then find the next whole word
    // This will be faster than parsing the file into an array and using explode
    // plus it works over line boundaries
    $point = rand (0, strlen ($text) - 1);
    $found = false;
    while (true)
    {
      $start = strpos ($text, ' ', $point + 1);
      if ($start == false)
      {
        if ($point == 0)
          return $text;
        $point = 0;
      }

      $end = strpos ($text, ' ', $start + 1);
      if ($end === false)
        return trim (substr ($text, $start));
      else
        return trim (substr ($text, $start, $end - $start));
    }
  }
  return '';
}

function inscript_func_random_line ($args)
{
  if (isset ($args['text']))
    $text = explode ("\n", $args['text']);
  else if (isset ($args['file']))
    $text = file (ABSPATH.$args['file']);

  if ($text)
  {
    while (!($return = trim ($text[rand (0, count ($text))])))
      ;
    return $return;
  }
  return '';
}

?>
