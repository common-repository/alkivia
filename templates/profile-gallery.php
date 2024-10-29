<?php
/**
 * Profiles template that shows the user gallery instead the profile picture.
 * Available data variables:
 * 		- text_domain: Translations textDomain.
 * 		- user: User object from all user data.
 * 		- author_link: If user is author and settings allow it.
 * 		- edit_link: Link to edit the user info (Depends on the user status and role, and can be empty).
 * 		- header: extra information for profile header. Comes from filter 'aoc_profile_header'
 * 		- footer: extra information for profile footer. Comes from filter 'aoc_profile_footer'
 * 		- prev_link: link to previous user.
 * 		- next_link: link to next user.
 *
 * @version		$Rev: 823 $
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

// We want the gallery displayed with no title (User name) so, we add a filter to remove it.
add_filter('aoc_gallery_title', create_function('', "return '';"));
?>

<h1 class="alkivia-title"><?php echo $user->display_name ?></h1>

<?php if ( ! empty($edit_link) ) { ?>
	<h3 class="alkivia-admin-link"><?php echo $edit_link; ?></h3>
<?php }

echo $header;

if ( ak_get_object('akucom')->activeComponent('gallery') ) {
    echo aoc_gallery_content($user->ID);
}

if ( ! empty($author_link) ) { ?>
	<p style="text-align:center;"><strong><a href="<?php echo $author_link; ?>"><?php _e('View this author\'s posts', $i18n); ?></a></strong></p>
<?php } ?>

<h2><?php _e('General Info', $i18n); ?></h2>
<table class='profile-info'>
	<?php if (! empty($user->aoc_full_name) ) { ?>
		<tr><td><?php _e('Real Name:', $i18n); ?> </td><td><strong><?php echo $user->aoc_full_name; ?></strong></td></tr>
	<?php } ?>

	<tr><td><?php _e('Member Role', $i18n); ?>: </td><td><?php echo $user->aoc_translated_role; ?></td></tr>
	<tr><td><?php _e('Member Since', $i18n); ?>: </td><td><?php echo mysql2date($dt_format, $user->user_registered); ?></td></tr>
</table>

<?php if ( ! empty($user->description) ) { ?>
	<h2><?php _e('About me', $i18n); ?></h2>
    <?php echo wpautop($user->description); ?>
<?php } ?>

<h2><?php _e('Where to contact', $i18n); ?></h2>
<table border="0" cellpadding="2" cellspacing="2" class="profile-info">

<?php if ( ! empty($user->user_url) ) { ?>
	<tr>
		<td><?php _e('Website', $i18n); ?>: </td><td><a href="<?php echo $user->user_url; ?>" target="_blank"><?php echo substr($user->user_url, 7) ?></a></td>
	</tr>
<?php }
	if ( ! empty($user->user_email) ) { ?>
		<tr>
			<td><?php _e('E-mail', $i18n); ?>: </td><td><a href="mailto:<?php echo antispambot($user->user_email, 1); ?>"><?php echo antispambot($user->user_email); ?></a></td>
		</tr>
<?php }
    if ( ! empty($user->aim) ) { ?>
		<tr>
			<td><?php echo apply_filters('user_aim_label', __('AIM', $i18n)); ?>: </td><td><?php echo antispambot($user->aim); ?></td>
		</tr>
<?php }
    if ( ! empty($user->yim) ) { ?>
		<tr>
			<td><?php echo apply_filters('user_yim_label', __('Yahoo IM', $i18n)); ?>: </td><td><?php echo antispambot($user->yim); ?></td>
		</tr>
<?php }
	if ( ! empty($user->jabber) ) { ?>
		<tr>
			<td><?php echo apply_filters('user_jabber_label', __('Jabber / Google Talk', $i18n)); ?>: </td><td><?php echo antispambot($user->jabber); ?></td>
		</tr>
<?php } ?>

</table>
<?php foreach ( $user->aoc_widgets as $name => $widget ) :
    if ( empty($widget) ) continue; ?>
	<h2><?php echo $widget['title']; ?></h2>
	<ul class='profiles-<?php echo $name; ?>postlist'>
	    <?php if (is_array($widget['content']) ) {
	        foreach ( $widget['content'] as $item ) {
			    echo '<li>' . $item . '</li>' . PHP_EOL;
	        }
	    } else {
	        echo '<li>' . $widget['content'] . '</li>' . PHP_EOL;
	    } ?>
	</ul>
<?php endforeach;
echo $footer; ?>

<br/><div class="navigator bottom_navigator"><div class="navleft"><?php echo $prev_link; ?></div>
<div class="navright"><?php echo $next_link; ?></div></div>
