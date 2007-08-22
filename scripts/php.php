<?php

function inscript_func_php_version ($args)
{
  return phpversion ();
}

function inscript_func_php_info ($args)
{
  if (isset ($args['what']))
  {
    if ($args['what'] == "general")
      $what = INFO_GENERAL;
    else if ($args['what'] == "credits")
      $what = INFO_CREDITS;
    else if ($args['what'] == "configuration")
      $what = INFO_CONFIGURATION;
    else if ($args['what'] == "modules")
      $what = INFO_MODULES;
    else if ($args['what'] == "variables")
      $what = INFO_VARIABLES;
    else if ($args['what'] == "license")
      $what = INFO_LICENSE;
  }
  
  $what = INFO_ALL;
	ob_start();
	phpinfo ($what);
	$code = ob_get_contents();
	ob_end_clean ();
	return $code;
}

function inscript_func_php_eval ($args)
{
  if (isset ($args['code']))
  {
    $full = str_replace ("< ?", "<?", $args['code']);     // wp messes with php code
 
    if (substr ($full, 0, 5) == "<?php")
      $full = substr ($full, 5);
      
    if (substr ($full, strlen ($full) - 2, 2) == "?>")
      $full = substr ($full, 0, strlen ($full) - 2);
    ob_start ();
    
    @eval ($full.";");
    $code = ob_get_contents ();
    ob_end_clean ();
    return $code;
  }
  return "";
}

function inscript_func_php_include ($args)
{
  if (isset ($args['file']))
  {
    $pathinfo = pathinfo ($args['file']);
    if ($pathinfo['extension'] == "php" || $pathinfo['extension'] == "phps" ||
        $pathinfo['extension'] == "html" || $pathinfo['extension'] == "htm")
    {
      // Include as PHP
      ob_start ();
      @include (ABSPATH.$args['file']);
      $result = ob_get_contents ();
      ob_end_clean ();
      return $result;
    }
    else
    {
      // Include as text
      if (($text = @file_get_contents ($args['file'])))
        return $text;
      return @file_get_contents (str_replace ('//', '/', ABSPATH.$args['file']));
    }
  }
  return "";
}

function inscript_func_php_virtual ($args)
{
  if (isset ($args['file']))
  {
    ob_start ();
    @virtual (str_replace ('//', '/', ABSPATH.$args['file']));
    $result = ob_get_contents ();
    ob_end_clean ();
    return $result;
  }
  return "";
}


?>