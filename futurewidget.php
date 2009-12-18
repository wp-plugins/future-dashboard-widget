<?php
/*
Plugin Name: Future Dashboard Widget
Plugin URI: http://janmi.com/future-dashboard-widget/
Description: Display scheduled posts in dashboard
Tags: post, future , widget, dashboard 
Version: 0.1
Author: Janmi
Author URI: http://janmi.com/
*/

// Changelog:
// 0.1 First Version of Future Dashboard Widget by Janmi.com

/********************************
* CONFIGURATION
* TODO: make configuration form
********************************/
$number_posts = 7;
$resource_file = "languages/en_EN.php";
/********************************/

include ($resource_file);

// Add the default options for plugin activation
register_activation_hook(__FILE__, futurewidget_init);
function futurewidget_init() {
	static $init = false;
	$options = get_option('futurewidget_options');
	
	if(!$init) {
		if(!$options) {
			$options = array();
		}	
		$defaultOptions = array(
			"showupdates"				=> "no"
		);
		
		$updated = false;
		$migration = false;
		
		foreach($defaultOptions as $option => $value) {
			if(!isset($options[$option])) {
				// Migrate old options
				$oldOption = 'futurewidget_'.$option;
				$updated = true;
			}
		}		
		if($updated) {
			update_option('futurewidget_options', $options);
		}

		$init = true;
	}
	return $options;
}

$futurewidget = new futurewidget;

class futurewidget {
	
	function futurewidget() {
		$options = futurewidget_init();
		if(is_array($options)) {
			foreach($options as $option => $value) {
				$this->$option = $value;
			}
		}
	}	
}

//Adds widget
//By Janmi.com
add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');

function my_custom_dashboard_widgets() {
   global $wp_meta_boxes;

   unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
   unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
   unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);

   wp_add_dashboard_widget('custom_future_widget', WIDGET_TITLE, 'custom_dashboard_future');
}

//Displays scheduled posts
//By Janmi.com
function custom_dashboard_future() 
{
	$drafts = false;   
	//Query
	if ( !$drafts ) {
			$drafts_query = new WP_Query( array(
				'post_type' => 'post',
				'what_to_show' => 'posts',
				'post_status' => 'future',
				'author' => $GLOBALS['current_user']->ID,
				'posts_per_page' => $number_posts,
				'orderby' => 'published',
				'order' => 'DESC'
			) );
			$drafts =& $drafts_query->posts;
		}
	
		if ( $drafts && is_array( $drafts ) ) {
			$list = array();
			foreach ( $drafts as $draft ) {
				$url = get_edit_post_link( $draft->ID );
				$title = _draft_or_post_title( $draft->ID );
				$item = "<h4>" . get_the_time(__(WIDGET_DATEFORMAT), $draft) . " <a href='". $url . "' title='" . sprintf( __( WIDGET_EDIT. ' "%s"' ), attribute_escape( $title ) ) . "'>". $title . "</a></h4>";
				if ( $the_content = preg_split( '#\s#', strip_tags( $draft->post_content ), 11, PREG_SPLIT_NO_EMPTY ) )
					$item .= '<p>' . join( ' ', array_slice( $the_content, 0, 10 ) ) . ( 10 < count( $the_content ) ? '&hellip;' : '' ) . '</p>';
				$list[] = $item;
			}
	?>
		<ul>
			<li><?php echo join( "</li>\n<li>", $list ); ?></li>
		</ul>
		<p class="textright"><a href="edit.php?post_status=future" class="button"><?php _e(WIDGET_ALLPOSTS); ?></a></p>
	<?php
		} else {
			_e(WIDGET_NOPOSTS);
		}      
}
?>