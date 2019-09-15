<?php
/**
 * Plugin Name: Crowd Favorite Plugin
 * Plugin URI: http://localhost/crowdfavorite/crowdfavorite
 * Description: Test plugin with custom post type and taxonomy. It will allow you to add team members with details and display them on a page using a custom template.
 * Version: 1.0
 * Author: Victor Arsenie
 * Author URI: http://www.victoralexandru.com
 */



// Register custom taxonomy
add_action( 'init', 'create_department_taxonomy' );
function create_department_taxonomy() {
    register_taxonomy( 'department', array(),
        array(
            'label'=>'Department',
            'public'=>true,
            'show_ui'=>true,
            'show_admin_column'=>true,
            'query_var'=>true,
            'hierarchical'=>true,
            'rewrite'=>array('with_front'=>true,'hierarchical'=>true),
        )
    );
}

// Register custom post type
add_action( 'init', 'create_team_member_cpt' );
function create_team_member_cpt() {
    register_post_type( 'team_member',
        array(
            'label'=>'Team Members',
            'public' => true,
            'publicly_queryable'=> true,
            'show_ui'=>true,
            'capability_type' => 'post',
            'has_archive' => true,
            'query_var'=> true,
            'can_export' => true,
            'hierarchical' =>true,
            'supports' =>array( 'title', 'editor', 'thumbnail' ),
            'rewrite' => array('with_front'=>true, ),
        ));

    register_taxonomy_for_object_type('department' ,'team_member');   // Set to custom taxonomy Department

}

// Add meta boxes
add_action('add_meta_boxes', 'wporg_add_custom_meta');
function wporg_add_custom_meta() {

    // Add position meta
    add_meta_box(
        'wporg_position_box_id',        // Unique ID
        'Position',                     // Box title
        'wporg_position_box_html',      // Content callback, must be of type callable
        'team_member'                   // Post type
    );
    // Add Twitter meta
    add_meta_box(
        'wporg_twitter_box_id',         // Unique ID
        'Twitter',                      // Box title
        'wporg_twitter_box_html',       // Content callback, must be of type callable
        'team_member'                   // Post type
    );
    // Add Facebook meta
    add_meta_box(
        'wporg_facebook_box_id',        // Unique ID
        'Facebook',                     // Box title
        'wporg_facebook_box_html',      // Content callback, must be of type callable
        'team_member'                   // Post type
    );

}

// HTML for position meta box
function wporg_position_box_html($post)
{

    ?>
    <!-- Description for meta box -->
    <label for="wporg_position_field">Add team member position</label>
    <!-- Text input for meta box -->
    <input type="text" name="wporg_position_field" id="wporg_position_field" class="postbox" value="<?= get_post_meta( get_the_ID(), '_wp_position_meta_key', true ); ?>" />
    <?php
}
// HTML for Twitter meta box
function wporg_twitter_box_html($post)
{
    ?>
    <!-- Description for meta box -->
    <label for="wporg_twitter_field">Add Twitter url</label>
    <!-- Text input for meta box -->
    <input type="text" name="wporg_twitter_field" id="wporg_twitter_field" class="postbox" value="<?= get_post_meta( get_the_ID(), '_wp_twitter_meta_key', true ); ?>" />
    <?php
}
// HTML for Facebook meta box
function wporg_facebook_box_html($post)
{
    ?>
    <!-- Description for meta box -->
    <label for="wporg_facebook_field">Add facebook url</label>
    <!-- Text input for meta box -->
    <input type="text" name="wporg_facebook_field" id="wporg_facebook_field" class="postbox" value="<?= get_post_meta( get_the_ID(), '_wp_facebook_meta_key', true ); ?>" />
    <?php
}

// Save meta box content
function wporg_save_meta_values($post_id)
{
    // Check if position meta is sent and save the value
    if (array_key_exists('wporg_position_field', $_POST)) {
        update_post_meta(
            $post_id,                                           // The post id to which the meta will be related
            '_wp_position_meta_key',                            // The meta key to be used later to retrieve the value
            sanitize_text_field($_POST['wporg_position_field']) // The user input which is sanitized with sanitize_text_field() function
        );
    }
    // Check if twitter meta is sent and save the value
    if (array_key_exists('wporg_twitter_field', $_POST)) {
        update_post_meta(
        $post_id,                                               // The post id to which the meta will be related
        '_wp_twitter_meta_key',                                 // The meta key to be used later to retrieve the value
            sanitize_text_field($_POST['wporg_twitter_field'])  // The user input which is sanitized with sanitize_text_field() function
        );
    }
    // Check if facebook meta is sent and save the value
    if (array_key_exists('wporg_facebook_field', $_POST)) {
        update_post_meta(
            $post_id,                                           // The post id to which the meta will be related
            '_wp_facebook_meta_key',                            // The meta key to be used later to retrieve the value
            sanitize_text_field($_POST['wporg_facebook_field']) // The user input which is sanitized with sanitize_text_field() function
        );
    }
}
add_action('save_post', 'wporg_save_meta_values');


// Add a custom template through the plugin - from wpexplorer.com
class PageTemplater {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;

	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new PageTemplater();
		}

		return self::$instance;

	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

			// 4.6 and older
			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);

		} else {

			// Add a filter to the wp 4.7 version attributes metabox
			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);

		}

		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'wp_insert_post_data',
			array( $this, 'register_project_templates' )
		);


		// Add a filter to the template include to determine if the page has our
		// template assigned and return it's path
		add_filter(
			'template_include',
			array( $this, 'view_project_template')
		);


		// Add your templates to this array.
		$this->templates = array(
			'cf_template.php' => 'Custom CF Template',
		);

	}

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {

		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( ! isset( $this->templates[get_post_meta(
			$post->ID, '_wp_page_template', true
		)] ) ) {
			return $template;
		}

		$file = plugin_dir_path( __FILE__ ). get_post_meta(
			$post->ID, '_wp_page_template', true
		);

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		// Return template
		return $template;

	}

}
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );

// Add style for the template
function cf_load_plugin_css() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style( 'cf_style', $plugin_url . 'css/cf_style.css' );
}
add_action( 'wp_enqueue_scripts', 'cf_load_plugin_css' );

// Add jquery for the template
function cf_load_plugin_jquery() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_script( 'jquery.min', $plugin_url . 'js/jquery.min.js' );
}
add_action( 'wp_enqueue_scripts', 'cf_load_plugin_jquery' );

// Add script for the template
function cf_load_plugin_js() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_script( 'cf_script', $plugin_url . 'js/cf_script.js' );
}
add_action( 'wp_enqueue_scripts', 'cf_load_plugin_js' );
?>
