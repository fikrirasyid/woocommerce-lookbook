<?php
/*
 Plugin Name: WooCommerce Lookbook
 Plugin URI: http://fikrirasy.id/project/woocommerce-lookbook/
 Description: Create lookbook for your WooCommerce based store
 Author: Fikri Rasyid
 Version: 0.1
 Author URI: http://fikrirasy.id
*/

/**
 * Constants
 */
if (!defined('WC_LOOKBOOK_DIR'))
    define('WC_LOOKBOOK_DIR', plugin_dir_path( __FILE__ ));


if (!defined('WC_LOOKBOOK_URL'))
    define('WC_LOOKBOOK_URL', plugin_dir_url( __FILE__ ));	 

/**
 * Requiring external files
 */
require_once( 'includes/class-woocommerce-lookbook-editor.php' );
require_once( 'includes/class-woocommerce-lookbook-frontend.php' );

/**
 * Setup plugin
 */
class WC_Lookbook_Setup{
	function __construct(){
		register_activation_hook( __FILE__, 	array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, 	array( $this, 'deactivation' ) );

		add_action( 'init', 					array( $this, 'register_post_type' ) );
	}

	/**
	 * Activation task. Do this when the plugin is activated
	 * 
	 * @access public 
	 * 
	 * @return void
	 */
	public function activation(){
		// Registering post type here so it can be flushed right away
		$this->register_post_type();

		flush_rewrite_rules();		
	}

	/**
	 * Deactivation task. Do this when the plugin is deactivated
	 * 
	 * @return void
	 */
	public function deactivation(){
	}

	/**
	 * Adding CPT for lookbook
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function register_post_type(){
		
		/* Set up the arguments for the post type. */
		$args = array(

			'description'         => __( 'Lookbook for your WooCommerce Product', 'woocommerce-lookbook' ), // string
			'public'              => true, // bool (default is FALSE)
			'publicly_queryable'  => true, // bool (defaults to 'public').
			'exclude_from_search' => apply_filters( 'woocommerce_lookbook_exclude_from_search', true ), // bool (defaults to FALSE - the default of 'internal')
			'show_in_nav_menus'   => false, // bool (defaults to 'public')
			'show_ui'             => true, // bool (defaults to 'public')
			'show_in_menu'        => true, // bool (defaults to 'show_ui')
			'show_in_admin_bar'   => true, // bool (defaults to 'show_in_menu')
			'menu_position'       => 21, // int (defaults to 25 - below comments)
			'menu_icon'           => 'dashicons-book-alt', // string (defaults to use the post icon)
			'can_export'          => true, // bool (defaults to TRUE)
			'delete_with_user'    => false, // bool (defaults to TRUE if the post type supports 'author')
			'hierarchical'        => false, // bool (defaults to FALSE)
			'has_archive'         => 'lookbook', // bool|string (defaults to FALSE)
			'query_var'           => 'lookbook', // bool|string (defaults to TRUE - post type name)
			'capability_type'     => 'post', // string|array (defaults to 'post')
			'rewrite' => array(
				'slug'       => 'lookbook', // string (defaults to the post type name)
				'with_front' => false, // bool (defaults to TRUE)
				'pages'      => false, // bool (defaults to TRUE)
				'feeds'      => false, // bool (defaults to the 'has_archive' argument)
				'ep_mask'    => EP_PERMALINK, // const (defaults to EP_PERMALINK)
			),
			'supports' => array(
				'title', 'editor', 'thumbnail'
			),
			'labels' => array(
				'name'               => __( 'Lookbooks',                   'woocommerce-lookbook' ),
				'singular_name'      => __( 'Lookbook',                    'woocommerce-lookbook' ),
				'menu_name'          => __( 'Lookbooks',                   'woocommerce-lookbook' ),
				'name_admin_bar'     => __( 'Lookbooks',                   'woocommerce-lookbook' ),
				'add_new'            => __( 'Add New',                    'woocommerce-lookbook' ),
				'add_new_item'       => __( 'Add New Lookbook',            'woocommerce-lookbook' ),
				'edit_item'          => __( 'Edit Lookbook',               'woocommerce-lookbook' ),
				'new_item'           => __( 'New Lookbook',                'woocommerce-lookbook' ),
				'view_item'          => __( 'View Lookbook',               'woocommerce-lookbook' ),
				'search_items'       => __( 'Search Lookbooks',            'woocommerce-lookbook' ),
				'not_found'          => __( 'No lookbooks found',          'woocommerce-lookbook' ),
				'not_found_in_trash' => __( 'No lookbooks found in trash', 'woocommerce-lookbook' ),
				'all_items'          => __( 'All Lookbooks',               'woocommerce-lookbook' ),
				'parent_item'        => __( 'Parent Lookbook',             'woocommerce-lookbook' ),
				'parent_item_colon'  => __( 'Parent Lookbook:',            'woocommerce-lookbook' ),
				'archive_title'      => __( 'Lookbooks',                   'woocommerce-lookbook' ),
			)
		);

		/* Register the post type. */
		register_post_type(
			'lookbook', // Post type name. Max of 20 characters. Uppercase and spaces not allowed.
			apply_filters( 'woocommerce_lookbook_post_type_args', $args )      // Arguments for post type.
		);
	}
}
new WC_Lookbook_Setup;