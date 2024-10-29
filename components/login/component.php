<?php
/**
 * AOC Login Form component.
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

require_once ( AK_CLASSES . '/abstract/component.php' );

/**
 * Class aocLoginForm.
 * Manages all about the WordPress Login Form.
 *
 * @since 		0.6
 * @author		Jordi Canals
 * @package		Alkivia
 * @subpackage	Community
 * @link		http://alkivia.org
 */
class aocLoginForm extends akComponentAbstract
{
	/**
	 * Sets the filters for the login page.
	 *
	 * @return void
	 */
	protected function moduleLoad ()
	{
		add_action('login_head', array($this, '_loginStyles'));
		add_filter('login_headerurl', array($this, '_loginURL'));
		add_filter('login_headertitle', array($this, '_loginSiteName'));
	}

	/**
	 * Updates the component settings.
	 * @return void
	 */
	protected function componentUpdate ( $version )
	{
		if ( version_compare($version, '0.8', '<') ) {
	        // Change NewUsers widget settings to new class names.
		    $widget = get_option('akucom_registered_widget');
		    if ( false !== $widget ) {
		        add_option('widget_aoc_new_users', $widget);
		        delete_option('akucom_registered_widget');
		    }
	        // Change Widget settings to new class names.
		    $widget = get_option('akucom_loggedin_widget');
		    if ( false !== $widget ) {
		        add_option('widget_aoc_logged_in', $widget);
		        delete_option('akucom_loggedin_widget');
		    }
		}
	}

	/**
	 * Inits the plugin widgets.
	 * Takes into consideration the alkivia privacy settings.
	 *
	 * @return void
	 */
	protected function registerWidgets ()
	{
        require_once ( dirname(__FILE__) . '/newusers.php' );
		register_widget('aocNewUsers');

		require_once ( dirname(__FILE__) . '/loggedin.php' );
		register_widget('aocLoggedIn');
	}

	/**
	 * Adds the Login Form menu to Alkivia.
	 *
	 * @hook action 'aoc_admin_menu'
	 * @return void
	 */
	function adminMenus()
	{
		add_submenu_page( $this->slug, __('Login Form', $this->PID), __('Login Form', $this->PID), 'aoc_manage_settings', $this->slug . '-login', array($this, '_loginSettings'));
	}

	/**
	 * Loads settings page for Login Form
	 *
	 * @hook add_submenu_page
	 * @return void
	 */
	function _loginSettings()
	{
		if ( ! current_user_can('aoc_manage_settings') ) {		// Verify user permissions.
			wp_die('<strong>' .__('What do you think you\'re doing?!?', $this->PID) . '</strong>');
		}

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$this->uploadLogo();
		}

		require ( dirname(__FILE__) . '/admin.php');
	}

	/**
	 * Process the uploaded image in the settings form.
	 * @return void
	 */
	private function uploadLogo()
	{
		check_admin_referer('upload-login-image');

		if ( isset($_POST['action']) && 'upload' == $_POST['action'] ) {
			// Process uploaded file
			require_once ( AK_VENDOR . '/upload/class.upload.php');
			$handle = new akUpload($_FILES['login_image'], $this->PID);
			if ( $handle->uploaded ) {
				$handle->image_resize	= true;
				$handle->image_ratio_y	= true;
				$handle->image_x		= 326;

				$handle->file_overwrite		= true;
				$handle->file_auto_rename	= false;
				$handle->file_new_name_body	= 'login';
				$handle->image_convert		= 'png';

				$uploads	= wp_upload_dir();
				$folder		= trailingslashit($uploads['basedir']) . 'alkivia';

				$handle->Process($folder);

				if ( $handle->processed ) {
					ak_admin_notify(__('File uploaded.', $this->PID));
				} else {
					ak_admin_error(__('Error', $this->PID) . ': ' . $handle->error);
				}
			} else {
				ak_admin_error(__('No file received.', $this->PID));
			}
		} else { // Missing action
			ak_admin_error(__('Bad form received.', $this->PID));
		}
	}

	/**
	 * Loads Login Form picture.
	 *
	 * @hook action 'login_head'
	 * @return void
	 */
	function _loginStyles ()
	{
		$logo = $this->getLogo();
		echo '<style type="text/css"> #login h1 a { width:' . $logo['width'] . 'px; height:' . $logo['height'] .'px; '
			. 'background: url(' . $logo['url'] . ') no-repeat top center; } </style>';
	}

	/**
	 * Sets the blog url as the custom link for the Login Form image.
	 *
	 * @hook filter 'login_headerurl'
	 * @return void
	 */
	function _loginURL()
	{
		return get_bloginfo('url');
	}

	/**
	 * Sets the blog name and description as title for the Login Form image.
	 *
	 * @hook filter 'login_headertitle'
	 * @return string New title for login image.
	 */
	function _loginSiteName()
	{
		$title = get_bloginfo('name') .' | '. get_bloginfo('description');
		return $title;
	}

	/**
	 * Returns all info about the logo image.
	 *
	 * @return array	Image information. With this indexes:
	 * 						- url:	The full url to the image.
	 * 						- width:	Image width.
	 * 						- height:	Image height
	 * 						- html:		The <img> tag for the image.
	 */
	private function getLogo()
	{
		$upload	= wp_upload_dir();
		$file	= trailingslashit($upload['basedir']) . 'alkivia/login.png';
		$logo	= array();

		if ( file_exists($file) ) {
			$logo['url'] = trailingslashit($upload['baseurl']) . 'alkivia/login.png';
		} else {
			$logo['url'] = ak_get_object($this->PID)->getURL() . 'images/login.png';
			$file = AOC_PATH . '/images/login.png';
		}

		$info = getimagesize($file);

		$logo['width']	= $info[0];
		$logo['height']	= $info[1];
		$logo['html']	= '<img src="' . $logo['url'] . '?'. rand() . '" border="0" ' . $info[3] . ' />';

		return $logo;
	}

}
