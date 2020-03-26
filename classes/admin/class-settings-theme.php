<?php
namespace lsx\business_directory\classes\admin;

use CMB_Tab_Field;

/**
 * Houses the functions for the CMB2 Settings page.
 *
 * @package lsx-business-directory
 */
class Settings_Theme {

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object \lsx\business_directory\classes\admin\Settings_Theme()
	 */
	protected static $instance = null;

	/**
	 * Will return true if this is the LSX BD settings page.
	 *
	 * @var array
	 */
	public $is_options_page = false;

	/**
	 * Holds the id and labels for the navigation.
	 *
	 * @var array
	 */
	public $navigation = array();

	/**
	 * Contructor
	 */
	public function __construct() {
		add_filter( 'cmb2_enqueue_css', array( $this, 'disable_cmb2_styles' ), 1, 1 );
		add_action( 'cmb2_before_form', array( $this, 'generate_navigation' ), 10, 4 );
		add_action( 'cmb2_before_title_field_row', array( $this, 'output_tab_open_div' ), 10, 1 );
		add_action( 'cmb2_after_tab_closing_field_row', array( $this, 'output_tab_closing_div' ), 10, 1 );
		add_action( 'cmb2_render_tab_closing', array( $this, 'cmb2_render_callback_for_tab_closing' ), 10, 5 );
		add_filter( 'cmb2_sanitize_tab_closing', array( $this, 'cmb2_sanitize_tab_closing_callback' ), 10, 2 );
		add_action( 'cmb2_after_form', array( $this, 'navigation_js' ), 10, 4 );
		add_filter( 'cmb2_options_page_redirect_url', array( $this, 'add_tab_argument' ), 10, 1 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \lsx\business_directory\classes\admin\Settings_Theme()    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Disable CMB2 styles on front end forms.
	 *
	 * @return bool $enabled Whether to enable (enqueue) styles.
	 */
	public function disable_cmb2_styles( $enabled ) {
		if ( is_admin() ) {
			$current_screen = get_current_screen();
			if ( is_object( $current_screen ) && 'business-directory_page_lsx-business-directory-settings' === $current_screen->id ) {
				$enabled = false;
			}
		}
		return $enabled;
	}

	/**
	 * Generates the tabbed navigation for the settings page.
	 *
	 * @param string $cmb_id
	 * @param string $object_id
	 * @param string $object_type
	 * @param object $cmb2_obj
	 * @return void
	 */
	public function generate_navigation( $cmb_id, $object_id, $object_type, $cmb2_obj ) {
		if ( 'lsx_bd_settings' === $cmb_id && 'lsx-business-directory-settings' === $object_id && 'options-page' === $object_type ) {
			$this->navigation      = array();
			$this->is_options_page = true;
			if ( isset( $cmb2_obj->meta_box['fields'] ) && ! empty( $cmb2_obj->meta_box['fields'] ) ) {
				foreach ( $cmb2_obj->meta_box['fields'] as $field_index => $field ) {
					if ( 'title' === $field['type'] ) {
						$this->navigation[ $field_index ] = $field['name'];
					}
				}
			}
			$this->output_navigation();
		}
	}

	/**
	 * Outputs the WP style navigation for the Settings page.
	 *
	 * @return void
	 */
	public function output_navigation() {
		if ( ! empty( $this->navigation ) ) {
			?>
			<div class="wp-filter hide-if-no-js">
				<ul class="filter-links">
					<?php
					$first_tab = true;
					$total = count( $this->navigation );
					$count = 0;
					$separator = ' |';
					foreach ( $this->navigation as $key => $label ) {
						$count++;
						$current_css = '';
						if ( true === $first_tab ) {
							$first_tab   = false;
							$current_css = 'current';
						}
						if ( $count === $total ) {
							$separator = '';
						}
						?>
							<li><a href="#" class="<?php echo esc_attr( $current_css ); ?>" data-sort="<?php echo esc_attr( $key ); ?>_tab"><?php echo esc_attr( $label ); ?></a><?php echo esc_attr( $separator ); ?></li>
						<?php
					}
					?>
				</ul>
			</div>
			<?php
		}
	}

	/**
	 * Outputs the opening tab div.
	 *
	 * @param object $field CMB2_Field();
	 * @return void
	 */
	public function output_tab_open_div( $field ) {
		if ( true === $this->is_options_page && isset( $field->args['type'] ) && 'title' === $field->args['type'] ) {
			?>
			<div id="<?php echo esc_attr( $field->args['id'] ); ?>_tab" class="tab tab-nav">
			<?php
		}
	}

	/**
	 * Outputs the opening closing div.
	 *
	 * @param object $field CMB2_Field();
	 * @return void
	 */
	public function output_tab_closing_div( $field ) {
		if ( true === $this->is_options_page && isset( $field->args['type'] ) && 'tab_closing' === $field->args['type'] ) {
			?>
			</div>
			<?php
		}
	}

	public function cmb2_render_callback_for_tab_closing( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		return;
	}

	public function cmb2_sanitize_tab_closing_callback( $override_value, $value ) {
		return '';
	}

	/**
	 * Outputs the Script for the tabbed navigation.
	 *
	 * @param string $cmb_id
	 * @param string $object_id
	 * @param string $object_type
	 * @param object $cmb2_obj
	 * @return void
	 */
	public function navigation_js( $cmb_id, $object_id, $object_type, $cmb2_obj ) {
		if ( 'lsx_bd_settings' === $cmb_id && 'lsx-business-directory-settings' === $object_id && 'options-page' === $object_type ) {
			?>
			<script>
				var LSX_BD_CMB2 = Object.create( null );

				;( function( $, window, document, undefined ) {

					'use strict';

					LSX_BD_CMB2.document = $(document);

					/**
					 * Start the JS Class
					 */
					LSX_BD_CMB2.init = function() {
						var tab = LSX_BD_CMB2.urlParam( 'cmb_tab' );
						if ( 0 === tab || '0' === tab ) {
							tab = '';
						}
						LSX_BD_CMB2.addTabInput( tab );
						LSX_BD_CMB2.prepNavigation( tab );
						LSX_BD_CMB2.watchNavigation();
					};

					LSX_BD_CMB2.addTabInput = function( tab = '' ) {
						var counter = 1;
						$( "form.cmb-form" ).append('<input type="hidden" name="cmb_tab" value="' + tab + '" />');
					}

					LSX_BD_CMB2.prepNavigation = function( tab = '' ) {
						var counter = 1;
						$( ".tab.tab-nav" ).each(function(){
							console.log( tab );
							if ( ( 1 !== counter && '' === tab ) || ( '' !== tab && 'settings_' + tab + '_tab' !== $( this ).attr('id') ) ) {
								$( this ).hide();
							} else {
								$( this ).addClass( 'current' );
							}
							counter++;
						});
					}

					LSX_BD_CMB2.watchNavigation = function() {
						$( ".wp-filter li a" ).on( 'click', function(event){
							event.preventDefault();
							// Change the current Tab heading.
							$( ".wp-filter li a" ).removeClass('current');
							$( this ).addClass('current');

							// Change the current tab div.
							var target = $( this ).attr('data-sort');
							$( ".tab.tab-nav.current" ).hide().removeClass('current');
							$( "#"+target ).show().addClass('current');

							console.log(target);
						});
					};

					LSX_BD_CMB2.urlParam = function(name){
						var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
						if ( results == null ){
							return 0;
						} else {
							return results[1] || 0;
						}
					}

					LSX_BD_CMB2.document.ready( function() {
						LSX_BD_CMB2.init();
					} );

				} )( jQuery, window, document );
			</script>
			<?php
		}
	}

	/**
	 * This will add the tab selection to the url.
	 *
	 * @param string $url
	 * @return void
	 */
	public function add_tab_argument( $url ) {
		if ( isset( $_POST['cmb2_tab_selection'] ) ) {
			$url = add_query_arg( 'cmb-tab', $_POST['cmb2_tab_selection'], $url );
		}
		return $url;
	}
}
