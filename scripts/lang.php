<?php

// Copyright (C) 2005 John Godley

function inscript_str_replace_once($needle, $replace, $haystack) {
   $pos = strpos($haystack, $needle);
   if ($pos === false)
     return $haystack;
   return substr_replace($haystack, $replace, $pos, strlen($needle));
}

function inscript_func_lang_pinyin ($args)
{
  if (isset ($args['text']))
  {
    // Split text into words
    $text = $args['text'];

    // First we do the special u character
    $text = str_replace ("v0", "&uuml;", $text);
    $text = str_replace ("v1", "&#470;", $text);
    $text = str_replace ("v2", "&#472;", $text);
    $text = str_replace ("v3", "&#474;", $text);
    $text = str_replace ("v4", "&#476;", $text);
    $words = explode (' ', $text);

    if (!$words)
      $words = array ($text);

    if ($words)
    {
      foreach ($words AS $word)
      {
        preg_match_all ("/([a-zA-Z]*)([0-9])/", $word, $matches);
        if (count ($matches[0]))
        {
          $convert = array
          (
            "1" => array ("a" => "&#257;", "e" => "&#275;", "i" => "&#299;", "o" => "&#333;", "u" => "&#363;"),
            "2" => array ("a" => "&#225;", "e" => "&#233;", "i" => "&#237;", "o" => "&#243;", "u" => "&#250;"),
            "3" => array ("a" => "&#462;", "e" => "&#283;", "i" => "&#464;", "o" => "&#466;", "u" => "&#468;"),
            "4" => array ("a" => "&#224;", "e" => "&#232;", "i" => "&#236;", "o" => "&#242;", "u" => "&#249;"),
          );

          $rep = "";
          for ($x = 0; $x < count ($matches[0]); $x++)
          {
            $tone = $matches[2][$x];

            // If tone is 1st, then look for 1st vowel, otherwise 2nd vowel
            $pos = strcspn ($matches[1][$x], "aeiou");
            if ($tone != 1 && strlen ($matches[1][$x]) > 0)
            {
              if (strlen ($matches[1][$x]) > $pos + 1 && strpos ("aeiou", $matches[1][$x][$pos + 1]))
                $pos = $pos + 1;
            }

            $rep .= substr_replace ($matches[1][$x], $convert[$tone][$matches[1][$x][$pos]], $pos, 1);
          }

          if ($rep != "")
            $text = inscript_str_replace_once ($word, $rep, $text);
        }
      }
    }

    return $text;
  }

  return "";
}

?>