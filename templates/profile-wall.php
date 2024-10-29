<?php
/**
 * Activity wall for user profile page.
 * Templates for the User Profile must be prefixed with 'profile-'.
 *
 * Available data variables:
 * 		- text_domain: Translations textDomain.
 * 		- user: User object from all user data.
 * 		- author_link: If user is author and settings allow it.
 * 		- edit_link: Link to edit the user info (Depends on the user status and role, and can be empty).
 * 		- header: extra information for profile header. Comes from filter 'aoc_profile_header'
 * 		- footer: extra information for profile footer. Comes from filter 'aoc_profile_footer'
 * 		- prev_link: link to previous user.
 * 		- next_link: link to next user.
 * Header and Footer are not used on this template.
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

// echo '<pre>'; print_r($user); echo '</pre>';
$dt_format = get_option('date_format') . ' | ' . get_option('time_format');    // Date/Time system format.
?>

<?php if ( ! empty($edit_link) ) { ?>
	<h3 class="alkivia-admin-link"><?php echo $edit_link; ?></h3>
<?php } ?>

<div class="profile-box">
	<h1 class="profile-name"><?php echo $user->display_name ?></h1>
	<?php if ( ak_get_object('akucom')->activeComponent('gallery') ) {
	    echo '<div class="left-side">'. aoc_profile_picture($user, true) . '</div>';
    }
    if ( ! empty($user->description) ) { ?>
		<h2><?php _e('About me', $i18n); ?></h2>
        <?php echo wpautop($user->description); ?>
    <?php } ?>
<div style="clear:both;"></div>
</div>
<?php if ( ! empty($author_link) ) { ?>
	<p style="text-align:center;"><strong><a href="<?php echo $author_link; ?>"><?php _e('View this author\'s posts', $i18n); ?></a></strong></p>
<?php } ?>

<br />

<div id="profile-info">
	<div id="profile-left">
		<ul>
            <li><h2><?php _e('General Info', $i18n); ?></h2></li>
            <li><ul>
			    <?php if (! empty($user->aoc_full_name) ) { ?>
					<li><?php _e('Real Name:', $i18n); ?> <?php echo $user->aoc_full_name; ?></li>
	            <?php } ?>
				<li><?php _e('Member Role', $i18n); ?>: <?php echo $user->aoc_translated_role; ?></li>
				<li><?php _e('Member Since', $i18n); ?>: <?php echo mysql2date($dt_format, $user->user_registered); ?></li>
			</ul></li>

			<li><h2><?php _e('Where to contact', $i18n); ?></h2></li>
			<li><ul>
            <?php if ( ! empty($user->user_url) ) { ?>
				<li><?php _e('Website', $i18n); ?>: <a href="<?php echo $user->user_url; ?>" target="_blank"><?php echo substr($user->user_url, 7) ?></a></li>
            <?php }
	        if ( ! empty($user->user_email) ) { ?>
				<li><?php _e('E-mail', $i18n); ?>: <a href="mailto:<?php echo antispambot($user->user_email, 1); ?>"><?php echo antispambot($user->user_email); ?></a></li>
            <?php }
            if ( ! empty($user->aim) ) { ?>
				<li><?php echo apply_filters('user_aim_label', __('AIM', $i18n)); ?>: <?php echo antispambot($user->aim); ?></li>
            <?php }
            if ( ! empty($user->yim) ) { ?>
				<li><?php echo apply_filters('user_yim_label', __('Yahoo IM', $i18n)); ?>: <?php echo antispambot($user->yim); ?></li>
            <?php }
        	if ( ! empty($user->jabber) ) { ?>
				<li><?php echo apply_filters('user_jabber_label', __('Jabber / Google Talk', $i18n)); ?>: <?php echo antispambot($user->jabber); ?></li>
            <?php } ?>
            </ul></li>
        <?php foreach ( $user->aoc_widgets as $name => $widget ) :
            if ( empty($widget) ) continue; ?>
			<li><h2><?php echo $widget['title']; ?></h2></li>
			<li><ul class='profiles-<?php echo $name; ?>postlist'>
	        <?php if (is_array($widget['content']) ) {
	            foreach ( $widget['content'] as $item ) {
			        echo '<li>' . $item . '</li>' . PHP_EOL;
	            }
	        } else {
	            echo '<li>' . $widget['content'] . '</li>' . PHP_EOL;
	        } ?></ul>
        <?php endforeach; ?>
		</ul>
	</div>

	<div id="profile-right">
		<ul>
			<li><h2><?php _e('Activity Wall', $i18n); ?></h2></li>
			<li><ul id="wall">
		    <?php if ( ak_get_object('akucom')->activeComponent('activity') ) :
	    	        $items = aoc_get_wall_items($user->ID);
                    foreach ( $items as $item ) :
                        echo '<li><p>' . $item['avatar'];
                        echo '<span class="datetime">' . ak_time_ago($item['date'], $i18n) . '</span><br />' . $item['text'] . '</p></li>';
                    endforeach;
                endif; ?>
		    </ul></li>
		</ul>
	</div>

	<div style="clear:both;"></div>
</div>

<br/><div class="navigator bottom_navigator"><div class="navleft"><?php echo $prev_link; ?></div>
<div class="navright"><?php echo $next_link; ?></div></div>
