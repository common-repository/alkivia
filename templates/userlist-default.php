<?php
/**
 * Default template for user list page.
 * There are available this variables:
 * 		- baselink: baselink to the user profile page.
 * 		- text_domain: Translations textDomain.
 * 		- avatar_size: The size for avatars set on settings page.
 * 		- users: An array with the users data for the page.
 * 		- pager: The page navigator output
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

$roles = ak_get_roles(true);                                                // Available roles (translated)
$dt_format = get_option('date_format') . ' | ' . get_option('time_format');  // Date-Time format

?>
<table class="profile-list">
	<thead>
		<tr>
			<th>&nbsp;</th>
			<th><?php _e('Nickname', $i18n); ?></th>
			<th><?php _e('Role', $i18n); ?></th>
			<th><?php _e('Website', $i18n); ?></th>
			<th><?php _e('Member Since', $i18n); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($users as $user ) : ?>
			<tr>
				<td><?php echo get_avatar($user['ID'], $avatar_size) ?></td>
				<td><a href="<?php echo $baselink . urlencode($user['user_login']); ?>"><?php echo $user['display_name']; ?></a></td>
				<td><?php echo $roles[ak_get_user_role($user['ID'])] ?></td>
				<td><?php if ( empty($user['user_url']) || 'http://' == $user['user_url'] ) {
				    echo '&nbsp;';
				} else {
				    echo '<a href="'. $user['user_url'] .'" target="_blank">'. substr($user['user_url'], 7) . '</a>';
				} ?>
				</td>
				<td nowrap="nowrap"><?php echo mysql2date($dt_format, $user['user_registered']); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php echo $pager ?>