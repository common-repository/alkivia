<?php
/**
 * General Widget structure for different users list.
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
 * Widget class for users lists.
 * Always shows the same output format and control.
 * Must define the methods startUp() and widget()
 *
 * @package Alkivia
 * @subpackage Community
 * @since 0.8
 */
abstract class aocUsersWidget extends WP_Widget
{
    /**
     * Plugin ID and translation textDomain.
     * @var string
     */
    protected $PID;

    /**
     * Widget ID.
     * This is the internal name for the widget. Ex.: aoc_widget_name
     * @var string
     */
    protected $wID;

    /**
     * Widget options.
     * Must define here the 'classname' and 'description'. Other widget options can be set.
     * @var array
     */
    protected $wOptions;

    /**
     * Widget title.
     * The translated title to be shown on the widgets dashboard.
     * @var string
     */
    protected $wTitle;

    /**
     * Class constructor.
     * @see WP_Widget::__construct()
     */
    final public function __construct ()
    {
        $this->PID = ak_get_object('akucom')->ID;     // Translation textdomain.

        $this->widgetLoad();
        parent::__construct($this->wID, $this->wTitle, $this->wOptions);
    }

    /**
     * Function used to define the widget options.
     * Have to define:
     * 		- wID: Widget ID (like aoc_widget_name).
     * 		- wOptions: Widget options array.
     * 		- wTitle: Title to show on widgets dashboard.
     * @return void
     */
    abstract protected function widgetLoad();

    /**
     * Widget Control form.
     * @see WP_Widget::form()
     */
    final public function form ( $instance )
    {
        $defaults = array (
				'title' => '',
				'number' => 5,
				'avatar' => 1,
				'avatar-size' => 16
			);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$title = attribute_escape($instance['title']);
        ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', $this->PID); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('avatar'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('avatar'); ?>" name="<?php echo $this->get_field_name('avatar'); ?>" value="1"<?php checked(1, $instance['avatar']); ?> /><?php _e('Show Avatars', $this->PID); ?></label>
		<p><label for="<?php echo $this->get_field_id('avatar-size'); ?>"><?php _e('Avatar size:', $this->PID); ?> <input style="width: 30px;" id="<?php echo $this->get_field_id('avatar-size'); ?>" name="<?php echo $this->get_field_name('avatar-size'); ?>" type="text" value="<?php echo $instance['avatar-size']; ?>" /> <?php _e('pixels', $this->PID)?></label>
		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of users:', $this->PID); ?> <input style="width: 30px;" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $instance['number']; ?>" /></label>
		<br /><small><?php printf(__('(At most %d)', $this->PID), 20); ?></small></p>
        <?php
    }

    /**
     * Widget data validation.
     * @see WP_Widget::update()
     */
    final public function update ( $newInstance, $oldInstance )
    {
        $instance = $oldInstance;

		$instance['title'] = strip_tags(stripslashes($newInstance['title']));
		$instance['number'] = (int) $newInstance['number'];
		$instance['avatar'] = intval($newInstance['avatar']);
		$instance['avatar-size'] = intval($newInstance['avatar-size']);

		return $instance;
    }
}
