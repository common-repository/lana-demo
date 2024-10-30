<?php
/**
 * Plugin Name: Lana Demo
 * Plugin URI: http://lana.codes/lana-product/lana-demo/
 * Description: Demo user with editable roles and dashboard widgets.
 * Version: 1.0.9
 * Author: Lana Codes
 * Author URI: http://lana.codes/
 * Text Domain: lana-demo
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) or die();
define( 'LANA_DEMO_VERSION', '1.0.9' );
define( 'LANA_DEMO_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'LANA_DEMO_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Language
 * load
 */
load_plugin_textdomain( 'lana-demo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

/**
 * Add plugin action links
 *
 * @param $links
 *
 * @return mixed
 */
function lana_demo_add_plugin_action_links( $links ) {

	$settings_url = esc_url( admin_url( 'options-general.php?page=lana-demo-settings.php' ) );

	/** add settings link */
	$settings_link = sprintf( '<a href="%s">%s</a>', $settings_url, __( 'Settings', 'lana-demo' ) );
	array_unshift( $links, $settings_link );

	return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'lana_demo_add_plugin_action_links' );

/**
 * Init
 * filter pre_update_option
 */
function lana_demo_init() {
	add_filter( 'pre_update_option_lana_demo_username', 'lana_demo_update_option_username', 10, 2 );
	add_filter( 'pre_update_option_lana_demo_password', 'lana_demo_update_option_password', 10, 2 );
}

add_action( 'init', 'lana_demo_init' );

/**
 * Lana Demo - add role
 */
function lana_demo_add_role() {

	add_role( 'lana_demo', __( 'Lana Demo', 'lana-demo' ), array(
		'read'         => true,
		'edit_posts'   => filter_var( get_option( 'lana_demo_role_edit_posts', '0' ), FILTER_VALIDATE_BOOLEAN ),
		'edit_pages'   => filter_var( get_option( 'lana_demo_role_edit_pages', '0' ), FILTER_VALIDATE_BOOLEAN ),
		'upload_files' => filter_var( get_option( 'lana_demo_role_upload_files', '0' ), FILTER_VALIDATE_BOOLEAN )
	) );
}

register_activation_hook( __FILE__, 'lana_demo_add_role' );

/**
 * Lana Demo - reload role for update option
 *
 * @param $option
 */
function lana_demo_update_option_reload_role( $option ) {

	$role_update_options = array(
		'lana_demo_role_edit_posts',
		'lana_demo_role_edit_pages',
		'lana_demo_role_upload_files'
	);

	if ( ! in_array( $option, $role_update_options ) ) {
		return;
	}

	remove_role( 'lana_demo' );
	lana_demo_add_role();
}

add_action( 'added_option', 'lana_demo_update_option_reload_role' );
add_action( 'updated_option', 'lana_demo_update_option_reload_role' );

/**
 * Update Option - username
 *
 * @param $new_username
 * @param $old_username
 *
 * @return mixed
 */
function lana_demo_update_option_username( $new_username, $old_username ) {

	$user_id = get_option( 'lana_demo_user_id', false );

	/**
	 * Add new user
	 */
	if ( ! $user_id ) {
		$userdata = array(
			'user_login' => $new_username,
			'user_pass'  => get_option( 'lana_demo_password', null ),
			'role'       => 'lana_demo'
		);

		$user_id = wp_insert_user( $userdata );

		if ( ! is_wp_error( $user_id ) ) {
			add_option( 'lana_demo_user_id', $user_id );

			return $new_username;
		}
	}

	return $old_username;
}

/**
 * Update Option - password
 *
 * @param $new_password
 * @param $old_password
 *
 * @return mixed
 */
function lana_demo_update_option_password( $new_password, $old_password ) {

	$user_id = get_option( 'lana_demo_user_id', false );

	/**
	 * Edit password
	 */
	if ( $user_id ) {

		$userdata = array(
			'ID'        => $user_id,
			'user_pass' => $new_password
		);

		$user_id = wp_update_user( $userdata );

		if ( ! is_wp_error( $user_id ) ) {
			return $new_password;
		}
	}

	return $old_password;
}

/**
 * Lana Demo
 * add settings page
 */
function lana_demo_admin_menu() {
	add_options_page( __( 'Lana Demo Settings', 'lana-demo' ), __( 'Lana Demo', 'lana-demo' ), 'manage_options', 'lana-demo-settings.php', 'lana_demo_settings_page' );

	/** call register settings function */
	add_action( 'admin_init', 'lana_demo_register_settings' );
}

add_action( 'admin_menu', 'lana_demo_admin_menu' );

/**
 * Register settings
 */
function lana_demo_register_settings() {
	register_setting( 'lana-demo-settings-group', 'lana_demo_username' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_password' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_role_edit_posts' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_role_edit_pages' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_role_upload_files' );
	register_setting( 'lana-demo-settings-group', 'lana_demo_widget_status' );
}

/**
 * Lana Demo Settings page
 */
function lana_demo_settings_page() {
	?>
    <div class="wrap">
        <h2><?php _e( 'Lana Demo Settings', 'lana-demo' ); ?></h2>

        <hr/>
        <a href="<?php echo esc_url( 'http://lana.codes/' ); ?>" target="_blank">
            <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/assets/img/plugin-header.png' ); ?>"
                 alt="<?php esc_attr_e( 'Lana Codes', 'lana-demo' ); ?>"/>
        </a>
        <hr/>

        <form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
			<?php settings_fields( 'lana-demo-settings-group' ); ?>

            <h2 class="title"><?php _e( 'User Settings', 'lana-demo' ); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="lana-demo-username">
							<?php _e( 'Username', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="lana_demo_username" id="lana-demo-username"
                               value="<?php echo esc_attr( get_option( 'lana_demo_username', '' ) ); ?>" <?php disabled( get_option( 'lana_demo_user_id', false ) ); ?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="lana-demo-password">
							<?php _e( 'Password', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="lana_demo_password" id="lana-demo-password"
                               value="<?php echo esc_attr( get_option( 'lana_demo_password', '' ) ); ?>"/>
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php _e( 'Role Settings', 'lana-demo' ); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="lana-demo-role-edit-posts">
							<?php _e( 'Edit Posts', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_demo_role_edit_posts" id="lana-demo-role-edit-posts">
                            <option value="0"
								<?php selected( get_option( 'lana_demo_role_edit_posts', '0' ), '0' ); ?>>
								<?php _e( 'Disabled', 'lana-demo' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_demo_role_edit_posts', '0' ), '1' ); ?>>
								<?php _e( 'Enabled', 'lana-demo' ); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="lana-demo-role-edit-pages">
							<?php _e( 'Edit Pages', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_demo_role_edit_pages" id="lana-demo-role-edit-pages">
                            <option value="0"
								<?php selected( get_option( 'lana_demo_role_edit_pages', '0' ), '0' ); ?>>
								<?php _e( 'Disabled', 'lana-demo' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_demo_role_edit_pages', '0' ), '1' ); ?>>
								<?php _e( 'Enabled', 'lana-demo' ); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="lana-demo-role-upload-files">
							<?php _e( 'Upload Files', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_demo_role_upload_files" id="lana-demo-role-upload-files">
                            <option value="0"
								<?php selected( get_option( 'lana_demo_role_upload_files', '0' ), '0' ); ?>>
								<?php _e( 'Disabled', 'lana-demo' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_demo_role_upload_files', '0' ), '1' ); ?>>
								<?php _e( 'Enabled', 'lana-demo' ); ?>
                            </option>
                        </select>
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php _e( 'Widget Settings', 'lana-demo' ); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="lana-demo-widget-status">
							<?php _e( 'Widget Status', 'lana-demo' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_demo_widget_status" id="lana-demo-widget-status">
                            <option value="0"
								<?php selected( get_option( 'lana_demo_widget_status', '0' ), '0' ); ?>>
								<?php _e( 'Disabled', 'lana-demo' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_demo_widget_status', '0' ), '1' ); ?>>
								<?php _e( 'Enabled', 'lana-demo' ); ?>
                            </option>
                        </select>
						<?php if ( get_option( 'lana_demo_widget_status', '0' ) ): ?>
                            <p class="description">
								<?php echo sprintf( __( 'You can edit widgets in <a href="%s">Settings > Lana Demo Widgets</a>.', 'lana-demo' ), esc_url( admin_url( 'edit.php?post_type=lana_demo_widget' ) ) ); ?>
                            </p>
						<?php endif; ?>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary"
                       value="<?php esc_attr_e( 'Save Changes', 'lana-demo' ); ?>"/>
            </p>

        </form>
    </div>
	<?php
}

/**
 * Add custom post types and taxonomies
 * - Widget
 */
function lana_demo_post_types() {

	/**
	 * Widget
	 */
	if ( get_option( 'lana_demo_widget_status', '0' ) ) {
		register_post_type( 'lana_demo_widget', array(
			'label'             => __( 'Widget', 'lana-demo' ),
			'labels'            => array(
				'name'           => __( 'Widgets', 'lana-demo' ),
				'singular_name'  => __( 'Widget', 'lana-demo' ),
				'menu_name'      => __( 'Widgets', 'lana-demo' ),
				'name_admin_bar' => __( 'Widgets', 'lana-demo' ),
				'new_item'       => __( 'New Widget', 'lana-demo' ),
				'edit_item'      => __( 'Edit Widget', 'lana-demo' ),
				'view_item'      => __( 'View Widget', 'lana-demo' ),
				'all_items'      => __( 'All Widgets', 'lana-demo' )
			),
			'capabilities'      => array(
				'edit_post'          => 'manage_options',
				'read_post'          => 'manage_options',
				'delete_post'        => 'manage_options',
				'edit_posts'         => 'manage_options',
				'edit_others_posts'  => 'manage_options',
				'publish_posts'      => 'manage_options',
				'read_private_posts' => 'manage_options',
				'create_posts'       => 'manage_options',
			),
			'public'            => false,
			'show_in_menu'      => false,
			'show_in_nav_menus' => false,
			'show_ui'           => true,
			'menu_position'     => 37,
			'supports'          => array( 'title', 'editor', 'page-attributes' ),
			'menu_icon'         => 'dashicons-feedback'
		) );
	}
}

add_action( 'init', 'lana_demo_post_types' );

/**
 * Lana Demo
 * add widgets settings page
 */
function lana_demo_widgets_admin_menu() {

	if ( ! get_option( 'lana_demo_widget_status', '0' ) ) {
		return;
	}

	add_options_page( __( 'Lana Demo Widgets', 'lana-demo' ), __( 'Lana Demo Widgets', 'lana-demo' ), 'manage_options', 'edit.php?post_type=lana_demo_widget' );
}

add_action( 'admin_menu', 'lana_demo_widgets_admin_menu' );

/**
 * Returns the translated role of the current user.
 * If that user has no role for the current blog, it returns false.
 * @return string The name of the current role
 **/
function lana_demo_get_current_user_role() {
	global $wp_roles;

	$current_user = wp_get_current_user();
	$roles        = $current_user->roles;
	$role         = array_shift( $roles );

	if ( isset( $wp_roles->role_names[ $role ] ) ) {
		return $role;
	}

	return false;
}

/**
 * Login styles
 */
function lana_demo_login_styles() {
	wp_register_style( 'lana-demo-login', plugin_dir_url( __FILE__ ) . '/assets/css/lana-demo-login.css', array(), LANA_DEMO_VERSION );
	wp_enqueue_style( 'lana-demo-login' );
}

add_action( 'login_enqueue_scripts', 'lana_demo_login_styles' );

/**
 * Login message
 * welcome message, username and password
 */
function lana_demo_login_message() {

	if ( ! get_option( 'lana_demo_user_id', false ) ) {
		return;
	}

	if ( ! get_option( 'lana_demo_username', false ) || empty( get_option( 'lana_demo_username' ) ) ) {
		return;
	}

	if ( ! get_option( 'lana_demo_password', false ) || empty( get_option( 'lana_demo_password' ) ) ) {
		return;
	}

	?>
    <div class="demo-login-message">
        <p>
            <strong>
				<?php echo sprintf( __( 'Welcome to %s demo.', 'lana-demo' ), get_bloginfo( 'name' ) ); ?>
            </strong>
        </p>

        <p>
			<?php echo __( 'Username', 'lana-demo' ); ?>: <?php echo get_option( 'lana_demo_username', '' ); ?>
            <br/>
			<?php echo __( 'Password', 'lana-demo' ); ?>: <?php echo get_option( 'lana_demo_username', '' ); ?>
        </p>
    </div>
	<?php
}

add_filter( 'login_message', 'lana_demo_login_message' );

/**
 * Add a widget to the dashboard
 */
function lana_demo_add_dashboard_widgets() {

	if ( lana_demo_get_current_user_role() != 'lana_demo' ) {
		return;
	}

	if ( ! get_option( 'lana_demo_widget_status', '0' ) ) {
		return;
	}

	/** @var WP_Post[] $lana_demo_widgets */
	$lana_demo_widgets = get_posts( array(
		'post_type'   => 'lana_demo_widget',
		'post_status' => 'publish',
		'numberposts' => - 1,
		'orderby'     => 'menu_order',
		'order'       => 'ASC'
	) );

	if ( $lana_demo_widgets ) {
		foreach ( $lana_demo_widgets as $lana_demo_widget ) {
			wp_add_dashboard_widget( 'lana_demo_widget_' . $lana_demo_widget->ID, $lana_demo_widget->post_title, 'lana_demo_add_dashboard_widget_content', null, array( 'content' => $lana_demo_widget->post_content ) );
		}
	}
}

add_action( 'wp_dashboard_setup', 'lana_demo_add_dashboard_widgets' );

/**
 * Create the function to output the contents of Widget
 *
 * @param $post
 * @param $callback_args
 */
function lana_demo_add_dashboard_widget_content( $post, $callback_args ) {
	echo wpautop( $callback_args['args']['content'] );
}

/**
 * Remove dashboard elements in demo user
 */
function lana_demo_remove_dashboard_meta() {

	if ( lana_demo_get_current_user_role() == 'lana_demo' ) {

		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	}
}

add_action( 'admin_init', 'lana_demo_remove_dashboard_meta' );

/**
 * Remove menus in demo user
 */
function lana_demo_remove_menus() {

	if ( lana_demo_get_current_user_role() == 'lana_demo' ) {
		remove_menu_page( 'profile.php' );
		remove_menu_page( 'tools.php' );
		remove_menu_page( 'edit-comments.php' );
	}
}

add_action( 'admin_menu', 'lana_demo_remove_menus' );

/**
 * Disable user profile for demo user
 */
function lana_demo_disable_user_profile() {
	global $pagenow;

	$user_pages = array( 'profile.php', 'user-edit.php' );

	if ( in_array( $pagenow, $user_pages ) && lana_demo_get_current_user_role() == 'lana_demo' ) {
		wp_redirect( admin_url() );
		exit;
	}
}

add_action( 'admin_init', 'lana_demo_disable_user_profile' );