<?php


function inscript_func_blog_name ($args)
{
  return get_bloginfo ('name');
}

function inscript_func_blog_description ($args)
{
  return get_bloginfo ('description');
}

function inscript_func_blog_url ($args)
{
  return get_bloginfo ('url');
}

function inscript_func_blog_admin ($args)
{
  return get_bloginfo ('admin_email');
}

function inscript_func_blog_version ($args)
{
  return get_bloginfo ('version');
}

function inscript_func_blog_template_url ($args)
{
  return get_bloginfo ('template_url');
}

function inscript_func_blog_template_directory ($args)
{
  return get_bloginfo ('template_directory');
}

function inscript_func_blog_stylesheet_url ($args)
{
  return get_bloginfo ('stylesheet_url');
}

function inscript_func_blog_stylesheet_directory ($args)
{
  return get_bloginfo ('stylesheet_directory');
}

function inscript_func_blog_wpurl ($args)
{
  return get_bloginfo ('wpurl');
}

?>
