<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup environment/variables etc if it is our profile photo change page
 */

function mpp_spp_setup_profile_photo_change_screen() {

	// Bail if not the correct screen.
	if ( ! mpp_spp_is_change_photo() ) {
		return false;
	}

	$bp = buddypress();

	add_action( 'wp_head', 'mpp_spp_add_custom_css');

	add_filter( 'bp_core_avatar_folder_dir', 'mpp_spp_create_user_avatar_dir_if_not_exists' );

	if ( ! isset( $bp->avatar_admin ) ) {
		$bp->avatar_admin = new stdClass();
	}

	mpp_spp_set_profile_photo();

	$bp->avatar_admin->step = 'crop-image';

	add_action( 'wp_print_scripts', 'bp_core_add_jquery_cropper' );

}

add_action( 'bp_screens', 'mpp_spp_setup_profile_photo_change_screen', 200 );

/**
 * Set profile photo
 * @return bool
 */
function mpp_spp_set_profile_photo() {

	// Setup some variables.
	$bp          = buddypress();
	$upload_path = bp_core_avatar_upload_path();

	$media = mpp_get_media( absint( $_GET['media-id'] ) );
	$file = mpp_get_media_path( '', $media );

	// fake it
	$fake_args = array(
		'file' => $file,
		'url'  => mpp_get_media_src( 'original', $media ),
		'type' => mime_content_type( $file ),
	);

	$avatar_attachment = new BP_Attachment_Avatar();
	$bp->avatar_admin->original = $fake_args;

	// The Avatar UI available width.
	$ui_available_width = 0;

	// Try to set the ui_available_width using the avatar_admin global.
	if ( isset( $bp->avatar_admin->ui_available_width ) ) {
		$ui_available_width = $bp->avatar_admin->ui_available_width;
	}

	// Maybe resize.
	//$bp->avatar_admin->resized = null;
	$bp->avatar_admin->resized = $avatar_attachment->shrink( $bp->avatar_admin->original['file'], $ui_available_width );
	$bp->avatar_admin->image   = new stdClass();

	// We only want to handle one image after resize.
	if ( empty( $bp->avatar_admin->resized ) ) {
		$bp->avatar_admin->image->file = $bp->avatar_admin->original['file'];
		$bp->avatar_admin->image->dir  = str_replace( $upload_path, '', $bp->avatar_admin->original['file'] );
	} else {
		$bp->avatar_admin->image->file = $bp->avatar_admin->resized['path'];
		$bp->avatar_admin->image->dir  = str_replace( $upload_path, '', $bp->avatar_admin->resized['path'] );
		//@unlink( $bp->avatar_admin->original['file'] );
	}

	// Check for WP_Error on what should be an image.
	if ( is_wp_error( $bp->avatar_admin->image->dir ) ) {
		bp_core_add_message( sprintf( __( 'Upload failed! Error was: %s', 'buddypress' ), $bp->avatar_admin->image->dir->get_error_message() ), 'error' );
		return false;
	}

	// Set the url value for the image.
	$bp->avatar_admin->image->url = bp_core_avatar_url() . $bp->avatar_admin->image->dir;

	return true;
}

/**
 * Don't let border radius fool you
 */
function mpp_spp_add_custom_css() {
?>
		<style type="text/css">
			.site #avatar-upload-form  .avatar {
				border-radius: 0;
			}
		</style>

<?php
}

/**
 * Create avatar directory if not exists
 *
 * @param $path
 *
 * @return mixed
 */
function mpp_spp_create_user_avatar_dir_if_not_exists( $path ) {

	if ( ! file_exists( $path ) ) {
		wp_mkdir_p( $path );
	}

	return $path;
}