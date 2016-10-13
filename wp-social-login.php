<?php
/*
Plugin Name: WordPress Social Login
Plugin URI: http://miled.github.io/wordpress-social-login/
Description: Allow your visitors to comment and login with social networks such as Twitter, Facebook, Google, Yahoo and more.
Version: 200.3.0
Author: Miled
Author URI: https://github.com/miled
License: MIT License
Text Domain: wordpress-social-login
Domain Path: /languages
*/

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// --------------------------------------------------------------------

session_id() or session_start();

global $WORDPRESS_SOCIAL_LOGIN_VERSION;
global $WORDPRESS_SOCIAL_LOGIN_PROVIDERS_CONFIG;
global $WORDPRESS_SOCIAL_LOGIN_COMPONENTS;
global $WORDPRESS_SOCIAL_LOGIN_ADMIN_TABS;

$WORDPRESS_SOCIAL_LOGIN_VERSION = "200.3.0";

$_SESSION["wsl::plugin"] = "WordPress Social Login " . $WORDPRESS_SOCIAL_LOGIN_VERSION;

// --------------------------------------------------------------------

/**
* This file might be used to :
*     1. Redefine WSL constants, so you can move WSL folder around.
*     2. Define WSL Pluggable PHP Functions. See http://miled.github.io/wordpress-social-login/developer-api-functions.html
*     5. Implement your WSL hooks.
*/
if( file_exists( WP_PLUGIN_DIR . '/wp-social-login-custom.php' ) )
{
	include_once( WP_PLUGIN_DIR . '/wp-social-login-custom.php' );
}

// --------------------------------------------------------------------

/**
* Define WSL constants, if not already defined
*/
defined( 'WORDPRESS_SOCIAL_LOGIN_ABS_PATH' )
	|| define( 'WORDPRESS_SOCIAL_LOGIN_ABS_PATH', plugin_dir_path( __FILE__ ) );

defined( 'WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL' )
	|| define( 'WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

defined( 'WORDPRESS_SOCIAL_LOGIN_HYBRIDAUTH_ENDPOINT_URL' )
	|| define( 'WORDPRESS_SOCIAL_LOGIN_HYBRIDAUTH_ENDPOINT_URL', WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL . 'hybridauth/' );

// --------------------------------------------------------------------

/**
* Check for Wordpress 3.0
*/
function wsl_activate()
{
	if( ! function_exists( 'register_post_status' ) )
	{
		deactivate_plugins( basename( dirname( __FILE__ ) ) . '/' . basename (__FILE__) );

		wp_die( __( "This plugin requires WordPress 3.0 or newer. Please update your WordPress installation to activate this plugin.", 'wordpress-social-login' ) );
	}
}

register_activation_hook( __FILE__, 'wsl_activate' );

// --------------------------------------------------------------------

/**
* Attempt to install/migrate/repair WSL upon activation
*
* Create wsl tables
* Migrate old versions
* Register default components
*/
function wsl_install()
{
	wsl_database_install();

	wsl_update_compatibilities();

	wsl_register_components();
}

register_activation_hook( __FILE__, 'wsl_install' );

// --------------------------------------------------------------------

/**
* Add a settings to plugin_action_links
*/
function wsl_add_plugin_action_links( $links, $file )
{
	static $this_plugin;

	if( ! $this_plugin )
	{
		$this_plugin = plugin_basename( __FILE__ );
	}

	if( $file == $this_plugin )
	{
		$wsl_links  = '<a href="options-general.php?page=wordpress-social-login">' . __( "Settings" ) . '</a>';

		array_unshift( $links, $wsl_links );
	}

	return $links;
}

add_filter( 'plugin_action_links', 'wsl_add_plugin_action_links', 10, 2 );

// --------------------------------------------------------------------

/**
* Add faq and user guide links to plugin_row_meta
*/
function wsl_add_plugin_row_meta( $links, $file )
{
	static $this_plugin;

	if( ! $this_plugin )
	{
		$this_plugin = plugin_basename( __FILE__ );
	}

	if( $file == $this_plugin )
	{
		$wsl_links = array(
			'<a href="http://miled.github.io/wordpress-social-login/">'             . _wsl__( "Docs"             , 'wordpress-social-login' ) . '</a>',
			'<a href="http://miled.github.io/wordpress-social-login/support.html">' . _wsl__( "Support"          , 'wordpress-social-login' ) . '</a>',
			'<a href="https://github.com/miled/wordpress-social-login">'            . _wsl__( "Fork me on Github", 'wordpress-social-login' ) . '</a>',
		);

		return array_merge( $links, $wsl_links );
	}

	return $links;
}

add_filter( 'plugin_row_meta', 'wsl_add_plugin_row_meta', 10, 2 );

// --------------------------------------------------------------------

/**
* Loads the plugin's translated strings.
*
* http://codex.wordpress.org/Function_Reference/load_plugin_textdomain
*/
if( ! function_exists( 'wsl_load_plugin_textdomain' ) )
{
	function wsl_load_plugin_textdomain()
	{
		load_plugin_textdomain( 'wordpress-social-login', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

add_action( 'plugins_loaded', 'wsl_load_plugin_textdomain' );

// --------------------------------------------------------------------

/**
* _e() wrapper
*/
function _wsl_e( $text, $domain )
{
	echo __( $text, $domain );
}

// --------------------------------------------------------------------

/**
* __() wrapper
*/
function _wsl__( $text, $domain )
{
	return __( $text, $domain );
}

// --------------------------------------------------------------------

/* includes */

# WSL Setup & Settings
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/settings/wsl.providers.php'            ); // List of supported providers (mostly provided by hybridauth library)
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/settings/wsl.database.php'             ); // Install/Uninstall WSL database tables
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/settings/wsl.initialization.php'       ); // Check WSL requirements and register WSL settings
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/settings/wsl.compatibilities.php'      ); // Check and upgrade WSL database/settings (for older versions)

# Services & Utilities
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/services/wsl.authentication.php'       ); // Authenticate users via social networks. <- that's the most important script
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/services/wsl.mail.notification.php'    ); // Emails and notifications
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/services/wsl.user.avatar.php'          ); // Display users avatar
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/services/wsl.user.data.php'            ); // User data functions (database related)
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/services/wsl.utilities.php'            ); // Unclassified functions & utilities
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/services/wsl.watchdog.php'             ); // WSL logging agent

# WSL Widgets & Front-end interfaces
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/widgets/wsl.auth.widgets.php'          ); // Authentication widget generators (where WSL widget/icons are displayed)
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/widgets/wsl.users.gateway.php'         ); // Accounts linking + Profile Completion
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/widgets/wsl.error.pages.php'           ); // Generate WSL notices end errors pages
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/widgets/wsl.loading.screens.php'       ); // Generate WSL loading screens

# WSL Admin interfaces
if( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) )
{
	require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'includes/admin/wsl.admin.ui.php'        ); // The entry point to WSL Admin interfaces
}

// --------------------------------------------------------------------
