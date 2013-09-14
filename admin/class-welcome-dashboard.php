<?php

/**
 *	Weclome Page Class
 *
 *	Adapted from code in EDD (Copyright (c) 2012, Pippin Williamson) and WP.
 *
 *	@author	Nate Jacobs
 *	@date	9/12/13
 * 	@since	1.4.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *	Brickset Welcome Class
 *
 *	Used to display a welcome page listing updates upon upgrade
 *
 *	@author	Nate Jacobs
 *	@date	9/12/13
 *	@since 	1.4.0
 */
class BricksetWelcome {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 *	Start things off when class is instantiated
	 *
	 *	@author	Nate Jacobs
	 *	@date	9/12/13
	 *	@since 	1.4.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	/**
	 * 	Add the admin dashboard pages
	 *
	 *	@date	9/12/13
	 *	@since 	1.4.0
	 */
	public function admin_menus() {
		add_dashboard_page(
			__( 'Welcome to Brickset API', 'bs_api' ),
			__( 'Welcome to Brickset API', 'bs_api' ),
			$this->minimum_capability,
			'bs-about',
			array( $this, 'about_screen' )
		);

		add_dashboard_page(
			__( 'Welcome to Brickset API', 'bs_api' ),
			__( 'Welcome to Brickset API', 'bs_api' ),
			$this->minimum_capability,
			'bs-credits',
			array( $this, 'credits_screen' )
		);
	}

	/**
	 * 	Hide Individual Dashboard Pages
	 *
	 *	@date	9/12/13
	 *	@since 	1.4.0
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'bs-about' );
		remove_submenu_page( 'index.php', 'bs-credits' );
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/
		.bs-welcome-screenshots {
			float: right;
			margin-left: 10px!important;
		}
		/*]]>*/
		</style>
		<?php
	}

	/**
	 * 	Render About Screen
	 *
	 *	@date	9/12/13
	 *	@since 	1.4.0
	 */
	public function about_screen() 
	{
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Brickset API %s', 'bs_api' ), BRICKSET_API_VERSION ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Brickset API helps you display and manage your LEGO&#174; collection right from your WordPress site.', 'bs_api' ), BRICKSET_API_VERSION ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'bs-about' ), 'index.php' ) ) ); ?>">
					<?php printf( __( "What's New in %s", 'bs_api' ), BRICKSET_API_VERSION ); ?>
				</a><a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'bs-credits' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Credits', 'bs_api' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'New Widgets', 'bs_api' );?></h3>
				<div class="feature-section">
					<img src="<?php echo BRICKSET_API_URI . 'assets/images/theme-years-widget.png'; ?>" class="bs-welcome-screenshots"/>
					<h4><?php _e( 'Count of Owned Minifigs','bs_api' );?></h4>
					<p><?php _e( 'Show off the total count of all the minifigs you own.', 'bs_api' );?></p>

					<h4><?php _e( 'Years a Theme was Available', 'bs_api' );?></h4>
					<p><?php _e( 'Display how many sets were made each year a theme was available. A link is included to the theme page on Brickset.com', 'bs_api' );?></p>


				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'For the Developers', 'bs_api' );?></h3>
				<div class="feature-section">
					<h4><?php _e( 'Transient Filters', 'bs_api' );?></h4>
					<p><?php _e( 'You can now override the default caching of the Brickset API using add_filter. By default the plugin caches Brickset data for 24 hours to reduce page load times. By increasing the time the data is stored it requires fewer requests to Brickset. However, if the data changes frequently then your site will display out-of-date data until the cache expires.', 'bs_api' );?></p>
					<code>add_filter('bs_get_themes_transient', 'bs_api_change_theme_cache_time');</code>

					<h4><?php _e( 'Filter Shortcodes', 'bs_api' );?></h4>
					<p><?php _e( 'The plugin now takes advantage of the feature added in WordPress 3.6 to enable filtering of the shortcode attributes. You can learn more by reading', 'bs_api' );?> <a href="http://markjaquith.wordpress.com/2013/04/04/wordpress-36-shortcode-attribute-filter/"><?php _e( 'this article', 'bs_api' ); ?></a> <?php _e( 'by WordPress lead developer Mark Jaquith.', 'bs_api' ); ?></p>

				</div>
			</div>
			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'brickset-api-options' ), 'options-general.php' ) ) ); ?>"><?php _e( 'Go to the Brickset API Settings', 'bs_api' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * 	Render Credits Screen
	 *
	 *	@date	9/12/13
	 *	@since 	1.4.0
	 */
	public function credits_screen() 
	{
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Brickset API %s', 'bs_api' ), BRICKSET_API_VERSION ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Brickset API helps you display and manage your LEGO&#174; collection right from your WordPress site.', 'bs_api' ), BRICKSET_API_VERSION ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'bs-about' ), 'index.php' ) ) ); ?>">
					<?php printf( __( "What's New in %s", 'bs_api' ), BRICKSET_API_VERSION ); ?>
				</a><a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'bs-credits' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Credits', 'bs_api' ); ?>
				</a>
			</h2>

			<p class="about-description"><?php _e( 'The Brickset API Plugin is created by the following developers.', 'bs_api' ); ?></p>

			<?php echo $this->contributors(); ?>
		</div>
		<?php
	}


	/**
	 * Render Contributors List
	 *
	 *	@date	9/12/13
	 *	@since 	1.4.0
	 */
	public function contributors() {
		$contributors = $this->get_contributors();

		if ( empty( $contributors ) )
			return '';

		$contributor_list = '<ul class="wp-people-group">';

		foreach ( $contributors as $contributor ) {
			$contributor_list .= '<li class="wp-person">';
			$contributor_list .= sprintf( '<a href="%s" title="%s">',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_html( sprintf( __( 'View %s', 'bs_api' ), $contributor->login ) )
			);
			$contributor_list .= sprintf( '<img src="%s" width="64" height="64" class="gravatar" alt="%s" />', esc_url( $contributor->avatar_url ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= sprintf( '<a class="web" href="%s">%s</a>', esc_url( 'https://github.com/' . $contributor->login ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= '</li>';
		}

		$contributor_list .= '</ul>';

		return $contributor_list;
	}

	/**
	 * Retreive list of contributors from GitHub.
	 *
	 *	@date	9/12/13
	 *	@since 	1.4.0
	 */
	public function get_contributors() {
		$contributors = get_transient( 'bs_api_contributors' );

		if ( false !== $contributors )
			return $contributors;

		$response = wp_remote_get( 'https://api.github.com/repos/NateJacobs/Brickset-API/contributors', array( 'sslverify' => false ) );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) )
			return array();

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_array( $contributors ) )
			return array();

		set_transient( 'bs_api_contributors', $contributors, 3600 );

		return $contributors;
	}

	/**
	 * Sends user to the Welcome page on first activation and upgrade
	 *
	 *	@date	9/12/13
	 *	@since 	1.4.0
	 */
	public function welcome() {

		// Bail if no activation redirect
		if ( ! get_transient( '_bs_api_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_bs_api_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		wp_safe_redirect( admin_url( 'index.php?page=bs-about' ) ); exit;
	}
}
new BricksetWelcome();