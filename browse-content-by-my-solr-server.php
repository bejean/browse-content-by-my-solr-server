<?php
/*
Plugin Name: Browse Content by My Solr Server
Plugin URI: http://wordpress.org/extend/plugins/browse-content-by-mysolr-server/
Donate link: 
Description: Browse content by custom fields or other attributes
Version: 1.0.0
Author: Dominiue Bejean
Author URI: http://www.eolya.fr
*/
/*  
    Copyright (c) 2009 Matt Weber

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
*/
require_once("solr.inc.php");

global $wp_version;

$bDebug = false;

# do version checking here  
if (version_compare($wp_version, '3.0', '<')) {
    exit (__('Browse Content requires WordPress 3.0 or greater. ', 'bba'));
}


function mssbc_browse_widget() {
    register_widget('mssbc_BrowseWidget');
}
function  mssbc_startswith($hay, $needle) {
	return substr($hay, 0, strlen($needle)) === $needle;
}

class mssbc_BrowseWidget extends WP_Widget {

	private static function isSelected($aFilters, $facetfield, $facetval) {
		for ($i=0; $i<count($aFilters); $i++) {
			if (($aFilters[$i]['facetfield']==$facetfield) && ($aFilters[$i]['facetval']==$facetval))
			return true;
		}
		return false;
	}  
	
    function mssbc_BrowseWidget() {
        $options = array('classname' => 'widget_mssbc_browse', 'description' => __( "Display attributes list for browsing content") );
        $this->WP_Widget('mssbc', __('Browse content by attribute'), $options);
    }

    function widget( $args, $instance ) {
        global $bDebug;
    	
        extract($args);
        $title = apply_filters('widget_title', empty($instance['title']) ? __('Browse content by attribute') : $instance['title']);
//        $exclude_post = $instance['exclude_post'];
//        $exclude_page = $instance['exclude_page'];
        $attribute_list = $instance['attribute_list'];
        $facet_fields = split(',', $attribute_list);
        for ($i=0;$i<count($facet_fields); $i++) {
        	$facet_fields[$i] = trim($facet_fields[$i]);
        	if (mssbc_startswith($facet_fields[$i], 'wp_'))    	
        	$facet_fields[$i] = preg_replace('/^wp_/i', '', $facet_fields[$i]);
        	else
        	$facet_fields[$i] = $facet_fields[$i] . "_str";
        }

        echo $before_widget;
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }
                
		$filters_json = get_query_var( 'mssbc_browse_filter' );
        
		if ($filters_json=='') {
			$filters_json = '{\"filters\": [], \"exclude_post\":\"' . $exclude_post . '\", \"exclude_page\":\"' . $exclude_page . '\" }';
		}
		
        if ($filters_json!="") {
        	$aFilters = json_decode ( str_replace ( '\"' , '"' , $filters_json) , true);
        	if ($bDebug) {
        		echo "Filters: <br />" . $filters_json . "<br />";
        		print_r($aFilters);
        		echo "<br /><br />";
        	}
        }
		
        $results = mssbc_query($aFilters['filters'], $facet_fields, $exclude_post, $exclude_page);
        //mssbc_result($result);
        if (file_exists(TEMPLATEPATH . '/mssbc_custom.php')) {
        	// use theme file
        	include_once(TEMPLATEPATH . '/mssbc_custom.php');
        } else if (file_exists(dirname(__FILE__) . '/template/mssbc_custom.php')) {
        	// use plugin supplied file
        	//add_action('wp_head', 's4w_default_head');
        	include_once(dirname(__FILE__) . '/template/mssbc_custom.php');
        } else {
        	// no template files found, just continue on like normal
        	// this should get to the normal WordPress search results
        	include_once(dirname(__FILE__) . '/template/mssbc_default.php');
        }
		
?>
		<form action="<?php echo get_bloginfo('wpurl') . "/"; ?>" method="get" id="mssbc_browse" name="mssbc_browse">
		<input id="mssbc_browse_filter" name="mssbc_browse_filter" type="hidden" value='<?php echo $filters_json; ?>' />
		</form>
<?php      
        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $new_instance = wp_parse_args( (array) $new_instance, array( 'title' => 'Browse content by attribute', 'attribute_list' => '') );
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['attribute_list'] = strip_tags($new_instance['attribute_list']);
//        $instance['exclude_post'] =$new_instance['exclude_post'];        
//        $instance['exclude_page'] =$new_instance['exclude_page'];        
//        if ($instance['exclude_post']=='1' && $instance['exclude_page']=='1') {
//			$instance['exclude_post'] = '';
//		}      
		return $instance;
    }

    function form( $instance ) {
    	$default = array( 'title' => 'Browse by attribute', 'attribute_list' => '');
        $instance = wp_parse_args( (array) $instance, $default );
        $title = strip_tags($instance['title']);
        $attribute_list = strip_tags($instance['attribute_list']);
//        $exclude_post = strip_tags($instance['exclude_post']);
//        $exclude_page = strip_tags($instance['exclude_page']);
//        
//        if ($exclude_post=='1' && $exclude_page=='1') {
//			$exclude_post = '';
//		  }
                
        $wp_attributes = array();
        $wp_customfields = array();
    	if (get_option('s4w_facet_on_categories', '1')=='1') $wp_attributes[] = "wp_categories";
    	if (get_option('s4w_facet_on_tags', '1')=='1') $wp_attributes[] = "wp_tags";
    	if (get_option('s4w_facet_on_author', '1')=='1') $wp_attributes[] = "wp_author";
//    	if (get_option('s4w_facet_on_type', '1')=='1') $wp_attributes[] = "wp_type";
	   	$wp_customfields = get_option('s4w_index_custom_fields', '');
    	?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </p>
<!-- 
            <p>
                <label for="<?php echo $this->get_field_id('exclude_page'); ?>"><?php _e('Exclude:'); ?></label><br/>
                <input type='checkbox' id="<?php echo $this->get_field_id('exclude_page'); ?>" name="<?php echo $this->get_field_name('exclude_page'); ?>" value="1" <?php echo ($exclude_page=='1') ? " checked" : ""; ?> /> <?php _e('exclude pages'); ?><br/>
                <input type='checkbox' id="<?php echo $this->get_field_id('exclude_post'); ?>" name="<?php echo $this->get_field_name('exclude_post'); ?>" value="1" <?php echo ($exclude_post=='1') ? " checked" : ""; ?> /> <?php _e('exclude posts'); ?>
            </p>
-->
            <p>
                <label for="<?php echo $this->get_field_id('attribute_list'); ?>"><?php _e('Ordered comma separated attribute list:'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('attribute_list'); ?>" name="<?php echo $this->get_field_name('attribute_list'); ?>" type="text" value="<?php echo esc_attr($attribute_list); ?>" />
            </p>
            <p>
                According to your <strong>Solr for Wordpress</strong> plugin settings ("Solr Options"), available attributes mnemonics are : 
            </p>
            <ul>
<?php
		for ($i=0;$i<count($wp_attributes); $i++) {
        	echo "<li>$wp_attributes[$i]</li>";
		}
    	for ($i=0;$i<count($wp_customfields); $i++) {
        	echo "<li>$wp_customfields[$i]</li>";
		}
?>
			</ul>
<?php
    }
}


function mssbc_options_init() {
}

add_action( 'admin_init', 'mssbc_options_init');
add_action( 'widgets_init', 'mssbc_browse_widget');

function mssbc_clauses( $clauses, $wp_query ) {
	global $wpdb;

	$filters_json = get_query_var( 'mssbc_browse_filter' );

	if ( isset( $filters_json ) && '' != $filters_json ) {
		$current_join_clause = $clauses['join'];
		$current_where_clause = $clauses['where'];
		$current_fields_clause = $clauses['fields'];

		$aFilters = json_decode ( str_replace ( '\"' , '"' , $filters_json) , true);
		
//		$exclude_post=($aFilters['exclude_post']=='1');
//		$exclude_page=($aFilters['exclude_page']=='1');			
		$filters = $aFilters['filters'];
		if (count($filters)>0) {
					
			$first = true;
			$wp_where = '';
			for ($i=0; $i<count($filters); $i++) {
				if ($filters[$i]['facetfield']!='categories' && $filters[$i]['facetfield']!='tags' && $filters[$i]['facetfield']!='author' && $filters[$i]['facetfield']!='type') {
					if ($first) {
						$clauses['where'] .= " AND (";
					}
					else {
						$clauses['where'] .= " AND ";
						$clauses['join'] .= " ";
					}
					$first=false;
					$meta = "meta" . $i;
					$clauses['join'] .= "JOIN $wpdb->postmeta $meta ON ($wpdb->posts.ID = $meta.post_id)";
					$clauses['where'] .= "($meta.meta_key = '" . preg_replace('/_str$/i', '', $filters[$i]['facetfield']) . "' AND $meta.meta_value = '" . $filters[$i]['facetval'] . "')";
				}
				else {
					if ($first) {
						$clauses['where'] .= " AND (";
					}
					else {
						$clauses['where'] .= " AND ";
						$clauses['join'] .= " ";
					}
					$first=false;
					
//					if ($filters[$i]['facetfield']=='type') {
//						if ($exclude_post && !$exclude_page) {
//							// replace post per page
//						}
//						if (!$exclude_post && !$exclude_page) {
//							// replace post per page
//						}
//						if (!$exclude_post && $exclude_page) {
//							// default nothing to do
//						}
//						$clauses['where'] .= "($wpdb->posts.post_type = '" . $filters[$i]['facetval'] . "')";		
//					}
					
					if ($filters[$i]['facetfield']=='author') {
						$clauses['join'] .= "JOIN $wpdb->users ON ($wpdb->posts.post_author = $wpdb->users.ID)";
						$clauses['where'] .= "($wpdb->users.user_login = '" . $filters[$i]['facetval'] . "')";		
					}
					
					if ($filters[$i]['facetfield']=='tags') {
						$in_select = "SELECT $wpdb->term_taxonomy.term_taxonomy_id FROM $wpdb->term_taxonomy JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id) WHERE $wpdb->term_taxonomy.taxonomy = 'post_tag' AND $wpdb->terms.name = '" . $filters[$i]['facetval'] . "'";
						$clauses['join'] .= "JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)";
						$clauses['where'] .= "($wpdb->term_relationships.term_taxonomy_id IN (" . $in_select . "))";		
					}
					
					if ($filters[$i]['facetfield']=='categories') {
						$aValues = explode ('^^', $filters[$i]['facetval']);
						$values = "";
						for ($i=0;$i<count($aValues);$i++) {
							if (trim($aValues[$i])!='') $values = $aValues[$i];
						}
						$in_select = "SELECT $wpdb->term_taxonomy.term_taxonomy_id FROM $wpdb->term_taxonomy JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id) WHERE $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->terms.name = '" . $values . "'";
						$clauses['join'] .= "JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)";
						$clauses['where'] .= "($wpdb->term_relationships.term_taxonomy_id IN (" . $in_select . "))";		
					}
				}
			}
			if (!$first) $clauses['where'] .= ")";			
		}
	}
	$sql = "SELECT " . $clauses['fields'] . " FROM wp_posts " . $clauses['join'] . " WHERE 1=1" . $clauses['where'] . " ORDER BY " . $clauses['orderby'];
	return $clauses;
}
add_filter( 'posts_clauses', 'mssbc_clauses');


function mssbc_query_vars($aVars) {
    $aVars[] = "mssbc_browse_filter";
    return $aVars;
}
add_filter('query_vars', 'mssbc_query_vars');

function mssbc_head() {
	
	if (file_exists(TEMPLATEPATH . '/mssbc_custom.css')) {
		// use theme file
		printf(__("<link rel=\"stylesheet\" href=\"%s\" type=\"text/css\" media=\"screen\" />\n"), TEMPLATEPATH . '/mssbc_custom.css');
	} else if (file_exists(dirname(__FILE__) . '/template/mssbc_custom.css')) {
        // use plugin supplied file
        printf(__("<link rel=\"stylesheet\" href=\"%s\" type=\"text/css\" media=\"screen\" />\n"), dirname(__FILE__) . '/mssbc_custom.css');
	} else {
        // no template files found, just continue on like normal
        // this should get to the normal WordPress search results
    	printf(__("<link rel=\"stylesheet\" href=\"%s\" type=\"text/css\" media=\"screen\" />\n"), plugins_url('/template/mssbc_default.css', __FILE__));
	}
	
?>
<script type="text/javascript">

	function mss_inStateArray(state, filter) {
	    for(var i = 0; i < state.filters.length; i++) {
	        if ((state.filters[i].facetfield == filter.facetfield) && (state.filters[i].facetval == filter.facetval)) {
	            return true;
	        }
	    }
	    return false;
	}

	function mss_browse(action, facetfield, facetval) {
		var f = jQuery("#mssbc_browse_filter").val();
		//if (f=='') 
		//	f = '{"filters": []}';
		//else 
			f=f.replace(/\\"/g, '"');
		var state = JSON.parse(f);
		if (action=='add') {
			var filter = {facetfield: facetfield, facetval: facetval};
			if (!mss_inStateArray(state, filter))
				state.filters.push(filter);
			jQuery("#mssbc_browse_filter").val(JSON.stringify(state));
		}
		if (action=='remove') {
			f2 = '{"filters": []}';
			var state2 = JSON.parse(f2);
			for(var i = 0; i < state.filters.length; i++) {
		        if ((state.filters[i].facetfield != facetfield) || (state.filters[i].facetval != facetval)) {
		        	state2.filters.push(state.filters[i]);
		        }
		    }
			jQuery("#mssbc_browse_filter").val(JSON.stringify(state2));		    
		}
		jQuery("#mssbc_browse").submit();
	    return false;
	}

</script>
<?php
}
add_action( 'wp_head', 'mssbc_head');


function mssbc_load_json_parser(){
	if (is_admin()) return;
	wp_enqueue_script('json2');
}
add_action('wp_print_scripts','mssbc_load_json_parser');

