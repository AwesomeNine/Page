<?php
/**
 * Page class
 *
 * @since   1.0.0
 * @package Awesome9\Admin
 * @author  Awesome9 <me@awesome9.co>
 */

namespace Awesome9\Admin;

/**
 * Page class
 */
class Page {

	/**
	 * Unique ID used for menu_slug.
	 *
	 * @var string
	 */
	public $id = null;

	/**
	 * The text to be displayed in the title tags of the page.
	 *
	 * @var string
	 */
	public $title = null;

	/**
	 * The slug name for the parent menu.
	 *
	 * @var string
	 */
	public $parent = null;

	/**
	 * The The on-screen name text for the menu.
	 *
	 * @var string
	 */
	public $menu_title = null;

	/**
	 * The capability required for this menu to be displayed to the user.
	 *
	 * @var string
	 */
	public $capability = 'manage_options';

	/**
	 * The icon for this menu.
	 *
	 * @var string
	 */
	public $icon = 'dashicons-art';

	/**
	 * The position in the menu order this menu should appear.
	 *
	 * @var int
	 */
	public $position = -1;

	/**
	 * The function/file that displays the page content for the menu page.
	 *
	 * @var string|callable
	 */
	public $render = null;

	/**
	 * The function that run on page POST to save data.
	 *
	 * @var callable
	 */
	public $onsave = null;

	/**
	 * Hold contextual help tabs.
	 *
	 * @var array
	 */
	public $help = null;

	/**
	 * Hold scripts and styles.
	 *
	 * @var array
	 */
	public $assets = null;

	/**
	 * Check if plugin is network active.
	 *
	 * @var array
	 */
	public $is_network = false;

	/**
	 * Hold classes for body tag.
	 *
	 * @var array
	 */
	public $classes = null;

	/**
	 * Resulting page hook suffix.
	 *
	 * @var bool|string
	 */
	public $hook_suffix = false;

	/**
	 * The Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id     Admin page unique id.
	 * @param string $title  Title of the admin page.
	 * @param array  $config Optional. Override page settings.
	 */
	public function __construct( $id, $title, $config = [] ) {

		// Early bail!
		if ( ! $id ) {
			wp_die( esc_html( '$id variable required' ), esc_html( 'Variable Required' ) );
		}

		if ( ! $title ) {
			wp_die( esc_html( '$title variable required' ), esc_html( 'Variable Required' ) );
		}

		$this->id    = $id;
		$this->title = $title;
		foreach ( $config as $key => $value ) {
			$this->$key = $value;
		}

		if ( ! $this->menu_title ) {
			$this->menu_title = $title;
		}

		add_action( 'init', [ $this, 'hooks' ], 25 );
	}

	/**
	 * Bind all hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		$priority = $this->parent ? intval( $this->position ) : -1;
		add_action( $this->is_network ? 'network_admin_menu' : 'admin_menu', [ $this, 'register_page' ], $priority );

		if ( $this->is_current_page() && ! is_null( $this->onsave ) && is_callable( $this->onsave ) ) {
			add_action( 'admin_init', [ $this, 'save' ] );
		}
	}

	/**
	 * Register page and hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_page() {
		$this->hook_suffix = $this->register_menu();

		// Early Bail!!
		if ( ! $this->hook_suffix ) {
			return;
		}

		if ( ! empty( $this->classes ) ) {
			add_action( 'admin_body_class', [ $this, 'body_class' ] );
		}

		if ( ! empty( $this->help ) ) {
			add_action( 'admin_head-' . $this->hook_suffix, [ $this, 'contextual_help' ] );
		}

		if ( ! empty( $this->assets ) ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		}
	}

	/**
	 * Register Admin Menu.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|string
	 */
	public function register_menu() {
		if ( ! $this->parent ) {
			return add_menu_page( $this->title, $this->menu_title, $this->capability, $this->id, [ $this, 'display' ], $this->icon, $this->position );
		}

		return add_submenu_page( $this->parent, $this->title, $this->menu_title, $this->capability, $this->id, [ $this, 'display' ] );
	}

	/**
	 * Render admin page content using render function you passed in config.
	 *
	 * @since 1.0.0
	 */
	public function display() {
		if ( is_null( $this->render ) ) {
			return;
		}

		if ( is_callable( $this->render ) ) {
			call_user_func( $this->onrender, $this );
			return;
		}

		include_once $this->render;
	}

	/**
	 * Save anything you want using onsave function.
	 *
	 * @since 1.0.0
	 */
	public function save() {
		call_user_func( $this->onsave, $this );
	}

	/**
	 * Add classes to <body> of WordPress admin.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $classes Space-separated list of CSS classes.
	 * @return string
	 */
	public function body_class( $classes = '' ) {
		return $classes . ' ' . join( ' ', $this->classes );
	}

	/**
	 * Contextual Help.
	 *
	 * @since 1.0.0
	 */
	public function contextual_help() {
		$screen = get_current_screen();

		foreach ( $this->help as $tab_id => $tab ) {
			$tab['id']      = $tab_id;
			$tab['content'] = $this->get_help_content( $tab );
			$screen->add_help_tab( $tab );
		}
	}

	/**
	 * Enqueue styles and scripts.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue( $hook_suffix ) {

		// Early Bail!!
		if ( $this->hook_suffix !== $hook_suffix ) {
			return;
		}

		foreach ( $this->assets  as $asset ) {
			$asset->enqueue();
		}
	}

	/**
	 * Is the page is currrent page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_current_page() {
		return filter_input( INPUT_GET, 'page' ) === $this->id;
	}

	/**
	 * Get tab content
	 *
	 * @since 1.0.0
	 *
	 * @param  array $tab Tab to get content for.
	 * @return string
	 */
	private function get_help_content( $tab ) {
		ob_start();

		// If it is a function.
		if ( isset( $tab['content'] ) && is_callable( $tab['content'] ) ) {
			call_user_func( $tab['content'] );
		}

		// If it is a file.
		if ( isset( $tab['view'] ) && $tab['view'] ) {
			require $tab['view'];
		}

		return ob_get_clean();
	}
}
