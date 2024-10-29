<?php
/*
Plugin Name: Alkivia Open Community
Plugin URI: http://alkivia.org/wordpress/community
Description: Create and manage user communities in any WordPress blog.
Version: 0.10.4
Author: Jordi Canals
Author URI: http://alkivia.org
*/

/**
 * AOC Builder. Main Plugin File.
 * Plugin to create and manage communities in any WordPress blog.
 *
 * @version		$Rev: 942 $
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

/** Path to the plugin folder **/
define ( 'AOC_PATH', dirname(__FILE__) );

// ================================================ HELPER FUNCTIONS ==========

/**
 * Sets an admin warning regarding required PHP version.
 *
 * @hook action 'admin_notices'
 * @return void
 */
function _aoc_php_warning() {

	$data = get_plugin_data(__FILE__);
	load_plugin_textdomain('akucom', false, basename(dirname(__FILE__)) .'/lang');

	echo '<div class="error"><p><strong>' . __('Warning:', 'akucom') . '</strong> '
		. sprintf(__('The active plugin %s is not compatible with your PHP version.', 'akucom') .'</p><p>', '&laquo;' . $data['Name'] . ' ' . $data['Version'] . '&raquo;')
		. sprintf(__('%s is required for this plugin.', 'akucom'), 'PHP 5.2 ') . '</p></div>';
}

// ================================================= START PROCEDURE ==========

// Check required PHP version.
if ( version_compare(PHP_VERSION, '5.2.0', '<') ) {
	// Send an armin warning
	add_action('admin_notices', '_aoc_php_warning');
} else {
    require_once ( AOC_PATH . '/framework/loader.php' );
	require_once ( AOC_PATH . '/includes/plugin.php' );
	require_once ( AOC_PATH . '/includes/templates.php' );

    ak_create_object('akucom', new Alkivia(__FILE__, 'akucom'));
}
