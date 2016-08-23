<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Is it change profile photo screen and was our media used?
 *
 * @return bool
 */
function mpp_spp_is_change_photo() {
	
	if ( ! bp_is_my_profile() || ! bp_is_user_change_avatar()  || ! isset( $_GET['mpp-set-profile-photo'] ) )  {
		return false;
	}
	
	$media_id = absint( $_GET['media-id'] );
	
	$media = mpp_get_media( $media_id );
	//must be a photo and owned by the logged in user
	if ( empty( $media ) || $media->type != 'photo' || $media->user_id != bp_loggedin_user_id() ) {
		return false;
	}
	
	return true;
	
}
