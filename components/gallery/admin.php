<?php
/**
 * AOC Gallery settings page.
 * This file is included from the gallery class and is in the class scope.
 *
 * @version		$Rev: 903 $
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

// File cannot be called directly
if ( ! defined('AOC_PATH') ) {
	die ('');	// Silence is gold.
}

$settings = $this->getOption();
?>
<div class="wrap">
	<div id="icon-akmin" class="icon32"></div>
	<h2><?php _e('Photo Gallery Settings', $this->PID) ?></h2>

	<table id="akmin">
	<tr>
		<td class="content">
		<form method="post" enctype="multipart/form-data" action="admin.php?page=<?php echo $this->slug; ?>-gallery">
		<?php wp_nonce_field('alkivia-gallery-settings'); ?>
		<fieldset>

		<dl>
			<dt><?php _e('General Settings', $this->PID); ?></dt>
			<dd>
				<table width="100%" class="form-table">
				<tr>
					<th scope="row"><?php _e('Gallery template:', $this->PID)?></th>
					<td>
					<?php if ( $this->allowAdmin('gallery_template') ) :?>
						<select name="settings[gallery_template]">
						<?php
						$templates = ak_get_templates(aoc_template_paths(), 'gallery-');
						foreach ( $templates as $tpl => $t_name ) : ?>
							<option value="<?php echo $tpl; ?>" <?php selected($tpl, $settings['gallery_template']); ?> >
								<?php echo $t_name; ?>&nbsp;
							</option>
						<?php endforeach; ?>
						</select>
						<span class="setting-description"><?php _e('This template is used on the gallery page.<br />You can upload additional templates to the "templates" directory.', $this->PID)?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Author Thumbnail:', $this->PID)?></th>
					<td>
					<?php if ( $this->allowAdmin('author_thumbnail') ) :?>
						<select name="settings[author_thumbnail]">
							<option value="disabled"<?php selected('disabled', $settings['author_thumbnail']); ?>><?php _e('Disabled', $this->PID); ?>&nbsp;</option>
							<option value="left"<?php selected('left', $settings['author_thumbnail']); ?>><?php _e('Left Align', $this->PID); ?>&nbsp;</option>
							<option value="right"<?php selected('right', $settings['author_thumbnail']); ?>><?php _e('Right Align', $this->PID); ?>&nbsp;</option>
						</select>
						<span class="setting-description"><?php _e('Show the author thumbnail on each post. Only works if author have an aproved image.', $this->PID)?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="settings[descending]"><?php _e('Newest images first:', $this->PID) ?></label></th>
					<td>
					<?php if ( $this->allowAdmin('descending') ) :?>
						<input type="checkbox" name="settings[descending]" value="1" <?php checked(1, $settings['descending']);?> /> <?php _e('Show recently uploaded images first.', $this->PID)?><br />
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="settings[avatar]"><?php _e('Galley Avatars:', $this->PID) ?></label></th>
					<td>
					<?php if ( $this->allowAdmin(array('local_avatar', 'check_gravatar')) ) :?>
					    <?php if ( $this->allowAdmin('local_avatar', false) ) :?>
							<input type="checkbox" name="settings[local_avatar]" value="1" <?php checked(1, $settings['local_avatar']);?> /> <?php _e('Replace Gravatars by user gallery avatars.', $this->PID)?><br />
						<?php endif;
						if ( $this->allowAdmin('check_gravatar', false) ) :?>
							<input type="checkbox" name="settings[check_gravatar]" value="1" <?php checked(1, $settings['check_gravatar']);?> /> <?php _e('If not local avatar, look for it at Gravatar.', $this->PID)?><br />
						<?php endif; ?>
						<span class="setting-description"><?php _e('Avatars are shown or hidden depenmding on Discussion Settings for WordPress.', $this->PID )?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Anonymous avatar URL:', $this->PID); ?></th>
					<td>
					<?php if ( $this->allowAdmin('anonymous_url') ) :?>
						<input type="text" name="settings[anonymous_url]" value="<?php echo $settings['anonymous_url'];?>" class="regular-text code" style="width:500px;"/><br />
						<span class="setting-description"><?php _e('A valid avatar URL. Must be a full URL to an small image. Leaving it empty will reset to a default anonymous avatar.', $this->PID )?></span>
					<?php endif; ?>
					</td>
				</tr>
				</table>
			</dd>
		</dl>

		<dl>
			<dt><?php _e('Privacy', $this->PID); ?></dt>
			<dd>
				<table width="100%" class="form-table">
				<tr>
					<th scope="row"><?php _e('Hide Pictures:', $this->PID) ?></th>
					<td>
					<?php if ( $this->allowAdmin('needs_load') ) :?>
						<label for="settings[needs_load]"><input type="checkbox" name="settings[needs_load]" value="1" <?php checked(1, $settings['needs_load']);?> />
						<?php  _e('User has to load at least one image to see other users pictures.', $this->PID); ?></label><br />
					<?php endif; ?>
					</td>
				</tr>
				</table>
			</dd>
		</dl>

		<dl>
			<dt><?php _e('User Limits', $this->PID); ?></dt>
			<dd>
				<table width="100%" class="form-table">
				<tr>
					<th scope="row"><label for="settings[max_number]"><?php _e('Max. Upload images:', $this->PID) ?></label></th>
					<td>
					<?php if ( $this->allowAdmin('max_number') ) :?>
						<input type="text" name="settings[max_number]" size="6" value="<?php echo $settings['max_number'];?>" class="code" /><br />
						<span class="setting-description"><?php _e('Maximum number of images an user can upload.', $this->PID); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="settings[concurrent_up]"><?php _e('Concurrent Upload:', $this->PID) ?></label></th>
					<td>
					<?php if ( $this->allowAdmin('concurrent_up') ) :?>
						<select name=settings[concurrent_up]">
							<option value="1"<?php selected(1, $settings['concurrent_up']); ?>>&nbsp;1&nbsp;</option>
							<option value="2"<?php selected(2, $settings['concurrent_up']); ?>>&nbsp;2&nbsp;</option>
							<option value="3"<?php selected(3, $settings['concurrent_up']); ?>>&nbsp;3&nbsp;</option>
							<option value="4"<?php selected(4, $settings['concurrent_up']); ?>>&nbsp;4&nbsp;</option>
							<option value="5"<?php selected(5, $settings['concurrent_up']); ?>>&nbsp;5&nbsp;</option>
						</select>
						<span class="setting-description"><?php _e('Number of images an user can upload simultaneously.', $this->PID); ?><br />
						<?php _e('The maximum megabytes that can be uploaded depends on the post_max_size PHP directive.', $this->PID)?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="settings[max_size]"><?php _e('Max. Image size:', $this->PID) ?></label></th>
					<td>
					<?php if ( $this->allowAdmin('max_size') ) :?>
						<input type="text" name="settings[max_size]" size="6" value="<?php echo $settings['max_size'];?>" class="code" /> Kb.<br />
						<span class="setting-description"><?php printf(__('Maximum size for each image file an user can upload in Kb. System max is: %s', $this->PID), ak_return_units(ak_max_upload())); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="settings[megapixels]"><?php _e('Max. Image megapixels:', $this->PID) ?></label></th>
					<td>
					<?php if ( $this->allowAdmin('megapixels') ) :?>
						<input type="text" name="settings[megapixels]" size="6" value="<?php echo $settings['megapixels'];?>" class="code" /> <?php _e('Megapixels',$this->PID) ?>.<br />
						<span class="setting-description"><?php _e('Maximum megapixels an image can have to be processed.<br />More megapixels, more memory needed (about 9 Mb processing memory per megapixel)', $this->PID); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="settings[minpixels]"><?php _e('Min. Image megapixels:', $this->PID) ?></label></th>
					<td>
					<?php if ( $this->allowAdmin('minpixels') ) :?>
						<input type="text" name="settings[minpixels]" size="6" value="<?php echo $settings['minpixels'];?>" class="code" /> <?php _e('Megapixels',$this->PID) ?>.<br />
						<span class="setting-description"><?php _e('Minimum megapixels an image must have to be processed. This is used to prevent users sending too small pictures.<br />(Images are resized to setings sizes)', $this->PID); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				</table>
			</dd>
		</dl>

		<dl>
			<dt><?php _e('Image Sizes', $this->PID); ?></dt>
			<dd>
				<table width="100%" class="form-table">
					<tr>
						<th scope="row"><?php _e('Profile Image size:', $this->PID) ?></th>
						<td>
						<?php if ( $this->allowAdmin('large') ) :?>
							<input type="radio" name="settings[large]" value="1" <?php checked(1, $settings['large']); ?> /> <?php _e('Use WordPress large size', $this->PID); ?> &nbsp;&nbsp;
							<input type="radio" name="settings[large]" value="2" <?php checked(2, $settings['large']); ?> /> <?php _e('Use this settings', $this->PID); ?>: &nbsp;
							<?php _e('Width', $this->PID)?>: <input type="text" name="settings[large_w]" size="6" value="<?php echo $settings['large_w'];?>" class="code" />
							<?php _e('Height', $this->PID)?>: <input type="text" name="settings[large_h]" size="6" value="<?php echo $settings['large_h'];?>" class="code" /> <?php _e('pixels', $this->PID); ?><br />
							<span class="setting-description"><?php _e('Images are always resized to this settings and this is the size that shows in the user profile page.')?></span>
						<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Thumbnails size:', $this->PID) ?></th>
						<td>
						<?php if ( $this->allowAdmin('thumb') ) :?>
							<input type="radio" name="settings[thumb]" value="1" <?php checked(1, $settings['thumb']); ?> /> <?php _e('Use WordPress thumbnail size', $this->PID); ?> &nbsp;&nbsp;
							<input type="radio" name="settings[thumb]" value="2" <?php checked(2, $settings['thumb']); ?> /> <?php _e('Use this settings', $this->PID); ?>: &nbsp;
							<?php _e('Width', $this->PID)?>: <input type="text" name="settings[thumb_w]" size="6" value="<?php echo $settings['thumb_w'];?>" class="code" />
							<?php _e('Height', $this->PID)?>: <input type="text" name="settings[thumb_h]" size="6" value="<?php echo $settings['thumb_h'];?>" class="code" /> <?php _e('pixels', $this->PID); ?> &nbsp;
							<input type="checkbox" name="settings[thumb_crop]" value="1" <?php checked(1, $settings['thumb_crop']);?> /> <?php  _e('Crop at exact size', $this->PID); ?><br />
							<span class="setting-description"><?php _e('This is the size for the user gallery thumbnails.')?></span>
						<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Avatar max size:', $this->PID) ?></th>
						<td>
						<?php if ( $this->allowAdmin('avatar_size') ) :?>
							<input type="text" name="settings[avatar_size]" size="6" value="<?php echo $settings['avatar_size'];?>" class="code" /> <?php _e('pixels', $this->PID); ?><br />
							<span class="setting-description"><?php _e('It is recommended not to set this too big, site performance will be affected.', $this->PID); ?></span>
						<?php endif; ?>
						</td>
					</tr>
				</table>
			</dd>
		</dl>

		<?php if ( $this->allowAdmin('watermark', false) ) :?>
		<dl>
			<dt><?php _e('Watermarks', $this->PID); ?></dt>
			<dd>
				<table width="100%" class="form-table">
					<tr>
						<th scope="row"><label for="settings[watermark]"><?php _e('Images Watermark:', $this->PID) ?></label></th>
						<td>
							<input type="checkbox" name="settings[watermark]" value="1" <?php checked(1, $settings['watermark']);?> />
							<?php
								$uploads	= wp_upload_dir();
								$watermark	= trailingslashit($uploads['baseurl']) . 'alkivia/watermark.png';

								$string = __('Watermark large pictures', $this->PID);
								$image = '';

								$filename = trailingslashit($uploads['basedir']) . 'alkivia/watermark.png';
								if ( file_exists($filename) ) {
									$string .= ' ' . __('with this image', $this->PID) . ':<br />';

									$info = getimagesize($filename);
									$image = '<img src="'. $watermark .'?'. rand() . '" width="'. $info[0] .'" height="'. $info[1] .'" alt="" />';
								}
								echo $string . $image;
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Upload new watermark:', $this->PID) ?></th>
						<td>
							<input type="file" size="40" name="wmark_file" value="" />
							<br /><span class="setting-description"><?php _e('The uploaded image will be proportionally resized to 1/3 of profile images width.', $this->PID)?></span>
						</td>
					</tr>
				</table>
			</dd>
		</dl>
		<?php endif; ?>
		<p class="submit">
			<input type="hidden" name="action" value="update" />
			<input type="submit" name="Submit" value="<?php _e('Save Changes', $this->PID) ?>" class="button-primary" />
		</p>

		</fieldset>
		</form>

		<?php ak_admin_footer($this->ID, 2009); ?>

		</td>
		<td class="sidebar">
			<?php ak_admin_authoring($this->ID); ?>
		</td>
	</tr>
	</table>
</div>
