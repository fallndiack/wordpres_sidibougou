<?php
/**
 * Outputs the Album Code Metabox Content.
 *
 * @since   1.3.0
 *
 * @package Envira_Album
 * @author  Envira Team
 */

?>
<p><?php esc_html_e( 'You can place this album anywhere into your posts, pages, custom post types or widgets by using one of the shortcode(s) below:', 'envira-albums' ); ?></p>
<div class="envira-code">
	<input readonly type="text" class="code-textfield" id="envira_shortcode_id_<?php echo sanitize_html_class( $data['post']->ID ); ?>" value="[envira-album id=&quot;<?php echo sanitize_html_class( $data['post']->ID ); ?>&quot;]">
	<a href="#" title="<?php esc_attr_e( 'Copy Shortcode to Clipboard', 'envira-albums' ); ?>" data-clipboard-target="#envira_shortcode_id_<?php echo sanitize_html_class( $data['post']->ID ); ?>" class="dashicons dashicons-clipboard envira-clipboard">
		<span><?php esc_html_e( 'Copy to Clipboard', 'envira-albums' ); ?></span>
	</a>
</div>

<?php
if ( ! empty( $data['album_data']['config']['slug'] ) ) {
	?>
	<div class="envira-code">
	<input readonly type="text" class="code-textfield" id="envira_shortcode_id_<?php echo sanitize_html_class( $data['post']->ID ); ?>" value="[envira-album slug=&quot;<?php echo sanitize_html_class( $data['album_data']['config']['slug'] ); ?>&quot;]">
		<a href="#" title="<?php esc_attr_e( 'Copy Shortcode to Clipboard', 'envira-albums' ); ?>" data-clipboard-target="#envira_shortcode_slug_<?php echo sanitize_html_class( $data['post']->ID ); ?>" class="dashicons dashicons-clipboard envira-clipboard">
			<span><?php esc_html_e( 'Copy to Clipboard', 'envira-albums' ); ?></span>
		</a>
	</div>
	<?php
}
?>
<?php
if ( ! empty( $data['album_data']['config']['slug'] ) ) {
	?>
<p><?php esc_html_e( 'You can also place this album into your template files by using one of the template tag(s) below:', 'envira-albums' ); ?></p>
	<?php } ?>
<?php
if ( empty( $data['album_data']['config']['slug'] ) ) {
	?>
	<p><?php esc_html_e( 'You can also place this album into your template files by using the template tag below:', 'envira-albums' ); ?></p>
	<?php } ?>
<div class="envira-code">
	<textarea readonly rows="2" class="code-textfield" id="envira_template_tag_id_<?php echo sanitize_html_class( $data['post']->ID ); ?>"><?php echo 'if ( function_exists( \'envira_album\' ) ) { envira_album( \'' . sanitize_html_class( $data['post']->ID ) . '\' ); }'; ?></textarea>
	<a href="#" title="<?php esc_attr_e( 'Copy Template Tag to Clipboard', 'envira-albums' ); ?>" data-clipboard-target="#envira_template_tag_id_<?php echo sanitize_html_class( $data['post']->ID ); ?>" class="dashicons dashicons-clipboard envira-clipboard">
		<span><?php esc_html_e( 'Copy to Clipboard', 'envira-albums' ); ?></span>
	</a>
</div>

<?php
if ( ! empty( $data['album_data']['config']['slug'] ) ) {
	?>
	<div class="envira-code">
		<textarea readonly rows="2" class="code-textfield" id="envira_template_tag_slug_<?php echo sanitize_html_class( $data['post']->ID ); ?>"><?php echo 'if ( function_exists( \'envira_album\' ) ) { envira_album( \'' . sanitize_html_class( $data['album_data']['config']['slug'] ) . '\', \'slug\' ); }'; ?></textarea>
		<a href="#" title="<?php esc_attr_e( 'Copy Template Tag to Clipboard', 'envira-albums' ); ?>" data-clipboard-target="#envira_template_tag_slug_<?php echo sanitize_html_class( $data['post']->ID ); ?>" class="dashicons dashicons-clipboard envira-clipboard">
			<span><?php esc_html_e( 'Copy to Clipboard', 'envira-albums' ); ?></span>
		</a>
	</div>
	<?php
}
