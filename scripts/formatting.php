<?php
function inscript_func_format_highlight ($args)
{
  if (isset ($args['code']))
    return highlight_string ($args['code'], true);
  else if (isset ($args['file']))
    return @highlight_file (ABSPATH.$args['file'], true);
  return "";
}

function inscript_func_format_geshi ($args)
{
  include (dirname (__FILE__).DIRECTORY_SEPARATOR."geshi/geshi.php");
  
  if (isset ($args['code']))
    $code = trim (str_replace ('< ?', '<?', $args['code']));
  else if (isset ($args['file']))
    $code = @file_get_contents (str_replace ("//", "/", ABSPATH.$args['file']));
    
  $lang = "html";
  if (isset ($args['lang']))
    $lang = $args['lang'];
    
  if ($code)
  {
    $geshi = new GeSHi($code, $lang, dirname (__FILE__).DIRECTORY_SEPARATOR.'geshi/geshi/');
   	$geshi->set_header_type (GESHI_HEADER_DIV);
  	
  	if (isset ($args['line']))
  	{
  	  if ($args['line'] == '0')
  	    $geshi->enable_line_numbers (GESHI_NORMAL_LINE_NUMBERS);
  	  else
  	    $geshi->enable_line_numbers (GESHI_FANCY_LINE_NUMBERS, $args['line']);
    }

  	// If a class is given then use that and assume CSS, otherwise embed the style in the output
  	if (isset ($args['class']))
  	{
  	  $geshi->set_overall_class ($args['class']);
  	  $geshi->enable_classes();
    }
  	else
  	{
    	$geshi->set_line_style('font: normal normal 85% \'Courier New\', Courier, monospace; color: #003030;', 'background-color: #eaeaea; font-weight: bold; color: #006060;', true);
    	$geshi->set_code_style('color: #000020;', 'color: #000020;');
    	$geshi->set_link_styles(GESHI_LINK, 'color: #000060;');
    	$geshi->set_link_styles(GESHI_HOVER, 'background-color: #f0f000;');
    	$geshi->set_header_content_style('font-family: Verdana, Arial, sans-serif; color: #808080; font-size: 70%; font-weight: bold; background-color: #f0f0ff; border-bottom: 1px solid #d0d0d0; padding: 2px;');
    	$geshi->set_footer_content_style('font-family: Verdana, Arial, sans-serif; color: #808080; font-size: 70%; font-weight: bold; background-color: #f0f0ff; border-top: 1px solid #d0d0d0; padding: 2px;');
  	  $geshi->set_overall_class ('geshi');
  	}


    if (isset ($args['head']))
  	  $geshi->set_header_content($args['head']);

    if (isset ($args['foot']))
  	  $geshi->set_footer_content($args['foot']);

  	return $geshi->parse_code ();
  }
  return '';
}
?>