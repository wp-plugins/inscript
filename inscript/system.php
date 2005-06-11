<?php


function inscript_func_system ($args)
{
  return php_uname ();
}

function inscript_func_system_os ($args)
{
  return php_uname ('s');
}

function inscript_func_system_hostname ($args)
{
  return php_uname ('n');
}

function inscript_func_system_release ($args)
{
  return php_uname ('r');
}

function inscript_func_system_version ($args)
{
  return php_uname ('v');
}

function inscript_func_system_machine ($args)
{
  return php_uname ('m');
}

function inscript_func_system_mysql ($args)
{
  return mysql_get_server_info ();
}

function inscript_func_system_server ($args)
{
  return $_SERVER['SERVER_SOFTWARE'];
}

function inscript_func_system_ip ($args)
{
  return $_SERVER['SERVER_ADDR'];
}

function inscript_func_system_port ($args)
{
  return $_SERVER['SERVER_PORT'];
}

function inscript_func_system_root ($args)
{
  return $_SERVER['DOCUMENT_ROOT'];
}

function inscript_func_system_date ($args)
{
  if (isset ($args['format']))
    return date ($args['format']);
  return current_time ('mysql');
}



function inscript_func_file_size ($args)
{
  if (isset ($args['file']))
    return @filesize (ABSPATH.$args['file']);
  return 0;
}

function inscript_func_file_created ($args)
{
  if (isset ($args['file']))
  {
    $format = get_option ('date_format')." ".get_option ('time_format');
    if (isset ($args['format']))
      $format = $args['format'];
    return date ($format, filectime (ABSPATH.$args['file']));
  }
}

function inscript_func_file_modified ($args)
{
  if (isset ($args['file']))
  {
    $format = get_option ('date_format')." ".get_option ('time_format');
    if (isset ($args['format']))
      $format = $args['format'];
    return date ($format, filemtime (ABSPATH.$args['file']));
  }
}

?>