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

	$user_id              = get_current_user_id();
	$avatar_directory     = trailingslashit( bp_core_avatar_upload_path() ) . 'avatars/' . $user_id . '/';
	$avatar_directory_url = trailingslashit( bp_core_avatar_url() ) . 'avatars/' . $user_id . '/';

	if ( wp_mkdir_p( $avatar_directory ) ) {
		$new_file_name =  wp_unique_filename($avatar_directory , basename( $file ) );
		$new_file_path = path_join( $avatar_directory, $new_file_name );
		$done = @copy( $file, $new_file_path );
		// Set correct file permissions.
		$stat  = stat( dirname( $new_file_path ) );
		$perms = $stat['mode'] & 0000666;
		@ chmod( $new_file_path, $perms );
		$new_url = $avatar_directory_url . basename( $new_file_path );
	}

	if ( ! $done ) {
		bp_core_add_message( __( 'Upload Failed!', 'mpp-set-profile-photo' ), 'error' );
	}

	// fake it
	$fake_args = array(
		'file' => $new_file_path,
		'url'  => $new_url,
		'type' => mime_content_type( $new_file_path ),
	);

	$avatar_attachment = new BP_Attachment_Avatar();
	$bp->avatar_admin->original = $fake_args;

	// The Avatar UI available width.
	$ui_available_width = 0;

	if ( ! empty( $bp->avatar_admin->original['error'] ) ) {
		bp_core_add_message( sprintf( __( 'Upload Failed! Error was: %s', 'mpp-set-profile-photo' ), $bp->avatar_admin->original['error'] ), 'error' );
		return false;
	}

	// The Avatar UI available width.
	$ui_available_width = 0;

	// Try to set the ui_available_width using the avatar_admin global.
	if ( isset( $bp->avatar_admin->ui_available_width ) ) {
		$ui_available_width = $bp->avatar_admin->ui_available_width;
	}

	// Maybe resize.
	$bp->avatar_admin->resized = $avatar_attachment->shrink( $bp->avatar_admin->original['file'], $ui_available_width );
	$bp->avatar_admin->image   = new stdClass();

	// We only want to handle one image after resize.
	if ( empty( $bp->avatar_admin->resized ) ) {
		$bp->avatar_admin->image->file = $bp->avatar_admin->original['file'];
		$bp->avatar_admin->image->dir  = str_replace( $upload_path, '', $bp->avatar_admin->original['file'] );
	} else {
		$bp->avatar_admin->image->file = $bp->avatar_admin->resized['path'];
		$bp->avatar_admin->image->dir  = str_replace( $upload_path, '', $bp->avatar_admin->resized['path'] );
		@unlink( $bp->avatar_admin->original['file'] );
	}

	// Check for WP_Error on what should be an image.
	if ( is_wp_error( $bp->avatar_admin->image->dir ) ) {
		bp_core_add_message( sprintf( __( 'Upload failed! Error was: %s', 'mpp-set-profile-photo' ), $bp->avatar_admin->image->dir->get_error_message() ), 'error' );
		return false;
	}

	// If the uploaded image is smaller than the "full" dimensions, throw a warning.
	if ( $avatar_attachment->is_too_small( $bp->avatar_admin->image->file ) ) {
		bp_core_add_message( sprintf( __( 'You have selected an image that is smaller than recommended. For best results, upload a picture larger than %d x %d pixels.', 'mpp-set-profile-photo' ), bp_core_avatar_full_width(), bp_core_avatar_full_height() ), 'error' );
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