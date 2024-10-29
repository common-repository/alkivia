<?php
/*
Module Component: Gallery
Parent ID: akucom
Component Name: Photo Gallery
Description: Manages user photo galleries for Alkivia Open Community.
Author: Jordi Canals
Link: http://alkivia.org
*/

/**
 * AOC Gallery Component.
 * Plugin to create and manage communities in any WordPress blog.
 *
 * @version		$Rev: 841 $
 * @author		Jordi Canals
 * @copyright   Copyright (C) 2009, 2010 Jordi Canals
 * @license		GNU General Public License version 2
 * @link		http://alkivia.org
 * @package		Alkivia
 * @subpackage	Community
 *

	Copyright 2009, 2010 Jordi Canals <devel@jcanals.cat>

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	version 2 as published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// File cannot be called directly
if ( ! defined ('AOC_PATH') ) {
	die ('');	// Silence is gold.
}

require_once ( dirname(__FILE__) . '/component.php' );

/**
 * Displays a user profile activity wall entry
 *
 * @param array $args Event data.
 * @return string Text to display.
 */
function _aoc_wall_gallery ( $args )
{
    $tdomain = ak_get_object('akucom')->ID;

    extract($args, EXTR_SKIP);

    switch ( $object_action ) {
        case 'upload' :
            $text = sprintf(__('%s uploaded a new image to the user gallery.', $tdomain), aoc_user_anchor($owner_id));
            break;
        default :
            $text = $event_params['display'];
    }

    return $text;
}

// ============================================== SHORTCUT FUNCTIONS ==========

/**
 * Returns the max upload size in bytes.
 * Compares system to settings.
 *
 * @uses aocGallery::maxUpload()
 * @return int	Maximum upload size in bytes.
 */
function aoc_gallery_max_upload ()
{
	return ak_get_object('akucom_gallery')->maxUpload();
}

/**
 * Retrieves the imag tag with the link to the default user image.
 *
 * @uses aocGallery::getUserImage()
 * @param int $nice_name	User ID
 * @param string $alt		Alternate label for the image.
 * @return string			<img> tag for the image.
 */
function aoc_get_user_image ( $user_id, $class = '', $thumbnail = false )
{
	return ak_get_object('akucom_gallery')->getUserImage($user_id, $class, $thumbnail);
}

/**
 * Returns an array with all gallery links.
 * This provides two data for every image in the gallery: large image url and thumb <img> tag.
 *
 * @uses aocGallery::userGalleryLinks()
 * @param int $user_id	The user id we want the gallery
 * @param string $class	Class for the <img> tag.
 * @return array		Array cointaining links to large image and <img> tags for thumbnails.
 */
function aoc_get_user_gallery ( $user_id, $class = 'user-thumbnail' )
{
	return ak_get_object('akucom_gallery')->userGalleryLinks($user_id, $class);
}

/**
 * Returns the img tag for the user profile image with a link to gallery.
 *
 * @param object $user User object for which we want the image.
 * @param boolean $thumbnail Set to true if want thumbnail instead the large size.
 * @return string HTML format for the img tag and the link to gallery.
 */
function aoc_profile_picture ( $user, $thumbnail = false )
{
    return ak_get_object('akucom_gallery')->getProfilePicture($user, $thumbnail);
}
/**
 * Returns a User Gallery page.
 * @param string $user_login_or_id Login Name or ID for the user.
 * @return string|false	The formated user gallery or false if the gallery cannot be shown.
 */
function aoc_gallery_content ( $user_login_or_id )
{
    return ak_get_object('akucom_gallery')->userGalleryContent($user_login_or_id);
}

// ================================================= START PROCEDURE ==========

ak_create_object('akucom_gallery', new aocGallery(__FILE__));

if ( ak_get_object('akucom_gallery')->localAvatars() ) {
	/**
	 * Just to replace the get_avatar() function from WordPress.
	 *
	 * @see get_avatar() from WordPress core.
	 * @return string <img> tag for the avatar image if there is one.
	 */
	function get_avatar( $id_or_email, $size = 80, $default = '' ) {
		return ak_get_object('akucom_gallery')->getAvatar($id_or_email, $size, $default);
	}
}
