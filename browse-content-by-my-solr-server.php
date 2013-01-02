<?php
/*
Plugin Name: Browse Content by My Solr Server
Plugin URI: http://wordpress.org/extend/plugins/browse-content-by-mysolr-server/
Donate link:
Description: Browse content by custom fields or other attributes
Version: 2.0.4
Author: My Solr Server
Author URI: http://www.mysolrserver.com
*/
/*
Copyright (c) 2011-2013 www.mysolrserver.com

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


// checks if we need to check the checkbox
function mssbc_checkCheckbox( $bool ) {
	if($bool){
		echo 'checked="checked"';
	}
}
function mssbc_checkCheckboxInGroup( $groupValues, $checkboxValue ) {
	$aValues = explode(',', $groupValues);
	if (in_array($checkboxValue, $aValues))
	echo 'checked="checked"';
}

function mssbc_startswith($str, $sub) {
	return ( substr( $str, 0, strlen( $sub )) === $sub );
}
function mssbc_endswith($str, $sub) {
	return ( substr( $str, strlen( $str ) - strlen( $sub )) === $sub );
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
		$template = $instance['template'];
		$facet_fields = explode(',', $attribute_list);
		for ($i=0;$i<count($facet_fields); $i++) {
			$facet_fields[$i] = trim($facet_fields[$i]);
			//if (mssbc_startswith($facet_fields[$i], 'wp_')) {
			$facet_fields[$i] = preg_replace('/^wp_/i', '', $facet_fields[$i]);
			//}
			$facet_fields[$i] = $facet_fields[$i] . "_str";
			if ($facet_fields[$i]=='category_str') $facet_fields[$i] = 'categories';
			if ($facet_fields[$i]=='tag_str') $facet_fields[$i] = 'tags';
			if ($facet_fields[$i]=='author_str') $facet_fields[$i] = 'author';
			if ($facet_fields[$i]=='type_str') $facet_fields[$i] = 'type';
			$facet_fields[$i] =  strtolower(str_replace(' ', '_', $facet_fields[$i]));
		}

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		$filters_json = get_query_var( 'mssbc_browse_filter' );

		if ($filters_json=='') {
			//$filters_json = '{\"filters\": [], \"exclude_post\":\"' . $exclude_post . '\", \"exclude_page\":\"' . $exclude_page . '\" }';
			$filters_json = '{\"filters\": [] }';
		}
		else {
			// To improve !!!
			$filters_json = str_replace("\'", "'", $filters_json);
		}

		if ($filters_json!="") {
			$aFilters = json_decode ( str_replace ( '\"' , '"' , $filters_json) , true);
			if ($bDebug) {
				echo "Filters: <br />" . $filters_json . "<br />";
				print_r($aFilters);
				echo "<br /><br />";
			}
		}

		$results = mssbc_query($aFilters['filters'], $facet_fields);
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
			include_once(dirname(__FILE__) . '/template/mssbc_default.php');
		}

		// To improve !!!
		$filters_json = str_replace("&amp;", "&amp;amp;", $filters_json);
		$filters_json = str_replace("'", "&apos;", $filters_json);

		?>
<form action="<?php echo get_bloginfo('wpurl') . "/"; ?>" method="get"
	id="mssbc_browse" name="mssbc_browse">
	<input id="mssbc_browse_filter" name="mssbc_browse_filter"
		type="hidden" value='<?php echo $filters_json; ?>' /> <input
		id="mssbc_browse_template" name="mssbc_browse_template" type="hidden"
		value='<?php echo $template; ?>' />
</form>



<?php
echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => 'Browse content by attribute', 'attribute_list' => '') );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['attribute_list'] = strip_tags($new_instance['attribute_list']);
		$instance['template'] = strip_tags($new_instance['template']);
			
// 		$value = '';
// 		foreach($new_instance as $key => $option) {
// 			if (mssbc_startswith($key, 'post_type_')) {
// 				if ($value!='') $value .= ',';
// 				$value .= strip_tags($option);
// 			}
// 		}
// 		$instance['post_type'] = $value;
			
		//        $instance['exclude_post'] =$new_instance['exclude_post'];
		//        $instance['exclude_page'] =$new_instance['exclude_page'];
		//        if ($instance['exclude_post']=='1' && $instance['exclude_page']=='1') {
		//			$instance['exclude_post'] = '';
		//		  }
		return $instance;
	}

	function form( $instance ) {
		$default = array( 'title' => 'Browse by attribute', 'attribute_list' => '');
		$instance = wp_parse_args( (array) $instance, $default );
		$title = strip_tags($instance['title']);
		$attribute_list = strip_tags($instance['attribute_list']);
		$title = strip_tags($instance['title']);
		$template = (isset($instance['template'])) ? strip_tags($instance['template']) : '';
		
		//$post_types_in_facets = strip_tags($instance['post_types_in_facets']);
		if ($template=='') $template = 'search.php';
		//        $exclude_page = strip_tags($instance['exclude_page']);
		//
		//        if ($exclude_post=='1' && $exclude_page=='1') {
		//			$exclude_post = '';
		//		  }

		$wp_attributes = array();
		$wp_customfields = array();
			
		$plugin_s4w_settings = get_option('plugin_mss_settings');
		$host = $plugin_s4w_settings['mss_solr_host'];
		$port = $plugin_s4w_settings['mss_solr_port'];
		$path = $plugin_s4w_settings['mss_solr_path'];
		$version = 2;
		if (!$host && !$port && !$path) {

			$plugin_s4w_settings = get_option('plugin_s4w_settings');
			$s4w_solr_host = $plugin_s4w_settings['s4w_solr_host'];
			$s4w_solr_port = $plugin_s4w_settings['s4w_solr_port'];
			$s4w_solr_path = $plugin_s4w_settings['s4w_solr_path'];

			if (!$s4w_solr_host && !$s4w_solr_port && !$s4w_solr_path) {
				$wp_customfields = get_option('s4w_index_custom_fields', '');
			}
			else {
				$wp_customfields = $plugin_s4w_settings['s4w_index_custom_fields'];
			}
			$version = 1;
			if (get_option('s4w_facet_on_categories', '1')=='1') $wp_attributes[] = "wp_categories";
			if (get_option('s4w_facet_on_tags', '1')=='1') $wp_attributes[] = "wp_tags";
			if (get_option('s4w_facet_on_author', '1')=='1') $wp_attributes[] = "wp_author";
			//    	if (get_option('s4w_facet_on_type', '1')=='1') $wp_attributes[] = "wp_type";
		}
		else {
			$wp_customfields = explode(',',$plugin_s4w_settings['mss_facets']);
		}
		?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
	</label> <input class="widefat"
		id="<?php echo $this->get_field_id('title'); ?>"
		name="<?php echo $this->get_field_name('title'); ?>" type="text"
		value="<?php echo esc_attr($title); ?>" />
</p>
<p></p>
<p>
	<label for="<?php echo $this->get_field_id('attribute_list'); ?>"><?php _e('Ordered comma separated<br />attribute list:'); ?>
	</label> <input class="widefat"
		id="<?php echo $this->get_field_id('attribute_list'); ?>"
		name="<?php echo $this->get_field_name('attribute_list'); ?>"
		type="text" value="<?php echo esc_attr($attribute_list); ?>" />
</p>
<p>
	According to your <strong>Advanced Search by My Solr Server</strong>
	plugin settings, available attributes mnemonics are :
</p>
<ul
	style='list-style-type: square; list-style-position: inside; margin-left: 15px'>



	<?php
	if ($version == 1) {
		for ($i=0;$i<count($wp_attributes); $i++) {
			echo "<li>$wp_attributes[$i]</li>";
		}
	}
	for ($i=0;$i<count($wp_customfields); $i++) {
		//if ($wp_customfields[$i]=='type') continue;
		echo "<li>$wp_customfields[$i]</li>";
	}

//	$post_type_instance = strip_tags($instance['post_type']);
	?>
</ul>
<p></p>
<p>
	<label for="<?php echo $this->get_field_id('template'); ?>"><?php _e('Template:'); ?>
	</label> <input class="widefat"
		id="<?php echo $this->get_field_id('template'); ?>"
		name="<?php echo $this->get_field_name('template'); ?>" type="text"
		value="<?php echo esc_attr($template); ?>" />
</p>

<?php
	}
}


// function mssbc_options_init() {
// }
// add_action( 'admin_init', 'mssbc_options_init');
add_action( 'widgets_init', 'mssbc_browse_widget');

function mssbc_clauses( $clauses, $wp_query ) {
	global $wpdb;
		
	$keys = $wpdb->get_col( "
				SELECT meta_key
				FROM $wpdb->postmeta
				GROUP BY meta_key
				HAVING meta_key NOT LIKE '\_%'
				ORDER BY meta_key" );

	$wp_customfields = array();

	$plugin_s4w_settings = get_option('plugin_mss_settings');
	$host = $plugin_s4w_settings['mss_solr_host'];
	$port = $plugin_s4w_settings['mss_solr_port'];
	$path = $plugin_s4w_settings['mss_solr_path'];
	$version = 2;
	if (!$host && !$port && !$path) {

		$plugin_s4w_settings = get_option('plugin_s4w_settings');
		if (!$s4w_solr_host && !$s4w_solr_port && !$s4w_solr_path) {
			$wp_customfields = get_option('s4w_index_custom_fields', '');
		}
		else {
			$wp_customfields = $plugin_s4w_settings['s4w_index_custom_fields'];
		}
		$version = 1;
	}
	else {
		$wp_customfields = explode(',',$plugin_s4w_settings['mss_facets']);
	}

	$filters_json = get_query_var( 'mssbc_browse_filter' );

	if ( isset( $filters_json ) && '' != $filters_json && '{\"filters\":[]}' != $filters_json) {

		$type_done = false;
		
		$clauses['where'] = "AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'private')";
		
		//$current_join_clause = $clauses['join'];
		//$current_where_clause = $clauses['where'];
		//$current_fields_clause = $clauses['fields'];

		// To improve !!!
		$filters_json = str_replace("\'", "'", $filters_json);

		$aFilters = json_decode ( str_replace ( '\"' , '"' , $filters_json) , true);

		//		$exclude_post=($aFilters['exclude_post']=='1');
		//		$exclude_page=($aFilters['exclude_page']=='1');
		$filters = $aFilters['filters'];
		if (count($filters)>0) {

			$first = true;
			$wp_where = '';
			for ($i=0; $i<count($filters); $i++) {
				$meta = "meta" . $i;
				$facetfield = $filters[$i]['facetfield'];
				$facetval = $filters[$i]['facetval'];
				//if ($facetfield!='categories' && $facetfield!='tags' && $facetfield!='author' && $facetfield!='type') {
				if (in_array ( preg_replace('/_str$/i', '', $facetfield) , $keys )) {
					if ($first) {
						$clauses['where'] .= " AND (";
					}
					else {
						$clauses['where'] .= " AND ";
						$clauses['join'] .= " ";
					}
					$first=false;
						
					$wp_metaname = preg_replace('/_str$/i', '', $facetfield);
					if ($version == 2) {
						for ($i=0;$i<count($wp_customfields); $i++) {
							if (strtolower(str_replace(' ', '_', $wp_customfields[$i]))==$wp_metaname) {
								$wp_metaname = $wp_customfields[$i];
								break;
							}
						}
					}
						
					$clauses['join'] .= "JOIN $wpdb->postmeta $meta ON ($wpdb->posts.ID = $meta.post_id)";
					$clauses['where'] .= "($meta.meta_key = '" . $wp_metaname . "' AND $meta.meta_value = '" . addslashes($facetval) . "')";
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

					if ($facetfield!='categories' && $facetfield!='tags' && $facetfield!='author' && $facetfield!='type') {
						$in_select = "SELECT $wpdb->term_taxonomy.term_taxonomy_id FROM $wpdb->term_taxonomy JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id) WHERE $wpdb->term_taxonomy.taxonomy = '" . preg_replace('/_str$/i', '', $facetfield) . "' AND $wpdb->terms.name = '" . addslashes($facetval) . "'";
						$clauses['join'] .= " JOIN $wpdb->term_relationships $meta ON ($wpdb->posts.ID = $meta.object_id)";
						$clauses['where'] .= "($meta.term_taxonomy_id IN (" . $in_select . "))";
					}
					else {
						if ($facetfield=='type') {
							$clauses['where'] .= "($wpdb->posts.post_type = '" . addslashes($facetval) . "')";
							$type_done = true;
						}
							
						if ($facetfield=='author') {
							$clauses['join'] .= "JOIN $wpdb->users $meta ON ($wpdb->posts.post_author = $meta.ID)";
							$clauses['where'] .= "($meta.user_login = '" . addslashes($facetval) . "')";
						}

						if ($facetfield=='tags') {
							$in_select = "SELECT $wpdb->term_taxonomy.term_taxonomy_id FROM $wpdb->term_taxonomy JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id) WHERE $wpdb->term_taxonomy.taxonomy = 'post_tag' AND $wpdb->terms.name = '" . addslashes($facetval) . "'";
							$clauses['join'] .= " JOIN $wpdb->term_relationships $meta ON ($wpdb->posts.ID = $meta.object_id)";
							$clauses['where'] .= "($meta.term_taxonomy_id IN (" . $in_select . "))";
						}

						if ($facetfield=='categories') {
							$aValues = explode ('^^', $facetval);
							$values = "";
							for ($j=0;$j<count($aValues);$j++) {
								if (trim($aValues[$j])!='') $values = $aValues[$j];
							}
							$in_select = "SELECT $wpdb->term_taxonomy.term_taxonomy_id FROM $wpdb->term_taxonomy JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id) WHERE $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->terms.name = '" . addslashes($values) . "'";
							$clauses['join'] .= "JOIN $wpdb->term_relationships $meta ON ($wpdb->posts.ID = $meta.object_id)";
							$clauses['where'] .= "($meta.term_taxonomy_id IN (" . $in_select . "))";
						}
					}
				}
			}
			if (!$first) $clauses['where'] .= ")";
		}
		else {
			//$clauses['where'] = "AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'private')";
			//$clauses['where'] = "";
		}
		if (!$type_done) {
			$clauses['where'] .= " AND (";
			$types = $plugin_s4w_settings['mss_post_types'];
			$atypes = explode(",",$types);
			for ($i=0;$i<count($atypes); $i++) {
				if ($i>0) $clauses['where'] .= " OR ";
				$clauses['where'] .= "($wpdb->posts.post_type = '" . addslashes($atypes[$i]) . "')";
			}
			$clauses['where'] .= ")";
		}	
	}
	else {
		//$clauses['where'] = "AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'private')";
		//$clauses['where'] = "";
	}

	
	$sql = "SELECT " . $clauses['fields'] . " FROM wp_posts " . $clauses['join'] . " WHERE 1=1 " . $clauses['where'] . " ORDER BY " . $clauses['orderby'];
	return $clauses;
}
add_filter( 'posts_clauses', 'mssbc_clauses', 10, 2 );

function mssbc_get_template($template){
	$new_template = get_query_var( 'mssbc_browse_template' );
	if (!empty($new_template) && $new_template != '') {
		$new_template = preg_replace('/[.]php$/i', '', $new_template);
		return get_query_template($new_template);
	}
	return $template;
}
add_filter('home_template','mssbc_get_template');
add_filter('page_template','mssbc_get_template');


function mssbc_query_vars($aVars) {
	$aVars[] = "mssbc_browse_filter";
	$aVars[] = "mssbc_browse_template";
	return $aVars;
}
add_filter('query_vars', 'mssbc_query_vars');

function mssbc_head() {

	if (file_exists(TEMPLATEPATH . '/mssbc_custom.css')) {
		// use theme file customized css
		printf(__("<link rel=\"stylesheet\" href=\"%s\" type=\"text/css\" media=\"screen\" />\n"), bloginfo(template_url) . '/mssbc_custom.css');
	} else if (file_exists(dirname(__FILE__) . '/template/mssbc_custom.css')) {
		// use plugin customized css
		printf(__("<link rel=\"stylesheet\" href=\"%s\" type=\"text/css\" media=\"screen\" />\n"), plugins_url('/browse-search-by-my-solr-server/template/mssbc_custom.css'));
	} else {
		// no custom template files found, just continue on like normal
		printf(__("<link rel=\"stylesheet\" href=\"%s\" type=\"text/css\" media=\"screen\" />\n"), plugins_url('/browse-search-by-my-solr-server/template/mssbc_default.css'));
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
		
		// To improve !!!
		f=f.replace(/\\"/g, '"').replace(/\\'/g, "'");
		
		//alert (facetval + ' | ' + f);
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
		//alert(jQuery("#mssbc_browse_filter").val());
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

// add custom post type to wp loop

/*
add_filter( 'pre_get_posts', 'mssbc_add_to_query' );
function mssbc_add_to_query( $query ) {
	if ( is_home() || is_single() ) {
		if (!isset($query->post_type)) {
			$aPostType = array( 'post', 'page');
			$args=array(
 			  		'public'   => true,
 			  		'_builtin' => false
			);
			$output = 'names'; // names or objects, note names is the default
			$operator = 'and'; // 'and' or 'or'
			$post_types=get_post_types($args,$output,$operator);
			if ($post_types) {
				foreach ($post_types  as $post_type) {
					$aPostType[] = $post_type;
				}
				$query->set('post_type', $aPostType);
			}
		}
	}
	return $query;
}
*/


