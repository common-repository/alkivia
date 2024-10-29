<?php
/**
 * AOC Profiles settings page.
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


 *
 * @uses apply_filters() Calls the hooks 'user_aim_label', 'user_yim_label' and 'user_jabber_label' on their corresponding labels.
 */

// File cannot be called directly
if ( ! defined('AOC_PATH') ) {
	die ('');	// Silence is gold.
}

$settings  = $this->getOption();
?>
<div class="wrap">
	<div id="icon-akmin" class="icon32"></div>
	<h2><?php _e('Activity Wall Settings', $this->PID)?></h2>

	<table id="akmin">
	<tr>
		<td class="content">
		<form method="post" action="admin.php?page=<?php echo $this->slug; ?>-activity">
		<?php wp_nonce_field('alkivia-activity-settings'); ?>
		<fieldset>

		<dl>
			<dt><?php _e('User Activity Options', $this->PID)?></dt>
			<dd>
				<table width="100%" class="form-table">
				<tr>
					<th scope="row"><?php _e('User template:', $this->PID)?></th>
					<td>
					<?php if ( $this->allowAdmin('user_template') ) : ?>
						<select name="settings[user_template]">
						<?php
						$templates = ak_get_templates(aoc_template_paths(), 'userwall-');
						foreach ( $templates as $tpl => $t_name ) : ?>
							<option value="<?php echo $tpl; ?>" <?php selected($tpl, $settings['user_template']); ?> >
								<?php echo $t_name; ?>&nbsp;
							</option>
						<?php endforeach; ?>
						</select>
						<span class="setting-description"><?php _e('This template is used on the users wall page.<br />You can upload additional templates to the "templates" directory.', $this->PID)?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Avatar Size:', $this->PID); ?></th>
					<td>
					<?php if ( $this->allowAdmin('avatar_size') ) : ?>
						<input type="text" name="settings[avatar_size]" size="4" value="<?php echo $settings['avatar_size'];?>" class="code" />
						<?php _e('pixels', $this->PID);?>.&nbsp; <span class="setting-description"><?php _e('This size is only used on the activity list.', $this->PID); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Event timeout:', $this->PID); ?></th>
					<td>
					<?php if ( $this->allowAdmin('timeout') ) : ?>
						<input type="text" name="settings[timeout]" size="4" value="<?php echo $settings['timeout'];?>" class="code" />
						<?php _e('minutes', $this->PID);?>.&nbsp; <span class="setting-description"><?php _e('The minimum time between two actions on the same object to be recorded again, when doing the same action by the same user.', $this->PID); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('List items:', $this->PID); ?></th>
					<td>
					<?php if ( $this->allowAdmin('list_items') ) : ?>
						<input type="text" name="settings[list_items]" size="4" value="<?php echo $settings['list_items'];?>" class="code" />
						&nbsp; <span class="setting-description"><?php _e('Maximum number of items to be listed on any activity list.', $this->PID); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				</table>
			</dd>
		</dl>

		<dl>
			<dt><?php _e('Global activity page', $this->PID); ?></dt>
			<dd>
				<table width="100%" class="form-table">
				<tr>
					<th scope="row">
						<?php _e('Global Activity Page:', $this->ID); ?>
					</th>
					<td>
					<?php if ( $this->allowAdmin('global_wall') ) : ?>
						<select name="settings[global_wall]">
							<option value="0">-- <?php _e('Disabled', $this->ID);?> --</option>
							<?php parent_dropdown($settings['global_wall']); ?>
						</select><br />
						<span class="setting-description"><?php _e('The page where to show a global activity wall. <strong>You have to create this page first.</strong>', $this->ID); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Global template:', $this->PID)?></th>
					<td>
					<?php if ( $this->allowAdmin('wall_template') ) : ?>
						<select name="settings[wall_template]">
						<?php
						$templates = ak_get_templates(aoc_template_paths(), 'wall-');
						foreach ( $templates as $tpl => $t_name ) : ?>
							<option value="<?php echo $tpl; ?>" <?php selected($tpl, $settings['wall_template']); ?> >
								<?php echo $t_name; ?>&nbsp;
							</option>
						<?php endforeach; ?>
						</select>
						<span class="setting-description"><?php _e('This template is used on the system global activity page.<br />You can upload additional templates to the "templates" directory.', $this->PID)?></span>
					<?php endif; ?>
					</td>
				</tr>
				</table>
			</dd>
		</dl>

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
