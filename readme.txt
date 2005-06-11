=== InScript ===
Tags: scripting, meta, php, texturize, dates, times, titles, plugins, filters, action, customize
Contributors: John Godley

InScript is an extensible scripting framework that provides the capability to insert and modify data without needing to modify any WordPress files.  
At the simplest level, it is a generic pattern matcher � it searches for specific patterns, or tags, and replaces them with something else.  However, instead of replacing these patterns with static text, you can replace them with variables, functions, and even PHP code.

Some of the features that InScript provides are:
 * Dynamic variables, which can be used in many places and are automatically updated
 * Disable WordPress texturize functions across the whole blog, individual posts, or even individual words
 * Conversely, enable texturize, textile, markdown, or any formatting on individual posts or words
 * Embed well-defined scripts inside posts and any other part of WordPress, without modifying the theme

The embedded scripts are very powerful, and allow you to do things like:
 * Insert post & author information
 * Add HTTP meta-values and make them post-specific
 * Customise the appearance of words, paragraphs, or posts
 * Change date formats on individual sections
 * Insert highlighted code
 * Insert custom PHP code
 * Add custom stylesheets for specific posts

Because of the extensible nature of the plugin, you can add �scriptlets� (mini-plugins) that provide extra features and yet use the same InScript framework.

NO REALLY, WHAT DOES IT DO?
The best way to explain what InScript does is by providing an example.

Code highlighting
Often you want to highlight some code within a post.  This creates several problems:
 * Code uses special characters that interfere with HTML
 * WordPress mangles a lot of the characters
 * It�s a chore to color the code by hand
 
We can insert an InScript tag to do all this for us.
  %%format_highlight [file=/wp.php] [wp=off]%%

Modify times
You want more?  In addition to embedding functions within content, you can also apply a function to a whole WordPress element.  That is, you can apply it to something such as the date or title of a post.  You can choose to do this for all posts, or individual posts.
Here the date of one post changes by giving it a custom field:

  inscript_the_time = my home lappy, %%time_since [time=%2]%%

And now the date for that post (and no other post) will look like 'Posted on my home lappy, 4 hours 15 minutes ago'

Note that full documentation is available from the plugin website: http://www.urbangiraffe.com/plugins/inscript

== Installation ==

1)	Download the inscript.zip file
2)	Unpack the zip
3)	Upload the inscript.php file, and the inscript directory to the wp-content/plugins directory of your WordPress blog
4)	Enable the plugin from the WordPress administration page

Full documentation is included with the zip in the form of a PDF file in the inscript directory.

== Frequently Asked Questions ==

= Where do I find a list of filters I can hook? =

Most filters and actions are documented at the WordPress Codex (http://codex.wordpress.org/Plugin_API).  This is not a complete list by any means.  Chances are that if you to modify a piece of data it will be passed through a filter and until the Codex is complete the only reliable source of information is the code (or a forum!)

= I get an error complaining about write permissions - what does it mean? =

InScript needs to write to the wp-content/plugins/inscript directory and so this directory must have appropriate permissions to allow it to do so.  If you are using a Unix-based webserver then you need to make the directory writable.  Consult your hosting provider for more details

= I hook filters but they don't seem to work - why? = 

This is probably due to the above permissions problem - make sure the directory is writable.