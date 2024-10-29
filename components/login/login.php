<?php
/*
Module Component: Login Form
Parent ID: akucom
Component Name: Custom Login Form
Description: Configures custom styles, links and logo in the login form.
Author: Jordi Canals
Link: http://alkivia.org
*/

/**
 * Alkivia Login Form component.
 * Plugin to create and manage communities in any WordPress blog.
 *
 * @version		$Rev: 903 $
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

require_once ( dirname(__FILE__) . '/component.php' );
ak_create_object('akucom_login_form', new aocLoginForm(__FILE__));
