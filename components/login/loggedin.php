<?php
/**
 * AOC Login Form: Last users who logged in.
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

require_once( AOC_PATH . '/includes/users-widget.php' );

/**
 * Widget class to list last user access to the site.
 * @package AOC
 * @subpackage Login
 * @since 0.8
 */
final class aocLoggedIn extends aocUsersWidget
{
    /**
     * Widget StartUp.
     * @see aocUsersWidget::startUp()
     */
    protected function widgetLoad ()
    {
        $this->wID      = 'aoc_logged_in';
        $this->wTitle   = __('Logged in users', $this->PID);
        $this->wOptions = array( 'classname' => 'widget_loggedin_recent',
                                 'description' => __('Latest users that accessed the site.', $this->PID)
                          );
    }

    /**
     * Widget Output.
     * @see WP_Widget::widget()
     */
    public function widget ( $args, $instance )
    {
		global $wpdb;

		extract ( $args, EXTR_SKIP);
		$number = (int) $instance['number'];
		if ( 1 > $number ) {
			$number = 5;
		} elseif ( 20 < $number ) {
			$number = 20;
		}

		echo $before_widget;
		if ( !empty($instance['title']) ) {
			echo $before_title. $instance['title'] . $after_title;
		}
		echo '<ul>';

		$subquery = aoc_roles_subquery();
		$query = "SELECT ID, user_login, display_name FROM {$wpdb->usermeta} INNER JOIN ( {$subquery} ) AS usr "
			. "ON {$wpdb->usermeta}.user_id = usr.id "
			. "WHERE {$wpdb->usermeta}.meta_key='akucom_last_access' "
			. "ORDER BY {$wpdb->usermeta}.meta_value DESC "
			. "LIMIT 0,{$number};";
		$users = $wpdb->get_results($query);

		foreach ( $users as $user ) {
			echo '<li>';
			if ( $instance['avatar'] ) echo get_avatar($user->ID, $instance['avatar-size']);
			echo '<a href="' . aoc_profile_link(urlencode($user->user_login)) . '">'. $user->display_name . '</a></li>';
		}

		echo '</ul>';
		echo $after_widget;
    }
}
