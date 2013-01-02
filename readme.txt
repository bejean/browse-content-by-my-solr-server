=== Browse Content by My Solr Server ===
Contributors: www.mysolrserver.com
Author URI: http://www.mysolrserver.com
Plugin URI: http://wordpress.org/extend/plugins/browse-content-by-my-solr-server/
Donate link: 
Tags: custom fields, browse content, search
Requires at least: 3.0.0
Tested up to: 3.3.1
Stable tag: 2.0.4

A WordPress widget that browses content by standard WordPress attributes (categories, tags and authors) and custom fields.

== Description ==

Browse Content by My Solr Server widget allows to navigate through WordPress content by standard WordPress attributes (categories, tags and authors) and custom fields.

You can see a demo at http://www.eolya.fr/blog/ 

== Installation ==

= Prerequisite = 

Install and activate `Advanced Search by My Solr Server plugin 2.0.0 and greater` (http://wordpress.org/extend/plugins/advanced-search-by-my-solr-server/).
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
A browse.php sample file is provided in the sample directory.

= Customize the posts list =

As explained just before, you can create a dedicated template file in your theme based for example on search.php. This template is in charge to implement the WordPress loop.

&lt;?php if ( have_posts() ) : ?&gt;
...
&lt;?php while ( have_posts() ) : the_post(); ?&gt;
...
&lt;?php endwhile; ?&gt;

= Customize the widget display =

The widget uses the template files `template/mssbc_default.php` and `template/mssbc_default.css`.

You can implement your own template by copying theses two files as `mssbc_custom.php` and `mssbc_custom.css`. These two new files can be located in the widget's template directory or in your theme's main directory.


== Frequently Asked Questions ==

= What version of WordPress does Browse Content by My Solr Server plugin work with? =

Browse Content by My Solr Server plugin works with WordPress 3.0.0 and greater.

= What version of Solr does Advanced Search by My Solr Server plugin work with? =

Advanced Search by My Solr Server plugin works with Solr 1.4.x and 3.x

= How to manage Custom Post type, custom taxonomies and custom fields

Browse Content by My Solr Server plugin was tested with:
* "Custom Post Type UI" plugin for Custom Post type and custom taxonomies management 
* "Custom Field Template" plugin for custom fields management
* WP-Types plugin for Custom Post type and custom taxonomies management and for custom fields management


== Screenshots ==

1. Widget in sidebar

2. Configuration Page

== Changelog ==

= 2.0.4 =

* SolrPhpClient upgrade

= 2.0.2 =

* Bug fixing
* Provide a browse.php sample file to be use in the theme
* Tests with WP-Types plugin 

= 2.0.1 =

* SolrPhpClient library dependency update

= 2.0.0 =

* Add support for custom post types and custom taxonomies

= 1.1.1 =

* Fixes issue when attribute values orcustom field values contain special characters like & 

= 1.1.0 =

* Works with both a static page as front page or last posts as front page (`Reading settings` of WordPress)

= 1.0.0 =

* Initial version 



