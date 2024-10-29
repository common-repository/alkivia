<?php
/**
 * Alkivia Open Community General settings page.
 * Plugin to create and manage communities in any WordPress blog.
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

// File cannot be called directly
if ( ! defined('AOC_PATH') ) {
	die (''); // Silence is gold
}

$settings = $this->getOption();
?>

<div class="wrap">
	<div id="icon-akucom-admin" class="icon32"></div>
	<h2><?php _e('Alkivia General Settings', $this->ID) ?></h2>

	<table id="akmin">
	<tr>
		<td class="content">
		<form method="post" action="admin.php?page=<?php echo $this->getSlug(); ?>">
		<?php wp_nonce_field('alkivia-general-settings'); ?>
		<fieldset>

		<dl>
			<dt><?php _e('General Settings', $this->ID)?></dt>
			<dd>
				<table width='100%' class="form-table">
					<tr>
						<th scope="row">
							<?php _e('Community Page:', $this->ID); ?>
						</th>
						<td>
						<?php if ($this->allowAdmin('page_id') ) : ?>
							<select name="settings[page_id]">
								<option value="0">-- <?php _e('Not set', $this->ID);?> --</option>
								<?php parent_dropdown($settings['page_id']); ?>
							</select><br />
							<span class="setting-description"><?php _e('The page where to show all community outputs. <strong>You have to create this page first.</strong>', $this->ID); ?></span>
						<?php endif; ?>
						</td>
					</tr>
				</table>
			</dd>
		</dl>

		<dl>
			<dt><?php _e('Privacy Options', $this->ID); ?></dt>
			<dd>
				<table width="100%" class="form-table">
					<tr>
						<th scope="row"><?php _e('Visibility:', $this->ID) ?></th>
						<td>
						<?php if ($this->allowAdmin('privacy') ) : ?>
							<input type="radio" name="settings[privacy]" value="1" <?php checked(1, $settings['privacy']); ?> /> <?php _e('Everyone', $this->ID); ?> &nbsp;&nbsp;
							<input type="radio" name="settings[privacy]" value="2" <?php checked(2, $settings['privacy']); ?> /> <?php _e('Registered users', $this->ID); ?><br />
							<span class="setting-description"><?php _e('Determines if the community pages are available to everyone (public access) or only to registered users.', $this->ID); ?></span>
						<?php endif; ?>
						</td>
					</tr>
				</table>
			</dd>
		</dl>

		<?php $this->componentActivationForm(); ?>

		<p class="submit">
			<input type="hidden" name="action" value="update" />
			<input type="submit" name="Submit" value="<?php _e('Save Changes', $this->ID) ?>" class="button-primary" />
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
