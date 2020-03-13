<?php
namespace lsx\business_directory\classes\frontend;

/**
 * Banners Frontend Class
 *
 * @package lsx-business-directory
 */
class Banners {

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object \lsx\business_directory\classes\frontend\Banners()
	 */
	protected static $instance = null;

	/**
	 * This holds the current screen being displayed (single, archive or taxonomy ).
	 *
	 * @var string
	 */
	public $screen = '';

	/**
	 * Contructor
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'wp_head' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \lsx\business_directory\classes\frontend\Banners()    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Outputs the single
	 *
	 * @return void
	 */
	public function wp_head() {
		$this->set_screen();
		if ( '' !== $this->screen ) {
			remove_action( 'lsx_content_wrap_before', 'lsx_global_header' );
			add_filter( 'body_class', array( $this, 'banner_class' ), 10, 1 );
			add_action( 'lsx_header_wrap_after', array( $this, 'maybe_display_banner' ) );

			// These can be removed if an action is run later in the `wp_head`.
			add_filter( 'lsx_bd_banner_title', array( $this, 'default_banner_title' ), 10, 1 );
			add_filter( 'lsx_bd_banner_colour', array( $this, 'default_banner_colour' ), 10, 1 );
		}
	}

	/**
	 * Detects what screen it is and saves it.
	 *
	 * @return void
	 */
	public function set_screen() {
		if ( is_singular( 'business-directory' ) ) {
			$this->screen = 'single';
		} elseif ( is_post_type_archive( 'business-directory' ) ) {
			$this->screen = 'archive';
		} elseif ( is_tax( array( 'lsx-bd-industry', 'lsx-bd-region' ) ) ) {
			$this->screen = 'taxonomy';
		} else {
			$this->screen = '';
		}
	}

	/**
	 * Adds a body class if the banner is disabled.
	 *
	 * @param array $classes The current <body> tag classes.
	 * @return array
	 */
	public function banner_class( $classes = array() ) {
		switch ( $this->screen ) {
			case 'single':
				$disable = get_post_meta( get_the_ID(), 'lsx_bd_banner_disable', true );
				break;
			case 'archive':
				$disable = lsx_bd_get_option( 'archive_banner_disable' );
				break;
			case 'taxonomy':
				$disable = get_term_meta( get_queried_object_id(), 'archive_banner_disable', true );
				break;
			default:
				$disable = '';
				break;
		}
		if ( 'on' === $disable ) {
			$classes[] = 'banner-disabled';
		}
		return $classes;
	}

	/**
	 * Checks to see if it one of the listings pages and calls the banner ouput.
	 *
	 * @return void
	 */
	public function maybe_display_banner() {
		switch ( $this->screen ) {
			case 'single':
				$this->single_banner();
				break;
			case 'archive':
				$this->archive_banner();
				break;
			case 'taxonomy':
				$this->term_banner();
				break;
			default:
				break;
		}
	}

	/**
	 * Outputs the single listing banner
	 *
	 * @return void
	 */
	public function single_banner() {
		$disable = get_post_meta( get_the_ID(), 'lsx_bd_banner_disable', true );
		if ( true !== $disable && 'on' !== $disable ) {
			$args = array(
				'image'    => apply_filters( 'lsx_bd_banner_image', get_post_meta( get_the_ID(), 'lsx_bd_banner', true ) ),
				'colour'   => apply_filters( 'lsx_bd_banner_colour', get_post_meta( get_the_ID(), 'lsx_bd_banner_colour', true ) ),
				'title'    => apply_filters( 'lsx_bd_banner_title', get_post_meta( get_the_ID(), 'lsx_bd_banner_title', true ) ),
				'subtitle' => apply_filters( 'lsx_bd_banner_subtitle', get_post_meta( get_the_ID(), 'lsx_bd_banner_subtitle', true ) ),
			);
			$this->do_banner( $args );
		}
	}

	/**
	 * Outputs the post type archive listing banner.
	 *
	 * @return void
	 */
	public function archive_banner() {
		$disable = lsx_bd_get_option( 'archive_banner_disable' );
		if ( true !== $disable && 'on' !== $disable ) {
			$args = array(
				'image'    => apply_filters( 'lsx_bd_banner_image', lsx_bd_get_option( 'archive_banner' ) ),
				'colour'   => apply_filters( 'lsx_bd_banner_colour', lsx_bd_get_option( 'archive_banner_colour' ) ),
				'title'    => apply_filters( 'lsx_bd_banner_title', lsx_bd_get_option( 'archive_banner_title' ) ),
				'subtitle' => apply_filters( 'lsx_bd_banner_subtitle', lsx_bd_get_option( 'archive_banner_subtitle' ) ),
			);
			$this->do_banner( $args );
		}
	}

	/**
	 * Outputs the taxonomies term banner
	 *
	 * @return void
	 */
	public function term_banner() {
		$disable = get_term_meta( get_the_ID(), 'lsx_bd_banner_disable', true );
		if ( true !== $disable && 'on' !== $disable ) {
			$args = array(
				'image'    => apply_filters( 'lsx_bd_banner_image', get_term_meta( get_queried_object_id(), 'lsx_bd_banner', true ) ),
				'colour'   => apply_filters( 'lsx_bd_banner_colour', get_term_meta( get_queried_object_id(), 'lsx_bd_banner_colour', true ) ),
				'title'    => apply_filters( 'lsx_bd_banner_title', get_term_meta( get_queried_object_id(), 'lsx_bd_banner_title', true ) ),
				'subtitle' => apply_filters( 'lsx_bd_banner_subtitle', get_term_meta( get_queried_object_id(), 'lsx_bd_banner_subtitle', true ) ),
			);
			$this->do_banner( $args );
		}
	}

	/**
	 * Outputs the banners based on the arguments.
	 *
	 * @param array $args The parameters for the banner
	 * @return void
	 */
	public function do_banner( $args = array() ) {
		$defaults = array(
			'image'    => false,
			'colour'   => '#333',
			'title'    => '',
			'subtitle' => '',
		);
		$args     = wp_parse_args( $args, $defaults );
		// Generate the background atts.
		$background_image_attr = '';
		$css_classes           = '';
		if ( '' === $args['image'] || false === $args['image'] ) {
			$background_image_attr = 'background-color:' . $args['colour'];
		} else {
			$background_image_attr = 'background-image:url(' . $args['image'] . ')';
			$css_classes           = apply_filters( 'lsx_bd_banner_css_class', 'has-background-img' );
		}
		$background_image_attr = apply_filters( 'lsx_bd_banner_style_attr', $background_image_attr );
		?>
		<div class="business-banner lsx-full-width">
			<div class="wp-block-cover alignfull has-background-dim <?php echo esc_html( $css_classes ); ?>" style="<?php echo esc_html( $background_image_attr ); ?>">
				<div class="wp-block-cover__inner-container">
					<?php if ( '' !== $args['title'] && false !== $args['title'] ) { ?>
						<h1 class="has-text-align-center archive-title"><?php echo esc_html( $args['title'] ); ?></h1>
					<?php } ?>

					<?php if ( '' !== $args['subtitle'] && false !== $args['subtitle'] ) { ?>
						<p class="has-text-align-center"><?php echo esc_html( $args['subtitle'] ); ?></p>
					<?php } ?>
				</div>
			</div>
			<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
		</div>
		<?php
	}

	/**
	 * Adds default title to the banner if there is none.
	 *
	 * @param string $title
	 * @return void
	 */
	public function default_banner_title( $title ) {
		if ( '' === $title || false === $title ) {
			switch ( $this->screen ) {
				case 'single':
					$title = get_the_title();
					break;
				case 'archive':
				case 'taxonomy':
					$title = get_the_archive_title();
					break;
				default:
					break;
			}
		}
		return $title;
	}
	/**
	 * Adds the default banner colour if there is none
	 *
	 * @param string $colour
	 * @return void
	 */
	public function default_banner_colour( $colour ) {
		if ( false === $colour || '' === $colour ) {
			$colour = '#333';
		}
		return $colour;
	}

	/**
	 * Changes the single business listing title to an H2.
	 *
	 * @param string $title The listing title wrapped in an <h1>.
	 * @return string
	 */
	public function change_single_business_listing_tag( $title ) {
		$title = '<h2 class="entry-title">' . get_the_title() . '</h2>';
		return $title;
	}
}
