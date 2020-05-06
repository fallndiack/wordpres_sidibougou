<?php
/** // @codingStandardsIgnoreFile
 * User might have changed wp-content location, so we go through this dance *
 **/

$uri_array = explode( '/', $_SERVER['SCRIPT_FILENAME'] );
$counter   = 0;
foreach ( $uri_array as $uri_array_element ) {
	if ( $uri_array_element == 'envira-printing' ) {
		$element_position = $counter - 2;
	} else {
		$counter++;
	}
}

$parse_uri = explode( $uri_array[ $element_position ], $_SERVER['SCRIPT_FILENAME'] );
require_once $parse_uri[0] . 'wp-load.php';

$image_url = esc_url( $_REQUEST['envira_printing_image'] );

?>

<!DOCTYPE html>
<html lang="en-us">
<head>
	<style>
		img{display: block; width: 100%; height: 100%;}
	</style>
</head>
<body>
	<img src="<?php echo $image_url; ?>" />
	<script type="text/javascript">
		window.print();
	</script>
</body>
</html>
