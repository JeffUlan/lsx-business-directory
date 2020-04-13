<?php
namespace lsx\business_directory\classes\integrations;

use Yoast\WP\SEO\WordPress\Integration;

/**
 * Woocommerce Integration class
 *
 * @package lsx-business-directory
 */
class Woocommerce {

	/**
	 * Holds class instance
	 *
	 * @var      object \lsx\business_directory\classes\Woocommerce()
	 */
	protected static $instance = null;

	/**
	 * Holds the form handler class
	 *
	 * @var      object \lsx\business_directory\classes\integrations\woocommerce\Form_Handler()
	 */
	public $form_handler = null;

	/**
	 * Holds the array of WC query vars
	 *
	 * @var array()
	 */
	public $query_vars = array();

	/**
	 * Contructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5 );
		require_once LSX_BD_PATH . '/classes/integrations/woocommerce/class-form-handler.php';
		$this->form_handler = woocommerce\Form_Handler::get_instance();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \lsx\business_directory\classes\Woocommerce()    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initiator
	 */
	public function init() {
		if ( function_exists( 'WC' ) ) {
			$this->init_query_vars();
			add_filter( 'woocommerce_get_query_vars', array( $this, 'add_query_vars' ) );
			add_filter( 'woocommerce_account_menu_items', array( $this, 'register_my_account_tabs' ) );
			add_action( 'woocommerce_account_listings_endpoint', array( $this, 'endpoint_content' ) );
			add_action( 'woocommerce_account_add-listing_endpoint', array( $this, 'endpoint_content' ) );
			add_action( 'woocommerce_account_edit-listing_endpoint', array( $this, 'endpoint_content' ) );
			add_action( 'lsx_bd_settings_section_translations', array( $this, 'register_translations' ), 10, 2 );
			add_filter( 'woocommerce_account_menu_item_classes', array( $this, 'menu_item_classes' ), 10, 2 );
			add_filter( 'woocommerce_form_field_text', array( $this, 'replace_image_field' ), 10, 4 );
			add_filter( 'woocommerce_form_field_text', array( $this, 'replace_image_id_field' ), 10, 4 );
		}
	}

	/**
	 * Init query vars by loading options.
	 *
	 * @since 2.0
	 */
	public function init_query_vars() {
		$this->query_vars = array(
			'listings'     => lsx_bd_get_option( 'translations_listings_endpoint', 'listings' ),
			'add-listing'  => lsx_bd_get_option( 'translations_listings_add_endpoint', 'add-listing' ),
			'edit-listing' => lsx_bd_get_option( 'translations_listings_edit_endpoint', 'edit-listing' ),
		);
	}

	/**
	 * Hooks into `woocommerce_get_query_vars` to make sure query vars defined in
	 * this class are also considered `WC_Query` query vars.
	 *
	 * @param  array $query_vars
	 * @return array
	 */
	public function add_query_vars( $query_vars ) {
		return array_merge( $query_vars, $this->query_vars );
	}

	/**
	 * Registers the My Listing My account tab
	 *
	 * @param  array $menu_links
	 * @return void
	 */
	public function register_my_account_tabs( $menu_links ) {
		$new_links  = array(
			lsx_bd_get_option( 'translations_listings_endpoint', 'listings' ) => __( 'Listings', 'lsx-business-directory' ),
		);
		$menu_links = array_slice( $menu_links, 0, 1, true ) + $new_links + array_slice( $menu_links, 1, null, true );
		return $menu_links;
	}

	/**
	 * Highlight the listings menu item if you are adding or editing a listing.
	 *
	 * @param  array $classes
	 * @param  string $endpoint
	 * @return array
	 */
	public function menu_item_classes( $classes, $endpoint ) {
		global $wp;
		if ( lsx_bd_get_option( 'translations_listings_endpoint', 'listings' ) === $endpoint && ( isset( $wp->query_vars['add-listing'] ) || isset( $wp->query_vars['edit-listing'] ) ) ) {
			$classes[] = 'is-active';
		}
		return $classes;
	}

	/**
	 * Gets the endpoint content
	 *
	 * @return void
	 */
	public function endpoint_content() {
		lsx_business_template( 'woocommerce/listings' );
	}

	/**
	 * Configure Business Directory custom fields for the Settings page Translations section.
	 *
	 * @param object $cmb new_cmb2_box().
	 * @return void
	 */
	public function register_translations( $cmb, $place ) {
		if ( 'bottom' === $place ) {
			$cmb->add_field(
				array(
					'name'    => esc_html__( 'Listings Endpoint', 'lsx-business-directory' ),
					'id'      => 'translations_listings_endpoint',
					'type'    => 'text',
					'default' => 'listings',
					'desc'    => __( 'This is the endpoint for the My Account "Listings" page.', 'lsx-business-directory' ),
				)
			);
			$cmb->add_field(
				array(
					'name'    => esc_html__( 'Add Listing Endpoint', 'lsx-business-directory' ),
					'id'      => 'translations_listings_add_endpoint',
					'type'    => 'text',
					'default' => 'add-listing',
					'desc'    => __( 'This is the endpoint for the My Account "Add Listing" page.', 'lsx-business-directory' ),
				)
			);
			$cmb->add_field(
				array(
					'name'    => esc_html__( 'Edit Listing Endpoint', 'lsx-business-directory' ),
					'id'      => 'translations_listings_edit_endpoint',
					'type'    => 'text',
					'default' => 'edit-listing',
					'desc'    => __( 'This is the endpoint for the My Account "Edit Listing" page.', 'lsx-business-directory' ),
				)
			);
		}
	}

	/**
	 * Change the post_thumbnail into a file upload field.
	 *
	 * @param string $field
	 * @param string $key
	 * @param array $args
	 * @param string $value
	 * @return string
	 */
	public function replace_image_field( $field, $key, $args, $value ) {
		if ( in_array( $key, array( 'lsx_bd_thumbnail', 'lsx_bd_banner' ) ) ) {
			$field = '';
		}
		return $field;
	}

	/**
	 * Change the post_thumbnail ID into a hidden field with a thumbnail if set.
	 *
	 * @param string $field
	 * @param string $key
	 * @param array $args
	 * @param string $value
	 * @return string
	 */
	public function replace_image_id_field( $field, $key, $args, $value ) {
		if ( in_array( $key, array( 'lsx_bd__thumbnail_id', 'lsx_bd_banner_id' ) ) ) {
			$field = str_replace( 'woocommerce-input-wrapper', 'woocommerce-file-wrapper', $field );
			$field = str_replace( 'type="text"', 'type="hidden"', $field );

			$image = '';
			if ( ! empty( $value ) && '' !== $value ) {
				$image      .= '<input type="file" class="input-text form-control hidden" name="' . esc_attr( $args['id'] ) . '_upload" id="' . esc_attr( $args['id'] ) . '_upload" placeholder="" value="">';
				$temp_image = wp_get_attachment_image_src( $value, 'lsx-thumbnail-wide' );
				$image_src  = ( strpos( $image[0], 'cover-logo.png' ) === false ) ? $temp_image[0] : '';
				if ( '' !== $image_src ) {
					$image .= '<img src="' . $image_src . '">';
				}
				$image .= '<a class="remove-image" href="#"><i class="fa fa-close"></i> ' . __( 'Remove image', 'lsx-business-directory' ) . '</a>';
			} else {
				$image .= '<input type="file" class="input-text form-control" name="' . esc_attr( $args['id'] ) . '_upload" id="' . esc_attr( $args['id'] ) . '_upload" placeholder="" value="">';
				$image .= '<a class="remove-image hidden" href="#"><i class="fa fa-close"></i> ' . __( 'Remove image', 'lsx-business-directory' ) . '</a>';
			}

			$field = str_replace( '<span class="woocommerce-file-wrapper">', $image . '<span class="woocommerce-file-wrapper">', $field );
		}
		return $field;
	}

	/**
	 * Register and enqueue front-specific style sheet.
	 *
	 * @since 1.0.0
	 *
	 * @return    null
	 */
	public function enqueue_scripts() {
		if ( defined( 'SCRIPT_DEBUG' ) ) {
			$prefix = 'src/';
			$suffix = '';
		} else {
			$prefix = '';
			$suffix = '.min';
		}
		$dependacies = array( 'jquery', 'lsx-bd-frontend' );
		wp_enqueue_script( 'lsx-bd-listing-form', LSX_BD_URL . 'assets/js/' . $prefix . 'lsx-bd-listing-form' . $suffix . '.js', $dependacies, LSX_BD_VER, true );
		/*$param_array = array(
			'api_key'     => $this->api_key,
			'google_url'  => $google_url,
			'placeholder' => $placeholder,
		);
		wp_localize_script( 'lsx-bd-frontend-maps', 'lsx_bd_maps_params', $param_array );*/
	}
}