<?php
/**
 * AOC User Gallery manager.
 * Plugin to create and manage communities in any WordPress blog.
 *
 * @version		$Rev: 868 $
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

global $user_ID;

// Managing own or others gallery?

$settings	= $this->getOption();
$max_size	= $this->maxUpload();
$gallery	= $this->getUserGallery($user->ID);

?>

<div class="wrap">
	<div id="icon-akmin" class="icon32"></div>
	<h2><?php echo $page_title; ?></h2>

	<table id="akmin">
	<tr>
		<td class="content">

		<form method="post" enctype="multipart/form-data" action="<?php echo $action_link; ?>">
		<?php wp_nonce_field('photo-gallery-upload'); ?>
		<fieldset>

		<dl>
			<dt><?php _e('Upload new pictures', $this->PID); ?></dt>
			<dd>
			<?php
			$cur_num = count($gallery['images']);
			if ( $settings['max_number'] <= $cur_num ) : ?>
				<h3 style="text-align:center;"><?php _e('You already have uploaded the maximum allowed images.', $this->PID)?></h3>
				<p style="text-align:center;"><?php printf(__('The upload limit is set to %d images.', $this->PID), $settings['max_number']); ?>
			<?php else :?>
				<table width="100%" class="form-table">
					<tr>
						<td colspan="2" style="text-align:center;">
							<span class="setting-description"><?php
	    						printf(__('Maximum accepted sizes are %1$s and %2$s megapixels.', $this->PID), ak_return_units($max_size), $settings['megapixels']);
		    					echo ' ';
			    				printf(__('The upload limit is set to %d images.', $this->PID), $settings['max_number']);
				    		?></span>
						</td>
					</tr>
			    <?php
                    $n = 0;
			        for ( $i = $cur_num; $i < $settings['max_number'] && $i < $settings['concurrent_up'] + $cur_num; $i++) :
                ?>
					<tr>
						<th scope="row"><?php printf(__('Upload image [%d]:', $this->PID), ++$n); ?></th>
						<td>
						<input type="file" size="40" name="user_picture_<?php echo $n; ?>" value="" />
						</td>
					</tr>
			    <?php endfor; ?>
					<tr>
						<td colspan="2" style="text-align:center;">
							<p class="submit">
								<input type="hidden" name="action" value="upload" />
								<input type="submit" name="Submit" value="<?php _e('Upload Images', $this->PID) ?>" class="button-primary" />
							</p>
						</td>
					</tr>
				</table>
			<?php endif; ?>
			</dd>
		</dl>
		</fieldset>
		</form>

	<?php if ( 0 < count($gallery['images']) ): ?>

		<form method="post" action="<?php echo $action_link; ?>">
		<?php wp_nonce_field('manage-photo-gallery'); ?>
		<fieldset>

		<dl>
			<dt><?php echo $title_prefix . ' '; _e('Uploaded Images', $this->PID); ?></dt>
			<dd>
				<table class="widefat" cellspacing="0">
				<thead>
					<tr>
						<th scope="col"><?php _e('ID', $this->PID); ?></th>
						<th scope="col"><?php _e('Name', $this->PID); ?></th>
						<th scope="col"><?php _e('Avatar', $this->PID); ?></th>
						<th scope="col" style="text-align:center;"><?php _e('Caption', $this->PID); ?></th>
						<th scope="col" style="text-align:center;"><?php _e('Main Picture', $this->PID); ?></th>
						<th scope="col" style="text-align:center;"><?php _e('Is Avatar', $this->PID); ?></th>
						<th scope="col" style="text-align:center;"><?php _e('Moderation', $this->PID); ?></th>
						<th scope="col" style="text-align:center;"><span style="color:red;"><?php _e('Delete', $this->PID)?></span></th>
					</tr>
				</thead>

				<tbody>
			<?php
			    if ( $this->getOption('descending') ) {
    			    krsort($gallery['images']);
			    }
			    foreach ( $gallery['images'] as $imgID => $img ): ?>
					<tr>
						<td><?php echo $imgID; ?></td>
						<td><?php echo $img['name']; ?></td>
						<td><?php
							$img_base = $settings['baseurl'] .'/'. $img['name'];
							switch ( $img['approved'] ) {
								case 0 :	$status = __('Pending', $this->PID);
											break;
								case 1 :	$status = __('Approved', $this->PID);
											break;
								case 2 :	$status = __('Rejected', $this->PID);
											break;
							}
						?><img src="<?php echo $img_base; ?>_avatar.jpg" border="0" />
						<a href="<?php echo $img_base; ?>.jpg" class="thickbox" title="<?php printf(__('Image Preview %d', $this->PID), $imgID); ?>"><?php _e('View Larger', $this->PID); ?></a></td>
						<td style="text-align:center;"><input type="text" name="caption[<?php echo $imgID; ?>]" value="<?php echo $img['caption']; ?>" class="regular-text" /></td>
						<td style="text-align:center;"><?php if ( 1 == $img['approved'] ) { ?>
							<input type="radio" name="main" value="<?php echo $imgID; ?>" <?php checked($imgID, $gallery['main']); ?> />
						<?php } ?></td>
						<td style="text-align:center;"><?php if ( 1 == $img['approved'] ) { ?>
							<input type="radio" name="avatar" value="<?php echo $imgID; ?>" <?php checked($imgID, $gallery['avatar']); ?> />
						<?php } ?></td>
						<td><?php
							if ( current_user_can('aoc_manage_galleries')  ) {
								echo '<p><input type="radio" name="status['. $imgID .']" value="0"';  checked(0, $img['approved']); echo ' /> '. __('Pending', $this->PID) .'</p>';
								echo '<p><input type="radio" name="status['. $imgID .']" value="1"';  checked(1, $img['approved']); echo ' /> '. __('Approved', $this->PID) .'</p>';
								echo '<p><input type="radio" name="status['. $imgID .']" value="2"';  checked(2, $img['approved']); echo ' /> '. __('Rejected', $this->PID) .'</p>';
							} else {
								echo "<strong>{$status}</strong>";
							}
						?></td>
						<td style="text-align:center;"><input type="checkbox" name="delete[<?php echo $imgID; ?>]" value="1" /></td>
					</tr>
			<?php endforeach; ?>
				</tbody>

				<tfoot>
					<tr>
						<th scope="col"><?php _e('ID', $this->PID); ?></th>
						<th scope="col"><?php _e('Name', $this->PID); ?></th>
						<th scope="col"><?php _e('Avatar', $this->PID); ?></th>
						<th scope="col" style="text-align:center;"><?php _e('Caption', $this->PID); ?></th>
						<th scope="col" style="text-align:center;"><?php _e('Main Picture', $this->PID); ?></th>
						<th scope="col" style="text-align:center;"><?php _e('Is Avatar', $this->PID); ?></th>
						<th scope="col" style="text-align:center;"><?php _e('Moderation', $this->PID); ?></th>
						<th scope="col" style="text-align:center;"><span style="color:red;"><?php _e('Delete', $this->PID)?></span></th>
					</tr>
				</tfoot>
				</table>
			</dd>
		</dl>

		<p class="submit">
			<input type="hidden" name="action" value="update" />
			<input type="submit" name="Submit" value="<?php _e('Save Changes', $this->PID) ?>" class="button-primary" />
		</p>

		</fieldset>
		</form>
	<?php endif;
	        ak_admin_footer($this->PID, 2009);
		?>
		</td>
<!-- <td class="sidebar">
			<dl>
				<dt>SideBar Title</dt>
				<dd>
					SideBar Body
				</dd>
			</dl>
		</td>  -->
	</tr>
	</table>

</div>
