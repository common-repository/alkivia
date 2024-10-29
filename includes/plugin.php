<?php
/**
 * Alkivia Open Community Builder. Main Plugin Class.
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

require_once ( AK_CLASSES . '/abstract/plugin.php' );

/**
 * Class Alkivia.
 * Sets the main environment for the User Community.
 *
 * @author		Jordi Canals
 * @package		Alkivia
 * @subpackage	Community
 */
class Alkivia extends akPluginAbstract
{
	/**
	 * Constat to define public communities
	 * @var int
	 */
	const PUBLIC_COMMUNITY = 1;

	/**
	 * Constat to define private communities
	 * @var int
	 */
	const PRIVATE_COMMUNITY = 2;

	/**
	 * Starup functions. This runs at plugins load time.
	 *
	 * @return void
	 */
	protected function moduleLoad ()
	{
		// Register user access.
		add_action('set_current_user', array($this, 'registerUserAccess'));
		// Prepares the filter to output the community content.
		add_filter('the_content', array($this, 'communityPage'));
	}

	/**
	 * Checks conditions to allow component load.
	 *
	 * @return void
	 */
	protected function readyForComponents ()
	{
	    // page_id could not be set yet, but received in form post.
		if ( 0 == $this->preCheckPageId() ) {
			add_action('admin_notices', array($this, 'pageIdWarning'));
		}

		// Only if page_id is set in database, not valid if received in form post.
		if ( 0 != $this->getOption('page_id') ) {
		    return true;
		} else {
		    return false;
		}
	}

	/**
	 * Reads and load form data at startup time.
	 * This is done to ensure admin menus are show properly. Only loads needed settings for this.
	 *
	 * @return void
	 */
	private function preCheckPageId ()
	{
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_GET['page']) && $this->getSlug() == $_GET['page'] ) {
		    return intval($_POST['settings']['page_id']);
		} else {
		    return $this->getOption('page_id');
		}
	}

	/**
	 * Loads plugin defaults.
	 * This defaults can differ from install time defaults. Are used to fill the $settings array when loaded from DB.
	 *
	 * @since 0.6
	 * @return void
	 */
	protected function defaultOptions ()
	{
		return array (
			'page_id' => 0,	                        // Page holder for community is not set.
			'privacy' => self::PRIVATE_COMMUNITY    // Community is Private. Set to 1 for public.
		);
	}

	/**
	 * Processes and adds content to the community page.
	 *
	 * @uses apply_filters() Calls the 'aoc_alkivia_page' on the page content.
	 * @uses apply_filters() Calls the 'aoc_alkivia_home' on the page content.
	 * @uses apply_filters() Calls the 'aoc_access_login' on the login anchor/link.
	 * @hook filter 'the_content'
	 * @param string $content	The current page content
	 * @return string Community page content.
	 */
	function communityPage ( $content )
	{
		if ( $this->getOption('page_id') != get_the_ID() ) {				// If not the community page. Do nothing.
			return $content;
		}

		if ( self::PRIVATE_COMMUNITY == $this->getOption('privacy') && ! is_user_logged_in() ) {
			$link = sprintf(__('Please <a href="%s">Log In</a> to see the content.', $this->ID), wp_login_url());

			$content  = '<div class="error-doc">'
			          . '<h1 class="alkivia-error" style="text-align:center;">'. __('You have to be registered to access this page.', $this->ID) . '</h1>'
			          . '<h2 class="alkivia-error" style="text-align:center;">' . apply_filters('aoc_access_login', $link) . '</h2>'
			          . '</div>';
			return $content;
		} else {
			$page = apply_filters('aoc_alkivia_page', $content);
			if ( $page == $content ) {
				$page = apply_filters('aoc_alkivia_home', $content);
			}
			return $page;
		}
	}

	/**
	 * Registers the user access.
	 * This will be used to know the last user access. Updated at every page.
	 *
	 * @since 0.5.3
	 * @hook action 'set_current_user'
	 * @return void
	 */
	function registerUserAccess ()
	{
	    if ( is_user_logged_in() ) {
    		$user_id = ak_current_user_id();
	    	update_usermeta($user_id, $this->ID . '_last_access', gmdate('Y-m-d H:i:s'));
	    }
	}

	/**
	 * Updates the plugin from previous versions.
	 *
	 * @param string $version	Plugin version to update from.
	 * @return void
	 */
	protected function pluginUpdate ( $version )
	{
		if ( version_compare($version, '0.5', '<') ) {
			$this->update050();
		}

		if ( version_compare($version, '0.5.3', '<') ) {
			$this->update053();
		}

		if ( version_compare($version, '0.6', '<') ) {
			$this->update060();
		}

		if ( version_compare($version, '0.10', '<') ) {
		    $this->update0100($version);
		}
	}

	/**
	 * Installs the plugin and creates default options.
	 * First checks if it's a first time install or a plugin reactivation.  If reactivating, does nothing.
	 *
	 * @return void
	 */
	protected function pluginActivate ()
	{
		if ( ! $this->installing ) {
			return;
		}

		$this->updateOption('privacy', self::PUBLIC_COMMUNITY);
		$this->addCapabilities();
		ak_create_upload_folder('users');
	}

	/**
	 * Adds admin panel menus. (At plugins loading time. This is before plugins_loaded).
	 * User needs to have 'aoc_manage_settings' to access this menus.
	 * This is set as an action in the parent class constructor.
	 *
	 * @hook action admin_menu
	 * @uses do_action() Calls the 'aoc_admin_menu' action hook.
	 * @return void
	 */
	function adminMenus ()
	{
	    add_menu_page('Alkivia', 'Alkivia', 'aoc_manage_settings', $this->getSlug(), array($this, 'settingsAdmin'), $this->getURL() .'images/aoc16.png');
		add_submenu_page( $this->getSlug(), __('General Settings', $this->ID), __('General', $this->ID), 'aoc_manage_settings', $this->getSlug(), array($this, 'settingsAdmin'));
	}

	/**
	 * Includes global settings admin.
	 *
	 * @hook add_submenu_page
	 * @return void
	 */
	function settingsAdmin ()
	{
		if ( ! current_user_can('aoc_manage_settings') ) {		// Verify user permissions.
			wp_die('<strong>' .__('What do you think you\'re doing?!?', $this->ID) . '</strong>');
		}

		global $wp_rewrite;
		$wp_rewrite->flush_rules();	// Force save rules.

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$this->saveSettingsForm();
			ak_admin_notify();
		}

		require ( AOC_PATH . '/includes/admin.php');
	}

	/**
	 * Saves admin settings from settings form.
	 * Also reloads installed components and activate/deactivate depending on user selections.
	 *
	 * @since 0.6
	 * @return void
	 */
	private function saveSettingsForm ()
	{
		check_admin_referer('alkivia-general-settings');

		if ( isset($_POST['action']) && 'update' == $_POST['action'] ) {
			$post = stripslashes_deep($_POST['settings']);

			$settings = array();
			$settings['page_id']	= intval($post['page_id']);
			$settings['privacy']	= intval($post['privacy']);

			$settings = array_merge($post, $settings);
			$this->setNewOptions($settings);
		} else { // Missing or bad action
			wp_die('Bad form received.', $this->ID);
		}

		$this->saveActivationForm();
	}

	/**
	 * Populates default Capabilities.
	 * Only on install time.
	 *
	 * TODO: Capabilities will be prefixed 'aoc_'
	 *
	 * @return void
	 */
	private function addCapabilities ()
	{
		$role = get_role('administrator');
		$role->add_cap('aoc_manage_settings');

		$roles = ak_get_roles();
		foreach ( $roles as $name ) {
			$role = get_role($name);
			$role->add_cap('aoc_unmoderated');
		}
	}

	/**
	 * Creates the base link to a community page.
	 *
	 * @param $p_name	Parameter name for the URL.
	 * @param $p_value	Parameter value for the URL.
	 * @return string	Base link to the requested page.
	 */
	public function createLink ( $p_name , $p_value = '' )
	{
		$home = get_bloginfo('url');
		$permalinks = get_option('permalink_structure');

		if ( empty($permalinks) ) {
			$link = $home . '/?page_id=' . $this->getOption('page_id') . "&amp;{$p_name}={$p_value}";
		} else {
			if ( false !== strpos($permalinks, 'index.php') ) {
				$home .= '/index.php';
			}
			$link = $home .'/'. get_page_uri($this->getOption('page_id')) . "/{$p_name}/{$p_value}";
		}

		return $link;
	}

	/**
	 * Sets an admin warning regarding page id is not set.
	 *
	 * @since 0.6
	 *
	 * @hook action 'admin_notices'
	 * @return void
	 */
	function pageIdWarning ()
	{
		echo '<div class="error"><p><strong>' . __('Warning:', $this->ID) . '</strong> '
			. sprintf(__('Before start using the plugin %s you have to create and set the page to hold output data.', $this->ID) .'</p><p>',
				'&laquo;' . $this->getModData('Name') . ' ' . $this->getModData('Version') . '&raquo;')
			. sprintf(__('Set now the page holder for %s.', $this->ID), '&laquo;<a href="admin.php?page='. $this->getSlug() . '">' . $this->getModData('Name') . ' ' . $this->getModData('Version') . '</a>&raquo;')
			. '</p></div>';
	}

	/**
	 * Updates plugin to 0.5
	 * Moves page_id setting from profiles settings to plugin settings.
	 *
	 * @since 0.7
	 *
	 * @return void
	 */
	private function update050 ()
	{
		$profile = get_option($this->ID . '_profiles');
		$this->settings = array();
		$this->settings['page_id'] = intval($profile['page_id']);
	}

	/**
	 * Updates plugin to 0.5.3
	 * Creates the new setting 'privacy'
	 *
	 * @since 0.7
	 *
	 * @return void
	 */
	private function update053 ()
	{
		$this->settings['privacy'] = 1;	// Public by default.
	}

	/**
	 * Updates plugin to 0.6
	 * Changes components design to activation/deactivation hooks.
	 * Creates the unmoderated capability.
	 *
	 * @since 0.7
	 *
	 * @return void
	 */
	private function update060 ()
	{
		$old_components = get_option($this->ID . '_components');
		$components = ak_get_installed_components($this->componentsPath(), true);

		foreach ( $components as $key => $component ) {
			$cid = $component['Component'];
			if ( $component['Core'] || ( isset($old_components[$cid]) && $old_components[$cid] ) ) {
				$components[$key]['active'] = true;
			} else {
				$components[$key]['active'] = false;
			}
		}
		update_option($this->ID . '_components', $components);

		$roles = ak_get_roles();
		foreach ( $roles as $name ) {
			$role = get_role($name);
			$role->add_cap('aoc_unmoderated');
		}
	}

	/**
	 * Update to Community 0.10
	 * We create the components old version swetting to fire the components update.
	 *
	 * @since 0.10
	 *
	 * @return void
	 */
	private function update0100 ( $version )
	{
	    add_option('akucom_activity_version', $version);
        $options = get_option('akucom_activity');
        if ( false != $options ) {
            add_option('akucom_activity_settings', $options);
            delete_option('akucom_activity');
        }

	    add_option('akucom_gallery_version', $version);
        $options = get_option('akucom_gallery');
        if ( false != $options ) {
            add_option('akucom_gallery_settings', $options);
            delete_option('akucom_gallery');
        }

	    add_option('akucom_login_form_version', $version);
        $options = get_option('akucom_login-form');
        if ( false != $options ) {
            add_option('akucom_login_form_settings', $options);
            delete_option('akucom_login-form');
        }

	    add_option('akucom_profiles_version', $version);
        $options = get_option('akucom_profiles');
        if ( false != $options ) {
            $options['order_by'] = $options['order']['by'];
            $options['order_dir'] = $options['order']['type'];
            unset($options['order']);

            add_option('akucom_profiles_settings', $options);
            delete_option('akucom_profiles');
        }

        $roles = ak_get_roles();
		foreach ( $roles as $name ) {
		    $role = get_role($name);
		    if ( $role->has_cap('akuc_unmoderated') ) {
			    $role->add_cap('aoc_unmoderated');
			    $role->remove_cap('akuc_unmoderated');
		    }
		    if ( $role->has_cap('akuc_manage_settings') ) {
			    $role->add_cap('aoc_manage_settings');
			    $role->remove_cap('akuc_manage_settings');
		    }
		}
	}
}
