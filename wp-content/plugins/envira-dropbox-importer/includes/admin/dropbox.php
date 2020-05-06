<?php
/**
 * Dropbox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Dropbox_Importer
 * @author  Envira Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dropbox class.
 *
 * Acts as a wrapper for Dropbox v2 API
 * v2.0.0+ was written to be as minimal as possible
 *
 * @since 1.0.0
 *
 * @package Envira_Dropbox_Importer
 * @author  Envira Team
 */
class Envira_Dropbox_Importer_Dropbox {

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
	 * Holds the common class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $common;

	/**
	 * Holds settings
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $settings;

	/**
	 * Holds the dropbox auth url path
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	public $dropbox_token_url = 'https://api.dropboxapi.com/oauth2';

	/**
	 * Holds the dropbox api url path
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	public $dropbox_api_url = 'https://api.dropboxapi.com/2';

	/**
	 * Holds the dropbox api content path
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	public $dropbox_api_content_url = 'https://content.dropboxapi.com/2';

	/**
	 * The Dropbox APP Key/Secret For Envira
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */

	public $app_key = '2fq1plxv9pb0n9t';

	/**
	 * The Dropbox APP Key/Secret For Envira
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $app_secret = 'gx6ib57iz7hefew';

	/**
	 * Pagination Variable - Last Path
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $last_path = '';

	/**
	 * Pagination Variable - Last Cursor
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $last_cursor = '';

	/**
	 * Pagination Variable - Has More
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $has_more = '';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Set base instance.
		$this->base = Envira_Dropbox_Importer::get_instance();

		// Get and store settings so we don't have to get_option every time.
		$this->settings = get_option( $this->base->plugin_slug );

	}

	/**
	 * Returns stored settings
	 *
	 * @since 2.0.0
	 *
	 * @return mixed Array of parameters
	 */
	public function get_settings() {

		if ( $this->settings ) {
			return $this->settings;
		} else {
			$settings = Envira_Dropbox_Importer_Common::get_instance()->get_settings();
			return $settings;
		}

	}

	/**
	 * Sets up basic $args for wp_remote_post
	 *
	 * @since 2.0.0
	 *
	 * @param string $content_type Overriding default json header.
	 * @param int    $access_token Access Token.
	 * @return mixed Array of parameters
	 */
	public function set_remote_post_args( $content_type = 'application/json', $access_token = false ) {

		// We must have access token, so if false try to get it out of the database/transient.
		if ( ! $access_token ) {
			$settings = $this->get_settings();
			if ( ! isset( $settings['access_token'] ) || ! $settings['access_token'] ) {
				return new WP_Error( 'dropbox_api', __( 'You need to authorise Envira Gallery to access your Dropbox account. Do this through Envira Gallery > Settings > Dropbox.', 'envira-dropbox-importer' ) );
			} else {
				$access_token = $settings['access_token'];
			}
		}

		// Build base args.
		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
			),
			'timeout' => 300,
		);

		// If Content Type is false, don't pass it. If true, pass Dropbox's required JSON header.
		if ( $content_type ) {
			$args['headers']['Content-Type'] = $content_type;
		} else {
			$args['headers']['Content-Type'] = false;
		}

		return $args;

	}

	/**
	 * Authorization and returns settings passed by Dropbox
	 *
	 * @since 2.0.0
	 *
	 * @param string $importer_code Importer Code.
	 * @return mixed Array of Images | WP_Error
	 */
	public function get_access_token( $importer_code ) {

		if ( ! $importer_code ) {
			return false;
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->app_key . ':' . $this->app_secret ),
				'Content-Type'  => 'application/json',
			),
			'timeout' => 65,
		);

		$response = wp_remote_post( $this->dropbox_token_url . '/token?grant_type=authorization_code&code=' . $importer_code, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_body  = wp_remote_retrieve_body( $response );
		$response_array = json_decode( $response_body );

		if ( ! empty( $response_array->error ) ) {
			return $response_array;
		}

		$settings = array();
		if ( isset( $response_array->access_token ) ) {
			$settings['access_token'] = $response_array->access_token;
		}
		if ( isset( $response_array->token_type ) ) {
			$settings['token_type'] = $response_array->token_type; // bearer.
		}
		if ( isset( $response_array->uid ) ) {
			$settings['uid'] = $response_array->uid;
		}
		if ( isset( $response_array->account_id ) ) {
			$settings['account_id'] = $response_array->account_id;
		}

		return $settings;

	}

	/**
	 * Returns an array of Dropbox account information
	 *
	 * @since 2.0.0
	 *
	 * @param string $account_id Account ID.
	 * @return mixed Array of Images | WP_Error
	 */
	public function get_account( $account_id = false ) {

		$args = $this->set_remote_post_args();

		// if the account_id isn't being supplied, pull it from settings.
		if ( ! $account_id ) {
			$settings = $this->get_settings();
			if ( isset( $settings['account_id'] ) ) {
				$account_id = $settings['account_id'];
			}
		}

		if ( ! $account_id ) {
			return false;
		}

		$args['body'] = wp_json_encode( array( 'account_id' => $account_id ) );

		$url = $this->dropbox_api_url . '/users/get_account';

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			print_r( $response ); // @codingStandardsIgnoreLine
			exit;
		}

		$response_body  = wp_remote_retrieve_body( $response );
		$response_array = json_decode( $response_body );

		if ( ! empty( $response_array->error ) ) {
			return $response_array;
		} else {
			return $response_array;
		}

	}

	/**
	 * Returns an array of Dropbox images
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Path, specify the root folder as an empty string.
	 * @param int    $offset Offset.
	 * @param int    $cursor Cursor.
	 * @return mixed Array of Images | WP_Error
	 */
	public function get_files_folders( $path = '', $offset = 0, $cursor = false ) {

		// Ensure the root folder isn't a slash anymore.
		if ( '/' === $path || ! $path ) {
			$path = '';
		}

		// Get and store settings so we don't have to get_option every time.
		$this->settings = get_option( $this->base->plugin_slug );

		$formatted_results = get_transient( 'envira_dropbox_dir_' . $path );

		if ( false === ( $formatted_results ) ) {

			// Get the settings.
			// $settings = $this->get_settings();.
			$args = $this->set_remote_post_args();
			if ( is_wp_error( $args ) ) {
				if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
					error_log( 'response from args get_files_folders:', 0 ); // @codingStandardsIgnoreLine
					error_log( print_r( $args, true ), 0 ); // @codingStandardsIgnoreLine
				}
				return;
			}
			// check and see if there's a cursor, if so there's more to catch.
			if ( ! $cursor ) {
				$url          = $this->dropbox_api_url . '/files/list_folder';
				$args['body'] = wp_json_encode(
					array(
						'path'               => $path,
						'include_media_info' => false,
					)
				);
			} else {
				$url          = $this->dropbox_api_url . '/files/list_folder/continue';
				$args['body'] = wp_json_encode( array( 'cursor' => $cursor ) );
			}

			$response = wp_remote_post( $url, $args );
			if ( is_wp_error( $response ) ) {
				if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
					error_log( 'ERROR. response from get_files_folders:', 0 ); // @codingStandardsIgnoreLine
					error_log( print_r( $response, true ), 0 ); // @codingStandardsIgnoreLine
				}
				print_r( $response ); // @codingStandardsIgnoreLine
				exit;
			}

			if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
				error_log( 'response from get_files_folders:', 0 ); // @codingStandardsIgnoreLine
				error_log( print_r( $response, true ), 0 ); // @codingStandardsIgnoreLine
			}

			$status_code = wp_remote_retrieve_response_code( $response );

			if ( '400' === intval( $status_code ) ) {
				return new WP_Error( 'dropbox_api', 'An error has been encountered. You may need to reauthenticate with Dropbox (see Envira Settings -> Dropbox).' );
			} elseif ( 200 !== intval( $status_code ) ) {
				return new WP_Error( 'dropbox_api', 'An error has been encountered. You may need to try again later or reauthenticate with Dropbox (see Envira Settings -> Dropbox).' );
			}

			$response_body  = wp_remote_retrieve_body( $response );
			$response_array = json_decode( $response_body, true );

			if ( ! empty( $response_array->error ) ) {
				if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
					error_log( 'response (array) from get_files_folders:', 0 ); // @codingStandardsIgnoreLine
					error_log( print_r( $response_array, true ), 0 ); // @codingStandardsIgnoreLine
				}
				// An error occured querying Dropbox.
				return new WP_Error( 'dropbox_api', $response_array->error );
			}

			if ( ! empty( $this->settings['filename_order'] ) ) {

				if ( ! function_exists( 'compare_dropbox_results' ) ) {

					/**
					 * Compare Results
					 *
					 * @since 1.0.0
					 *
					 * @param int $a A.
					 * @param int $b B.
					 */
					function compare_dropbox_results_desc( $a, $b ) {
						if ( $a['name'] === $b['name'] ) {
							return 0;
						}
						return strcmp( $a['name'], $b['name'] );
					}
				}

				if ( ! function_exists( 'compare_dropbox_results' ) ) {

					/**
					 * Compare Results
					 *
					 * @since 1.0.0
					 *
					 * @param int $a A.
					 * @param int $b B.
					 */
					function compare_dropbox_results_asc( $a, $b ) {
						if ( $a['name'] === $b['name'] ) {
							return 0;
						}
						return strcmp( $b['name'], $a['name'] );
					}
				}

				if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
					error_log( 'Envira Dropbix API:', 0 ); // @codingStandardsIgnoreLine
					error_log( print_r( $response_array['entries'], TRUE ) , 0); // @codingStandardsIgnoreLine
				}
				if ( 'new-to-old' === $this->settings['filename_order'] ) {

					usort( $response_array['entries'], 'compare_dropbox_results_desc' );

				} else {

					usort( $response_array['entries'], 'compare_dropbox_results_asc' );

				}
			}

			// Prep thumbnails and results.
			if ( ! empty( $this->settings['thumbnail_view'] ) ) {
				$results = $this->prepare_thumbnails( $response_array['entries'], null, $path, false );
			} else {
				$results = $this->prepare_thumbnails( $response_array['entries'], null, $path, true );
			}

			$formatted_results = $this->format_results( $results );

			$this->last_cursor = $response_array['cursor'];
			$this->has_more    = $response_array['has_more'];
			$this->last_path   = $path;

			// Micro cache.
			$expire_time = apply_filters( 'envira_dropbox_cache_time', 15 * MINUTE_IN_SECONDS, $path );
			set_transient( 'envira_dropbox_dir_' . $path, $formatted_results, $expire_time );

		}

		// print_r ($this->format_results( $results )); exit;
		// Return results in the required format.
		return array(
			'data'        => $formatted_results,
			'last_cursor' => $this->last_cursor,
			'last_path'   => $this->last_path,
			'has_more'    => $this->has_more,
		);

	}

	/**
	 * Returns the Dropbox Authorization URL used to get a code
	 *
	 * @since 1.0.0
	 */
	public function get_authorize_url() {

		$url = 'https://www.dropbox.com/1/oauth2/authorize?locale=&client_id=' . $this->app_key . '&response_type=code';

		return $url;

	}

	/**
	 * Returns an array of Dropbox images based on the given path and search terms
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Path.
	 * @param string $search Search Term(s).
	 * @return mixed Array of Images | WP_Error
	 */
	public function search_files_folders( $path = '', $search ) {

		// Ensure the root folder isn't a slash anymore.
		if ( '/' === $path || ! $path ) {
			$path = '';
		}

		$transient_name = sanitize_text_field( 'envira_dropbox_search_dir_' . $path . '_' . $search );

		$formatted_results = get_transient( $transient_name );

		if ( false === ( formatted_results ) ) {

			// Get settings.
			$settings = $this->get_settings();

			$url  = $this->dropbox_api_url . '/files/search';
			$args = $this->set_remote_post_args();

			if ( is_wp_error( $args ) ) {
				print_r( $args ); // @codingStandardsIgnoreLine
				exit;
			}

			$args['body'] = wp_json_encode(
				array(
					'path'        => $path,
					'query'       => (string) $search,
					'max_results' => 20,
				)
			);

			$response = wp_remote_post( $url, $args );
			if ( is_wp_error( $response ) ) {
				print_r( $response ); // @codingStandardsIgnoreLine
				exit;
			}

			$response_body  = wp_remote_retrieve_body( $response );
			$response_array = json_decode( $response_body, true );

			if ( ! empty( $response_array->error ) ) {
				// An error occured querying Dropbox.
				return new WP_Error( 'dropbox_api', $response_array->error );
			}

			// Check results exist.
			if ( empty( $response_array['matches'] ) ) {
				return new WP_Error( 'dropbox_api', __( 'No Results found...', 'envira-dropbox-importer' ) );
			}

			foreach ( $response_array['matches'] as $match ) {
				$response_array['entries'][] = $match['metadata'];
			}

			if ( empty( $this->settings['thumbnail_view'] ) || ( ! empty( $this->settings['thumbnail_view'] ) && 'yes' === $this->settings['thumbnail_view'] ) ) {
				$results = $this->prepare_thumbnails( $response_array['entries'], null, $path, false );
			} else {
				$results = $this->prepare_thumbnails( $response_array['entries'], null, $path, true );
			}

			$formatted_results = $this->format_results( $results );

			// Micro cache.
			$expire_time = apply_filters( 'envira_dropbox_search_dir_', 15 * MINUTE_IN_SECONDS, $path );
			set_transient( $transient_name, $formatted_results, $expire_time );

		}

		// Return results in the required format.
		return $formatted_results;

	}

	/**
	 * Iterates through a Dropbox resultset of files and folders,
	 * generating local thumbnails before returning results
	 *
	 * @since 1.0.0
	 *
	 * @param array   $results    Dropbox Files / Folders.
	 * @param object  $client     Dropbox Client Instance.
	 * @param string  $path       Dropbox Path.
	 * @param boolean $no_thumbnails Get/Show Thumbnails.
	 * @return array                Dropbox Files / Folders
	 */
	private function prepare_thumbnails( $results, $client, $path = '', $no_thumbnails = false ) {

		// Get thumbnails dir.
		$common              = Envira_Dropbox_Importer_Common::get_instance();
		$thumbnails_dir_path = $common->get_thumbnails_dir_path();
		$thumbnails_dir_url  = $common->get_thumbnails_dir_url();

		// Setup WP_Filesystem.
		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' );
		}
		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			define( 'FS_CHMOD_DIR', 0755 );
		}
		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', 0666 );
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		global $wp_filesystem;
		WP_Filesystem();

		// Create thumbnails dir if it doesn't exist.
		if ( ! $wp_filesystem->is_dir( $thumbnails_dir_path ) ) {
			$result = $wp_filesystem->mkdir( $thumbnails_dir_path );
			if ( ! $result ) {
				return false;
			}
		}

		// Get support filetypes for Envira.
		$supported_filetypes = Envira_Gallery_Common::get_instance()->get_supported_filetypes();

		// If the path isn't a top level path, prepend results with a parent folder option
		// This allows the user to navigate up one level to go back a step.
		$parent_path = Envira_Dropbox_Importer_Common::get_instance()->get_parent_path( $path );
		if ( ! empty( $parent_path ) ) {
			$result = array(
				'.tag'         => 'folder',
				'name'         => $parent_path,
				'rev'          => '0',
				'thumb_exists' => false,
				'path_lower'   => $parent_path,
				'is_dir'       => true,
				'icon'         => 'folder',
				'read_only'    => '',
				'modifier'     => '',
				'bytes'        => 0,
				'modified'     => '',
				'size'         => '',
				'root'         => '',
				'revision'     => '',
			);

			array_unshift( $results, $result );
		}

		$images = array();

		// Iterate through results. If a result is an image, get the thumbnail
		// and store it in $thumbnails_dir_path if we don't already have it.
		foreach ( $results as $key => $result_object ) {

			if ( is_object( $result_object ) ) {
				$result = get_object_vars( $result_object );
			} elseif ( is_array( $result_object ) ) {
				$result = $result_object;
			}

			// Check if a directory.
			if ( 'folder' === $result['.tag'] ) {
				// Add thumbnail to results array.
				$images[ $key ] = $result;
				continue;
			}

			$images[ $key ] = $result;

			// Check file is a support image type.
			$supported_filetype = false;
			foreach ( $supported_filetypes as $types ) {
				$extension = substr( strrchr( $result['name'], '.' ), 1 );
				if ( strpos( $types['extensions'], $extension ) !== false ) {
					$supported_filetype = true;
				}
			}

			// Remove any non-supported files from results.
			if ( 'file' === $result['.tag'] && ! $supported_filetype ) {
				unset( $images[ $key ] );
				continue;
			}

			if ( $no_thumbnails ) {
				$images[ $key ]['thumbnail'] = false;
				continue;
			}

			// Check if a thumbnail already exists.
			$local_thumbnail = $thumbnails_dir_path . $result['path_lower'];
			if ( $wp_filesystem->is_file( $local_thumbnail ) ) {
				$images[ $key ]['thumbnail'] = $thumbnails_dir_url . $result['path_lower'];
				continue;
			}

			// Thumbnail does not exist
			// Split path and filename by /
			// All paths start with /, so we ignore the first one.
			$path_parts = explode( '/', substr( $result['path_lower'], 1 ) );

			// If we have more than 1 value in the array, this file is in a Dropbox subfolder
			// Check subfolder(s) exist on this WordPress install + create if necessary.
			$count = count( $path_parts );
			if ( $count > 1 ) {
				$local_thumbnails_dir_path = $thumbnails_dir_path;

				foreach ( $path_parts as $i => $subfolder ) {
					// Skip last array value, as this is the filename.
					if ( ( $count - 1 ) === $i ) {
						break;
					}

					// Create subfolder if it doesn't exist.
					$local_thumbnails_dir_path .= '/' . $subfolder;
					if ( ! $wp_filesystem->is_dir( $local_thumbnails_dir_path ) ) {
						if ( ! $wp_filesystem->mkdir( $local_thumbnails_dir_path ) ) {
							continue;
						}
					}
				}
			}

			// Create the thumbnail.
			$thumb = $this->get_thumbnail( $result['path_lower'], 'jpeg', 'w128h128' );

			if ( is_null( $thumb ) || ! $thumb ) {
				$images[ $key ]['thumbnail'] = false;
				continue;
			}
			if ( ! $wp_filesystem->put_contents( $thumbnails_dir_path . $result['path_lower'], $thumb ) ) {
				$images[ $key ]['thumbnail'] = false;
				continue;
			}

			// Add thumbnail to results array.
			$images[ $key ]['thumbnail'] = $thumbnails_dir_url . $result['path_lower'];
		}

		return $images;

	}

	/**
	 * Uses cURL rather than file_get_contents to make a request to the specified URL.
	 *
	 * @param    string $path       File path.
	 * @param    string $format     Format (default JPEG).
	 * @param    string $size       Size.
	 * @return   string    $output    The result of the request.
	 */
	public function get_thumbnail( $path, $format = 'jpeg', $size = 'w128h128' ) {

		if ( ! $path ) {
			return;
		}

		// if the account_id isn't being supplied, pull it from settings.
		$settings = $this->get_settings();
		$url      = $this->dropbox_api_content_url . '/files/get_thumbnail';

		$args                               = $this->set_remote_post_args( false, $settings['access_token'] );
		$args['headers']['Dropbox-API-Arg'] = wp_json_encode(
			array(
				'path'   => $path,
				'format' => $format,
				'size'   => $size,
			)
		);

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response->get_error_message();
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== intval( $status_code ) ) {
			return false;
		}

		$thumb = wp_remote_retrieve_body( $response );

		return $thumb;

	}

	/**
	 * Downloads the contents of a Dropbox File into the specified local file
	 *
	 * @since 1.0.0
	 *
	 * @param string $path  Path and Filename on Dropbox.
	 * @param array  $settings  Settings.
	 * @return array        File Contents
	 */
	public function download_file( $path, $settings = false ) {

		if ( ! $path ) {
			return;
		}

		// If the account_id or settings hasn't been passed through pull from database/transient.
		if ( ! $settings ) {
			$settings = $this->get_settings();
		}

		// Check For Token.
		if ( ! isset( $settings['access_token'] ) ) {
			return new WP_Error( 'dropbox_api', __( 'You need to authorise Envira Gallery to access your Dropbox account. Do this through Envira Gallery > Settings > Dropbox.', 'envira-dropbox-importer' ) );
		}

		// Get instances.
		$common = Envira_Dropbox_Importer_Common::get_instance();

		// Assembly URL for Dropbox API.
		$url = $this->dropbox_api_content_url . '/files/download';

		$args                               = $this->set_remote_post_args( false, $settings['access_token'] );
		$args['headers']['Dropbox-API-Arg'] = wp_json_encode( array( 'path' => $path ) );

		// Determine if temp directory exists, if not create it...
		// ...also setup the global vars just in case.
		$temp_dir_path = $common->get_tmp_dir_path();
		$temp_dir_url  = $common->get_tmp_dir_url();

		// Setup WP_Filesystem.
		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' );
		}
		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			define( 'FS_CHMOD_DIR', 0755 );
		}
		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', 0666 );
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		global $wp_filesystem;
		WP_Filesystem();

		// Create thumbnails dir if it doesn't exist.
		if ( ! $wp_filesystem->is_dir( $temp_dir_path ) ) {
			$result = $wp_filesystem->mkdir( $temp_dir_path );
			if ( ! $result ) {
				return new WP_Error( 'dropbox_api', 'Unable to create tmp directory. Check file permissions.' );
			}
		}

		// Query Dropbox.
		try {

			// we used to have global $wp_filesystem;
			// WP_Filesystem();.
			$response = wp_remote_post( $url, $args );
			if ( is_wp_error( $response ) ) {
				echo esc_html( $return->get_error_message() );
				exit;
			}
			if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
				error_log( 'got a response from download dropbox', 0 );
			}

			$status_code = wp_remote_retrieve_response_code( $response );
			if ( defined( 'ENVIRA_DEBUG' ) && ENVIRA_DEBUG ) {
				error_log( 'status code:', 0 );
				error_log( $status_code );
			}

			if ( is_wp_error( $status_code ) || 200 !== intval( $status_code ) ) {
				return new WP_Error( 'dropbox_api', '123Dropbox Error: File may not exist or is too large.' );
			}

			$photo = wp_remote_retrieve_body( $response );

			// Create file to store downloaded image in.
			$local_file     = $common->get_tmp_dir_path() . '/' . basename( $path );
			$local_file_url = $common->get_tmp_dir_url() . '/' . rawurlencode( basename( $path ) );
			if ( ! $wp_filesystem->put_contents( $local_file, 'test test test me', FS_CHMOD_FILE ) ) {
				/* // @codingStandardsIgnoreLine
				Comment: echo $local_file;
				Comment: print_r( $photo );
				*/
				return new WP_Error( 'dropbox_api', 'Error saving file.' );
			}

			$result = $wp_filesystem->put_contents( $local_file, $photo );
			// fclose( $f );
			// Return result.
			return array(
				'result'         => $result,
				'local_file'     => $local_file,
				'local_file_url' => $local_file_url,
			);
		} catch ( Exception $e ) {
			// An error occured querying Dropbox.
			return new WP_Error( 'dropbox_api', $e->getMessage() );
		}

	}

	/**
	 * Builds an array of Dropbox results that are compatible with Envira Gallery
	 *
	 * @since 1.1.6
	 *
	 * @param   array $results    Dropbox Results.
	 * @return  array               Envira Gallery Results
	 */
	public function format_results( $results ) {

		// Iterate through results, building in the format that we support.
		$items = array();

		foreach ( $results as $key => $result ) {

			$extension = substr( strrchr( $result['name'], '.' ), 1 );

			$items[] = array(
				'id'        => ( isset( $result['path_lower'] ) ? $result['path_lower'] : '' ),
				'is_dir'    => ( ( isset( $result['is_dir'] ) && true === $result['is_dir'] ) || ( ( isset( $result['.tag'] ) && 'folder' === $result['.tag'] ) ) ? true : false ),
				'mime_type' => ( isset( $extension ) ? $extension : '' ),
				'title'     => ( isset( $result['name'] ) ? $result['name'] : '' ),
				'thumbnail' => ( isset( $result['thumbnail'] ) ? $result['thumbnail'] : false ),
			);
		}

		return $items;

	}

	/**
	 * Builds an array of Dropbox search results that are compatible with Envira Gallery
	 *
	 * @since 1.1.6
	 *
	 * @param   array $results    Dropbox Results.
	 * @return  array               Envira Gallery Results
	 */
	public function format_search_results( $results ) {

		// Iterate through results, building in the format that we support.
		$items = array();
		foreach ( $results as $key => $search_result ) {

			$result = get_object_vars( $search_result['metadata'] );

			$extension = substr( strrchr( $result['name'], '.' ), 1 );

			$items[] = array(
				'id'        => ( isset( $result['path_lower'] ) ? $result['path_lower'] : '' ),
				'is_dir'    => ( ( isset( $result['is_dir'] ) && true === $result['is_dir'] ) || ( ( isset( $result['.tag'] ) && 'folder' === $result['.tag'] ) ) ? true : false ),
				'mime_type' => ( isset( $extension ) ? $extension : '' ),
				'title'     => ( isset( $result['name'] ) ? $result['name'] : '' ),
				'thumbnail' => ( isset( $result['thumbnail'] ) ? $result['thumbnail'] : false ),
			);
		}

		return $items;

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Dropbox_Importer_Metaboxes object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Dropbox_Importer_Dropbox ) ) {
			self::$instance = new Envira_Dropbox_Importer_Dropbox();
		}

		return self::$instance;

	}

}

// Load the dropbox class.
$envira_dropbox_importer_dropbox = Envira_Dropbox_Importer_Dropbox::get_instance();
