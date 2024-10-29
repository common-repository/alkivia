<?php
/**
Core Component: Profiles
Parent ID: akucom
Component Name: User Profiles
Description: Manages public profiles pages.
Author: Jordi Canals
Link: http://alkivia.org
*/

/**
 * AOC User Profiles component.
 * Plugin to create and manage communities in any WordPress blog.
 *
 * @version		$Rev: 203885 $
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
if ( ! defined('AOC_PATH') ) {
	die ('');	// Silence is gold.
}

require_once( dirname(__FILE__) . '/component.php');

/**
 * Displays a user profile activity wall entry
 *
 * @param array $args Event data.
 * @return string Text to display.
 */
function _aoc_wall_profile ( $args )
{
    $tdomain = ak_get_object('akucom')->ID;
    extract($args, EXTR_SKIP);

    switch ( $object_action ) {
        case 'update' :
            $text = sprintf(__('%s updated the profile data.', $tdomain), aoc_user_anchor($owner_id));
            break;
        case 'image' :
            $text = sprintf(__('%s selected a new profile image.', $tdomain), aoc_user_anchor($owner_id));
            break;
        default :
            $text = $event_params['display'];
    }

    return $text;
}

// ============================================== SHORTCUT FUNCTIONS ==========

/**
 * Checks id a user is in an allowed role.
 * This is an Alias of aocProfiles::userInAllowedRole()
 *
 * @see aocProfiles::userInAllowedRole()
 * @param int|object $user	User ID or user object to check
 * @return boolean	If the user can be shown or not.
 */
function aoc_can_show_user ( $user )
{
    return ak_get_object('akucom_profiles')->canShowUser($user);
}

/**
 * Shortcut to create a link to the user profile.
 * To be used in other components without worring about changing the 'user' slug.
 *
 * @uses aoc_create_link()
 * @param $user_name	User login name.
 * @return string	Base link to the user profile page.
 */
function aoc_profile_link( $user_name = '' )
{
	return aoc_create_link('user', urlencode($user_name));
}

/**
 * Shortcut to return the roles that can be shown in Community
 * This is an alias of aocProfiles::getAllowedRoles()
 *
 * @see aocProfiles::getAllowedRoles()
 * @return array	The allowed roles.
 */

function aoc_allowed_roles()
{
	return ak_get_object('akucom_profiles')->getAllowedRoles();
}

/**
 * Returns a subquery string to select users from allowed roles.
 *
 * @uses aocProfiles::getAllowedRoles()
 * @return string	The select subquery.
 */
function aoc_roles_subquery()
{
	global $wpdb;

	$keys = array();
	$roles = ak_get_object('akucom_profiles')->getAllowedRoles();
	foreach ($roles as $value) {
		$keys[] = "meta_value LIKE '%{$value}%'";
	}

	$subquery = "SELECT * FROM {$wpdb->usermeta} INNER JOIN {$wpdb->users} "
		. "ON {$wpdb->usermeta}.user_id = {$wpdb->users}.id "
		. "WHERE meta_key='{$wpdb->prefix}capabilities' AND (". implode(' OR ', $keys) .")";

	return $subquery;
}

// ================================================= START PROCEDURE ==========

ak_create_object('akucom_profiles', new aocProfiles(__FILE__));
