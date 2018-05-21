<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * Class MPP_Set_Profile_Photo_Template_Helper
 */
class MPP_Set_Profile_Photo_Template_Helper {

	/**
	 * The constructor.
	 */
	public function __construct () {
		$this->setup();
	}

	/**
	 * Setup actions
	 */
	private function setup() {
		//add links for changing cover/profile photo
		add_action( 'mpp_media_meta', array( $this, 'add_link' ) );
		add_action( 'mpp_lightbox_media_meta', array( $this, 'add_link' ) );
	}

	/**
	 * Add links
	 *
	 * @param null|MPP_Media $media
	 */
	public function add_link(  $media = null ) {

		$media = mpp_get_media( $media );

		//The media must be photo and uploaded by the user
		if ( $media->type != 'photo'  || bp_loggedin_user_id() != $media->user_id ) {
			return;
		}

		//add change avatar link
		if ( ! bp_disable_avatar_uploads() ) {
			echo $this->get_change_avatar_url( $media->id );
		}
	}

	/**
	 * Get change avatar url
	 *
	 * @param int    $media_id Media id.
	 * @param string $label Label.
	 * @param string $css_class CSS class.
	 *
	 * @return mixed|void
	 */
	public function get_change_avatar_url( $media_id, $label = '', $css_class = '' ) {

		if( ! $label ) {
			$label = __( 'Set Profile Photo', 'mpp-set-profile-photo' );
		}

		$css_class  = 'mpp-set-profile-photo ' . $css_class;
		$url        = $this->get_query_string( $media_id );
		$link       = sprintf(  '<a href="%s" class="%s" title="%s">%s</a>', $url, $css_class, $label, $label );

		return apply_filters( 'mpp_set_profile_avatar_link', $link, $url, $media_id );
	}

	/**
	 * Get Query string.
	 *
	 * @param int $media_id Media id.
	 *
	 * @return string
	 */
	public function get_query_string( $media_id ) {

		$url = trailingslashit( bp_loggedin_user_domain() . bp_get_profile_slug() ) . 'change-avatar/';
		$url = add_query_arg( array( 'mpp-set-profile-photo'=> 1, 'media-id' => $media_id ), $url );

		return $url;
	}
}

new MPP_Set_Profile_Photo_Template_Helper();

