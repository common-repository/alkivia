<?php
/**
 * General functions for Community templates.
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

/**
 * Returns a list of templates in the /templates dir.
 *
 * @deprecated since 0.10
 * @see ak_get_templates()
 *
 * @param $prefix Templates prefix. Each component uses a diferent prefix followed by a dash and the template name.
 * @return array List of templates found.
 */
function aoc_get_templates ( $prefix )
{
    $folders = aoc_template_paths();
    return ak_get_templates($folders, $prefix.'-');
}

/**
 * Returns an array with paths to template folders.
 * Considers default path and the 'templates-path' in alkivia.ini.
 *
 * @since 0.10
 *
 * @return array List of template paths.
 */
function aoc_template_paths ()
{
    $folders = array(AOC_PATH . '/templates');
    if ( $path = ak_get_object('akucom')->getOption('templates-path') ) {
        $folders[] = $path;
    }

    return $folders;
}

/**
 * Creates the base link to a community page.
 * Alias of Alkivia::createLink()
 *
 * @see Alkivia::createLink()
 * @param string $var	Name of the variable
 * @param mixed $value	Value for the variable
 * @return string	Base link to the requeste page/var
 */
function aoc_create_link( $var, $value = '' )
{
	return ak_get_object('akucom')->createLink($var, $value);
}

/**
 * Creates the HTMML anchor for user profile page.
 *
 * @param int $user_id User ID we want to retrieve the anchor
 * @param object $user Optional user data object
 * @return string The HTML anchor.
 */
function aoc_user_anchor( $user_id, &$user = false )
{
    if ( ! is_object($user) ) {
        $user = get_userdata($user_id);
    }
    return '<a href="' . aoc_profile_link($user->user_login) . '">' . $user->display_name . '</a>';
}
