<?php
/**
 * AOC Activity Class Component.
 * Plugin to create and manage communities in any WordPress blog.
 *
 * @version		$Rev: 208165 $
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
 * Class aocActivity.
 * Records and displays all about the user activity.
 *
 * @since 		0.9
 * @author		Jordi Canals
 * @package		AOC
 * @subpackage	Activity
 * @link		http://alkivia.org
 */
class aocActivity extends akComponentAbstract
{
	/**
	 * Gallery component Statup.
	 * Sets all needed filters and actions to the WordPress API.
	 *
	 * @return void
	 */
	protected function moduleLoad ()
	{
	    // Global activity content
	    add_filter('the_content', array($this, '_globalActivityPage'));

		// User Page content
	    add_filter('aoc_alkivia_page', array($this, '_userWallContent'));

		// Setup table name on database object.
        global $wpdb;
        $wpdb->aoc_activity = $wpdb->prefix . 'aoc_activity';

        // Post addition and update
        add_action('wp_insert_post', array($this, '_postHook'), 10, 2);

        // Comments: additions and approvals
        add_action('wp_insert_comment', array($this, '_commentHook'), 10, 2);
        add_action('transition_comment_status', array($this, '_commentApprovedHook'), 10, 3);

        // User profile update
        add_action('personal_options_update', array($this, '_profileHook'));

        // Generic activity hook.
        add_action('aoc_generic_event', array($this, '_genericHook'));

		// Rewrite Rules
		add_filter('query_vars', array($this, '_rewriteVars'));
		add_filter('generate_rewrite_rules', array($this, '_rewriteRules'));
	}

	/**
	 * Sets default component settings.
	 *
	 * @return void
	 */
    protected function defaultOptions ()
    {
        return array(
            'timeout' => 5,                 // 5 minutes to save a new object/action on the same object_id
            'list_items' => 50,             // Show 50 items on activity list
            'avatar_size' => 32,            // Avatar size for the activity wall
            'global_wall' => 0,             // Page ID that holds the global site activity
            'wall_template' => 'default',   // Template to be used on global wall pages.
            'user_template' => 'default'	// Template to be used for a user wall.
            );
    }

	/**
	 * Component activation.
	 *
	 * @return void
	 */
	protected function componentActivate ()
	{
	    $this->createTable();
	}

    /**
     * Component update.
     *
     * @return void
     */
	protected function componentUpdate ( $version )
	{
	    $this->createTable();
	}

	/**
     * Create the database table to save activity items.
     *
	 * @return void
	 */
	private function createTable ()
	{
        global $wpdb;

        $query = "CREATE TABLE IF NOT EXISTS `{$wpdb->aoc_activity}` (
                        `even_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                        `owner_id` bigint(20) NOT NULL,
                        `object_type` varchar(20) NOT NULL,
                        `object_action` varchar(20) NOT NULL,
                        `object_id` bigint(20) NOT NULL DEFAULT '0',
                        `event_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `event_hook` varchar(100) NOT NULL,
                        `event_params` longtext NOT NULL,
                    PRIMARY KEY (`even_id`),
                    KEY `objects` (`owner_id`,`object_type`,`object_action`),
                    KEY `date_owner` (`event_date`,`owner_id`)
                    ) AUTO_INCREMENT=1 ;";
        $wpdb->query($query);
	}

	/**
	 * Adds the Activity settings menu.
	 *
	 * @return void
	 */
	public function adminMenus ()
	{
		add_submenu_page( $this->slug, __('Activity Wall', $this->PID), __('Activity Wall', $this->PID), 'aoc_manage_settings', $this->slug . '-activity', array($this, '_activitySettings'));
	}

	/**
	 * Loads settings page for Activity Wall.
	 *
	 * @return void
	 */
	public function _activitySettings ()
	{
		if ( ! current_user_can('aoc_manage_settings') ) {		// Verify user permissions.
			wp_die('<strong>' .__('What do you think you\'re doing?!?', $this->PID) . '</strong>');
		}

		global $wp_rewrite;
		$wp_rewrite->flush_rules();	// Force save rules.

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$this->saveAdminSettings();
		}

		require ( dirname(__FILE__) . '/admin.php');
    }

	/**
	 * Saves activity settings from admin page.
	 *
	 * @return void
	 */
    private function saveAdminSettings ()
    {
		check_admin_referer('alkivia-activity-settings');

		if ( isset($_POST['action']) && 'update' == $_POST['action'] ) {
			$post = stripslashes_deep($_POST['settings']);
			$post = array_merge($this->defaultOptions(), $post);

			$settings = array(
			    'timeout'	     => intval($post['timeout']),
				'list_items'     => intval($post['list_items']),
				'avatar_size'    => intval($post['avatar_size']),
				'global_wall'    => intval($post['global_wall']),
			    'wall_template'	 => $post['wall_template'],
				'user_template'  => $post['user_template']
			);
			$this->setNewOptions($settings);
			ak_admin_notify();
		} else { // Missing action
			wp_die('Bad form received.', $this->PID);
		}
    }

   /**
     * Gets the formated site wall content.
     * Uses the configured template.
     *
     * @hook filter 'the_content'
     *
     * @param string $content The original page content.
     * @return string Formated wall content.
     */
    public function _globalActivityPage ( $content )
    {
		if ( $this->getOption('global_wall') != get_the_ID() ) {		// If not the activity page. Do nothing.
			return $content;
		}

        $wall_items = $this->getTheWall();
        if ( empty($wall_items) ) {
            return $content;
        }

        // Load and process the page template.
		require_once ( AK_CLASSES . '/template.php');
        $template = new akTemplate( aoc_template_paths() );

        $template->textDomain($this->PID);
        $template->assign('items', $wall_items);

		// TODO: Page the wall activity.
        return $content . $template->getDisplay('wall-' . $this->getOption('wall_template'), 'wall-default');
    }

    /**
	 * Checks if an event log was recently recorded for an object/action
	 *
	 * @param array $args Event arguments.
	 * @return boolean returns true if an event log was recently added.
	 */
    private function didEvent ( $args )
    {
        global $wpdb;

        if ( ! is_array($args) ) {
            return false;
        }
        extract($args, EXTR_SKIP);

        $time = time() - ( (int) $this->getOption('timeout') * 60 );
        $dt = date('Y-m-d H:i:s', $time);

        $q = "SELECT COUNT(*) FROM {$wpdb->aoc_activity} WHERE `owner_id` = '{$owner_id}' AND `object_id` = '{$object_id}' AND `object_type` = '{$object_type}' AND `event_date` >= '{$dt}' AND `object_action` = '{$object_action}'";
        return ( 0 == $wpdb->get_var($q) ) ? false : true;
    }

	/**
	 * Generic event hook.
	 * Used to save generic actions to activity log.
	 *
	 * TODO: When inserting a generic hook, the display param have to be provided to know how the info has to display.
	 *
	 * @hook action aoc_generic_event
	 * @param array $args Hook data. The following data is available:
	 *				- owner_id: User who started the event. By default the logged in user.
	 *				- object-type: The object where the event occurs (profile, galery, post, comment, etc.)
	 *				- object-action: The action performed on the object (insert, edit, update...)
	 *				- object-id: The object ID where event occurs.
	 *				- eventy-date: The date/time when the event was done.
	 *				- event_hook: Custom hook to display the event log description.
	 *				- event_params: An array of additional params to be used by the event_hook, If received an string, will be used as param['display']
	 * @return void
	 */
    public function _genericHook ( $args )
    {
        global $wpdb;

        if ( ! isset($args['owner_id']) && is_user_logged_in() ) {
            return;
        }

        $defaults = array(
            'owner_id'      => 0,
        	'object_type'   => 'generic',
            'object_action' => 'default',
            'object_id'	    => 0,
            'event_date'    => date('Y-m-d H:i:s'),
            'event_hook'    => 'default',
            'event_params'  => ''
        );

        $args = wp_parse_args($args, $defaults );
        if ( is_array($args['event_params']) ) {
            $args['event_params'] = serialize($args['event_params']);
        } else {
            $args['event_params'] = serialize(array( 'display' => $args['event_params'] ));
        }

        if ( ! $this->didEvent($args) ) {
            $wpdb->insert($wpdb->aoc_activity, $args);
        }
    }

	/**
	 * Adds an activity entry every time a post/page is edited.
	 *
	 * @hook action 'wp_insert_post'
	 * @param int $id Post or page ID (Not used by needed by action hook)
	 * @param object post Post data object.
	 * @return void
	 */
    public function _postHook ( $id, $post )
    {
        if ( 'publish' != $post->post_status ) {
            return;
        }

        $action = ( '0000-00-00 00:00:00' != $post->post_date && $post->post_date != $post->post_modified )
                ? 'edit'
                : 'insert';

        $activity = array(
            'owner_id'       => $post->post_author,
        	'object_type'    => $post->post_type,
            'object_action'  => $action,
            'object_id'      => $post->ID,
            'event_date'     => $post->post_modified,
        	'event_hook'     => 'aoc_wall_post',
            'event_params'   => array( 'title' => $post->post_title )
        );

        $this->_genericHook($activity);
    }

	/**
	 * Adds an activity entry every time a comment is added.
	 *
	 * @hook action 'wp_insert_comment'
	 * @param int $id Comment ID (Not used by needed by action hook)
	 * @param object post Comment data object.
	 * @return void
	 */
    public function _commentHook ( $id, $comment )
    {
        if ( 0 == $comment->user_id || 1 != $comment->comment_approved ) {
            return;
        }

        $activity = array(
        	'owner_id'      => $comment->user_id,
            'object_type'   => 'comment',
            'object_action' => 'insert',
            'object_id'	    => $comment->comment_post_ID,
        	'event_date'    => $comment->comment_date,
            'event_hook'    => 'aoc_wall_comment',
            'event_params'  => array( 'comment_ID' => $comment->comment_ID )
        );

        $this->_genericHook($activity);
    }

	/**
	 * Adds an activity entry every time a comment is approved.
	 *
	 * @hook action 'transition_comment_status'
	 * @param string $new New comment status (Only processed when 'approved')
	 * @param string $old Old comment status (Only processed when not 'approved')
	 * @param object post Comment data object.
	 * @return void
	 */
    public function _commentApprovedHook ( $new, $old, $comment )
    {
        if ( 'approved' == $new && $new != $old ) {
            $comment->comment_approved = 1;
            $this->_commentHook( $comment->comment_ID, $comment);
        }
    }

	/**
	 * Adds an activity entry every time a comment is approved.
	 *
	 * @hook action 'personal_options_update'
	 * @param string $user_id User profile ID
	 * @return void
	 */
    public function _profileHook ( $user_id )
    {
        $activity = array(
            'owner_id'      => $user_id,
        	'object_type'   => 'profile',
            'object_action' => 'update',
            'object_id'	    => $user_id,
            'event_date'    => date('Y-m-d H:i:s'),
        	'event_hook'    => 'aoc_wall_profile',
        );

        $this->_genericHook($activity);
    }

    /**
     * Creates the content page for user activity wall.
     *
     * @param string $content Original page content.
     * @return string Replaced page content.
     */
    public function _userWallContent ( $content )
    {
		$user_name = urldecode(get_query_var('wall'));

		if ( ! empty($user_name) ) {
		    $wall = $this->getUserWall($user_name);
		    if ( $wall ) {
			    	$content = $wall;
    		} else {
	    			$content = '<h1 class="alkivia-error">' . __('User with this name not found.', $this->PID) . '</h1>';
    		}
		}

		return $content;
    }

    /**
     * Gets the formated user wall content.
     * Uses the configured template.
     *
     * @param string $user_login User login to retrieve the wall for.
     * @return string Formated wall content.
     */
    private function getUserWall ( $user_login )
    {
        if ( empty($user_login) ) {
            return false;
        }

        $user = get_userdatabylogin($user_login);
        if ( ! $user ) {
            return false;
        }

        $wall_items = $this->getTheWall($user->ID);
        if ( empty($wall_items) ) {
            return false;
        }

        // Load and process the page template.
		require_once ( AK_CLASSES . '/template.php');
        $template = new akTemplate( aoc_template_paths() );

        $template->textDomain($this->PID);
        $template->assignByRef('user', $user);
        $template->assign('items', $wall_items);

		if ( is_user_logged_in() ) {
		    $cur_user = wp_get_current_user();
		    if ( $cur_user->user_login == $user->user_login ) {
			    $link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/users.php?page='. $this->slug .'-my-gallery">' . __('Manage your photo gallery', $this->PID) . '</a>'	;
    		} elseif (current_user_can('aoc_manage_galleries') ) {
	    		$link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/users.php?user_id='. $user->ID .'&amp;page='. $this->slug .'-my-gallery">' . __('Manage this user gallery', $this->PID) . '</a>';
		    }
		    $template->assign('edit_link', $link);
		}

		// TODO: Page the wall activity.
		return $template->getDisplay('userwall-' . $this->getOption('user_template'), 'userwall-default');
    }

    /**
     * Returns a list of arrays for activity items.
     * Each array will contain:
     * 		- avatar: The user avatar url.
     * 		- display: Text to display (with anchors to items)
     * 		- date: the event date.
     * @param $owner_id	User id to get the activity from. If 0, will return flobal activity.
     * @return array List of activity items
     */
    public function getTheWall ( $owner_id = 0)
    {
        global $wpdb;

        if ( 0 != $owner_id && ! aoc_can_show_user($owner_id)) {
            return array();
        }

        $where = ( 0 == $owner_id ) ? '' : "WHERE `owner_id` = '{$owner_id}'";
        $q = "SELECT * FROM {$wpdb->aoc_activity} {$where} ORDER BY `event_date` DESC LIMIT 0,{$this->getOption('list_items')}";
        $wall_data = $wpdb->get_results($q, 'ARRAY_A');
        if ( empty($wall_data) ) {
            return array();
        }

        $wall_items = array();
        foreach ( $wall_data as $item ) {
            $display = $this->getHookDisplay($item);
            if ( $display ) {
                $wall_items[] = array(
                    'avatar' => get_avatar($item['owner_id'], $this->getOption('avatar_size')),
                    'date'	 => $item['event_date'],
                	'text'   => $display
                );
            }
        }

        return $wall_items;
    }

    /**
     * Formats an activity item to be displayed.
     * To do it calls the function hook set for each item.
     *
     * @param array $args Item arguments, as read from DB.
     * @return string The item text to display.
     */
    private function getHookDisplay ( $args )
    {
        $hook_function = '_' . $args['event_hook'];
        $args['event_params'] == unserialize($args['event_params']);
        if ( ! isset($args['event_params']['display']) ) {
            $args['event_params']['display'] = '';
        }

        if ( 'default' != $args['event_hook'] && function_exists($hook_function) ) {
            $text = call_user_func($hook_function, $args);
        } else {
            $text = $args['event_params']['display'];
        }

        return $text;
    }

    /**
	 * Creates the needed rewrite rules for user wall.
	 *
	 * @hook action by ref 'generate_rewrite_rules'
	 * @param object $wp_rewrite	Current rewrite rules. Received by ref.
	 * @return void
	 */
	public function _rewriteRules ( &$wp_rewrite )
	{
		$pid = ak_get_object($this->PID)->getOption('page_id');
		$slug = basename(get_page_uri($pid));

		$rules = array ( $slug . '/wall/(.+)/?$' => 'index.php?page_id='. $pid .'&wall='. $wp_rewrite->preg_index(1),
						'(.+)/' . $slug . '/wall/(.+)/?$' => 'index.php?page_id='. $pid .'&wall=' . $wp_rewrite->preg_index(2) );
		$wp_rewrite->rules = $rules + $wp_rewrite->rules;
	}

	/**
	 * Creates the query var to get a user wall page.
	 *
	 * @hook filter 'query_vars'
	 * @param array $vars	Current WordPress query vars.
	 * @return array		New Query vars.
	 */
	public function _rewriteVars ( $vars )
	{
		$vars[] = 'wall';
		return $vars;
	}
}
