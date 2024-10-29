<?php
/*
Module Component: Activity
Parent ID: akucom
Component Name: Activity Wall
Description: Records user activity into a log and provides an activity wall.
Author: Jordi Canals
Link: http://alkivia.org
*/

/**
 * AOC Activity Component.
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
if ( ! defined ('AOC_PATH') ) {
	die ('');	// Silence is gold.
}

require_once ( dirname(__FILE__) . '/component.php' );

/**
 * Displays a post or page activity wall entry
 *
 * @param array $args Event data.
 * @return string Text to display.
 */
function _aoc_wall_post ( $args )
{
    $tdomain = ak_get_object('akucom')->ID;

    extract($args, EXTR_SKIP);
    if ( 'insert' != $object_action && 'edit' != $object_action && 'post' != $object_type && 'page' != $object_type) {
        return $event_params['display'];
    }

    // added the page / post
    // updated the page / post

    $literal_type   = ( 'post' == $object_type ) ? __('the post', $tdomain) : __('the page', $tdomain);
    $literal_action = ( 'insert' == $object_action ) ? __('%1$s added %2$s titled %3$s', $tdomain) : __('%1$s updated %2$s %3$s', $tdomain);

    $text = sprintf($literal_action, aoc_user_anchor($owner_id), $literal_type,
            '<a href="' . get_permalink($args['object_id']) . '" rel="bookmark">' . get_the_title($args['object_id']) . '</a>');
    return $text;
}

/**
 * Displays a Comment activity wall entry
 *
 * @param array $args Event data.
 * @return string Text to display.
 */
function _aoc_wall_comment ( $args )
{
    $tdomain = ak_get_object('akucom')->ID;

    if ( 'insert' == $args['object_action'] ) {
        $text = sprintf(__('%1$s added a comment to %2$s', $tdomain),
                    aoc_user_anchor($args['owner_id']),
                    '<a href="' . get_permalink($args['object_id']) . '" rel="bookmark">' . get_the_title($args['object_id']) . '</a>');
        return $text;
    } else {
        return $args['event_params']['display'];
    }
}

// ============================================== SHORTCUT FUNCTIONS ==========

/**
 * Returns a list of arrays for activity items.
 *
 * @see aocActivity::getTheWall()
 * @param $owner_id	User id to get the activity from. If 0, will return flobal activity.
 * @return array List of activity items
 */

function aoc_get_wall_items( $user_id )
{
    return ak_get_object('akucom_activity')->getTheWall($user_id);
}

// ================================================= START PROCEDURE ==========

ak_create_object('akucom_activity', new aocActivity(__FILE__));
