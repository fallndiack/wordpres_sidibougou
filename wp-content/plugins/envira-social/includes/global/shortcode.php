<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Pagination
 * @author  Envira Team
 */
class Envira_Social_Shortcode {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Holds a flag to determine whether metadata has been set
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $meta_data_set = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the base class object.
		$this->base = Envira_Social::get_instance();

		$version = ( defined( 'ENVIRA_DEBUG' ) && 'true' === ENVIRA_DEBUG ) ? $version = time() . '-' . $this->base->version : $this->base->version;

		// Register CSS.
		wp_register_style( $this->base->plugin_slug . '-style', plugins_url( 'assets/css/social-style.css', $this->base->file ), array(), $version );

		// Register JS.
		wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-social-min.js', $this->base->file ), array( 'jquery' ), $version, true );
		wp_register_script( $this->base->plugin_slug . '-pinterest-pinit', '//assets.pinterest.com/js/pinit.js', array( 'jquery' ), $version, true );

		// Init Scripts.
		add_action( 'init', array( $this, 'maybe_prevent_caching' ) );
		add_action( 'wp_head', array( $this, 'metadata' ), -99999 );
		add_action( 'wp_head', array( $this, 'facebook_sdk_init' ) );

		// Gallery.
		add_action( 'envira_gallery_before_output', array( $this, 'gallery_output_css_js' ) );
		add_action( 'envira_link_before_output', array( $this, 'gallery_output_css_js' ) );

		add_filter( 'envira_gallery_output_dynamic_position', array( $this, 'gallery_output_html_high_priority' ), 0, 6 );
		add_filter( 'envira_gallery_output_dynamic_position', array( $this, 'gallery_output_html_low_priority' ), 100, 6 );
		add_action( 'envira_gallery_api_before_show', array( $this, 'gallery_output_lightbox_data_attributes' ) );
		add_action( 'envirabox_output_dynamic_position', array( $this, 'gallery_output_legacy_lightbox_html_high_priority' ), 0, 3 );
		add_action( 'envirabox_output_dynamic_position', array( $this, 'gallery_output_legacy_lightbox_html_low_priority' ), 100, 3 );
		add_action( 'envirabox_inner_below', array( $this, 'gallery_output_lightbox_html' ), 0, 3 );
		add_filter( 'envirabox_margin', array( $this, 'envirabox_margin' ), 11, 2 );
		add_filter( 'envira_gallery_output_image_attr', array( $this, 'gallery_output_image_attr' ), 11, 5 );

		// Schema Microdata.
		add_filter( 'envira_gallery_output_schema_microdata', array( $this, 'envira_output_schema_microdata' ), 10, 6 );
		add_filter( 'envira_gallery_output_shortcode_schema_microdata', array( $this, 'envira_gallery_output_shortcode_schema_microdata' ), 10, 2 );
		add_filter( 'envira_gallery_output_schema_microdata_itemprop_thumbnailurl', array( $this, 'envira_gallery_output_schema_microdata_itemprop_thumbnailurl' ), 10, 2 );
		add_filter( 'envira_gallery_output_schema_microdata_itemprop_contenturl', array( $this, 'envira_gallery_output_schema_microdata_itemprop_contenturl' ), 10, 2 );
		add_filter( 'envira_gallery_output_schema_microdata_imageobject', array( $this, 'envira_gallery_output_schema_microdata_imageobject' ), 10, 2 );

		// Album.
		add_action( 'envira_albums_before_output', array( $this, 'albums_output_css_js' ) );
		add_filter( 'envira_albums_output_dynamic_position', array( $this, 'gallery_output_html_high_priority' ), 0, 6 );
		add_filter( 'envira_albums_output_dynamic_position', array( $this, 'gallery_output_html_low_priority' ), 100, 6 );
		add_action( 'envira_albums_api_before_show', array( $this, 'gallery_output_lightbox_data_attributes' ) );

		// Third-Party Social/SEO Plugins.
		add_action( 'wp_head', array( $this, 'envira_yoast_remove_all_wpseo_og' ), 1 );

	}

	/**
	 * Yoast: Remove OG Tags Only On Envira's Share Link Pages
	 * This should allow users to continue to use Yoast's OG Tags w/o interferring with Envira's sharing
	 *
	 * @since 1.1.7
	 */
	public function envira_yoast_remove_all_wpseo_og() {

		// If this doesn't exist in the querystring, then it's not an Envira social share link, so don't remove Yoast.
		if ( ! isset( $_REQUEST['envira_social_gallery_id'] ) ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			return;
		}

		if ( isset( $GLOBALS['wpseo_og'] ) ) {
			remove_action( 'wpseo_head', array( $GLOBALS['wpseo_og'], 'opengraph' ), 30 );
		}

	}

	/**
	 * Determine image size for email sharing
	 *
	 * @param string $html HTML.
	 * @param string $id Gallery ID.
	 * @param array  $item Item data.
	 * @param array  $data Gallery Data.
	 * @param int    $i Index.
	 *
	 * @since 1.1.7
	 */
	public function gallery_output_image_attr( $html = false, $id, $item, $data, $i ) {

		if ( ! empty( $data['config']['social_lightbox'] ) ) {
			$email_share_image_size = envira_get_config( 'social_email_image_size', $data ) ? envira_get_config( 'social_email_image_size', $data ) : 'full';
			$photo_url              = wp_get_attachment_image_src( $id, $email_share_image_size );
		} else {
			return $html;
		}

		return $html . ' data-envira-fullsize-src="' . ( ( ! empty( $data['config']['social_email'] ) || ! empty( $data['config']['social_lightbox_email'] ) ) && ! empty( $photo_url ) ? esc_url( $photo_url[0] ) : '' ) . '" ';

	}

	/**
	 * Remove schema data because Google+ will use this over the Open Graph data the social addon uses
	 *
	 * @param string $html HTML.
	 * @param array  $gallery Gallery data.
	 * @param string $id Gallery ID.
	 * @param array  $item Item data.
	 * @param array  $data Gallery Data.
	 * @param int    $i Index.
	 *
	 * @since 1.1.7
	 */
	public function envira_output_schema_microdata( $html, $gallery, $id, $item, $data, $i ) {

		if ( empty( $gallery['config']['social_google'] ) && empty( $gallery['config']['social_lightbox_google'] ) ) {
			return $html;
		} else {
			return false;
		}

	}

	/**
	 * Remove schema data because Google+ will use this over the Open Graph data the social addon uses
	 *
	 * @param string $html Album Data.
	 * @param array  $gallery Gallery Data.
	 *
	 * @since 1.1.7
	 */
	public function envira_gallery_output_shortcode_schema_microdata( $html, $gallery ) {

		if ( isset( $_GET['google'] ) ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			return false;
		}

		if ( empty( $gallery['config']['social_google'] ) && empty( $gallery['config']['social_lightbox_google'] ) ) {
			return $html;
		} else {
			return false;
		}

	}

	/**
	 * Remove schema data because Google+ will use this over the Open Graph data the social addon uses
	 *
	 * @param string $html Album Data.
	 * @param array  $gallery Gallery Data.
	 *
	 * @since 1.1.7
	 */
	public function envira_gallery_output_schema_microdata_itemprop_thumbnailurl( $html, $gallery ) {

		if ( isset( $_GET['google'] ) ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			return false;
		}

		if ( empty( $gallery['config']['social_google'] ) && empty( $gallery['config']['social_lightbox_google'] ) ) {
			return $html;
		} else {
			return false;
		}

	}

	/**
	 * Remove schema data because Google+ will use this over the Open Graph data the social addon uses
	 *
	 * @param string $html Album Data.
	 * @param array  $gallery Gallery Data.
	 *
	 * @since 1.1.7
	 */
	public function envira_gallery_output_schema_microdata_itemprop_contenturl( $html, $gallery ) {

		if ( isset( $_GET['google'] ) ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			return false;
		}

		if ( empty( $gallery['config']['social_google'] ) && empty( $gallery['config']['social_lightbox_google'] ) ) {
			return $html;
		} else {
			return false;
		}

	}

	/**
	 * Remove schema data because Google+ will use this over the Open Graph data the social addon uses
	 *
	 * @param string $html Album Data.
	 * @param array  $gallery Gallery Data.
	 *
	 * @since 1.1.7
	 */
	public function envira_gallery_output_schema_microdata_imageobject( $html, $gallery ) {

		if ( isset( $_GET['google'] ) ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			return false;
		}

		if ( empty( $gallery['config']['social_google'] ) && empty( $gallery['config']['social_lightbox_google'] ) ) {
			return $html;
		} else {
			return false;
		}

	}

	/**
	 * If an envira_social_gallery_id and envira_social_gallery_item_id are present in the URL,
	 * force the server to fetch a fresh version of the page, and not use cache.
	 *
	 * This prevents some social networks, such as Google, from always returning the first image
	 * the user chose to share, because its cached.  If the user then tries to share a different
	 * second image, the social network will (wrongly) share the first again.
	 *
	 * @since 1.1.7
	 */
	public function maybe_prevent_caching() {

		// Check if specific request parameters exist.
		if ( ! isset( $_REQUEST['envira_social_gallery_id'] ) || ! isset( $_REQUEST['envira_social_gallery_item_id'] ) ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			return;
		}

		// Add some headers to prevent caching.
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );

	}

	/**
	 * Set Open Graph and Twitter Card metadata to share the chosen gallery and image
	 * The Gallery ID and Gallery Item ID will be specified in the URL
	 *
	 * @since 1.0.5
	 */
	public function facebook_sdk_init() {

		global $locale;

		$locale_fb = empty( $locale ) ? 'en_US' : esc_html( $locale );
		// Get instance.
		$common = Envira_Social_Common::get_instance();

		if ( ! $common->get_setting( 'facebook_app_id' ) ) {
			return; }

		?>

		<script>

			if ( window.fbAsyncInit === undefined ) {

				window.fbAsyncInit = function() {
					FB.init({
					appId      : '<?php echo esc_html( $common->get_setting( 'facebook_app_id' ) ); ?>',
					xfbml      : true,
					version    : 'v2.7'
					});
				};

				(function(d, s, id){
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)) {return;}
					js = d.createElement(s); js.id = id;
					js.src = '//connect.facebook.net/<?php echo esc_html( $locale_fb ); ?>/sdk.js';
					fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));
			}

		</script>

		<?php
	}


	/**
	 * Set Open Graph and Twitter Card metadata to share the chosen gallery and image
	 * The Gallery ID and Gallery Item ID will be specified in the URL
	 *
	 * @since 1.0.5
	 */
	public function metadata() {

		global $post;

		// Bail if metadata already set.
		if ( $this->meta_data_set ) {
			return;
		}

		// Copy of the global post so it's not overwritten.
		$the_post = $post;

		// @codingStandardsIgnoreStart - potentially add nonce to querystring?

		// Get gallery ID and gallery item ID.
		$gallery_id           = ( isset( $_GET['envira_social_gallery_id'] ) ? sanitize_text_field( wp_unslash( $_GET['envira_social_gallery_id'] ) ) : '' );
		$album_id             = ( isset( $_GET['envira_album_id'] ) && 'false' !== $_GET['envira_album_id'] ? sanitize_text_field( wp_unslash( $_GET['envira_album_id'] ) ) : false );
		$album_id             = ( isset( $_GET['envira_social_album_id'] ) && 'false' !== $_GET['envira_social_album_id'] ? sanitize_text_field( wp_unslash( $_GET['envira_social_album_id'] ) ) : $album_id );
		$gallery_item_id      = ( isset( $_GET['envira_social_gallery_item_id'] ) ? sanitize_text_field( wp_unslash( $_GET['envira_social_gallery_item_id'] ) ) : '' );
		$dynamic_gallery_post = false;

		// @codingStandardsIgnoreEnd - potentially add nonce to querystring?

		// Check for dynamic gallery.
		if ( substr( $gallery_id, 0, 7 ) === 'custom_' ) {
			// this is a dynamic gallery, so let's use the dynamic gallery for settings
			// step one: find the dynamic gallery, if it exists.
			$args                 = array(
				'name'        => 'envira-dynamic-gallery',
				'post_type'   => 'envira',
				'post_status' => 'publish',
			);
			$dynamic_gallery_post = get_posts( $args );
			if ( ! $dynamic_gallery_post ) {
				return; }
			// revise our gallery id.
			$gallery_id = $dynamic_gallery_post[0]->ID;
			$the_post   = $dynamic_gallery_post[0];

		} else {

			// If either ID is missing, don't bail yet - attempt to find the featured image for the gallery
			// TO-DO: CHECK POST TYPE?
			if ( ( empty( $gallery_id ) || empty( $gallery_item_id ) ) && ! empty( $the_post->ID ) ) {
				$images_in_gallery = get_post_meta( $the_post->ID, '_eg_in_gallery', true );
				if ( ! empty( $images_in_gallery ) ) {
					$gallery_id = $the_post->ID;
					if ( ! empty( $images_in_gallery[0] ) ) {
						$gallery_item_id = $images_in_gallery[0];
					}
				}
			}
		}

		// If we have album ID and gallery ID, get the cover image and that is the gallery_item_id.
		if ( $album_id && ( empty( $gallery_item_id ) || 0 === $gallery_item_id ) ) {
			$album_data = envira_get_album( $album_id );
			if ( isset( $album_data['galleries'][ $gallery_id ]['cover_image_id'] ) ) {
				$gallery_item_id = $album_data['galleries'][ $gallery_id ]['cover_image_id'];
			}
		}
		// NOW we bail if either ID are missing.
		if ( empty( $gallery_id ) || empty( $gallery_item_id ) ) {
			return;
		}

		// Get gallery.
		if ( $album_id ) {
			$data         = Envira_Albums::get_instance()->get_album( $album_id );
			$gallery_data = Envira_Gallery::get_instance()->get_gallery( $gallery_id );
		} elseif ( $gallery_id ) {
			$data = Envira_Gallery::get_instance()->get_gallery( $gallery_id );
			if ( empty( $data ) && class_exists( 'Envira_Dynamic' ) ) {
				// see if the $gallery_id is a page or post, then it might be dynamic.
				$dynamic_gallery_post = get_post( intval( $gallery_id ) );
				if ( 'post' === $dynamic_gallery_post->post_type || 'page' === $dynamic_gallery_post->post_type ) {
					$dynamic_id = Envira_Dynamic_Common::get_instance()->get_gallery_dynamic_id();
					$data       = get_post_meta( $dynamic_id, '_eg_gallery_data', true );
				}
			}
		}
		if ( ! $data ) {
			return;
		}
		// Get gallery item - check first if it's dynamic.
		if ( $dynamic_gallery_post ) {

			$media_item = get_post( $gallery_item_id );

			if ( $media_item ) {

				$item = array(
					'status'  => 'active',
					'src'     => $media_item->guid,
					'title'   => ( 'post' === $dynamic_gallery_post->post_type || 'page' === $dynamic_gallery_post->post_type ) ? $dynamic_gallery_post->post_title : $media_item->post_title,
					'link'    => $media_item->guid,
					'alt'     => ( 'post' === $dynamic_gallery_post->post_type || 'page' === $dynamic_gallery_post->post_type ) ? $dynamic_gallery_post->post_title : $media_item->post_title,
					'caption' => ( 'post' === $dynamic_gallery_post->post_type || 'page' === $dynamic_gallery_post->post_type ) ? $dynamic_gallery_post->post_title : $media_item->post_title,
				);

			}
		} elseif ( $album_id ) {

			$media_item = get_post( $gallery_item_id );

			if ( $media_item ) {

				$item = array(
					'status'  => 'active',
					'src'     => wp_get_attachment_url( $gallery_item_id ),
					'title'   => $media_item->post_title,
					'link'    => $media_item->guid,
					'alt'     => $media_item->post_title,
					'caption' => $media_item->post_title,
				);

			}
		} else {

			$item = isset( $data['gallery'][ $gallery_item_id ] ) ? $data['gallery'][ $gallery_item_id ] : false;

		}

		if ( ! $item ) {
			return;
		}

		// Allow devs to filter image.
		$item = apply_filters( 'envira_social_metadata_image', $item, $gallery_item_id, $data, $gallery_id );

		// If here, we have an item
		// Get instance.
		$common           = Envira_Social_Common::get_instance();
		$facebook_app_id  = $common->get_setting( 'facebook_app_id' );
		$twitter_username = $common->get_setting( 'twitter_username' );

		// If there's an author, get the name information.
		if ( ! empty( $the_post ) && $the_post->post_author ) {
			$user        = get_user_by( 'id', $the_post->post_author );
			$author_name = $user->first_name . ' ' . $user->last_name;
		}

		// If there's a post, get the date publish information.
		if ( ! empty( $the_post ) && $the_post->ID ) {
			// format needs to be 2014-08-12T00:01:56+00:00.
			$date_published = gmdate( 'c', strtotime( $the_post->post_date ) );
		}

		// @codingStandardsIgnoreStart - potentially add nonce to querystring?

		// If there's a post, get the permalink.
		$social_url = false;
		if ( ! empty( $the_post ) && $the_post->ID ) {
			if ( isset( $_GET['envira_social_gallery_item_id'] ) ) {
				$social_url = get_permalink( $the_post->ID ) . '?1=1';
				if ( isset( $_GET['envira_album_id'] ) ) {
					$social_url .= '&envira_album_id=' . intval( $_GET['envira_album_id'] );
				}
				if ( isset( $_GET['envira_social_album_id'] ) ) {
					$social_url .= '&envira_social_album_id=' . intval( $_GET['envira_social_album_id'] );
				}
				if ( isset( $_GET['envira_social_gallery_item_id'] ) ) {
					$social_url .= '&envira_social_gallery_item_id=' . intval( $_GET['envira_social_gallery_item_id'] );
				}
				if ( isset( $_GET['envira_social_gallery_id'] ) ) {
					$social_url .= '&envira_social_gallery_id=' . intval( $_GET['envira_social_gallery_id'] );
				}
				if ( isset( $_GET['google'] ) ) {
					$social_url .= '&google=true';
				}
				if ( isset( $_GET['rand'] ) ) {
					$social_url .= '&rand=' . intval( $_GET['rand'] );
				}
			} else {
				$social_url = get_permalink( $the_post->ID );
			}
		}

		// @codingStandardsIgnoreEnd - potentially add nonce to querystring?

		/* OPEN GRAPH TAGS */

		// The Title.
		if ( ! empty( $gallery_data['id'] ) && is_int( $gallery_data['id'] ) ) {
			$gallery_post = get_post( intval( $gallery_data['id'] ) );
			$social_title = $gallery_post->post_title;
		} elseif ( ! empty( $gallery_data['config']['title'] ) ) {
			// if this exists, we are looking at an album and we want to pass along the title of the GALLERY, not the GALLERY IMAGE.
			$social_title = $gallery_data['config']['title'];
		} elseif ( $item['title'] ) {
			$social_title = $item['title'];
		} elseif ( $the_post->post_title ) {
			$social_title = $the_post->title;
		} else {
			$social_title = $data['config']['social_google_text'];
		}

		// Clean Up Title.
		$social_title = str_replace( '"', '&quot;', $social_title );
		$social_title = str_replace( array( '<br/>', '<br>', '<br />', '</br>' ), ' ', $social_title );
		$social_title = wp_strip_all_tags( $social_title );

		// The Description.
		$override_description = envira_get_config( 'social_google_desc', $data );
		$social_description   = false;

		// @codingStandardsIgnoreStart - potentially add nonce to querystring?

		if ( isset( $_GET['google'] ) && ! empty( $override_description ) ) {
			$social_description = $override_description;
		} elseif ( ! empty( $item['caption'] ) ) {
			$social_description = $item['caption'];
		} elseif ( isset( $data['config']['description'] ) ) { // last resort - grab the gallery description.
			$social_description = $data['config']['description'];
		}

		if ( ! isset( $_GET['google'] ) && envira_get_config( 'social_facebook_show_option_optional_text', $data ) && envira_get_config( 'social_facebook_text', $data ) ) {
			$social_description .=  ! empty( $social_description ) ? ' ' . esc_textarea( envira_get_config( 'social_facebook_text', $data ) ) : esc_textarea( envira_get_config( 'social_facebook_text', $data ) );
		}

		// @codingStandardsIgnoreEnd - potentially add nonce to querystring?

		// Clean Up Title.
		if ( $social_description ) {
			$social_description = str_replace( '"', '&quot;', $social_description );
		}

		// Make sure the description has spaces if the description is false.
		// Otherwise Facebook takes this a sign to try to parse the page, which is rarely good.
		if ( ! $social_description || strlen( $social_description ) === 0 ) {
			$social_description = '&nbsp;';
		}

		// The Image.
		if ( ! empty( $item['src'] ) ) {
			$social_image = esc_url( $item['src'] );
		} elseif ( ! empty( $item['link'] ) ) {
			$social_image = esc_url( $item['link'] );
		} else {
			$social_image = false;
		}

		echo '<!-- ENVIRA SOCIAL TAGS -->

';

		// Add Tag If User Doesn't Have "Rich Pins" checked.
		if ( empty( $data['config']['social_pinterest_rich'] ) ) {
			?>
<meta name="pinterest-rich-pin" content="false" />
			<?php
		}

		// Apply filters, allowing customer to override these for any specific circumstances, debugging, etc.
		$og_type            = apply_filters( 'envira_social_sharing_og_type', 'article', $data, $gallery_id, $album_id );
		$social_url         = apply_filters( 'envira_social_sharing_og_url', $social_url, $data, $gallery_id, $album_id );
		$social_title       = apply_filters( 'envira_social_sharing_og_title', $social_title, $data, $gallery_id, $album_id );
		$social_description = apply_filters( 'envira_social_sharing_og_description', $social_description, $data, $gallery_id, $album_id );

		// We should display this for almost any social network choosen, outside of Twitter which has it's own tags.
		if ( envira_get_config( 'social', $data ) || envira_get_config( 'social_lightbox', $data ) ) :

			?>

<meta property="og:type" content="<?php echo esc_html( $og_type ); ?>" />
<meta name="title" property="og:title" content="<?php echo esc_html( $social_title ); ?>" />
<meta property="og:description" content="<?php echo esc_html( wp_strip_all_tags( $social_description ) ); ?>" />
<meta property="og:image" content="<?php echo esc_html( $social_image ); ?>" />
			<?php if ( ! empty( $data['config']['crop_width'] ) && ! empty( $data['config']['crop_height'] ) ) { ?>
<meta property="og:image:width" content="<?php echo esc_html( $data['config']['crop_width'] ); ?>" />
<meta property="og:image:height" content="<?php echo esc_html( $data['config']['crop_height'] ); ?>" />
			<?php } ?>
<meta property="og:url" content="<?php echo esc_url( $social_url ); ?>" />
			<?php /* Below tags are more for Pinterest than any of the other social networks */ ?>
<meta property="og:site_name" content="<?php bloginfo( 'name' ); ?>" />
			<?php if ( $date_published ) { ?>
<meta property="article:published_time" content="<?php echo esc_html( $date_published ); ?>" />
			<?php } ?>
			<?php if ( $author_name ) { ?>
<meta property="article:author" content="<?php echo esc_html( $author_name ); ?>" />
			<?php } ?>

			<?php

			// This allows some tracking features, although most probably won't take advantage of this.
			if ( envira_get_config( 'social_facebook', $data ) && $facebook_app_id ) {
				?>
<meta property="fb:app_id" content="<?php echo esc_attr( $facebook_app_id ); ?>" />
<?php } ?>

			<?php

		endif;

		/* TWITTER META TAGS */

		if ( ! empty( $gallery_data['config']['title'] ) ) {
			// if this exists, we are looking at an album and we want to pass along the title of the GALLERY, not the GALLERY IMAGE.
			$summary_card_title = $gallery_data['config']['title'];
		} elseif ( $item['title'] ) {
			$summary_card_title = $item['title'];
		} elseif ( $post->post_title ) {
			$summary_card_title = $post->title;
		} else {
			$summary_card_title = $data['config']['social_twitter_text'];
		}

		// Clean Up Title.
		$summary_card_title = str_replace( '"', '&quot;', $social_title );

		$override_description = envira_get_config( 'social_twitter_summary_card_desc', $data );

		if ( $override_description ) {
			$summary_card_description = esc_html( $override_description );
		} elseif ( ! empty( $item['caption'] ) ) {
			$summary_card_description = $item['caption'];
		} elseif ( ! empty( $data['description'] ) ) {
			$summary_card_description = $data['config']['description'];
		} elseif ( ! empty( $data['config']['social_twitter_text'] ) ) {
			$summary_card_description = $data['config']['social_twitter_text'];
		} else {
			$summary_card_description = false;
		}

		// Did the user select a summary card for Twitter?
		// If so, spit out the meta-data for Twitter Summary Card.
		if ( ( envira_get_config( 'social', $data ) || envira_get_config( 'social_lightbox', $data ) ) && ( envira_get_config( 'social_twitter', $data ) || envira_get_config( 'social_lightbox_twitter', $data ) ) ) :

			if ( envira_get_config( 'social_twitter_sharing_method', $data ) === 'card' ) {
				?>
<meta name="twitter:card" content="summary" />
<?php } elseif ( envira_get_config( 'social_twitter_sharing_method', $data ) === 'card-photo' ) { ?>
<meta name="twitter:card" content="summary_large_image">
<?php } ?>
			<?php if ( envira_get_config( 'social_twitter_summary_card_site', $data ) ) { ?>
<meta name="twitter:site" content="<?php echo esc_html( sanitize_text_field( envira_get_config( 'social_twitter_summary_card_site', $data ) ) ); ?>" />
			<?php } ?><meta name="twitter:title" content="<?php echo esc_html( $summary_card_title ); ?>" />
<meta name="twitter:description" content="<?php echo esc_html( $summary_card_description ); ?>" />
			<?php $twitter_image = ( ! empty( $item['src'] ) ) ? $item['src'] : $item['link']; ?>
<meta name="twitter:image" content="<?php echo esc_url( $twitter_image ); ?>" />

			<?php

		endif; // end Twitter Summary Card meta-data.

		// Mark our metadata as loaded.
		$this->meta_data_set = true;

	}

	/**
	 * Enqueue CSS and JS if Social Sharing is enabled
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Gallery Data.
	 */
	public function gallery_output_css_js( $data ) {

		// Check if Social Sharing Buttons output is enabled.
		if ( ! envira_get_config( 'social', $data ) && ! envira_get_config( 'social_lightbox', $data ) && ! envira_get_config( 'mobile_social', $data ) && ! envira_get_config( 'mobile_social_lightbox', $data ) ) {
			return;
		}

		// Get instance.
		$common = Envira_Social_Common::get_instance();

		// Enqueue CSS + JS.
		wp_enqueue_style( $this->base->plugin_slug . '-style' );
		wp_enqueue_script( $this->base->plugin_slug . '-script' );
		wp_localize_script(
			$this->base->plugin_slug . '-script',
			'envira_social',
			array(
				'facebook_app_id' => $common->get_setting( 'facebook_app_id' ),
				'debug'           => ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ? true : false ),
			)
		);

		// If the user has enabled Pinterest.
		if ( envira_get_config( 'social_pinterest', $data ) || envira_get_config( 'social_lightbox_pinterest', $data ) ) {
			wp_enqueue_script( $this->base->plugin_slug . '-pinterest-pinit' );
		}

	}

	/**
	 * Enqueue CSS and JS for Albums if Social Sharing is enabled
	 *
	 * @since 1.0.3
	 *
	 * @param array $data Album Data.
	 */
	public function albums_output_css_js( $data ) {

		global $post;

		// Check if Social Sharing Buttons output is enabled.
		if ( ! envira_get_config( 'social', $data ) && ! envira_get_config( 'social_lightbox', $data ) && ! envira_get_config( 'mobile_social', $data ) && ! envira_get_config( 'mobile_social_lightbox', $data ) ) {
			return;
		}

		// Get instance.
		$common = Envira_Social_Common::get_instance();

		// Enqueue CSS + JS.
		wp_enqueue_style( $this->base->plugin_slug . '-style' );
		wp_enqueue_script( $this->base->plugin_slug . '-script' );
		wp_localize_script(
			$this->base->plugin_slug . '-script',
			'envira_social',
			array(
				'facebook_app_id' => $common->get_setting( 'facebook_app_id' ),
				'debug'           => ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ? true : false ),
			)
		);

		// If the user has enabled Pinterest.
		if ( envira_get_config( 'social_pinterest', $data ) || envira_get_config( 'social_lightbox_pinterest', $data ) ) {
			wp_enqueue_script( $this->base->plugin_slug . '-pinterest-pinit' );
		}
	}

	/**
	 * Set margine of lightbox.
	 *
	 * @since 1.0.3
	 *
	 * @param array $margin Margin amount.
	 * @param array $data Album Data.
	 */
	public function envirabox_margin( $margin, $data ) {

		// Check if Social Sharing Buttons output is enabled.
		if ( ! envira_get_config( 'social_lightbox', $data ) ) {
			return $margin;
		}

		if ( in_array( envira_get_config( 'lightbox_theme', $data ), array( 'space_dark', 'space_light' ), true ) ) {
			if ( ! envira_get_config( 'thumbnails', $data ) ) {
				$margin = '[35, 35, 60, 35]';
			}
		} elseif ( in_array( envira_get_config( 'lightbox_theme', $data ), array( 'base', 'legacy', 'subtle', 'sleek', 'showcase', 'polaroid', 'captioned' ), true ) ) {
			if ( envira_get_config( 'social_lightbox_outside', $data ) && ! envira_mobile_detect()->isMobile() ) {
				$margin = '[80, 75, 80, 75]';
			}
		}

		return $margin;

	}


	/**
	 * Outputs Social Media Sharing HTML for the Gallery thumbnail with a high priority
	 *
	 * @since 1.0.0
	 *
	 * @param string $output HTML Output.
	 * @param int    $id Attachment ID.
	 * @param array  $item Image Item.
	 * @param array  $data Gallery Config.
	 * @param int    $i Image number in gallery.
	 * @param string $position Position.
	 * @return string HTML Output
	 */
	public function gallery_output_html_high_priority( $output, $id, $item, $data, $i, $position ) {

		// Check if Social Sharing Buttons output is enabled.
		if ( ! envira_get_config( 'social', $data ) ) {
			return $output;
		}

		if ( envira_get_config( 'social_position', $data ) !== $position
			|| ( envira_get_config( 'social_orientation', $data ) === 'horizontal' && 'bottom-left' === $position )
			|| 'bottom-right' === $position
		) {
			return $output;
		}

		// Prepend Button(s).
		$buttons = $this->get_social_sharing_buttons( $id, $item, $data, $i, $position );

		return $output . $buttons;

	}

	/**
	 * Outputs Social Media Sharing HTML for the Gallery thumbnail with a low priority
	 *
	 * @since 1.0.0
	 *
	 * @param string $output HTML Output.
	 * @param int    $id Attachment ID.
	 * @param array  $item Image Item.
	 * @param array  $data Gallery Config.
	 * @param int    $i Image number in gallery.
	 * @param int    $position Position.
	 * @return string HTML Output
	 */
	public function gallery_output_html_low_priority( $output, $id, $item, $data, $i, $position ) {

		// Check if Social Sharing Buttons output is enabled.
		if ( ! envira_get_config( 'social', $data ) ) {
			return $output;
		}

		if ( envira_get_config( 'social_position', $data ) !== $position
			|| 'top-left' === $position
			|| ( 'vertical' === envira_get_config( 'social_orientation', $data ) && 'top-right' === $position )
			|| ( 'vertical' === envira_get_config( 'social_orientation', $data ) && 'bottom-left' === $position )
			|| ( 'horizontal' === envira_get_config( 'social_orientation', $data ) && 'top-right' === $position )
		) {
			return $output;
		}

		// Prepend Button(s).
		$buttons = $this->get_social_sharing_buttons( $id, $item, $data, $i, $position );

		return $output . $buttons;

	}

	/**
	 * Outputs data- attributes on the Lightbox image for the Facebook and Twitter Text settings
	 * for the given Gallery.
	 *
	 * @since 1.1.2
	 *
	 * @param   array $data   Gallery Data.
	 * @return  JS
	 */
	public function gallery_output_lightbox_data_attributes( $data ) {

		global $wp;

		// Check if Social Sharing Buttons output is enabled.
		if ( ! envira_get_config( 'social_lightbox', $data ) ) {
			return;
		}

		$tags = false;

		// there needs to be a description in $facebook_text, otherwise Facebook will try to grab/make one with poor results.
		if ( envira_get_config( 'social_facebook_text', $data ) === '' ) {
			$facebook_text = '    ';
		} else {
			$facebook_text = envira_get_config( 'social_facebook_text', $data );
		}

		$current_url = home_url( add_query_arg( array(), $wp->request ) );

		?>

		var envira_fb_tags = {};

		<?php

		if ( ! empty( $data['config']['social_facebook_show_option_tags'] ) && ! empty( $data['gallery'] ) ) {

			$tag_counter = 0;

			?>
			<?php foreach ( $data['gallery'] as $image_id => $image_data ) { ?>
				<?php
				if ( $data['config']['social_facebook_show_option_tags'] ) {
					if ( 'manual' === $data['config']['social_facebook_tag_options'] ) {
						$tag_to_output = sanitize_text_field( $data['config']['social_facebook_tags_manual'] );
					} elseif ( 'envira-tags' === $data['config']['social_facebook_tag_options'] && ! empty( $image_id ) ) {
						// If no more tags, return the classes.
						$terms = wp_get_object_terms( $image_id, 'envira-tag' );
						if ( count( $terms ) > 0 ) {
							// we are only grabbing the first tag.
							$tags = '#' . $terms[0]->slug;
						}
					}
				}

				?>
				<?php

				if ( $tags ) {
					echo esc_html( 'envira_fb_tags[' . $image_id . '] = "' . $tags . '";' );
				}

				if ( 0 === $tag_counter ) {
					$tag_to_output = $tags;
				}

				$tag_counter++;

				?>
			<?php } ?>
			<?php
		}

		?>

		this.inner.find('img').attr('data-envira-social-url', '<?php echo rawurlencode( $current_url ); ?>' );

		this.inner.find('img').attr('data-envira-social-facebook-text', '<?php echo esc_html( $facebook_text ); ?>' );
		this.inner.find('img').attr('data-envira-facebook-quote',       '<?php echo esc_html( envira_get_config( 'social_facebook_quote', $data ) ); ?>');
		<?php if ( $data['config']['social_facebook_show_option_tags'] ) { ?>
		this.inner.find('img').attr('data-envira-facebook-tags-manual', '<?php echo esc_html( $tag_to_output ); ?>');
		<?php } ?>

		this.inner.find('img').attr('data-envira-social-twitter-text',  '<?php echo esc_html( envira_get_config( 'social_twitter_text', $data ) ); ?>');

		<?php

	}

	/**
	 * Gallery: Outputs Social Lightbox data when a lightbox image is displayed from a Gallery with a high priority
	 *
	 * @param array  $template Gallery template.
	 * @param array  $data Gallery Data.
	 * @param string $position Position.
	 * @return JS
	 */
	public function gallery_output_lightbox_html( $template, $data, $position = false ) {

		// Check if Social Sharing Buttons output is enabled.
		if ( empty( $data['config']['social_lightbox'] ) ) {
			return $template;
		}

		if ( empty( $data['config']['lightbox_theme'] ) || ( 'base_dark' !== $data['config']['lightbox_theme'] && 'base_light' !== $data['config']['lightbox_theme'] ) ) {
			return $template;
		}

		// Get Button(s).
		$buttons = $this->get_lightbox_social_sharing_buttons( $data, $position );

		return $template . $buttons;

	}

	/**
	 * Gallery: Outputs EXIF Lightbox data when a lightbox image is displayed from a Gallery with a high priority
	 *
	 * @param array  $template Template.
	 * @param array  $data Gallery Data.
	 * @param string $position Position.
	 * @return JS
	 */
	public function gallery_output_legacy_lightbox_html_high_priority( $template, $data, $position = false ) {

		// Check if Social Sharing Buttons output is enabled.
		if ( empty( $data['config']['social_lightbox'] ) ) {
			return $template;
		}

		if ( 'base_dark' === $data['config']['lightbox_theme'] || 'base_light' === $data['config']['lightbox_theme'] ) {
			return $template;
		}

		if ( $position && ( envira_get_config( 'social_lightbox_position', $data ) !== $position
			|| ( 'horizontal' === envira_get_config( 'social_lightbox_orientation', $data ) && 'bottom-left' === $position )
			|| 'bottom-right' === $position )
		) {
			return $template;
		}

		// Get Button(s).
		$buttons = $this->get_lightbox_social_sharing_buttons( $data, $position );

		return $template . $buttons;

	}

	/**
	 * Gallery: Outputs EXIF Lightbox data when a lightbox image is displayed from a Gallery with a low priority
	 *
	 * @param array  $template Template Data.
	 * @param array  $data Gallery Data.
	 * @param string $position Position.
	 * @return JS
	 */
	public function gallery_output_legacy_lightbox_html_low_priority( $template, $data, $position = false ) {

		// Check if Social Sharing Buttons output is enabled.
		if ( empty( $data['config']['social_lightbox'] ) ) {
			return $template;
		}

		if ( 'base_dark' === $data['config']['lightbox_theme'] || 'base_light' === $data['config']['lightbox_theme'] ) {
			return $template;
		}

		if ( $position && ( envira_get_config( 'social_lightbox_position', $data ) !== $position
			|| 'top-left' === $position
			|| ( 'vertical' === envira_get_config( 'social_lightbox_orientation', $data ) && 'top-right' === $position )
			|| ( 'vertical' === envira_get_config( 'social_lightbox_orientation', $data ) && 'bottom-left' === $position )
			|| ( 'horizontal' === envira_get_config( 'social_lightbox_orientation', $data ) && 'top-right' === $position )
		) ) {
			return $template;
		}

		// Get Button(s).
		$buttons = $this->get_lightbox_social_sharing_buttons( $data, $position );

		return $template . $buttons;

	}

	/**
	 * Helper to output social sharing buttons for an image
	 *
	 * @since 1.0.0
	 *
	 * @global object $post Gallery
	 *
	 * @param int    $id   Image ID.
	 * @param array  $item Image Data.
	 * @param array  $data Gallery Data.
	 * @param int    $i Index.
	 * @param string $position Index.
	 * @return string HTML
	 */
	public function get_social_sharing_buttons( $id, $item, $data, $i, $position ) {

		global $post, $wp;

		// Init $post_id var.
		$post_id = false;
		$paged   = false;

		// Get instance.
		$common = Envira_Social_Common::get_instance();

		// Mobile check, is user allowing ANY social sharing on mobile for galleries?
		if ( envira_mobile_detect()->isMobile() && ! $data['config']['mobile_social'] ) {
			return;
		}

		// Start.
		$buttons = '<div class="envira-social-buttons position-' . envira_get_config( 'social_position', $data ) . ' orientation-' . envira_get_config( 'social_orientation', $data ) . '">';

		// Ready the current url.
		$current_url = home_url( add_query_arg( array(), $wp->request ) );

		// Get the Post/Page/CPT we're viewing
		// However, check AJAX $_POST call for post/page/CPT id FIRST.
		if ( ! empty( $_POST['envira_post_social_url'] ) ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			$post_url = esc_url( wp_unslash( $_POST['envira_post_social_url'] ) ); // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			$post_id  = false;
		} elseif ( ! empty( $_POST['post_id'] ) ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			$post_id  = intval( $_POST['post_id'] ); // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			$post_url = get_permalink( $post_id );
		} elseif ( $current_url ) {
			$post_url = $current_url;
		} elseif ( ! empty( $post ) ) {
			$post_url = get_permalink( $post->ID );
			$post_id  = $post->ID;
		}

		// Permalink check -> if the user has permalinks set to off/plain.
		if ( ! empty( $_REQUEST['envira'] ) ) { // @codingStandardsIgnoreLine - potentially add nonce to querystring?
			// include this in the url we are building for social, otherwise link might just point back to the homepage.
			$envira_permalink = 'envira=' . esc_html( ( wp_unslash( $_REQUEST['envira'] ) ) ) . '&'; // @codingStandardsIgnoreLine - potentially add nonce to querystring?
		} else {
			$envira_permalink = false;
		}

		$post_url = trailingslashit( $post_url );

		$paged           = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : false;
		$paged           = ( ! empty( $_POST['page'] ) ) ? intval( $_POST['page'] ) : $paged; // @codingStandardsIgnoreLine - potentially add nonce to querystring?
		$gallery_id      = false;
		$gallery_item_id = false;
		$album_id        = false;

		// Define the gallery_id -> can't assume it's $data['id'] because an album (gallery view) might be passed in.
		if ( ! empty( $data['album_id'] ) ) {

			// there's an album id, so this should be an album
			// therefore make the id of $item the id to share.
			$gallery_id = $id; // $item['id'];
			// also make the image id to pass the cover image of the gallery.
			$gallery_item_id = intval( $item['cover_image_id'] );
			// we HAVE to pass the album id.
			$album_id = $data['album_id'];

			// the envira-social-picture is the cover_image_url.
			if ( ! empty( $item['cover_image_url'] ) ) {
				$item['src'] = $item['cover_image_url'];
			} elseif ( $gallery_item_id ) {
				$item['src'] = wp_get_attachment_url( $gallery_item_id );
			} else {
				$item['src'] = false;
			}

			$item['caption'] = isset( $item['caption'] ) ? $item['caption'] : '';

		} else {

			$gallery_id      = $data['id'];
			$gallery_item_id = $id;

		}

		// Allow devs to filter the title and caption
		// Don't worry about url encoding - we'll handle this!
		$title         = apply_filters( 'envira_social_sharing_title', $item['title'], $id, $item, $data, $i );
		$caption       = apply_filters( 'envira_social_sharing_caption', $item['caption'], $id, $item, $data, $i );
		$facebook_text = apply_filters( 'envira_social_sharing_facebook_text', envira_get_config( 'social_facebook_text', $data ), $id, $item, $data, $i );
		$twitter_text  = apply_filters( 'envira_social_sharing_twitter_text', envira_get_config( 'social_twitter_text', $data ), $id, $item, $data, $i );

		// Combine list of networks available via mobile only and desktop/mobile.
		$networks        = ( ! empty( $common->get_networks() ) ) ? $common->get_networks() : false;
		$networks_mobile = ( ! empty( $common->get_networks_mobile() ) ) ? $common->get_networks_mobile() : false;
		$social_networks = array_merge( $networks, $networks_mobile );

		if ( empty( $social_networks ) ) {
			return;
		}

		// Iterate through networks, adding a button if enabled in the settings.
		foreach ( $social_networks as $network => $name ) {

			// Unset vars that might have been set in a previous loop.
			unset( $url, $width, $height );

			// Skip network if not enabled.
			if ( envira_mobile_detect()->isMobile() && ! envira_get_config( 'mobile_social_' . $network, $data ) ) {
				continue;
			}
			if ( ! envira_mobile_detect()->isMobile() && ! envira_get_config( 'social_' . $network, $data ) ) {
				continue;
			}

			// If the facebook text is nothing, add some spaces so that Facebook ignores the description and doesn't attempt to scrape it.
			if ( trim( $facebook_text ) === '' ) {
				$facebook_text = '  ';
			} else {
				$facebook_text = rawurlencode( $facebook_text );
			}

			$tags                 = false;
			$pinterest_additional = false;
			$email_url            = false;
			$button_specific_html = false;

			// Define sharing URL and popup window dimensions.
			switch ( $network ) {

				/**
				* Facebook
				*/
				case 'facebook':
					// Mobile check, is user allowing facebook on mobile for galleries?
					if ( envira_mobile_detect()->isMobile() && ! $data['config']['mobile_social_facebook'] ) {
						break;
					}

					// Get App ID.
					$app_id = $common->get_setting( 'facebook_app_id' );
					$url    = 'https://www.facebook.com/dialog/feed?app_id=' . $app_id . '&display=popup&link=' . rawurlencode( $post_url ) . '?' . $envira_permalink . 'picture=' . rawurlencode( $item['src'] ) . '&name=' . rawurlencode( wp_strip_all_tags( $title ) ) . '&caption=' . rawurlencode( wp_strip_all_tags( $caption ) ) . '&description=' . $facebook_text . '&redirect_uri=' . rawurlencode( $post_url . '#envira_social_sharing_close' );
					$width  = 626;
					$height = 436;
					if ( ! isset( $data['config']['social_facebook_show_option_optional_text'] ) || ! $data['config']['social_facebook_show_option_optional_text'] ) {
						$facebook_text = '  ';
					}
					if ( ! empty( $data['config']['social_facebook_show_option_quote'] ) ) {
						$facebook_quote = esc_html( $data['config']['social_facebook_quote'] );
					} else {
						$facebook_quote = false;
					}
					if ( ! empty( $data['config']['social_facebook_show_option_tags'] ) ) {

						if ( 'manual' === $data['config']['social_facebook_tag_options'] ) {
							$tags = sanitize_text_field( $data['config']['social_facebook_tags_manual'] );
						} elseif ( 'envira-tags' === $data['config']['social_facebook_tag_options'] ) {
							// If no more tags, return the classes.
							$terms = wp_get_object_terms( $id, 'envira-tag' );
							if ( count( $terms ) > 0 ) {
								// we are only grabbing the first tag.
								$tags = '#' . $terms[0]->slug;
							}
						}
					}
					if ( ! empty( $data['config']['social_facebook_show_option_caption'] ) ) {
						$fb_caption = 'data-envira-facebook-caption="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '"';
					} else {
						$fb_caption = 'data-envira-facebook-caption=""';
					}

					// Build Button HTML.
					$button_specific_html = '<a data-envira-album-id="' . $album_id . '" data-envira-social-picture="' . $item['src'] . '" ' . $fb_caption . ' data-envira-facebook-tags="' . $tags . '" data-envira-gallery-id="' . $gallery_id . '" data-envira-item-id="' . $gallery_item_id . '" data-envira-social-facebook-text="' . $facebook_text . '" data-envira-facebook-quote="' . $facebook_quote . '" data-envira-caption="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '" data-envira-title="' . rawurlencode( wp_strip_all_tags( $title ) ) . '" href="' . $url . '" class="envira-social-button button-' . $network . '" data-envira-post-id="' . $post_id . '" >' . __( 'Share', 'envira-social' ) . ' <span>on ' . $name . '</span></a>';

					break;

				/**
				* Twitter
				*/
				case 'twitter':
					// Mobile check, is user allowing twitter on mobile for galleries?
					if ( envira_mobile_detect()->isMobile() && ! $data['config']['mobile_social_twitter'] ) {
						break;
					}

					$url    = 'https://twitter.com/intent/tweet?text=' . rawurlencode( wp_strip_all_tags( $caption ) ) . rawurlencode( $twitter_text ) . '&url=' . rawurlencode( $post_url . '?' . $envira_permalink . 'envira_album_id=' . $album_id . '&envira_social_gallery_id=' . $gallery_id . '&envira_social_gallery_item_id=' . $gallery_item_id . '&rand=' . wp_rand( 0, 99999 ) );
					$width  = 500;
					$height = 300;

					// Build Button HTML.
					$button_specific_html = '<a href="' . $url . '" class="envira-social-button button-' . $network . '" >' . __( 'Share', 'envira-social' ) . ' <span>on ' . $name . '</span></a>';

					break;

				/**
				* Pinterest
				*/
				case 'pinterest':
					// Mobile check, is user allowing pinterest on mobile for galleries?
					if ( envira_mobile_detect()->isMobile() && ! $data['config']['mobile_social_pinterest'] ) {
						break;
					}

					$url                  = 'javascript:null(0);';
					$width                = 500;
					$height               = 400;
					$pinterest_share_type = envira_get_config( 'social_pinterest_type', $data );
					if ( ! $pinterest_share_type ) { // just in case we don't have anything, go with the default.
						$pinterest_share_type = 'pin-one';
					}
					if ( ! $caption || envira_get_config( 'social_pinterest_title', $data ) === 'title' ) {
						// without a caption, pInterest grabs the page description
						// so for now let's make the caption the title.
						$caption = $title;
					}
					$pinterest_additional = 'data-envira-pinterest-type="' . $pinterest_share_type . '" data-pin-do="buttonPin" data-pin-custom="true" data-envira-social-pinterest-description="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '"';

					// Build Button HTML.
					$button_specific_html = '<a data-envira-album-id="' . $album_id . '" ' . $pinterest_additional . ' data-envira-social-picture="' . $item['src'] . '" data-envira-gallery-id="' . $gallery_id . '" data-envira-item-id="' . $gallery_item_id . '" data-envira-social-url="' . $url . '" data-envira-caption="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '" data-envira-title="' . rawurlencode( wp_strip_all_tags( $title ) ) . '" href="' . $url . '" class="envira-social-button button-' . $network . '">' . __( 'Share', 'envira-social' ) . ' <span>on ' . $name . '</span></a>';

					break;

				/**
				* LinkedIn
				*/
				case 'linkedin':
					// Mobile check, is user allowing pinterest on mobile for galleries?
					if ( envira_mobile_detect()->isMobile() && ! $data['config']['mobile_social_linkedin'] ) {
						break;
					}

					$width  = 640;
					$height = 600;

					$title   = $data['config']['social_linkedin_show_option_title'] ? $title : false;
					$summary = $data['config']['social_linkedin_show_option_summary'] ? $caption : false;
					$source  = $data['config']['social_linkedin_show_option_source'] ? get_bloginfo( 'name' ) : false;

					// Add filters.
					$title   = rawurlencode( wp_strip_all_tags( apply_filters( 'envira_social_gallery_linkedin_title', $title, $data ) ) );
					$summary = rawurlencode( wp_strip_all_tags( apply_filters( 'envira_social_gallery_linkedin_summary', $summary, $data ) ) );
					$source  = rawurlencode( wp_strip_all_tags( apply_filters( 'envira_social_gallery_linkedin_source', $source, $data ) ) );

					if ( ! empty( $gallery_item_id ) || ! empty( $gallery_id ) || ! empty( $album_id ) ) {
						$post_url_updated = add_query_arg(
							array(
								'envira_social_gallery_item_id' => $gallery_item_id,
								'envira_social_gallery_id' => $gallery_id,
								'envira_social_album_id'   => $album_id,
								'random'                   => time(),
							),
							$post_url
						);
					}

					$base_url = 'https://www.linkedin.com/shareArticle?mini=true&url=' . rawurlencode( $post_url_updated );

					if ( $title ) {
						$base_url .= '&title=' . $title;
					}
					if ( $summary ) {
						$base_url .= '&summary=' . $summary;
					}
					if ( $source ) {
						$base_url .= '&source=' . $source;
					}

					// Build Button HTML.
					$button_specific_html = '<a data-envira-album-id="' . $album_id . '" data-envira-social-picture="' . $item['src'] . '" data-envira-gallery-id="' . $gallery_id . '" data-envira-item-id="' . $gallery_item_id . '" data-envira-social-url="' . $base_url . '" data-envira-caption="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '" data-envira-title="' . rawurlencode( wp_strip_all_tags( $title ) ) . '" href="' . $base_url . '" class="envira-social-button button-' . $network . '">' . __( 'Share', 'envira-social' ) . ' <span>on ' . $name . '</span></a>';

					break;

				/**
				* WhatsApp
				*/
				case 'whatsapp':
					// Mobile check, is user allowing whatsapp on mobile for galleries? Note that whatsapp is ONLY for mobile so don't show for non-mobile.
					if ( ! envira_mobile_detect()->isMobile() || ( envira_mobile_detect()->isMobile() && ! $data['config']['mobile_social_whatsapp'] ) ) {
						break;
					}

					$url    = rawurlencode( $post_url . '?' . $envira_permalink . 'envira_album_id=' . $album_id . '&envira_social_gallery_id=' . $gallery_id . '&envira_social_gallery_item_id=' . $gallery_item_id . '&rand=' . wp_rand( 0, 99999 ) );
					$width  = 500;
					$height = 400;
					if ( ! $caption ) {
						// without a caption, pInterest grabs the page description
						// so for now let's make the caption the title.
						$caption = $title;
					}
					// $whatsapp_additional = 'data-envira-pinterest-type="'.$pinterest_share_type.'" data-pin-do="buttonPin" data-pin-custom="true" data-envira-social-pinterest-description="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '"';
					// Build Button HTML
					$button_specific_html = '<a data-envira-album-id="' . $album_id . '" data-envira-social-picture="' . $item['src'] . '" data-envira-gallery-id="' . $gallery_id . '" data-envira-item-id="' . $gallery_item_id . '" data-envira-social-url="' . $url . '" data-envira-caption="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '" data-envira-title="' . rawurlencode( wp_strip_all_tags( $title ) ) . '" href="whatsapp://send?text=' . $url . '" class="envira-social-button path1 path2 button-' . $network . '">' . __( 'Share', 'envira-social' ) . ' <span>on ' . $name . '</span></a>';

					break;

				/**
				* Email
				*/
				case 'email':
					// Mobile check, is user allowing email on mobile for galleries?
					if ( envira_mobile_detect()->isMobile() && ! $data['config']['mobile_social_email'] ) {
						break;
					}

					if ( $post->ID ) {
						$email_url = $post_url;
						if ( $envira_permalink ) {
							$email_url .= '?' . str_replace( '&', '', $envira_permalink );
						}
						$email_url .= '%0D%0A%0D%0A';
					}

					// share the right sized image so check the 'social_email_image_size' option - default is a fulld image.
					$email_share_image_size = envira_get_config( 'social_email_image_size', $data ) ? envira_get_config( 'social_email_image_size', $data ) : 'full';
					if ( 'full' === $email_share_image_size ) {
						if ( empty( $item['cover_image_url'] ) ) {
							$photo_url = $item['src'];
						} else {
							$photo_url = $item['cover_image_url'];
						}
					} else {
						$photo_url = wp_get_attachment_image_src( $gallery_item_id, $email_share_image_size );
						if ( is_array( $photo_url ) ) {
							$photo_url = $photo_url[0];
						}
					}

					$sizes = wp_get_attachment_metadata( $gallery_item_id );

					$photo_url = apply_filters( 'envira_get_email_image_sizes_photo', $photo_url, $data );
					$email_url = apply_filters( 'envira_get_email_image_sizes_email', $email_url, $data );
					$title     = ! empty( $data['album_id'] ) ? apply_filters( 'envira_get_email_image_sizes_email', $data['config']['title'], $data ) : apply_filters( 'envira_get_email_image_sizes_email', $item['title'], $data );
					$subject   = ! empty( $data['config']['social_email_subject'] ) ? $this->parse_email_subject( esc_html( $data['config']['social_email_subject'] ), $data, $photo_url, $email_url, $title ) : $title;
					$body      = ! empty( $data['config']['social_email_message'] ) ? $this->parse_email_message( $data['config']['social_email_message'], $data, $photo_url, $email_url, $title ) : $email_url . 'Photo: ' . rawurlencode( $photo_url );

					$url = apply_filters( 'get_email_image_sizes', ( 'mailto:?subject=' . ( $subject ) . '&body=' . $body ), $data );

					// Build Button HTML.
					$button_specific_html = '<a href="' . $url . '" class="envira-social-button button-' . $network . '">' . __( 'Share', 'envira-social' ) . ' <span>on ' . $name . '</span></a>';

					break;

			}

			// Build the button HTML, but with the specific data tags so we aren't needlessly repeating attributes.
			if ( $button_specific_html ) {

				// Only build if there is HTML.
				if ( ! isset( $width ) ) {
					$width = false;
				}

				if ( ! isset( $height ) ) {
					$height = false;
				}

				$buttons .= '<div class="envira-social-network ' . $network . '" data-width="' . $width . '" data-height="' . $height . '" data-network="' . $network . '">' . $button_specific_html . '</div>';

			}
		}

		// Close button HTML.
		$buttons .= '
        </div>';

		// Return.
		return $buttons;
	}

	/**
	 * Helper to parse tags in email subject
	 *
	 * @since 1.0.0
	 *
	 * @param string $message Message text.
	 * @param array  $data Gallery data.
	 * @param string $photo_url Photo url.
	 * @param string $email_url Email url.
	 * @param string $title Title text.
	 * @return string HTML
	 */
	public function parse_email_subject( $message, $data, $photo_url, $email_url, $title ) {

		// Replace tags with real content.
		$message = str_replace( '{title}', $title, $message );
		$message = str_replace( '{url}', $email_url, $message );
		$message = str_replace( '{photo_url}', $photo_url, $message );

		return $message;

	}

	/**
	 * Helper to parse tags in email body
	 *
	 * @since 1.0.0
	 *
	 * @param string $message Message text.
	 * @param array  $data Gallery data.
	 * @param string $photo_url Photo url.
	 * @param string $email_url Email url.
	 * @param string $title Title text.
	 * @return string HTML
	 */
	public function parse_email_message( $message, $data, $photo_url, $email_url, $title ) {

		// Replace tags with real content.
		$message = str_replace( '{title}', $title, $message );
		$message = str_replace( '{url}', $email_url, $message );
		$message = str_replace( '{photo_url}', $photo_url, $message );
		$message = rawurlencode( $message );
		return $message;

	}

	/**
	 * Helper to output social sharing buttons for the lightbox
	 *
	 * @since 1.0.0
	 *
	 * @param array   $data  Gallery Data.
	 * @param boolean $position Position Data.
	 * @return string HTML
	 */
	public function get_lightbox_social_sharing_buttons( $data, $position = false ) {

		// Mobile check, is user allowing ANY social sharing on mobile for lightboxes?
		if ( envira_mobile_detect()->isMobile() && ! $data['config']['mobile_social_lightbox'] ) {
			return;
		}

		// Get instance and other variables.
		$common   = Envira_Social_Common::get_instance();
		$deeplink = envira_get_config( 'deeplinking', $data ) ? true : false;

		if ( 'base_dark' === $data['config']['lightbox_theme'] || 'base_light' === $data['config']['lightbox_theme'] ) {
			$buttons = '<div class="envira-social-buttons-exterior"><div class="envira-social-buttons" data-gallery-id="" data-gallery_item_id="" >';
		} else { /* legacy */
			$buttons = '<div class="envira-social-buttons test456 position-' . envira_get_config( 'social_lightbox_position', $data ) . ' ' . ( ( envira_get_config( 'social_lightbox_outside', $data ) === 1 && ! envira_mobile_detect()->isMobile() ) ? 'outside' : 'inside' ) . ' orientation-' . envira_get_config( 'social_lightbox_orientation', $data ) . '" data-gallery-id="" data-gallery_item_id="" >';
		}

		// Start.
		$facebook_text = apply_filters( 'envira_social_sharing_facebook_text', envira_get_config( 'social_facebook_text', $data ), $data, $position );
		$twitter_text  = apply_filters( 'envira_social_sharing_twitter_text', envira_get_config( 'social_twitter_text', $data ), $data, $position );

		// Combine list of networks available via mobile only and desktop/mobile.
		$networks        = ( ! empty( $common->get_networks() ) ) ? $common->get_networks() : false;
		$networks_mobile = ( ! empty( $common->get_networks_mobile() ) ) ? $common->get_networks_mobile() : false;
		$social_networks = array_merge( $networks, $networks_mobile );

		if ( empty( $social_networks ) ) {
			return;
		}

		// Iterate through networks, adding a button if enabled in the settings.
		foreach ( $social_networks as $network => $name ) {

			// Unset vars that might have been set in a previous loop.
			unset( $url, $width, $height );
			$deeplink = envira_get_config( 'deeplinking', $data ) ? true : false;

			// Skip network if not enabled.
			if ( envira_mobile_detect()->isMobile() && ! envira_get_config( 'mobile_social_lightbox_' . $network, $data ) ) {
				continue;
			} elseif ( ! envira_mobile_detect()->isMobile() && ! envira_get_config( 'social_lightbox_' . $network, $data ) ) {
				continue;
			}

			$button_specific_html = false;
			$caption              = false;
			$post_url             = false;
			$title                = false;
			$src                  = false;

			// Define sharing URL and popup window dimensions.
			switch ( $network ) {

				/**
				* Facebook
				*/
				case 'facebook':
					// Get App ID.
					$app_id = $common->get_setting( 'facebook_app_id' );
					$url    = 'https://www.facebook.com/dialog/feed?app_id=' . $app_id . '&display=popup&link=' . rawurlencode( $post_url ) . '?picture=' . rawurlencode( $src ) . '&name=' . rawurlencode( wp_strip_all_tags( $title ) ) . '&caption=' . rawurlencode( wp_strip_all_tags( $caption ) ) . '&description=' . $facebook_text . '&redirect_uri=' . rawurlencode( $post_url . '#envira_social_sharing_close' );
					$width  = 626;
					$height = 436;
					if ( empty( $data['config']['social_facebook_show_option_optional_text'] ) ) {
						$facebook_text = '  ';
					}
					if ( ! empty( $data['config']['social_facebook_show_option_quote'] ) ) {
						$facebook_quote = esc_html( $data['config']['social_facebook_quote'] );
					} else {
						$facebook_quote = false;
					}
					$tags = false;
					if ( ! empty( $data['config']['social_facebook_show_option_tags'] ) ) {

						if ( 'manual' === $data['config']['social_facebook_tag_options'] ) {
							$tags = sanitize_text_field( $data['config']['social_facebook_tags_manual'] );
						} elseif ( 'envira-tags' === $data['config']['social_facebook_tag_options'] && ! empty( $id ) ) {
							// If no more tags, return the classes.
							$terms = wp_get_object_terms( $data['config']['id'], 'envira-tag' );
							if ( count( $terms ) > 0 ) {
								// we are only grabbing the first tag.
								$tags = '#' . $terms[0]->slug;
							}
						}
					}
					if ( ! empty( $data['config']['social_facebook_show_option_caption'] ) ) {
						$fb_caption = 'data-envira-facebook-caption="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '"';
					} else {
						$fb_caption = 'data-envira-facebook-caption=""';
					}

					$button_specific_html = '<a href="#" class="envira-social-button" data-facebook-tags-manual="' . esc_html( $tags ) . '" data-envira-social-facebook-text="' . esc_html( $facebook_text ) . '" data-envira-facebook-quote="' . $facebook_quote . '" data-envira-caption="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '" >' . __( 'Share', 'envira-social' ) . ' <span> on ' . $name . '</span></a>';

					break;

				/**
				* Twitter
				*/
				case 'twitter':
					$url    = 'https://twitter.com/intent/tweet?';
					$width  = 500;
					$height = 300;

					if ( ! $data['config']['social_twitter_text'] ) {
						$twitter_text = '  ';
					}

					$button_specific_html = '<a href="#" class="envira-social-button" data-envira-social-twitter-text="' . esc_html( $twitter_text ) . '" data-envira-caption="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '" >' . __( 'Share', 'envira-social' ) . ' <span> on ' . $name . '</span></a>';

					break;

				/**
				* Pinterest
				*/
				case 'pinterest':
					$url    = 'http://pinterest.com/pin/create/button/?';
					$width  = 500;
					$height = 400;

					$button_specific_html = '<a href="#" class="envira-social-button" data-envira-caption="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '" >' . __( 'Share', 'envira-social' ) . ' <span> on ' . $name . '</span></a>';

					break;

				/**
				* LinkedIn
				*/
				case 'linkedin':
					$url    = 'http://pinterest.com/pin/create/button/?';
					$width  = 500;
					$height = 400;

					$button_specific_html = '<a href="#" class="envira-social-button" data-envira-caption="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '" >' . __( 'Share', 'envira-social' ) . ' <span> on ' . $name . '</span></a>';

					break;

				/**
				* WhatsApp Mobile Only
				*/
				case 'whatsapp':
					// Whatsapp is ONLY for mobile so don't show for non-mobile.
					if ( ! envira_mobile_detect()->isMobile() || ( envira_mobile_detect()->isMobile() && ! $data['config']['mobile_social_lightbox_whatsapp'] ) ) {
						break;
					}

					$url    = 'whatsapp://send?text=';
					$width  = 500;
					$height = 400;

					$button_specific_html = '<a href="#" class="envira-social-button path1" data-envira-caption="' . rawurlencode( wp_strip_all_tags( $caption ) ) . '" >' . __( 'Share', 'envira-social' ) . ' <span> on ' . $name . '</span></a>';

					break;

				/**
				* Email
				*/
				case 'email':
					$url    = 'mailto:?';
					$width  = 500;
					$height = 400;

					$button_specific_html = '<a href="#" class="envira-social-button">' . __( 'Share', 'envira-social' ) . ' <span> on ' . $name . '</span></a>';

					break;

			}

			if ( $button_specific_html ) {

				// Only build if there is HTML
				// Build Button HTML.
				$buttons .= '<div class="envira-social-network ' . $network . '" data-width="' . $width . '" data-height="' . $height . '" data-network="' . $network . '" data-deeplinking="' . $network . '">' . $button_specific_html . '</div>';

			}
		}

		// Close button HTML.
		$buttons .= '
        </div>';

		if ( 'base_dark' === $data['config']['lightbox_theme'] || 'base_light' === $data['config']['lightbox_theme'] ) {
			$buttons .= '</div>'; // end div for external.
		}

		// Return.
		return str_replace( "\n", '', $buttons );
	}

	/**
	 * Helper method for retrieving gallery config values.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The config key to retrieve.
	 * @param array  $data The gallery data to use for retrieval.
	 * @return string     Key value on success, default if not set.
	 */
	public function get_config( $key, $data ) {

		// Determine whether data is for a gallery or album.
		$post_type = get_post_type( $data['id'] );

		// If post type is false, we're probably on a dynamic gallery/album
		// Grab the ID from the config.
		if ( ! $post_type && isset( $data['config']['id'] ) ) {
			$post_type = get_post_type( $data['config']['id'] );
		}

		switch ( $post_type ) {
			case 'envira':
				$instance = Envira_Gallery_Shortcode::get_instance();
				break;
			case 'envira_album':
				$instance = Envira_Albums_Shortcode::get_instance();
				break;
		}

		// If no instance was set, bail.
		if ( ! isset( $instance ) ) {
			return false;
		}

		// Return value.
		return $instance->get_config( $key, $data );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Social_Shortcode object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Social_Shortcode ) ) {
			self::$instance = new Envira_Social_Shortcode();
		}

		return self::$instance;

	}

}

// Load the shortcode class.
$envira_social_shortcode = Envira_Social_Shortcode::get_instance();
