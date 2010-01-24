<?php
/*
Plugin Name: InScript
Plugin URI: http://urbangiraffe.com/plugins/inscript/
Description: Extensible scripting framework.  Allows embedding of custom functions inside posts and any other piece of text.  Functions can manipulate and transform the text in a variety of ways, including the removal of text from texturizing.  Read the <a href="../wp-content/plugins/inscript/inscript.pdf">guide</a> or consult the <a href="http://www.urbangiraffe.com">UrbanGiraffe</a> website for full details.
Author: John Godley
Author URI: http://urbangiraffe.com
Version: 1.7.4

1.7.2 - Remove dependency on auto.php.  Fix script path
1.7.3 - Add a random argument to post functions
1.7.4 - Replace get_alloptions with wp_load_alloptions
*/

/*
 * This code (and all code contained within the zip, except Geshi) is released under LGPL.
 * That is the Lesser GNU Public License.  This is similar to the GPL, but allows you to use
 * the code in a commercial environment without requiring everything else to be GLPed.  Full
 * details can be found at http://www.gnu.org/copyleft/lesser.html
 *
 * If you do find the plugin useful then drop by http://www.urbangiraffe.com and say a few nice words.
 * If you find it really useful and/or want to use it commercially then consider leaving a tip at the
 * same website to say thank-you.  I'm sure the WordPress guys would appreciate a nod too.
 *
 * Remember: A goat with two legs is not a goat
 *
 */

// ====================================================================================
// Some globals.  Change these if you really want to
// ====================================================================================

$inscript_global_body     = null;    // Don't touch me otherwise I'll explode

// Prefixes for settings
$inscript_option_name     = "inscript_option_";
$inscript_restrict_name   = "inscript_restrict_";

// Default filters
$inscript_default_filters = array
(
  "the_content" => "Post content",
  "the_excerpt" => "Post excerpt"
);

// Default actions
$inscript_default_actions = array
(
  "wp_head"   => "Header data",
  "wp_footer" => "Footer data",
  "wp_meta"   => "Meta data"
);

// Used for caching
$inscript_cache = array ();
$local_disable  = false;    // Don't both changing this




// ====================================================================================
// If we are on the admin plugin page, then display the options, otherwise do the
// plugin
// ====================================================================================

if (is_plugin_page())
  inscript_options ();
else
{
  // ====================================================================================
  // Little function to escape strings, taking magic_quotes into consideration
  // ====================================================================================

	function escape_string ($str)
	{
    if (get_magic_quotes_gpc())
      $str = stripslashes ($str);
    return mysql_real_escape_string ($str);
	}

  // ====================================================================================
  // Now we have some classes.  The classes are used as namespaces, so as not to create
  // lots of small functions.  They should not be instantied as objects.
  //
  // This first class handles InScript variables
  // ====================================================================================

  class inscript_var
  {
    // Get variable value, given name
    function get ($name)
    {
      global $inscript_option_name;
      $val = get_option ($inscript_option_name.$name);
      if ($val)
        return $val;
      return "-- $name not defined --";
    }


    // See if the variable exists, given a name
    function exists ($name)
    {
      global $inscript_option_name;
      if (get_option ($inscript_option_name.$name))
        return true;
      return false;
    }


    // Add a new variable
    function add ($name, $value)
    {
      global $inscript_option_name;

      // Only add if it doesnt exist
      if (!inscript_var::exists ($name))
        add_option ($inscript_option_name.inscript_var::sanitise_name ($name), inscript_var::sanitise_value ($value));
    }


    // Update a variable, given its ID
    function update ($id, $name, $value)
    {
      $name = inscript_var::sanitise_name ($name);
      $value = inscript_var::sanitise_value ($value);

      global $inscript_option_name, $wpdb, $cache_settings;
    	$wpdb->query("UPDATE $wpdb->options SET option_value = '$value', option_name = '$inscript_option_name$name' WHERE option_id = '$id'");
    	$cache_settings = wp_load_alloptions(); // Re cache settings
    }


    // Remove a variable, given its ID
    function remove ($id)
    {
      global $wpdb, $cache_settings;
      $wpdb->query("DELETE FROM $wpdb->options WHERE option_id = '$id'");
    	$cache_settings = wp_load_alloptions(); // Re cache settings
    }


    // Return list of all variables
    function listall ()
    {
      global $wpdb, $inscript_option_name;
      $vars = $wpdb->get_results ("SELECT option_id AS id, option_name AS name,option_value AS value FROM $wpdb->options WHERE left(option_name,".strlen ($inscript_option_name).") = '$inscript_option_name'");

      // It seems the substr is not available to all MySQL versions... damn
      for ($x = 0; $x < count ($vars); $x++)
        $vars[$x]->name = substr ($vars[$x]->name, strlen ($inscript_option_name), strlen ($vars[$x]->name) - strlen ($inscript_option_name));
      return $vars;
    }


    // Sanitise a variable name
    function sanitise_name ($name)
    {
      return strip_tags (str_replace (' ', '', $name));
    }


    // Sanitise a variable value
    function sanitise_value ($value)
    {
      return escape_string ($value);
    }
  }


  // ====================================================================================
  // Handle all function restrictions
  // ====================================================================================

  class inscript_restrict
  {
    // Returns all function restrictions
    function get ()
    {
      global $wpdb, $inscript_restrict_name;
      $vars = $wpdb->get_results ("SELECT option_id AS id, option_name AS name,option_value AS value FROM $wpdb->options WHERE left(option_name,".strlen ($inscript_restrict_name).") = '$inscript_restrict_name'");

      // It seems the substr is not available to all MySQL versions... damn
      for ($x = 0; $x < count ($vars); $x++)
        $vars[$x]->name = substr ($vars[$x]->name, strlen ($inscript_restrict_name), strlen ($vars[$x]->name) - strlen ($inscript_restrict_name));
      return $vars;
    }

    // Deletes a given function restriction (name is function)
    function delete ($name)
    {
      global $inscript_restrict_name;
      delete_option ($inscript_restrict_name.$name);
    }

    // Restrict a function to the given level
    function add ($func, $level)
    {
      global $inscript_restrict_name;
      if ($level == "Default")
        delete_option ($inscript_restrict_name.$func);
      else if (get_option ($inscript_restrict_name.$func) == false)
        add_option ($inscript_restrict_name.$func, $level);
      else
        update_option ($inscript_restrict_name.$func, $level);
    }

    // Set the default restriction
    function set_default ($level)
    {
      if (get_option ('inscript_default_level') == false)
        add_option ('inscript_default_level', $level);
      else
        update_option ('inscript_default_level', $level);
    }

    // Get the default restriction
    function get_default ()
    {
      return get_option ('inscript_default_level');
    }

    // Check if the given author can access a function
    function is_allowed ($func, $authorlevel)
    {
      global $inscript_restrict_name;
      if (($level = get_option ($inscript_restrict_name.$func)) == false)
      {
        // Default level
      }

      if ($authorlevel >= $level)
        return true;
      return false;
    }
  }


  // ====================================================================================
  // Handle global keyword settings
  // ====================================================================================



  class inscript_global
  {
    // Return all globals
    function get_list ()
    {
      global $wpdb;
    	$vars = $wpdb->get_results ("SELECT meta_id AS id, meta_key AS name, meta_value AS value FROM $wpdb->postmeta WHERE post_id = 0 AND left(meta_key,8) = 'inscript'");

      // It seems the substr is not available to all MySQL versions... damn
      for ($x = 0; $x < count ($vars); $x++)
        $vars[$x]->name = substr ($vars[$x]->name, 9, strlen ($vars[$x]->name) - 9);
      return $vars;
    }

    // Update a given global ID
    function update ($id, $name, $value)
    {
      global $wpdb;
      $name = escape_string ($name);
      $value = escape_string ($value);
		  $wpdb->query ("UPDATE $wpdb->postmeta SET meta_key = 'inscript_$name', meta_value = '$value' WHERE meta_id = '$id'");
    }

    // Delete a global
    function remove ($id)
    {
      global $wpdb;
      $wpdb->query ("DELETE FROM $wpdb->postmeta WHERE meta_id = '$id'");
    }

    // Get a global, given its name
    function get ($name)
    {
      global $wpdb;

    	$list = $wpdb->get_results ("SELECT meta_value AS value FROM $wpdb->postmeta WHERE post_id = 0 AND meta_key = 'inscript_$name'");
    	$meta = array ();
    	if (count ($list) > 0)
    	{
        foreach ($list AS $item)
          $meta[] .= $item->value;
      }
      return $meta;
    }
  }


  // Re-define ctype functions if they don't exist.  Thanks to Jeena Paradies for these functions.

  if (!function_exists('ctype_alpha'))
  {
    function ctype_alpha ($string)
    {
      return preg_match('/^[a-z]*$/i', $string);
    }
  }

  if (!function_exists('ctype_alnum'))
  {
    function ctype_alnum($string)
    {
      return preg_match('/^[a-z0-9]*$/i', $string);
    }
  }


  // ====================================================================================
  // Handle hooks into filters and actions
  // ====================================================================================

  class inscript_hook
  {
    // Determine if the given name is a valid hook
    function valid_hook ($name)
    {
      if (strlen ($name) > 0 && ctype_alpha ($name[0]))
      {
        for ($x = 0; $x < strlen ($name); $x++)
        {
          if (!ctype_alnum ($name[$x]) && $name[$x] != '_')
            return false;
        }
        return true;
      }
      return false;
    }


    // This does all the donkey work for hooks.  It takes two arrays of filter and actions
    // and stores them in the options, and then creates the auto.php file with the functions
    function set_hooks ($filters, $actions)
    {
      $func      = "<?php\r\n// Do not edit this file - it is automatically generated by InScript\r\n\r\n";
      $filterstr = "";
      $actionstr = "";

      // Process filters
      if (count ($filters) > 0)
      {
        foreach ($filters AS $key => $hook)
        {
          if (inscript_hook::valid_hook ($hook))
          {
            $func .= "function inscript_hook_$hook (\$text)\r\n{\r\n  return inscript_filter (\$text, '$hook');\r\n}\r\n\r\nadd_filter ('$hook', 'inscript_hook_$hook', 1);\r\n\r\n";
            $func .= "function inscript_hookend_$hook (\$text)\r\n{\r\n  return inscript_filter_cache (\$text);\r\n}\r\n\r\nadd_filter ('$hook', 'inscript_hookend_$hook', 10);\r\n\r\n";
          }
          else
            unset ($filters[$key]);
        }

        $filters = array_values ($filters);
      }

      // Process actions
      if (count ($actions) > 0)
      {
        foreach ($actions AS $key => $hook)
        {
          if (inscript_hook::valid_hook ($hook))
            $func .= "function inscript_hook_$hook (\$text)\r\n{\r\n  echo inscript_filter (\$text, '$hook');\r\n}\r\n\r\nadd_action ('$hook', 'inscript_hook_$hook', 1);\r\n\r\n";
          else
            unset ($actions[$key]);
        }

        $actions = array_values ($actions);
      }

      $func .= "?>";

      // Update options
      if (get_option ('inscript_filters') === false)
        add_option ('inscript_filters', count ($filters) > 0 ? implode (' ', $filters) : '');
      else
        update_option ('inscript_filters', count ($filters) > 0 ? implode (' ', $filters) : '');

      if (get_option ('inscript_actions') === false)
        add_option ('inscript_actions', count ($actions) > 0 ? implode (' ', $actions) : '');
      else
        update_option ('inscript_actions', count ($actions) > 0 ? implode (' ', $actions) : '');

      // Create the include file
			update_option ('inscript_auto', $func);
			return true;
    }


    // Return list of all filters as space-separated string
    function get_filters ()
    {
      return get_option ('inscript_filters');
    }


    // Return list of all filters as array
    function get_filters_array ()
    {
      return explode (' ', inscript_hook::get_filters ());
    }


    // Return list of actions as space-separated stirng
    function get_actions ()
    {
      return get_option ('inscript_actions');
    }


    // Return true if the given filter is hooked
    function is_hooked_filter ($func)
    {
      if (strpos (get_option ('inscript_filters'), $func) === false)
        return false;
      return true;
    }


    // Return true if the given action is hooked
    function is_hooked_action ($func)
    {
      if (strpos (get_option ('inscript_actions'), $func) === false)
        return false;
      return true;
    }
  }


  // ====================================================================================
  // General InScript functions
  // ====================================================================================

  class inscript
  {
    // Load all the scripts
    function load_scripts ()
    {
      $path = dirname (__FILE__).DIRECTORY_SEPARATOR."scripts".DIRECTORY_SEPARATOR;
      if (($dir = @opendir ($path)))
      {
        while ($file = readdir ($dir))
        {
          if (is_file ($path.$file) && $file != "auto.php" && substr ($file, -3, 3) == "php")
            include ($path.$file);
        }

        closedir ($dir);
      }
    }


    // Return array of all function names, taken from the scripts
    function get_function_names ()
    {
      $names = array ();
      $path = dirname (__FILE__).DIRECTORY_SEPARATOR."inscript".DIRECTORY_SEPARATOR;
      if (($dir = @opendir ($path)))
      {
        while ($file = readdir ($dir))
        {
          if (is_file ($path.$file))
          {
            $contents = @file_get_contents ($path.$file);
            preg_match_all ("/function[\s]*inscript_func_(\w+)/", $contents, $matches);
            $names = array_merge ($names, $matches[1]);
          }
        }

        closedir ($dir);
      }

      sort ($names);
      return $names;
    }


    // Change the wpautop setting
    function set_autop ($val)
    {
      update_option ('inscript_autop', $val ? "true" : "false");
    }


    // Change the wptexturize setting
    function set_texturize ($val)
    {
      update_option ('inscript_texturize', $val ? "true" : "false");
    }


    // Change the InScript disabled setting
    function set_disable ($val)
    {
      update_option ('inscript_disable', $val ? "true" : "false");
    }


    // Return true if InScript is disabled
    function is_disabled ()
    {
      global $local_disable;
      if ($local_disable == true)
        return true;

      if (($val = get_option ('inscript_disable')) == false)
      {
        add_option ('inscript_disable', "false");
        return false;
      }

      return $val == "true" ? true : false;
    }


    // Return true if wpautop is disabled
    function is_autopdisabled ()
    {
      if (get_option ('inscript_autop') == 'true')
        return true;
      return false;
    }

    // Return true if wptexturize is disabled
    function is_texturizedisabled ()
    {
      if (get_option ('inscript_texturize') == 'true')
        return true;
      return false;
    }
  }


  // ====================================================================================
  // Called by preg_callback to replace an InScript variable with the value
  // ====================================================================================

  function replace_var ($matches)
  {
    return inscript_var::get ($matches[1]);
  }


  // ====================================================================================
  // Helper function for scripts
  // ====================================================================================

  function inscript_helper_get ($args, $key, $default)
  {
    if (isset ($args[$key]))
      return $args[$key];
    return $default;
  }

  // ====================================================================================
  // Called by preg_callback to replace an InScript function with the results of the
  // function.  This is the bulk of the work!
  // ====================================================================================

  function run_function ($matches)
  {
    // $matches[1] = function
    // $matches[2] = arguments
    // $matches[3] = optional 'block'
    $func = "inscript_func_".$matches[1];

    if (function_exists ($func))
    {
      global $inscript_global_body;

      // Parse the arguments
      preg_match_all ("/(\w+)=\"(.*?)\"/", $matches[2], $params);
      preg_match_all ("/\[(\w+)=(.*?)\]/s", $matches[2], $params2);

      $params[1] = array_merge ($params[1], $params2[1]);
      $params[2] = array_merge ($params[2], $params2[2]);

      // Convert into an array of name => value
      // $params[1] = param names
      // $params[2] = param values
      $args = array ();
      for ($x = 0; $x < count ($params[1]); $x++)
      {
        if (isset ($matches[3]))
          $args[$params[1][$x]] = str_replace ('%1', $matches[3], $params[2][$x]);
        else
          $args[$params[1][$x]] = $params[2][$x];
        $args[$params[1][$x]] = str_replace ('%2', $inscript_global_body, $args[$params[1][$x]]);
      }

      // If we are allowed then execute the func
      if (inscript_restrict::is_allowed ($matches[1], 3))
      {
        $result = $func ($args);      // This is the important bit - finally!

        // Do entity encoding
        if (isset ($args['ent']) && $args['ent'] == "on")
          $result = htmlentities ($result);

        // Check if we should stop wordpress messing with the text
        if (isset ($args['wp']))
        {
          global $inscript_cache;
          $inscript_cache[] = array ($result, $args['wp']);
          return "!!__cache_".(count ($inscript_cache) - 1)."!!";
        }

        return $result;
      }

      return "-- user not allowed ".$matches[1]." --";
    }

    return "-- no func ".$matches[1]." --";
  }


  // ====================================================================================
  // Called by an end hook to replace the cache tags with the cached data
  // ====================================================================================

  function inscript_filter_cache ($text)
  {
    // Cache replacement
    global $inscript_cache;

    // Replace all the cached elements
    for ($x = 0; $x < count ($inscript_cache); $x++)
    {
      if ($inscript_cache[$x][1] == 'p')
        $replace = wpautop ($inscript_cache[$x][0]);
      else if ($inscript_cache[$x][1] == 'texturize')
        $replace = wptexturize ($inscript_cache[$x][0]);
      else if ($inscript_cache[$x][1] == 'full')
        $replace = wpautop (wptexturize ($inscript_cache[$x][0]));
      else
        $replace = $inscript_cache[$x][0];
      $text = str_replace ('!!__cache_'.$x.'!!', $replace, $text);
    }

    empty ($inscript_cache);
    return $text;
  }


  // ====================================================================================
  // Runs all the InScript regex stuff over some text
  // ====================================================================================

  function run_patterns ($text)
  {
    if ($text)
    {
      if (inscript::is_disabled ())
      {
        // Quietly remove inscript markup
        $text = preg_replace ("/!!(\w+)!!/",                                                 "", $text);
        $text = preg_replace ("/%%[\s]*(\w*)[\s]*(.*?)%%/s",                                 "", $text);
        $text = preg_replace ("/<inscript[\s]+func=\"(\w*)\"[\s]*(.*?)\/>/s",                "", $text);
        $text = preg_replace ("/<inscript[\s]+func=\"(\w+)\"[\s]+(.*?)>(.*?)<\/inscript>/s", "", $text);
      }
      else
      {
        // Do the inscript markup
        $text = preg_replace_callback ("/!!(\w+)!!/",                                                 "replace_var",  $text);
        $text = preg_replace_callback ("/%%[\s]*(\w*)[\s]*(.*?)%%/s",                                 "run_function", $text);
        $text = preg_replace_callback ("/<inscript[\s]+func=\"(\w*)\"[\s]*(.*?)\/>/",                 "run_function", $text);
        $text = preg_replace_callback ("/<inscript[\s]+func=\"(\w+)\"[\s]+(.*?)>(.*?)<\/inscript>/s", "run_function", $text);
      }
    }

    return $text;
  }


  // ====================================================================================
  // The entry point for filters and actions
  // ====================================================================================

  function inscript_filter ($text, $hook)
  {
    global $inscript_global_body;

    // Apply all patterns
    if (inscript::is_disabled () == false)
    {
      // Now perform any meta-keywords
      // First we get the meta keyword value and tag it onto the end
      global $post, $posts;
      if ($post->ID)
        $id = $post->ID;
      else if (count ($posts) == 1 && $posts[0]->ID)
        $id = $posts[0]->ID;

      $meta = array ();
      if ($id)
      {
        if (get_post_meta ($id, "inscript_disable") || (is_archive () && $hook == "the_time"))
          return $text;
        $meta = (array)get_post_meta ($id, "inscript_$hook");
      }

      // Global keywords
      $meta = array_merge ($meta, inscript_global::get ($hook));
    }

    // Run patterns in the text itself
    $inscript_global_body = run_patterns ($text);

    // Now see if we need to apply custom fields to the text as a whole
    if (inscript::is_disabled () == false && count ($meta) > 0)
    {
      foreach ($meta AS $item)
      {
        if (strstr ($item, '%2') !== FALSE)
          $metastr = run_patterns ($item);
        else
          $metastr .= run_patterns ($item);
        $inscript_global_body = $metastr;
      }

      $inscript_global_body = $metastr;
    }

    return $inscript_global_body;
  }


  // ====================================================================================
  // The basic plugin functions:
  //   - load the scripts
  //   - load the auto-generated file
  //   - disable wpautop/wptexturize
  // ====================================================================================

  // Include all script files
  inscript::load_scripts ();

  // Include the auto-generated filters
  $auto = get_option ('inscript_auto');
	if ($auto)
		eval ("?>".$auto);
		
  // Disable wpautop
  if (inscript::is_autopdisabled ())
  {
    $filters = inscript_hook::get_filters_array ();
    if ($filters)
    {
      foreach ($filters AS $filter)
        remove_filter ($filter, 'wpautop');
    }
  }

  // Disable wptexturize
  if (inscript::is_texturizedisabled ())
  {
    $filters = inscript_hook::get_filters_array ();
    if ($filters)
    {
      foreach ($filters AS $filter)
        remove_filter ($filter, 'wptexturize');
    }
  }
}

if (!function_exists ('inscript_disable'))
{
  function inscript_disable ()
  {
    global $local_disable;
    $local_disable = true;
  }

  function inscript_enable ()
  {
    global $local_disable;
    $local_disable = false;
  }
}

// ====================================================================================
// Define the functions for the InScript options screen
// ====================================================================================

if (!function_exists ('inscript_options'))
{
  // Cheeky little helper function
  function is_checked ($bool)
  {
    if ($bool)
      echo ' checked="checked"';
  }


  // Function to add InScript into the options page
  function inscript_add_options_page()
  {
	  add_options_page ('InScript options', 'InScript', 'edit_plugins', basename(__FILE__), 'inscript_options');
	}

  add_action('admin_menu', 'inscript_add_options_page');


  // ====================================================================================
  // Displays the InScript options page
  // ====================================================================================

  function inscript_options ()
  {
    // if (isset ($_GET['error']))
    //   echo '<div class="updated" style="text-align: center"><p><strong>WARNING!</strong></p><p>Could not update the <code>auto.php</code> file - check write permissions on the <code>inscript</code> directory</p></div>';
    ?>
  	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    <div class="wrap">
      <h2>InScript</h2>
    	  <fieldset class="options">
    	    <legend>General</legend>
    	    <p><input type="checkbox" name="disable" id="disable" value="<?php echo inscript::is_disabled ();?>"<?php is_checked(inscript::is_disabled ()); ?>/> <label for="disable">Globally disable InScript (plugin is enabled, but InScript tags are quietly removed)</label></p>
    	    <p><input type="checkbox" name="texturize" id="texturize"<?php is_checked(inscript::is_texturizedisabled ()) ?>> <label for="texturize">Disable <code>wptexturize</code> in all hooked filters (automatic WordPress text enhancement)</label></p>
    	    <p><input type="checkbox" name="autop" id="autop"<?php is_checked(inscript::is_autopdisabled ()) ?>> <label for="autop">Disable <code>wpautop</code> in all hooked filters (automatic WordPress paragraphing)</label></p>
    	  </fieldset>
        <fieldset class="options">
          <legend>Variables</legend>
          <p>Variables are inserted using <code>!!variablename!!</code>, and will be replaced with the data in any hooked filter/action.  A variable can contain data and InScript tags.</p>
          <input type="hidden" name="inscript" value="on"/>
          <table cellspacing="2" cellpadding="2">
            <tr><th>Name</th><th align="left">Value</th><th>Delete</th>
          <?php
            // List all the InScript variables as table elements
            $vars = inscript_var::listall ();
            if ($vars)
            {
              foreach ($vars AS $variable)
              {
                $rows = substr_count ($variable->value, "\n") + 1;
                echo '<tr><td valign="top"><input type="text" name="varname_'.$variable->id.'" value="'.$variable->name.'"/></td>';
                echo '<td><textarea wrap="off" cols="40" rows="'.$rows.'" name="var_'.$variable->id.'">'.htmlentities ($variable->value).'</textarea></th>';
                echo '<th valign="top"><input type="checkbox" name="deletevar_'.$variable->id.'"/></td></tr>';
              }
            }
            else
              echo '<tr><td colspan="3"><em>No variables defined</em></td></tr>'
          ?>
        </table>
        <br/>
        <p><strong>Add new variable:</strong></p>
        <table>
          <tr><td>Name:</td><td><input type="text" name="newvar_name"/></td></tr>
          <tr><td>Value:</td><td><textarea wrap="off" cols="70" name="newvar_value"></textarea></td></tr>
        </table>
        </fieldset>
        <fieldset class="options">
          <legend>User level restrictions</legend>
          <p>You can restrict function access to authors of a certain level.  If no specific restriction is give, the default level is set to
          <select name="defaultlevel"><option name="0"<?php if (inscript_restrict::get_default () == 0) echo ' checked="checked"'?>>no restrictions</option>
          <?php
            // Create list of 10 user-level restrictions
            for ($x = 1; $x <= 10; $x++)
            {
              echo '<option name="'.$x.'"';
              if (inscript_restrict::get_default () == $x)
                echo ' selected="selected"';
              echo '>'.$x.'</option>';
            }
              ?>
            </select>
          </p>
          <table style="border: 1px solid #ccc">
            <tr><th>Function</th><th>Level</th><th>Delete</th></tr>
            <?php
              // List all the restricted functions
              $restricted = inscript_restrict::get ();
              if ($restricted)
              {
                $x = 0;
                foreach ($restricted AS $restrict)
                {
                  if ($x++ % 2 == 1)
                    echo '<tr class="alternate">';
                  else
                    echo '<tr>';

                  echo '<td>'.$restrict->name.'</td><td align="center">'.$restrict->value.'</td><td align="center"><input type="checkbox" name="dellevel_'.$restrict->name.'"/></tr>';
                }
              }
              else
                echo '<tr><td colspan="2"><em>No restrictions</em></td></tr>';
            ?>
          </table><br/>
          <?php
            // Create drop-down box of functions
            $names = inscript::get_function_names ();
            if ($names)
            {
              echo 'Restrict access of function <select name="funcname"><option name="-" selected="selected">-----</option>';
              foreach ($names AS $name)
                echo '<option name="'.$name.'">'.$name.'</option>';

              echo '</select> to level <select name="funclevel"><option name="default" selected="selected">Default</option>';
              for ($x = 1; $x <= 10; $x++)
                echo '<option name="'.$x.'">'.$x.'</option>';
              echo '</select>';
            }
          ?>
        </fieldset>
      	<p class="submit">
      		<input type="submit" name="Submit" value="Update Options &raquo;" />
      	</p>
      </div>
      <div class="wrap">
        <h2>Hooks</h2>
        <fieldset class="options">
          <legend>Hooked filters</legend>
          <p>Hooked functions are where you tell InScript to attach to WordPress filters.  These allow you to change data. For example, to use InScript inside a post you need to
          hook <em>the_content</em>.</p>
          <ul>
          <?php
            // List the default filters
            global $inscript_default_filters;
            $hooks = inscript_hook::get_filters ();
            foreach ($inscript_default_filters AS $name => $data)
            {
              echo '<li><input type="checkbox" id="filter_'.$name.'" name="filter_'.$name.'"';
              if (inscript_hook::is_hooked_filter ($name))
              {
                echo ' checked="checked"';
                $hooks = str_replace ($name, '', $hooks);    // Remove the default hook from the string
              }
              echo '/> <label for="filter_'.$name.'">'.$data.' <em>('.$name.')</em></label></li>';
            }
            ?>
          </ul>
          <p>Other filters (separate with spaces) - see <a href="http://codex.wordpress.org/Plugin_API#Current_hooks_for_filters">Codex</a> for full list:</p><input type="text" name="otherfilters" value="<?php echo trim ($hooks) ?>" style="width: 50%"/>
        </fieldset>
        <fieldset class="options">
          <legend>Hooked actions</legend>
          <p>Actions are triggered by events within WordPress, and can also be hooked by InScript.  Note that hooked actions are only available with WordPress meta keywords.</p>
          <ul>
          <?php
            // List default actions
            global $inscript_default_actions;
            $hooks = inscript_hook::get_actions ();
            foreach ($inscript_default_actions AS $name => $data)
            {
              echo '<li><input type="checkbox" id="action_'.$name.'" name="action_'.$name.'"';
              if (inscript_hook::is_hooked_action ($name))
              {
                echo ' checked="checked"';
                $hooks = str_replace ($name, '', $hooks);    // Remove the default hook from the string
              }
              echo '/> <label for="action_'.$name.'">'.$data.' <em>('.$name.')</em></label></li>';
            }
            ?>
          </ul>
          <p>Other actions (separate with spaces) - see <a href="http://codex.wordpress.org/Plugin_API#Current_Hooks_For_Actions">Codex</a> for full list:</p><input type="text" name="otheractions" value="<?php echo trim ($hooks) ?>" style="width: 50%"/>
        </fieldset>
        <fieldset class="options">
          <legend>Global keywords</legend>
          <p>This sections allows the setting of global keywords.  These are like post keywords, but are applied regardless of current post, and are the
          only way to get hooked actions on the front page.</p>
          <table>
          <tr><th>Action/Filter</th><th>Data</th><th>Delete</th></tr>
          <?php
            // List global data
            $list = inscript_global::get_list ();
            if ($list)
            {
              foreach ($list AS $meta)
              {
                $rows = substr_count ($meta->value, "\n") + 1;
                echo '<td valign="top"><input type="text" name="metaname_'.$meta->id.'" value="'.$meta->name.'"/></td>';
                echo '<td><textarea wrap="off" name="metavalue_'.$meta->id.'" cols="50" rows="'.$rows.'">'.htmlentities ($meta->value).'</textarea></td>';
                echo '<td valign="top" align="center"><input type="checkbox" name="metadelete_'.$meta->id.'"/></td></tr>';
              }
            }
            else
              echo '<tr><td colspan="3"><em>No global data</em></td></tr>';
          ?>
          </table>
          <p><strong>Create new global keyword</strong></p>
          <table>
          <tr><td>Action/filter:</td><td><input type="text" name="newglobal"/></td></tr>
          <tr><td>Data:</td><td><textarea wrap="off" name="newdata" cols="80"></textarea></td></tr>
          </table>
        </fieldset>
      	<p class="submit">
      		<input type="submit" name="Submit" value="Update Options &raquo;" />
      	</p>
    </div>
    </form>
    <?php
  }


  // ====================================================================================
  // Processes the POST information from InScript options
  // ====================================================================================

  function inscript_process ()
  {
    // Update the InScript variables
	  $vars = inscript_var::listall ();
	  if ($vars)
	  {
	    foreach ($vars AS $variable)
	    {
	      // Has it been deleted?
	      if (array_key_exists ('deletevar_'.$variable->id, $_POST))
	        inscript_var::remove ($variable->id);
	      else
	        inscript_var::update ($variable->id, $_POST['varname_'.$variable->id], $_POST['var_'.$variable->id]);
	    }
	  }

	  // New variable?
	  if ($_POST['newvar_name'] && $_POST['newvar_value'])
	    inscript_var::add ($_POST['newvar_name'], $_POST['newvar_value']);

	  // Update global options
	  inscript::set_disable   (isset ($_POST['disable']) ? true : false);
	  inscript::set_autop     (isset ($_POST['autop']) ? true : false);
	  inscript::set_texturize (isset ($_POST['texturize']) ? true : false);

	  // User-level restrictions
	  if ($_POST['funcname'] != "-----" && $_POST['funclevel'])
	    inscript_restrict::add ($_POST['funcname'], $_POST['funclevel']);

	  inscript_restrict::set_default ($_POST['defaultlevel']);

	  $restrict = inscript_restrict::get ();
	  if ($restrict)
	  {
	    foreach ($restrict AS $item)
	    {
	      if (isset ($_POST['dellevel_'.$item->name]))
	        inscript_restrict::delete ($item->name);
	    }
	  }

	  // Filters - first get 'other' hooks
	  if ($_POST['otherfilters'])
      $filters = explode (' ', $_POST['otherfilters']);

    // Now add the default filters
	  global $inscript_default_filters;
	  foreach ($inscript_default_filters AS $key => $value)
	  {
	    // See if the default hooks are enabled
	    if (isset ($_POST['filter_'.$key]))
	      $filters[] = $key;
	  }

    // Actions - first get 'other' actions
    if ($_POST['otheractions'])
      $actions = explode (' ', $_POST['otheractions']);

    global $inscript_default_actions;
	  foreach ($inscript_default_actions AS $key => $value)
	  {
	    // See if the default hooks are enabled
	    if (isset ($_POST['action_'.$key]))
	      $actions[] = $key;
	  }

    // Set the hooks, ensuring we have no duplicates
    if (count ($filters) > 0)
      $filters = array_unique ($filters);
    if (count ($actions) > 0)
      $actions = array_unique ($actions);

    if (inscript_hook::set_hooks ($filters, $actions) == false)
      $error = true;

    // Update all other meta values
    $metas = inscript_global::get_list ();
    if (count ($metas) > 0)
    {
      foreach ($metas AS $meta)
      {
        if (isset ($_POST['metadelete_'.$meta->id]))
          inscript_global::remove ($meta->id);
        else
          inscript_global::update ($meta->id, $_POST['metaname_'.$meta->id], $_POST['metavalue_'.$meta->id]);
      }
    }

    // And finally the global data
    if ($_POST['newdata'] && $_POST['newglobal'])
    {
      $name = escape_string ($_POST['newglobal']);
      $value = escape_string ($_POST['newdata']);
      add_post_meta (0, "inscript_$name", $value);
    }

    // Redirect to the options page so we don't get POST reload issues
  	$location = get_option('siteurl') . '/wp-admin/admin.php?page=inscript.php';
  	if ($error == true)
  	  $location .= "&error=true";
  	header('Location: '.$location);
  }
}

// ====================================================================================
// Naughty little check to see if this is a POST from the options screen
// ====================================================================================

if (isset ($_POST['inscript']))
  inscript_process ();



// Phew, we've done