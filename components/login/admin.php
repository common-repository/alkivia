<?php
/**
 * AOC Login form settings page.
 * Plugin to create and manage communities in any WordPress blog.
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
?>

<div class="wrap">
	<div id="icon-akmin" class="icon32"></div>
	<h2><?php _e('Login Form Settings', $this->PID) ?></h2>

	<table id="akmin">
	<tr>
		<td class="content">
		<form method="post" enctype="multipart/form-data" action="admin.php?page=<?php echo $this->slug; ?>-login">
		<?php wp_nonce_field('upload-login-image'); ?>
		<fieldset>

		<dl>
			<dt><?php _e('Login form image', $this->PID); ?></dt>
			<dd>
				<table width="100%" class="form-table">
					<tr>
						<th scope="row"><?php _e('Current login image:', $this->PID) ?></th>
						<td>
							<?php $image = $this->getLogo();
							echo $image['html']; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Upload new image:', $this->PID) ?></th>
						<td>
							<input type="file" size="40" name="login_image" value="" />
							<br /><span class="setting-description"><?php _e('Recommended size: 326x67 pixels. The uploaded image will be proportionally resized to 326 pixels wide.', $this->PID)?></span>
						</td>
					</tr>
				</table>
			</dd>
		</dl>

		<p class="submit">
			<input type="hidden" name="action" value="upload" />
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
