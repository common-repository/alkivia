<?php
/**
 * Sample template for user list page.
 * Templates for the User List must be prefixed with 'userlist-'.
 *
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
<div id="mini-profiles">
<?php foreach ( $users as $user ) : ?>
	<h1 class="user-name"><?php echo $user['display_name']; ?></h1>
	<div class="short-profile">
		<?php
    	/* Sample to get the thumbnail instead the avatar.
        	if ( ak_get_object('akucom')->activeComponent('gallery') ) {
    	    	echo aoc_get_user_image($user['ID'], 'alignleft', true);  // Get thumnail.
	        }
    	*/
        ?>
		<div class="image"><a href="<?php echo $baselink . urlencode($user['user_login']); ?>"><?php echo get_avatar($user['ID'], $avatar_size) ?></a></div>
	<p><?php echo $roles[ak_get_user_role($user['ID'])];
    if ( empty($user['user_url']) || 'http://' == $user['user_url'] ) {
        echo '</p>';
    } else { ?>
        <br /><?php _e('My site:', $i18n); ?>
	    <a href="<?php echo $user['user_url']; ?>" target="_blank"><?php echo substr($user['user_url'], 7); ?></a></p>
	<?php }
	$userData = get_userdata($user['ID']);
    if ( ! empty($userData->description) ) {
        echo wpautop('<p class="title">' . __('About Me', $i18n) . '</p>' . $userData->description);
    }
	?>
	</div>
<?php endforeach; ?>
</div>
<?php echo $pager; ?>