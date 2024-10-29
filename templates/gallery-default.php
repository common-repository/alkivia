<?php
/**
 * Default template for user profile page.
 * Available data variables:
 * 		- text_domain: Translations textDomain.
 * 		- user: User object from all user data.
 * 		- edit_link: Link to edit the user info (Depends on the user status and role, and can be empty).
 * 		- images: An array with all images data and links.
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

$gallery_title = '<h1 class="alkivia-title">' . sprintf(__("%s's Photo Gallery", $i18n),
                                '<a href="' . aoc_profile_link($user->user_login) . '">' . $user->display_name . '</a>')
               . '</h1>';

// Apply a filter to the title to allow changing the title when using the gallery in other places.
echo apply_filters('aoc_gallery_title', $gallery_title);
?>

<?php if ( ! empty($edit_link) ) { ?>
	<h3 class="alkivia-admin-link"><?php echo $edit_link; ?></h3>
<?php } ?>

<div class='gallery'>
	<?php foreach ( $images as $thumb ) { ?>
		<dl class='gallery-item'><dt class='gallery-icon'>
		<dt>
			<a href="<?php echo $thumb['link'] ?>" title="<?php echo $thumb['caption']; ?>"><?php echo $thumb['img']; ?></a>
		</dt><dd class='gallery-caption'><?php echo $thumb['caption']; ?>
		<dd></dl>
	<?php } ?>
	<br style="clear: both;" />
</div>
