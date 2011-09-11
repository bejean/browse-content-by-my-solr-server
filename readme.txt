=== Browse Content by My Solr Server ===
Contributors: www.mysolrserver.com
Author URI: http://www.mysolrserver.com
Plugin URI: http://wordpress.org/extend/plugins/browse-content-by-mysolr-server/
Donate link: 
Tags: custom fields, browse content, search
Requires at least: 3.0.0
Tested up to: 3.2.1
Stable tag: 1.1.0

A WordPress widget that browses content by standard WordPress attributes (categories, tags and authors) and custom fields.

== Description ==

`Browse Content by My Solr Server` allows to navigate through WordPress content by standard WordPress attributes (categories, tags and authors) and custom fields.

== Installation ==

= Prerequisite = 

Install and activate `Solr for WordPress plugin 0.4.1 and greater` (http://wordpress.org/extend/plugins/solr-for-wordpress/).
Index your blog content into Solr (see Solr hosting provider here : http://www.mysolrserver.com/) 

= Installation =

1. Upload the `browse-content-by-my-solr-server` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. Drad & drop the widget in the sidebar
3. Edit the widget settings

= Configuration =

1. Provide a widget title
2. Provide the standard WordPress attributes (categories, tags and authors) and custom fields to be used in the widget
3. Select the template file from you theme to be used to show posts list. By default, the `search.php` template is used. You can chose to create a specific template. For exemple, with the default Wordpress themes, you can copy search.php to browse.php and remove the line that display the message "Search Results for:".

= Customization of the posts list =

As explained just before, you can create a dedicated template file in your theme based for example on search.php. This template is in charge to implement the WordPress loop.

<?php if ( have_posts() ) : ?>
...
<?php while ( have_posts() ) : the_post(); ?>
...
<?php endwhile; ?>

= Custimized the widget display =

The widget uses the template files `template/mssbc_default.php` and `template/mssbc_default.css`.

You can implement your own template by copying theses two files as `mssbc_custom.php` and `mssbc_custom.css`. These two new files can be located in the widget's template directory or in your theme's main directory.


== Frequently Asked Questions ==

= What version of WordPress does Browse Content by My Solr Server plugin work with? =

Browse Content by My Solr Server plugin works with WordPress 3.0.0 and greater.

= What version of Solr for WordPress plugin does Browse Content by My Solr Server plugin work with? =

Browse Content by My Solr Server plugin works with Solr for WordPress 0.4.1 and greater.


== Screenshots ==

1. Widget in sidebar

2. Configuration Page

== Changelog ==

= 1.0.0 =

Initial version 

= 1.1.0 =

Works with both a static page as front page or last posts as front page (`Reading settings` of WordPress)

