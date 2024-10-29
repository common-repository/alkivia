<?php
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

require_once ( AK_CLASSES . '/abstract/component.php' );

/**
 * Class aocProfiles.
 * Manages all about user profiles.
 *
 * @since 		0.6
 * @author		Jordi Canals
 * @package		Alkivia
 * @subpackage	Community
 */
final class aocProfiles extends akComponentAbstract
{
	/**
	 * Profiles component Statup.
	 * Sets all needed filters and actions to the WordPress API.
	 *
	 * @return void
	 */
	protected function moduleLoad ()
	{
		// Community home Page and user profile page.
		if ( ! $this->getOption('disable_list') ) {
			add_filter('aoc_alkivia_home', array($this, 'userList'));
		}
		add_filter('aoc_alkivia_page', array($this, 'profilesContent'));

		// Rewrite Rules.
		add_filter('generate_rewrite_rules', array($this, 'rewriteRules'));
		add_filter('query_vars', array($this, 'rewriteVars'));

		// IM Labels (Only works with WP 2.8 and above).
		add_filter('user_aim_label', array($this, 'aimLabel'));
		add_filter('user_yim_label', array($this, 'yimLabel'));
		add_filter('user_jabber_label', array($this, 'jabberLabel'));

		// Checks nickname uniqueness (Only works with WP 2.8 and above).
		add_action('user_profile_update_errors', array($this, 'uniqueNickname'), 10, 3);

		switch ( $this->getOption('author_link') ) {
			case 1 :
				add_filter('author_link', array($this, 'authorReplaceLink'), 15, 3);
				add_filter('ak_chameleon_author', array($this, 'authorCreateLink'));	// For chameleon theme
				break;
			case 2:
				add_filter('the_author', array($this, 'authorCreateLink'));
				add_filter('ak_chameleon_author', array($this, 'authorCreateLink'));	// For chameleon theme
				break;
		}
		if ( $this->getOption('comments_link') ) {
			add_filter('get_comment_author_link', array($this, 'commentAuthorLink'));
		}

		if ( $this->getOption('last_posts') ) {
		    add_action('aoc_profile_data', array($this, 'latestUserPosts'));
		}
		if ( $this->getOption('last_comments') ) {
		    add_action('aoc_profile_data', array($this, 'recentUserComments'));
		}

	}

	/**
	 * Returns component defaults.
	 * This defaults can differ from install time defaults.
	 * Here have to remember that array_merge is not recursive. So 'roles' is not changed item by item, all roles array is merged as is.
	 * This behavior is used to do not fill the roles and get in $settings['roles'] only the active items (those with value 1).
	 *
	 * @return void
	 */
	protected function defaultOptions ()
	{
		return array(
			'show_email'		=> 0,
			'show_aim'			=> 0,
			'show_yahoo'		=> 0,
			'show_jabber'		=> 0,
			'show_firstname'	=> 0,
			'show_lastname'		=> 0,
			'roles'	=> array(
				'administrator'	=> 0,
				'editor'		=> 0,
				'author'		=> 0,
				'contributor'	=> 0,
				'subscriber'	=> 0
        	),
			'avatar_size'		=> 20,
			'order_by'          => 'display_name',
			'order_dir'			=> 'ASC',
			'per_page'			=> 20,
    	    'author_link'		=> 0,
    	    'comments_link'		=> 0,
    	    'author_page'		=> 0,
    	    'last_posts'		=> 0,
    	    'last_comments'		=> 0,
			'aim_label' 		=> '',
			'yim_label' 		=> '',
			'jabber_label'		=> '',
			'disable_list'		=> 0,
    	    'list_template'		=> 'default',
    	    'profile_template'	=> 'default'
		);

	}

	/**
	 * Component activation.
	 * We change the default settings that differ from defaults (as defaults already are set).
	 *
	 * @return void
	 */
	protected function componentActivate ()
	{
	    $options = array(
			'show_firstname'	=> 1,
			'roles'	=> array(
				'administrator'	=> 1,
				'editor'		=> 1,
				'author'		=> 1,
				'contributor'	=> 1,
				'subscriber'	=> 1
        	),
        	'author_page'		=> 1,
    	    'last_posts'		=> 1,
    	    'last_comments'		=> 1,
		);

		$this->mergeOptions($options);
	}

	/**
	 * Adds the Profiles menu to Alkivia.
	 *
	 * @hook action 'aoc_admin_menu'
	 * @return void
	 */
	function adminMenus ()
	{
		add_submenu_page( $this->slug, __('User Profiles', $this->PID), __('User Profiles', $this->PID), 'aoc_manage_settings', $this->slug . '-profiles', array($this, 'profileSettings'));
	}

	/**
	 * Selects which page type to output depending on the url query.
	 *
	 * @hook filter 'aoc_alkivia_page'
	 * @uses apply_filters() Calls the 'aoc_access_login' hooc on the login anchor.
	 *
	 * @param string $content	Post Content as entered when editing the page.
	 * @return string 	User Profile info.
	 */
	function profilesContent ( $content )
	{
		$user_name = urldecode(get_query_var('user'));

		if ( ! empty($user_name) ) {
			$profile = $this->userProfile($user_name);
			if ( $profile ) {
				$content = $profile;
			} else {
				$content = '<h1 class="alkivia-error">' . __('User with this name not found.', $this->PID) . "</h1>\n";
			}
		}

		return $content;
	}

	/**
	 * Displays the a paged user list. This is the community home.
	 * Only users of configured roles are displayed.
	 *
	 * @hook filter 'aoc_alkivia_home'
	 *
	 * @param string $content	Post Content as entered when editing the page.
	 * @return string	The HTML formated user list added to $content.
	 */
	function userList( $content )
	{
		$page = (int) get_query_var('page');
		if ( 0 == $page ) $page = 1;

		$start = ($page - 1) * $this->getOption('per_page');
		$order = array( 'by' => $this->getOption('order_by'), 'dir' => $this->getOption('order_dir'));
		$users = $this->getUsersByRoles($this->getAllowedRoles(), $order, $start, $this->getOption('per_page'));
		if ( ! $users ) {
			// Normally occurs when a wrong page number has been requested.
			return '<h1 class="alkivia-error">'. __('No users found in this page.', $this->PID) ."</h1>\n";
		}

        // Load and process the page template.
		require_once ( AK_CLASSES . '/template.php');
        $template = new akTemplate( aoc_template_paths() );
        $template->textDomain($this->PID);

        $template->assign('baselink', aoc_create_link('user'));
        $template->assign('avatar_size', $this->getOption('avatar_size'));
        $template->assignByRef('users', $users);

		$total = $this->countUsersByRoles($this->getAllowedRoles());
		$template->assign('pager', ak_pager($total, $this->getOption('per_page'), aoc_create_link('page'), $page));

        $content .= $template->getDisplay('userlist-' . $this->getOption('list_template'), 'userlist-default');
		return $content;
	}

	/**
	 * Display a User Profile page.
	 *
	 * @uses apply_filters() Calls the 'user_aim_label', 'user_yim_label' and 'user_jabber_label' hooks on their corresponding labels.
	 * @uses apply_filters() Calls the 'aoc_profile_header' on an empty string with user object as an extra param.
	 * @uses apply_filters() Calls the 'aoc_profile_footer' on an empty string with user object as an extra param.
	 * @uses do_action() Calls the 'aoc_profile_data' to get user additional data.
	 *
	 * @param string $name	Login Name for the user.
	 * @return string|false	The formated profile or false if the user cannot be shown.
	 */
	private function userProfile ( $name )
	{
		global $wpdb;

		$user_login = sanitize_user( $name );
		$out = '';

		if ( empty($user_login) ) {	// No user name provided
			return false;
		}

		$user = get_userdatabylogin($user_login);
		if ( ! $user ) {			// User not found.
			return false;
		}

		if ( ! $this->canShowUser($user) ) {
			return false;
		}

        // Load and process the page template.
		require_once ( AK_CLASSES . '/template.php');
        $template = new akTemplate( aoc_template_paths() );
        $template->textDomain($this->PID);

        $template->assign('header', apply_filters('aoc_profile_header', '', $user));
        $template->assign('footer', apply_filters('aoc_profile_footer', '', $user));

        if ( is_user_logged_in() ) {
		    $cur_user = wp_get_current_user();
		    if ( $cur_user->user_login == $user->user_login ) {
		        $link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/profile.php">' . __('Edit your profile', $this->PID) . '</a>';
	    	} elseif ( current_user_can('edit_users') ) {
		    	$link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/user-edit.php?user_id='. $user->ID . '">' . __('Edit this user', $this->PID) . '</a>';
		    }
    		$template->assign('edit_link', $link);
        }

		if ( $this->getOption('author_page') ) {
			$template->assign('author_link', $this->authorPostsLink($user));
		}

		// Now, we set/unset the user data based on privacy settings.
        unset($user->user_pass); // Just for security do not provide the password to templates.

        // User full name.
		if ( $this->getOption('show_firstname') && ! empty($user->first_name) ) {
		    $user->aoc_full_name = $user->first_name;
			if ( $this->getOption('show_lastname') ) {
				$user->aoc_full_name .= ' '. $user->last_name;
			}
			unset($user->first_name); unset($user->last_name);
			unset($user->user_firstname); unset($user->user_lastname);
		}

		// Translated Role
		$roles = ak_get_roles(true);
		$user->aoc_translated_role = $roles[ak_get_user_role($user)];

		$user->user_url = ( 'http://' == $user->user_url ) ? '' : $user->user_url;

		// Email and IM addresses
		if ( ! $this->getOption('show_email') ) {
			unset($user->user_email);
		}
		if ( ! $this->getOption('show_aim') ) {
			unset($user->aim);
		}
		if ( ! $this->getOption('show_yahoo') ) {
			unset($user->yim);
		}
		if ( ! $this->getOption('show_jabber') ) {
			unset($user->jabber);
		}

		// Pager links
		$template->assign('prev_link', $this->pageNavigator($user, 'prev'));
		$template->assign('next_link', $this->pageNavigator($user, 'next'));

		// Get user data from action hooks.
		$user->aoc_widgets = array();
		do_action('aoc_profile_data', $user);
        $template->assignByRef('user', $user);

        return $template->getDisplay('profile-' . $this->getOption('profile_template'), 'profile-default');
	}

	/**
	 * Creates and returns previous and next user links.
	 * For paged user profiles.
	 *
 	 * @param object $current	Current user object.
	 * @param string $direction	Direction for the link. Is 'prev' or 'next'.
	 * @return string			Previous or next user link.
	 */
	private function pageNavigator ( $current, $direction = 'next' )
	{
		global $wpdb;

		$roles   = $this->getAllowedRoles();
		$ord_by  = $this->getOption('order_by');
		$ord_dir = $this->getOption('order_dir');

		$keys = array();
		foreach ($roles as $value) {
			$keys[] = "meta_value LIKE '%{$value}%'";
		}

		// Prepare the order field
		switch ( $ord_by ) {
			case 'user_registered':
				$by = $current->user_registered;
				break;
			case 'user_login':
				$by = $current->user_login;
				break;
			case 'ID':
				$by = $current->ID;
				break;
			case 'display_name':
			default:
				$by = $current->display_name;
				break;
		}

		// $ord_by contains the ordering field name.
		if ( 'ASC' == $ord_dir ) {
			$op = ( 'next' == $direction ) ? '>' : '<';
		} else {
			$op = ( 'next' == $direction ) ? '<' : '>';
		}
		$condition = "{$ord_by} {$op} '{$by}'";
		$order_dir = ( '>' == $op ) ? 'ASC' : 'DESC';

		// Compose the query
		$query = "SELECT user_login, display_name FROM {$wpdb->usermeta} INNER JOIN {$wpdb->users} "
			. "ON {$wpdb->usermeta}.user_id = {$wpdb->users}.id "
			. "WHERE {$condition} AND meta_key='{$wpdb->prefix}capabilities' AND (". implode(' OR ', $keys) .") "
			. "ORDER BY {$ord_by} {$order_dir} "
			. "LIMIT 0,1;";

		$user = $wpdb->get_row($query);
		if ( empty($user) ) {	// If no user, no link.
			$link = '';
		} else {				// Create the base url for the link to user profile page.
			$plink = aoc_create_link('user', urlencode($user->user_login));
			$link	= ( 'next' == $direction )
					? '<a href="'. $plink .'">'. $user->display_name . '</a> &raquo;'
					: '&laquo; <a href="'. $plink .'">'. $user->display_name . '</a>';
		}

		return  $link;
	}

	/**
	 * Return the roles that can be shown in Community
	 *
	 * @param $as_values Role names are returned as values insted as in keys with '1' or '0' as value.
	 * @return array	The allowed roles.
	 */
	public function getAllowedRoles ( $as_values = true )
	{
		// return array_keys($this->getOption('roles'));
	    $roles = $this->cfg->getSetting($this->ID, 'roles');
	    if ( ! is_array($roles) ) {
            $a = explode(',', $roles);
            if ( $as_values ) {
                $roles = $a;
            } else {
                $roles = array();
                foreach ( $a as $role ) {
                    $roles[trim($role)] = 1;
                }
            }
	    } elseif ( $as_values ) {
            $roles = array_keys($roles);
	    }

	    return $roles;
	}

	/**
	 * Creates an array as a paged list of users which have some roles.
	 *
	 * @param array|string $roles	Roles names users need to have to be retrieved. Can be an string with only one role name.
	 * @param array $order	Array for list ordering. [by] Field to order by, [dir] is ASC or DESC
	 * @param int $start	User to start at.
	 * @param int $num		Number of users per page.
	 * @return array		Retrieved Users.
	 */
	private function getUsersByRoles ( $roles = '', $order = array(), $start = 0, $num = 20 )
	{
		global $wpdb;

		if ( empty($roles) ) {
			$roles = ak_get_roles();
		}
		if ( ! is_array($roles) ) {
			$roles = array($roles);
		}
		if ( empty($order) ) {
			$order['by']	= 'display_name';
			$order['dir']	= 'ASC';
		}

		$keys = array();
		foreach ($roles as $value) {
			$keys[] = "meta_value LIKE '%{$value}%'";
		}

		$query = "SELECT {$wpdb->users}.* FROM {$wpdb->usermeta} INNER JOIN {$wpdb->users} "
			. "ON {$wpdb->usermeta}.user_id = {$wpdb->users}.id "
			. "WHERE meta_key='{$wpdb->prefix}capabilities' AND (". implode(' OR ', $keys) .") "
			. "ORDER BY {$order['by']} {$order['dir']} "
			. "LIMIT {$start},{$num};";

		return $wpdb->get_results($query, 'ARRAY_A');
	}

	/**
	 * Counts the total users based on roles list.
	 *
	 * @param array|string $roles	Roles names users to be counted from. Can be an string with only one role name.
	 * @return int			Total users with any of the roles.
	 */
	private function countUsersByRoles ( $roles = '' )
	{
		global $wpdb;

		if ( empty($roles) ) {
			$roles = ak_get_roles();
		}
		if ( ! is_array($roles) ) {
			$roles = array($roles);
		}

		$keys = array();
		foreach ($roles as $value) {
			$keys[] = "meta_value LIKE '%{$value}%'";
		}

		$query = "SELECT COUNT(*) FROM {$wpdb->usermeta} INNER JOIN {$wpdb->users} "
			. "ON {$wpdb->usermeta}.user_id = {$wpdb->users}.id "
			. "WHERE meta_key='{$wpdb->prefix}capabilities' AND (". implode(' OR ', $keys) .")";

		return $wpdb->get_var($query);
	}

	/**
	 * Checks id a user is in an allowed role.
	 *
	 * @param int|object $user	User ID or user object to check
	 * @return boolean	If the user can be shown or not.
	 */
	public function canShowUser ( $user )
	{
		$roles = $this->getAllowedRoles();
		$user_role = ak_get_user_role($user);

		return ( in_array($user_role, $roles) ) ? true : false;
	}

	/**
	 * Creates the needed rewrite rules for user profiles.
	 *
	 * @hook filter 'generate_rewrite_rules'
	 * @param object $wp_rewrite	Current rewrite rules. Received by ref.
	 * @return void
	 */
	function rewriteRules ( &$wp_rewrite )
	{
		$pid  = ak_get_object($this->PID)->getOption('page_id');
		$slug = basename(get_page_uri($pid));

		$rules = array ( $slug . '/user/(.+)/?$' => 'index.php?page_id='. $pid .'&user='. $wp_rewrite->preg_index(1),
						'(.+)/' . $slug . '/user/(.+)/?$' => 'index.php?page_id='. $pid .'&user=' . $wp_rewrite->preg_index(2),
						$slug . '/page/(.+)/?' => 'index.php?page_id=' . $pid . '&page=' . $wp_rewrite->preg_index(1),
						'(.+)/' . $slug . '/page/(.+)/?$' => 'index.php?page_id='. $pid .'&page=' . $wp_rewrite->preg_index(2) );
		$wp_rewrite->rules = $rules + $wp_rewrite->rules;
	}

	/**
	 * Creates the query var to get a user profile page.
	 *
	 * @hook filter 'query_vars'
	 * @param array $vars	Current WordPress query vars.
	 * @return array		New Query vars.
	 */
	function rewriteVars ( $vars )
	{
		$vars[] = 'user';
		$vars[] = 'page';
		return $vars;
	}

	/**
	 * Creates the link to the author pages.
	 *
	 * @param $user	Author object.
	 * @return string Link to author pages.
	 */
	private function authorPostsLink ( $user )
	{
		$count = get_usernumposts($user->ID);
		$link = '';

		if ( 0 < $count ) {
			global $wp_rewrite;
			$link = $wp_rewrite->get_author_permastruct();

			if ( empty($link) ) {
				$file = get_option('home') . '/';
				$link = $file . '?author=' . $user->ID;
			} else {
				$link = str_replace('%author%', $user->user_login, $link);
				$link = get_option('home') . trailingslashit($link);
			}
		}
		return $link;
	}

	/**
	 * Filter to replace the link for author to his profile page.
	 * Only if the theme already sets the author links.
	 *
	 * @hook filter 'author_link'
	 * @param string $link	Old author link
	 * @param int $auth_id	Author user ID
	 * @param string $nicename Author nice name.
	 * @return string	The link to the author profile.
	 */
	function authorReplaceLink ( $link, $auth_id, $nicename )
	{
		$auth = get_userdata($auth_id);
		return aoc_profile_link($auth->user_login);
	}

	/**
	 * Filter to create a link for the author to his profile page.
	 * It does not adds the link to feeds, as author links on feeds arre not supported by RSS/Atom standards.
	 * This only can be used if currently the author has no links.
	 *
	 * @hook filter 'the_author'
	 * @return string	The link to the author profile.
	 */
	function authorCreateLink ( $display_name )
	{
		if ( is_feed() ) {
			return $display_name; 	// Feed standards do not allow links on dc:creator.
		}

		global $authordata;
		$plink = aoc_profile_link($authordata->user_login);
		$link = '<a href="' . $plink . '">' . $authordata->display_name . '</a>';
		return $link;
	}

	/**
	 * Filter to replace the comments author link.
	 * The link is only replaced if the user is registered and is from an allowed role.
	 *
	 * @hook filter 'get_comment_author_link'
	 * @param string $link	Link to author home page generated by WordPress
	 * @return string Link to the user profile page if user is registered and from an allowed role.
	 */
	function commentAuthorLink ( $link )
	{
		global $comment;

		if ( ! empty($comment->user_id) && $this->canShowUser($comment->user_id) ) {
			$user = get_userdata($comment->user_id);
			$link = '<a href="' . aoc_profile_link($user->user_login) . '" class="url">' . $user->display_name . '</a>';
		}

		return $link;
	}

	/**
	 * Action to add the latest user comments to the profile page.
	 * Be careful, as this filter needs two parameters, the extra content and the user ID.
	 *
	 * @hook action 'aoc_profile_data'.
	 * @param object $user User Object.
	 * @return void.
	 */
	function latestUserPosts ( & $user )
	{
		global $wpdb;
		$data = array();

		$p = new WP_Query(array('showposts' => 5, 'what_to_show' => 'posts', 'nopaging' => 0, 'post_status' => 'publish', 'author' => $user->ID));
		if ( $p->have_posts() ) {
		    $data['title'] = __('Recent user posts:', $this->PID);
			while ( $p->have_posts() ) {
				$p->the_post();
				$data['content'][] = '<a href="' . get_permalink() . '">'
				                   . get_the_title() . '</a>';
			}
			wp_reset_query();  // Restore global post data stomped by the_post().
		}

		$user->aoc_widgets['postlist'] = $data;
	}

	/**
	 * Action to add the latest comments user has made.
	 * Be careful, as this filter needs two parameters, the extra content and the user ID.
	 *
	 * @hook action 'aoc_profile_data'.
	 * @param object $user User Object.
	 * @return void.
	 */
	function recentUserComments ( & $user )
	{
		global $wpdb;
		$data = array();

		$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_approved = '1' AND user_id = '{$user->ID}' GROUP BY comment_post_ID ORDER BY comment_date_gmt DESC LIMIT 0,5");
		if ( $comments ) {
		    $data['title'] = __('Latest comments on:', $this->PID);
		    foreach ( (array) $comments as $comment ) {
		        $data['content'][] = '<a href="' . clean_url( get_comment_link($comment->comment_ID) ) . '">' . get_the_title($comment->comment_post_ID) . '</a>';
		    }

		    $user->aoc_widgets['commentlist'] = $data;
		}
	}

	/**
	 * Filter to change the standard AIM label.
	 * Only works for WP 2.8 and above.
	 *
	 * @hook filter 'user_aim_label'
	 * @param $label	Default label.
	 * @return string	New label by profiles settings.
	 */
	function aimLabel ( $label )
	{
	    $value = $this->getOption('aim_label');
		return ( empty($value) ) ? $label : $value;
	}

	/**
	 * Filter to change the standard YIM label.
	 * Only works for WP 2.8 and above.
	 *
	 * @hook filter 'user_yim_label'
	 * @param $label	Default label.
	 * @return string	New label by profiles settings.
	 */
	function yimLabel ( $label )
	{
	    $value = $this->getOption('yim_label');
		return ( empty($value) ) ? $label : $value;
	}

	/**
	 * Filter to change the standard Jabber Label.
	 * Only Works for WP 2.8 and above.
	 *
	 * @hook filter 'user_jabber_label'
	 * @param $label	Default label.
	 * @return string	New label by profiles settings.
	 */
	function jabberLabel ( $label )
	{
	    $value = $this->getOption('jabber_label');
		return ( empty($value) ) ? $label : $value;
	}

	/**
	 * Filter to force unique nicknames when updating a user.
	 * Checks on different fields: user_login, user_nicename, display_name and usermeta->nickname.
	 * Only works on wordpress 2.8 and above due to the filter used.
	 *
	 * @hook action 'user_profile_update_errors'
	 * @since 0.7
	 * @param WP_Error $errors	Errors object to add the new error if nickname exists.
	 * @param boolean $update	True if updating an existing user. False if creating a new one.
	 * @param object $user		User object with the form data.
	 * @return void
	 */
	function uniqueNickname ( & $errors, $update, & $user )
	{
		if ( ! $update ) {
			return;
		}

		global $wpdb;
		$user_id = $wpdb->get_var("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key='nickname' AND meta_value='{$user->nickname}' AND user_id <> {$user->ID};");

		if ( ! $user_id ) {
			$nicename = sanitize_title($user->nickname);
			$login = sanitize_user($user->nickname, true);
			$nick = $user->nickname;

			$user_id = $wpdb->get_var("SELECT ID FROM {$wpdb->users} WHERE ID <> {$user->ID} AND ( user_login='{$nick}' OR user_login='{$login}' OR user_nicename='{$nicename}' OR user_nicename='{$nick}' OR display_name='{$nick}' );");
		}

		if ( $user_id ) {
			$errors->add( 'nickname_exists', __('<strong>ERROR</strong>: This nickname is already in use, please choose another one.', $this->PID), array( 'form-field' => 'nickname' ) );
		}
	}

	/**
	 * Loads settings page for User Profiles
	 *
	 * @hook add_submenu_page
	 * @return void
	 */
	function profileSettings() {
		if ( ! current_user_can('aoc_manage_settings') ) {		// Verify user permissions.
			wp_die('<strong>' .__('What do you think you\'re doing?!?', $this->PID) . '</strong>');
		}

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$this->saveAdminSettings();
		}

		require ( dirname(__FILE__) . '/admin.php');
	}

	/**
	 * Saves settings from admin form.
	 * TODO: Check settings with intval.
	 *
	 * @return void
	 */
	private function saveAdminSettings ()
	{
		check_admin_referer('alkivia-profile-settings');

		if ( isset($_POST['action']) && 'update' == $_POST['action'] ) {
		    $options = stripslashes_deep($_POST['profiles']);
		    $this->setNewOptions($options);
			ak_admin_notify();
		} else {                         // Missing action
			ak_admin_error(__('Bad form received.', $this->PID));
		}
	}
}
