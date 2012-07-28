<?php 

/*
Plugin Name: Ajax post from front
Plugin URI: 
Description:Plugin to allow users to post from sites' front-end
Author: Clinton Warren
Version: 
Author URI: 
*/

define('APFSURL', WP_PLUGIN_URL . "/".dirname(plugin_basename(__FILE__) ) );
define('APFPATH' , WP_PLUGIN_DIR. "/".dirname(plugin_basename(__FILE__) ) );

function apf_enqueuescripts()
{
	wp_enqueue_script('apf' , APFSURL.'/js/apf.js',array('jquery'));
	wp_localize_script('apf','apfajax',array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts' , apf_enqueuescripts);

function apf_post_form($allowNotLoggedInuser='yes') {
	if ( $allowNotLoggedInuser == 'no' &&  !is_user_logged_in() ) {
		echo "Please Login to create new post";
		return;
	}
	?>

	<form id="apfform" action="" method="post"enctype="multipart/form-data">

		<div id="apf-text">

			<div id="apf-response" style="background-color:#E6E6FA ;color:blue;"></div>

			<strong>Title </strong> <br/>
			<input type="text" id="apftitle" name="apftitle"/><br />
			<br/>

			<strong>Contents </strong> <br/>
			<textarea id="apfcontents" name="apfcontents"  rows="10" cols="20"></textarea><br />

			<br/>

			<a onclick="apfaddpost(apftitle.value,apfcontents.value);" style="cursor: pointer"><b>Create Post</b></a>

		</div>
	</form>

	<?php
}

class AjaxPostFromFrontWidget extends WP_Widget {
	function AjaxPostFromFrontWidget() {
		// widget actual processes
		$widget_ops = array('classname' => 'AjaxPostFromFrontWidget', 'description' => 'Lets you create post from front end' );
		$this->WP_Widget('AjaxPostFromFrontWidget','AjaxPostFromFrontWidget', $widget_ops);
	}

	function form($instance) {
		// outputs the options form on admin
		$defaults = array( 'title' => 'Ajax Post From Front','allow_not_logged_users' => 'no' );
		$instance = wp_parse_args( (array) $instance, $defaults );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo 'Title:'; ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'allow_not_logged_users' ); ?>">allow_not_logged_users:</label>
			<select id="<?php echo $this->get_field_id( 'allow_not_logged_users' ); ?>" name="<?php echo $this->get_field_name( 'allow_not_logged_users' ); ?>" class="widefat" style="width:100%;">
				<option <?php if ( 'no' == $instance['allow_not_logged_users'] ) echo 'selected="selected"'; ?>>no</option>
				<option <?php if ( 'yes' == $instance['allow_not_logged_users'] ) echo 'selected="selected"'; ?>>yes</option>
			</select>
		</p>

		<?php
	}

	function update($new_instance, $old_instance) {
		// processes widget options to be saved
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['allow_not_logged_users'] = $new_instance['allow_not_logged_users'];
		return $instance;
	}

	function widget($args, $instance) {
		// outputs the content of the widget
		extract( $args );
		$allow_not_logged_users = isset( $instance['allow_not_logged_users'] ) ? $instance['allow_not_logged_users'] : 'no';

		$title = apply_filters('widget_title', $instance['title'] );
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		echo '<ul>';
		echo apf_post_form($allow_not_logged_users);
		echo '</ul>';
		echo $after_widget;
	}
}

function apf_widget_init() {
	// Check for the required API functions
	if ( !function_exists('register_widget') )
		return;

	register_widget('AjaxPostFromFrontWidget');
}
add_action('widgets_init', 'apf_widget_init');

function apf_addpost() {
	$results = '';
	$title = $_POST['apftitle'];
	$content = $_POST['apfcontents'];
	
	$post_id = wp_insert_post( array(
		'post_title' => $title,
		'post_content' => $content,
		'post_status' => 'pending',
		'post_author' => '1'
		));
	
	if( $post_id !=0 )
	{
		$results = 'Post added';
	}
	else {
		$results = 'Error';
	}
	die($results);
}

add_action( 'wp_ajax_nopriv_apf_addpost','apf_addpost');
add_action( 'wp_ajax_apf_addpost' , 'apf_addpost');


			
			