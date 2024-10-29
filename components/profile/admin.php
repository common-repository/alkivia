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
	<h2><?php _e('User Profiles Settings', $this->PID)?></h2>

	<table id="akmin">
	<tr>
		<td class="content">
		<form method="post" action="admin.php?page=<?php echo $this->slug; ?>-profiles">
		<?php wp_nonce_field('alkivia-profile-settings'); ?>
		<fieldset>

		<dl>
			<dt><?php _e('Privacy Options', $this->PID)?></dt>
			<dd>
				<table width="100%" class="form-table">
				<tr>
					<th scope="row"><?php _e('Show addresses:',$this->PID)?></th>
					<td>
					<?php if ( $this->allowAdmin(array('show_email', 'show_aim', 'show_yahoo', 'show_jabber')) ) : ?>
						<?php if ( $this->allowAdmin('show_email', false) ) : ?>
							<label for="profiles[show_email]"><input type="checkbox" name="profiles[show_email]" value="1" <?php checked(1, $settings['show_email']);?> /> <?php  _e('E-mail', $this->PID); ?></label><br />
						<?php endif;
						if ( $this->allowAdmin('show_aim', false) ) : ?>
							<label for="profiles[show_aim]"><input type="checkbox" name="profiles[show_aim]" value="1" <?php checked(1, $settings['show_aim']);?> /> <?php echo apply_filters('user_aim_label', __('AIM', $this->PID)); ?></label><br />
						<?php endif;
						if ( $this->allowAdmin('show_yahoo', false) ) : ?>
							<label for="profiles[show_yahoo]"><input type="checkbox" name="profiles[show_yahoo]" value="1" <?php checked(1, $settings['show_yahoo']);?> /> <?php echo apply_filters('user_yim_label', __('Yahoo IM', $this->PID)); ?></label><br />
						<?php endif;
						if ( $this->allowAdmin('show_jabber', false) ) : ?>
							<label for="profiles[show_jabber]"><input type="checkbox" name="profiles[show_jabber]" value="1" <?php checked(1, $settings['show_jabber']);?> /> <?php echo apply_filters('user_jabber_label', __('Jabber / Google Talk', $this->PID)); ?></label><br />
						<?php endif; ?>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Show real name:',$this->PID)?></th>
					<td>
					<?php if ( $this->allowAdmin('show_firstname') ) : ?>
						<label for="profiles[show_firstname]"><input type="checkbox" name="profiles[show_firstname]" value="1" <?php checked(1, $settings['show_firstname']);?> /> <?php _e('First Name', $this->PID)?></label>&nbsp;
					    <?php if ( $this->allowAdmin('show_lastname', false) ) : ?>
							<label for="profiles[show_lastname]"><input type="checkbox" name="profiles[show_lastname]" value="1" <?php checked(1, $settings['show_lastname']);?> /> <?php _e('Last Name', $this->PID)?></label>&nbsp;
							<span class="setting-description"><?php _e('(Last name is only shown if first name is also shown)', $this->PID )?></span>
						<?php endif; ?>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Show only users with this roles:', $this->PID); ?></th>
					<td>
					<?php if ( $this->allowAdmin('roles') ) :
					    $roles = $this->getAllowedRoles(false);
						foreach (ak_get_roles(true) as $name => $label) {
							echo '<label for="profiles[roles]['. $name .']"><input type="checkbox" name="profiles[roles]['. $name .']" value="1" tabindex="1" ';
							checked(1, $roles[$name]);
							echo ' /> '. $label .'</label><br />';
						}
                    endif; ?>
					</td>
				</tr>
				</table>
			</dd>
		</dl>

		<dl>
			<dt><?php _e('List Options', $this->PID); ?></dt>
			<dd>
				<table width="100%" class="form-table">
				<tr>
					<th scope="row"><?php _e('Disable user list:',$this->PID)?></th>
					<td>
					<?php if ( $this->allowAdmin('disable_list') ) : ?>
						<label for="profiles[disable_list]"><input type="checkbox" name="profiles[disable_list]" value="1" <?php checked(1, $settings['disable_list']);?> /> <?php  _e('Disable user listing', $this->PID); ?></label><br />
						<span class="setting-description"><?php _e('The page will show edited content with no user list and will be used as holder for user profiles.', $this->PID)?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('List template:', $this->PID)?></th>
					<td>
					<?php if ( $this->allowAdmin('list_template') ) : ?>
						<select name="profiles[list_template]">
						<?php
						$templates = ak_get_templates(aoc_template_paths(), 'userlist-');
						foreach ( $templates as $tpl => $t_name ) : ?>
							<option value="<?php echo $tpl; ?>" <?php selected($tpl, $settings['list_template']); ?> >
								<?php echo $t_name; ?>&nbsp;
							</option>
						<?php endforeach; ?>
						</select>
						<span class="setting-description"><?php _e('This template is used on the users list page.<br />You can upload additional templates to the "templates" directory.', $this->PID)?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Avatar Size:', $this->PID); ?></th>
					<td>
					<?php if ( $this->allowAdmin('avatar_size') ) : ?>
						<input type="text" name="profiles[avatar_size]" size="4" value="<?php echo $settings['avatar_size'];?>" class="code" />
						<?php _e('pixels', $this->PID);?>.&nbsp; <span class="setting-description"><?php _e('This size is only used on users list.', $this->PID); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Order users by:', $this->PID)?></th>
					<td>
					<?php if ( $this->allowAdmin('order_by') ) : ?>
						<select name="profiles[order_by]">
							<option value="display_name" <?php selected('display_name', $settings['order_by']); ?> >
								<?php _e('Display Name', $this->PID); ?>
							</option>
							<option value="user_registered" <?php selected('user_registered', $settings['order_by']); ?> >
								<?php _e('Date Registered', $this->PID); ?>
							</option>
							<option value="user_login" <?php selected('user_login', $settings['order_by']); ?> >
								<?php _e('User Login', $this->PID); ?>
							</option>
							<option value="ID" <?php selected('ID', $settings['order_by']); ?> >
								<?php _e('User ID', $this->PID); ?>
							</option>
						</select>
						<?php if ( $this->allowAdmin('order_dir', false) ) : ?>
							<select name="profiles[order_dir]">
								<option value="ASC" <?php selected('ASC', $settings['order_dir']); ?> >
								    <?php _e('Ascending', $this->PID); ?>
								</option>
								<option value="DESC" <?php selected('DESC', $settings['order_dir']); ?> >
									<?php _e('Descending', $this->PID); ?>
								</option>
							</select>
						<?php endif; ?>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Number of users per page:', $this->PID); ?></th>
					<td>
					<?php if ( $this->allowAdmin('per_page') ) : ?>
						<input type="text" name="profiles[per_page]" size="6" value="<?php echo $settings['per_page'];?>" class="code" />
					<?php endif; ?>
					</td>
				</tr>
				</table>
			</dd>
		</dl>

		<dl>
			<dt><?php _e('Additional Options', $this->PID); ?></dt>
			<dd>
				<table width="100%" class="form-table">
				<tr>
					<th scope="row"><?php _e('Profile template:', $this->PID)?></th>
					<td>
					<?php if ( $this->allowAdmin('profile_template') ) : ?>
						<select name="profiles[profile_template]">
						<?php
						$templates = ak_get_templates(aoc_template_paths(), 'profile-');
						foreach ( $templates as $tpl => $t_name ) : ?>
							<option value="<?php echo $tpl; ?>" <?php selected($tpl, $settings['profile_template']); ?> >
								<?php echo $t_name; ?>&nbsp;
							</option>
						<?php endforeach; ?>
						</select>
						<span class="setting-description"><?php _e('This template is used on the user profile page.<br />You can upload additional templates to the "templates" directory.', $this->PID)?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Author Link', $this->PID); ?>:</th>
					<td>
					<?php if ( $this->allowAdmin('author_link') ) : ?>
						<label for="profiles[author_link]"><input type="radio" name="profiles[author_link]" value="0" <?php checked(0, $settings['author_link']);?> /> <?php _e('Disabled', $this->PID); ?></label> &nbsp;
						<label for="profiles[author_link]"><input type="radio" name="profiles[author_link]" value="1" <?php checked(1, $settings['author_link']); ?> /> <?php _e('Replace current author link', $this->PID); ?></label> &nbsp;
						<label for="profiles[author_link]"><input type="radio" name="profiles[author_link]" value="2" <?php checked(2, $settings['author_link']); ?> /> <?php _e('Create new links to profile page', $this->PID); ?></label><br />
						<span class="setting-description"><?php _e('Select <strong>replace</strong> if your site shows links to author page when disabled. Select <strong>create</strong> if there are no links to author pages when disabled.', $this->PID); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Comments Link', $this->PID); ?>:</th>
					<td>
					<?php if ( $this->allowAdmin('comments_link') ) : ?>
						<label for="profiles[comments_link]"><input type="checkbox" name="profiles[comments_link]" value="1" <?php checked(1, $settings['comments_link']);?> /> <?php _e('Replace comments author link', $this->PID); ?></label><br />
						<span class="setting-description"><?php _e('This replaces the author comments link to the user profile page if exists', $this->PID); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Show on profile', $this->PID)?>:</th>
					<td>
					<?php if ( $this->allowAdmin(array('author_page', 'last_posts', 'last_comments')) ) : ?>
						<?php if ( $this->allowAdmin('author_page', false) ) : ?>
							<label for="profiles[author_page]"><input type="checkbox" name="profiles[author_page]" value="1" <?php checked(1, $settings['author_page']);?> /> <?php  _e('Link to author posts page', $this->PID); ?></label> &nbsp; <span class="setting-description"><?php _e('(Almost all themes support this)', $this->PID); ?></span><br />
						<?php endif;
						if ( $this->allowAdmin('last_posts', false) ) : ?>
							<label for="profiles[last_posts]"><input type="checkbox" name="profiles[last_posts]" value="1" <?php checked(1, $settings['last_posts']);?> /> <?php  _e('Last user posts', $this->PID); ?></label><br />
						<?php endif;
						if ( $this->allowAdmin('last_comments', false) ) : ?>
							<label for="profiles[last_comments]"><input type="checkbox" name="profiles[last_comments]" value="1" <?php checked(1, $settings['last_comments']);?> /> <?php _e('Last user comments', $this->PID); ?></label><br />
						<?php endif; ?>
					<?php endif; ?>
					</td>
				</tr>
				</table>
			</dd>
		</dl>

		<dl>
			<dt><?php _e('Custom labels', $this->PID)?></dt>
			<dd>
				<table width="100%" class="form-table">
				<tr>
					<th scope="row"><?php _e('AIM custom label:', $this->PID); ?></th>
					<td>
					<?php if ( $this->allowAdmin('aim_label') ) : ?>
						<input type="text" name="profiles[aim_label]" value="<?php echo $settings['aim_label'];?>" class="regular-text" />
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('YIM custom label:', $this->PID); ?></th>
					<td>
					<?php if ( $this->allowAdmin('yim_label') ) : ?>
						<input type="text" name="profiles[yim_label]" value="<?php echo $settings['yim_label'];?>" class="regular-text" />
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Jabber custom label:', $this->PID); ?></th>
					<td>
					<?php if ( $this->allowAdmin('jabber_label') ) : ?>
						<input type="text" name="profiles[jabber_label]" value="<?php echo $settings['jabber_label'];?>" class="regular-text" />
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<em><?php _e('This will change the default labels for your IM addresses in Alkivia and WordPress user profile.', $this->PID); ?><br />
						<?php
						global $wp_version;
						if ( version_compare('2.8', $wp_version, '>') ) {
						?>
							<span class="setting-description"><?php printf(__('To be changed in the WordPress user profile, this feature requires WordPress 2.8 or higher. To see what to hack to make it run in your version, <a href="%s" target="_blank">read this post</a>.', $this->PID), 'http://alkivia.org/community/wordpress-hack-for-community/'); ?></span></em>
						<?php } ?>
					</td>
				</tr>
				</table>
			</dd>
			<dd>
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
