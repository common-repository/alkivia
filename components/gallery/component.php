<?php
/**
 * AOC Gallery Class Component.
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

require_once ( AK_CLASSES . '/abstract/component.php' );
require_once ( dirname(__FILE__) . '/gallery-upload.php' );

/**
 * Class aocGallery.
 * Manages all about the user gallery.
 *
 * @since 		0.6
 * @author		Jordi Canals
 * @package		AOC
 * @subpackage	Gallery
 * @link		http://alkivia.org
 */
class aocGallery extends akComponentAbstract
{
	/**
	 * Gallery component Statup.
	 * Sets all needed filters and actions to the WordPress API.
	 *
	 * @return void
	 */
	protected function moduleLoad ()
	{
        // Show author thumbnails on posts.
        add_filter('the_content', array($this, '_authorThumbnail'));

	    // Community Page
		add_filter('aoc_alkivia_page', array($this, '_galleryContent'));

		// Actions on user management
		add_action('edit_user_profile', array($this, '_profileAdminLink'));
		add_action('delete_user', array($this, '_deleteUserImages'));

		// Rewrite Rules
		add_filter('query_vars', array($this, '_rewriteVars'));
		add_filter('generate_rewrite_rules', array($this, '_rewriteRules'));

		if ( $this->isUserAdmin() ) {	// If we are editing the user gallery.
			// Load ThickBox scripts.
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
		}
	}

	/**
	 * Loads component defaults.
	 * This defaults can differ from install time defaults. Are used to fill the $settings array when loaded from DB.
	 *
	 * @return void
	 */
	protected function defaultOptions ()
	{
		// We need to save the uploads folder URL. Mainly to be used in bbPress.
		$upload  = wp_upload_dir();
		$img_url = trailingslashit($upload['baseurl']) . 'alkivia/users';

		return array(
		    'descending'		=> 0,      // Newest images first.
			'local_avatar'		=> 0,      // Use local avatars.
			'check_gravatar'	=> 0,      // Check Gravatar if local is missing.
			'max_number'		=> 10,     // Max number of images.
		    'concurrent_up'		=> 3,      // Concurrent uploads.
			'max_size'			=> 2048,   // Max upload image size = 2 Mb.
			'megapixels'		=> 2,      // Max upload image megapixels.
		    'minpixels'			=> 0.2,    // Min image megapixels to upload.
			'large'				=> 2,      // Use plugin settings for profile images size.
    	    'large_w'			=> 560,    // large image max width
        	'large_h'			=> 480,    // large image max height
	        'thumb'				=> 2,      // Use plugin settings for thumbnail sizes.
    	    'thumb_w'			=> 180,    // Thumbnail image max width
        	'thumb_h'			=> 180,    // Thumbnail image max height
	        'thumb_crop'		=> 1,      // Crop thumbnails to the exact sizes.
    	    'avatar_size'		=> 100,    // Avatar size (avatars are square).
        	'baseurl'			=> $img_url,     // Base url to galleries folder.
	        'anonymous_url'		=> ak_get_object($this->PID)->getURL() . 'images/anonymous.png',	// Anonymous avatar.
    	    'watermark'			=> 0,      // Watermark uploaded images.
			'needs_load'		=> 0,      // User have to load an image to see others images.
		    'gallery_template'	=> 'default', // Template for gallery output.
		    'author_thumbnail'	=> 'disabled' // Show author thumbnail on posts.

		);

	}

	/**
	 * Component activation.
	 * We change the default settings that differ from defaults (as defaults already are set).
	 *
	 * @return void
	 */
	protected function componentActivate ()
	{
		$settings = array(
		    'descending'		=> 1,
			'local_avatar'		=> 1,
			'check_gravatar'	=> 1
		);
		$this->mergeOptions($settings);

		if ( $this->installing ) {	// First time activation
		    $role = get_role('administrator');
		    $role->add_cap('aoc_view_images');
			$role->add_cap('aoc_manage_galleries');

			$roles = ak_get_roles();
			foreach ( $roles as $name ) {
				$role = get_role($name);
				$role->add_cap('aoc_upload_images');
			}
		}
	}

	/**
	 * Updates the component settings.
	 * @return void
	 */
	protected function componentUpdate ( $version )
	{
		// Delete unusued settings.
		$this->deleteOption('upload');

		if ( version_compare($version, '0.8', '<') ) {
		    // Change Widget settings to new class names.
		    $widget = get_option('akucom_gallery_widget');
		    if ( false !== $widget ) {
		        add_option('widget_aoc_updated_gallery', $widget);
		        delete_option('akucom_gallery_widget');
		    }
		}

		if ( version_compare($version, '0.10', '<') ) {
			$admin = get_role('administrator');
			$admin->add_cap('aoc_view_images');

			$roles = ak_get_roles();
			foreach ( $roles as $name ) {
			    $role = get_role($name);
			    if ( $role->has_cap('akuc_manage_galleries') ) {
			        $role->add_cap('aoc_manage_galleries');
    			    $role->remove_cap('akuc_manage_galleries');
	    	    }
			    if ( $role->has_cap('akuc_view_images') ) {
			        $role->add_cap('aoc_view_images');
    			    $role->remove_cap('akuc_view_images');
	    	    }
			    if ( $role->has_cap('akuc_upload_images') ) {
			        $role->add_cap('aoc_upload_images');
    			    $role->remove_cap('akuc_upload_images');
	    	    }
			}

		}

	}

	/**
	 * Initializes the component by settings gallery filters.
	 *
	 * @return void
	 */
	protected function componentsLoaded ()
	{
		// Add user image to the profile page (Needs 2 parameters).
		// add_filter('aoc_profile_header', array($this, '_profilePicture'), 8, 2);

		// User needs to load one picture to see others pictures (If does not have view images capability).
		if ( ! current_user_can('aoc_view_images') && $this->getOption('needs_load') ) {
			add_filter('aoc_gallery_page', array($this, '_needsLoadImage'));
			add_filter('aoc_profile_image', array($this, '_needsLoadImage'));

			if ( ! $this->hasApprovedImages() && is_admin() ) {
			    $warning = apply_filters('aoc_needs_load_warning', '<p class="alkivia-warning" style="text-align:center;"><strong>'
			             . sprintf(__('To see pictures from other users, you have to load at least one picture to <a href="%s">your gallery</a>.', $this->PID) , get_bloginfo('wpurl') . '/wp-admin/users.php?page='. $this->slug .'-my-gallery')
			             . '</strong></p>');
			    ak_dashboard_notice($warning);
			}
		}
	}

	/**
	 * Inits the plugin widgets.
	 * Takes into consideration the community privacy settings.
	 *
	 * @return void
	 */
	protected function registerWidgets ()
	{
	    $privacy = ak_get_object($this->PID)->getOption('privacy');

		if ( 2 != $privacy || is_user_logged_in() ) {
		    require_once ( dirname(__FILE__) . '/updates.php');
		    register_widget('aocGalleryUpdates');
		}
	}

	/**
	 * Adds the Gellery menus.
	 *
	 * @hook action 'aoc_admin_menu'
	 *
	 * @return void
	 */
	function adminMenus ()
	{
		add_submenu_page( $this->slug, __('Photo Gallery', $this->PID), __('Photo Gallery', $this->PID), 'aoc_manage_settings', $this->slug . '-gallery', array($this, '_gallerySettings'));
		// Add the Gallery Pages on Users menu.
		add_users_page( __('Your Gallery', $this->PID),  __('Your Gallery', $this->PID), 'aoc_upload_images', $this->slug . '-my-gallery', array($this, '_profileGallery'));
	}

	/**
	 * Outputs the user Gallery.
	 *
	 * @hook filter 'aoc_alkivia_page'
	 *
	 * @param $content
	 * @return unknown_type
	 */
	function _galleryContent ( $content)
	{
		$user_name = urldecode(get_query_var('gallery'));

		if ( ! empty($user_name) ) {
			$gallery = $this->userGalleryContent($user_name);
			if ( $gallery ) {
				$content = $gallery;
			} else {
				$content = '<h1 class="alkivia-error">' . __('User with this name not found.', $this->PID) . '</h1>';
			}
		}

		return $content;
	}

	/**
	 * Returns a User Gallery page.
	 *
	 * @uses apply_filters() Calls the 'aoc_gallery_page' hook on the function return value.
	 * @param string $user_login_or_id Login Name or ID for the user.
	 * @return string|false	The formated user gallery or false if the gallery cannot be shown.
	 */
	public function userGalleryContent ( $user_login_or_id )
	{
		global $wpdb;

        if ( empty($user_login_or_id) ) {
            return false;
        }

        if ( is_numeric($user_login_or_id) ) {
            $user_login_or_id = intval($user_login_or_id);
            $user = get_userdata($user_login_or_id);
        } else {
            $user_login_or_id = sanitize_user($user_login_or_id);
		    $user = get_userdatabylogin($user_login_or_id);
        }

		if ( ! $user ) {				// User not found.
			return false;
		}

		// Check if user has one role allowed to display
		if ( ! aoc_can_show_user($user) ) {
			return false;
		}

		// Get the gallery images. If no images, return false.
		$images = $this->userGalleryLinks($user->ID);
		if ( empty($images) ) {
			return false;
		}

		unset($user->user_pass);    // Security reasons.

        // Load and process the page template.
		require_once ( AK_CLASSES . '/template.php');
        $template = new akTemplate( aoc_template_paths() );

        $template->textDomain($this->PID);
        $template->assignByRef('user', $user);
        $template->assign('images', $images);

		if ( is_user_logged_in() ) {
		    $cur_user = wp_get_current_user();
		    if ( $cur_user->user_login == $user->user_login ) {
			    $link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/users.php?page='. $this->slug .'-my-gallery">' . __('Manage your photo gallery', $this->PID) . '</a>'	;
    		} elseif (current_user_can('aoc_manage_galleries') ) {
	    		$link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/users.php?user_id='. $user->ID .'&amp;page='. $this->slug .'-my-gallery">' . __('Manage this user gallery', $this->PID) . '</a>';
		    }
		    $template->assign('edit_link', $link);
		}

		// TODO: If there are to much images, page the gallery.
        $out = $template->getDisplay('gallery-' . $this->getOption('gallery_template'), 'gallery-default');
		return apply_filters('aoc_gallery_page', $out);
	}

	/**
	 * Content filter to add the author thumbnail to posts.
	 *
	 * @param string $content Original post content.
	 * @return string Filtered content.
	 */
    public function _authorThumbnail ( $content )
    {
        global $post;

        if ( 'disabled' == $this->getOption('author_thumbnail') || is_page() || ! is_single() ) {
            return $content;
        }

        $content = aoc_get_user_image($post->post_author, 'align' . $this->getOption('author_thumbnail'), true) . $content;
        return $content;
    }

	/**
	 * Loads settings page for Photo Gallery
	 *
	 * @hook add_submenu_page
	 * @return void
	 */
	function _gallerySettings ()
	{
		if ( ! current_user_can('aoc_manage_settings') ) {		// Verify user permissions.
			wp_die('<strong>' .__('What do you think you\'re doing?!?', $this->PID) . '</strong>');
		}

		global $wp_rewrite;
		$wp_rewrite->flush_rules();	// Force save rules.

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$this->saveAdminSettings();
		}

		require ( dirname(__FILE__) . '/admin.php');
	}

	/**
	 * Saves gallery settings from admin page.
	 * @return void
	 */
	private function saveAdminSettings ()
	{
		check_admin_referer('alkivia-gallery-settings');

		if ( isset($_POST['action']) && 'update' == $_POST['action'] ) {
			$post = stripslashes_deep($_POST['settings']);
			$post = array_merge($this->defaultOptions(), $post);

			$post['megapixels'] = str_replace(',', '.', $post['megapixels']);
			$post['minpixels']  = str_replace(',', '.', $post['minpixels']);
			$settings = array(
			    'descending'	 => intval($post['descending']),
				'local_avatar'   => intval($post['local_avatar']),
				'check_gravatar' => intval($post['check_gravatar']),
				'max_number'     => intval($post['max_number']),
			    'concurrent_up'	 => intval($post['concurrent_up']),
				'max_size'       => intval($post['max_size']),
				'megapixels'     => floatval($post['megapixels']),
			    'minpixels'		 => floatval($post['minpixels']),
				'large'          => intval($post['large']),
				'large_w'        => intval($post['large_w']),
				'large_h'        => intval($post['large_h']),
	        	'thumb'          => intval($post['thumb']),
    	    	'thumb_w'        => intval($post['thumb_w']),
        		'thumb_h'        => intval($post['thumb_h']),
				'thumb_crop'     => intval($post['thumb_crop']),
				'avatar_size'    => intval($post['avatar_size']),
    	    	'watermark'      => intval($post['watermark']),
				'needs_load'     => intval($post['needs_load']),
			    'author_thumbnail' => $post['author_thumbnail'],
			    'gallery_template' => $post['gallery_template']
			);
			$settings['anonymous_url'] = ( empty($post['anonymous_url']) ) ? ak_get_object($this->PID)->getURL() . 'images/anonymous.png' : $post['anonymous_url'];
			$this->setNewOptions($settings);

			// Upload the Watermark file
			require_once ( AK_VENDOR . '/upload/class.upload.php');
			$handle = new akUpload($_FILES['wmark_file'], $this->PID);
			if ( $handle->uploaded ) {
				$handle->image_resize	= true;
				$handle->image_ratio_y	= true;

				$img_w = ( 2 == $this->getOption('large') ) ? $this->getOption('large_w') : get_option('large_size_w');
				$handle->image_x = intval( (int) $img_w / 3 );	// Watermark is 1/3 large image width.

				$handle->file_overwrite		= true;
				$handle->file_auto_rename	= false;
				$handle->file_new_name_body	= 'watermark';
				$handle->image_convert		= 'png';

				$uploads = wp_upload_dir();
				$handle->Process($uploads['basedir'] . '/alkivia');
				if ( ! $handle->processed ) {
					ak_admin_error(__('Error', $this->PID) . ': ' . $handle->error);
				}
			}
			ak_admin_notify();
		} else { // Missing action
			wp_die('Bad form received.', $this->PID);
		}

	}

	/**
	 * Load gallery manager for users.
	 *
	 * @hook add_users_page
	 * @return void
	 */
	function _profileGallery()
	{
		if ( ! current_user_can('aoc_upload_images') && ! current_user_can('aoc_manage_galleries')) {	// Verify user permissions.
			wp_die('<strong>' .__('What do you think you\'re doing?!?', $this->PID) . '</strong>');
		}

		if ( isset($_GET['user_id']) ) {
			if ( current_user_can('aoc_manage_galleries') ) {
				$user_id = intval($_GET['user_id']);
				$user = get_userdata($user_id);

				$action_link = "users.php?user_id={$user_id}&page={$this->slug}-my-gallery";
				$page_title = __('Managing Gallery for', $this->PID) . ' ' . $user->user_login;
				$title_prefix = $user->user_login;
			} else {
				wp_die('<strong>' .__('What do you think you\'re doing?!?', $this->PID) . '</strong>');
			}
		} else {
			global $user_ID;
			$user_id = $user_ID;
			$user = get_userdata($user_id);

			$action_link = "profile.php?page={$this->slug}-my-gallery";
			$page_title = __('Manage your Photo Gallery', $this->PID);
			$title_prefix = __('Your', $this->PID);
		}

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			if ( isset($_POST['action']) && 'upload' == $_POST['action'] ) {
				$this->uploadUserImage($user);
			} elseif ( isset($_POST['action']) && 'update' == $_POST['action'] ) {
				$this->saveUserGallery($user_id);
			} else {
				wp_die('Bad form received.', $this->PID);
			}
		}

		require ( dirname(__FILE__) . '/user.php' );
	}

	/**
	 * Saves the user gallery and process all changes.
	 *
	 * @uses do_action() Calls 'aoc_gallery_first_upload' and 'aoc_gallery_deleted_all' action hooks on user ID.
	 * @uses apply_filters() Calls 'aoc_gallery_approved_mail' and 'aoc_gallery_rejected_mail' filters on mail body.
	 * @param int $usr_id User ID owner of gallery
	 * @return void
	 */
	private function saveUserGallery ( $user_id )
	{
		check_admin_referer('manage-photo-gallery');

        $activity_profile = array(
            'owner_id'      => $user_id,
        	'object_type'   => 'profile',
            'object_action' => 'image',
        	'object_id'	    => $user_id,
            'event_hook'	=> 'aoc_wall_profile'
		);

		$gallery = $this->getUserGallery($user_id);
		$post = stripslashes_deep($_POST);

		$new_main = intval($post['main']);
		if ( $new_main != $gallery['main']) {
		    $gallery['main'] = intval($post['main']);
		    do_action('aoc_generic_event', $activity_profile);
		}
		$gallery['avatar'] = intval($post['avatar']);

		// UPDATE CAPTIONS
		foreach ( $post['caption'] as $imgID => $value ) {
			$gallery['images'][$imgID]['caption'] = wp_specialchars($value);
		}

		// MANAGE APPROVALS
		if ( current_user_can('aoc_manage_galleries') ) {
			$approved_before = $this->countApprovedImages($gallery);

			$hostname = preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
			$mail_headers = 'From: ' . get_bloginfo('name') . ' <' . 'no-reply@' . $hostname . ">\n";

			foreach ( $post['status'] as $imgID => $value ) {
				if ( 1 == $value && 1 != $gallery['images'][$imgID]['approved'] ) { 			// Just aproved now.
					$mail_subject = get_bloginfo('name') . ' ' . __('Your uploaded image has been approved', $this->PID);

					$mail_body = sprintf(__('The recently uploaded image to %s has been approved. From now, you image is online and available to site visitors.', $this->PID), get_bloginfo('name'));
					$mail_body .= "\n" . __('Thanks for uploading your picture for sharing!', $this->PID);
					$mail_body .= "\n\n--\n" . get_bloginfo('name') ."\n". get_bloginfo('url');
					$mail_body = apply_filters('aoc_gallery_approved_mail', $mail_body);

					$user = get_userdata($user_id);
					wp_mail($user->user_email, $mail_subject, $mail_body, $mail_headers);

					update_usermeta($user_id, $this->ID . '_update', gmdate('Y-m-d H:i:s'));

					// Record activity log
					$activity = array(
		    	     	'owner_id'      => $user->ID,
        				'object_type'   => 'gallery',
					    'object_action' => 'upload',
        				'object_id'	    => $user->ID,
					    'event_hook'    => 'aoc_wall_gallery'
		    	    );
		    	    do_action('aoc_generic_event', $activity);

				} elseif ( '2' == $value && 2 != $gallery['images'][$imgID]['approved'] ) {		// Just rejected now
					$mail_subject = get_bloginfo('name') . ' ' . __('Your uploaded image has been rejected', $this->PID);

					$mail_body = sprintf(__('The recently uploaded image to %s has been rejected. Probably this is because the image does not follow the site rules.', $this->PID), get_bloginfo('name'));
					$mail_body .= "\n" . __('Please, try to upload a different new picture.', $this->PID);
					$mail_body .= "\n\n--\n" . get_bloginfo('name') ."\n". get_bloginfo('url');
					$mail_body = apply_filters('aoc_gallery_rejected_mail', $mail_body);

					$user = get_userdata($user_id);
					wp_mail($user->user_email, $mail_subject, $mail_body, $mail_headers);
				}

				$gallery['images'][$imgID]['approved'] = $value;
			}

			$approved = $this->countApprovedImages($gallery);
			if ( 0 == $approved_before && 0 < $approved ) {			// Approved first images.
				do_action('aoc_gallery_first_upload', $user_id);
			} elseif ( 0 < $approved_before && 0 == $approved ) {	// Rejected previously approved images.
				do_action('aoc_gallery_deleted_all', $user_id);
			}

		}

		// DELETE FILES (After updating captions and aprovals to ensure deletion).
		if ( isset($post['delete']) && is_array($post['delete']) ) {
			foreach ( $post['delete'] as $imgID => $value ) {
				$imgID = intval($imgID);

				$uploads	= wp_upload_dir();
				$folder		= trailingslashit($uploads['basedir']) . 'alkivia/users';
				$file_name =  $folder .'/'. $gallery['images'][$imgID]['name'];

				@ unlink($file_name . '.jpg');
				@ unlink($file_name . '_thumb.jpg');
				@ unlink($file_name . '_avatar.jpg');

				unset($gallery['images'][$imgID]);	// Remove filename from user meta.
			}
			if ( 0 == $this->countApprovedImages($gallery) ) {	// Just deleted all images
					do_action('aoc_gallery_deleted_all', $user_id);
			}
		}

		// SET MAIN PICTURE AND AVATAR
		if ( 0 == count($gallery['images']) ) {
			$gallery['main'] = 0;
			$gallery['avatar'] = 0;
		} else {
			$existing = array_keys($gallery['images']);
			$new_key = $existing[count($existing) - 1];					// New pict will be the last uploaded.
			if ( ! isset($gallery['images'][$gallery['main']]) ) {	    // Main pict has been deleted.
				$gallery['main'] = $new_key;
		        do_action('aoc_generic_event', $activity_profile);
			}
			if ( ! isset($gallery['images'][$gallery['avatar']]) ) {	// Avatar pict has been deleted.
				$gallery['avatar'] = $new_key;
			}
		}

		update_usermeta($user_id, $this->ID, $gallery);
		ak_admin_notify();
	}

	/**
	 * Uploads a user image to gallery.
	 *
	 * @uses do_action() Calls 'aoc_gallery_first_upload' action hook on user ID.
	 * @param object $user	User Object to manage.
	 * @return void
	 */
	private function uploadUserImage ( $user )
	{
		check_admin_referer('photo-gallery-upload');

		$gallery = $this->getUserGallery($user->ID);

		$notices = '';
		$errors = '';
		$cur_num = 0;

		foreach ( $_FILES as $user_picture ) {
    		$up_name = trim($user->user_nicename) . ( $gallery['lastID'] + 1);
	    	$handle = new aocGalleryUpload($user_picture, $this->PID);
            ++$cur_num;

		    if ( $handle->uploaded ) {
    			// Upload big size.
		    	if ( $handle->uploadImage($up_name) ) {

		    	    // Success upload.
		    	    if ( ! empty($notices) ) $notices .= '<br />';
		    	    if ( ! empty($errors) ) $errors .= '<br />';

				    $notices .= sprintf(__('File %d uploaded.', $this->PID), $cur_num) . ' ';
    				$generated_name = $handle->file_dst_name_body;

	    			// Create Thumbnail.
		    		if ( $handle->createThumb() ) {
			    		$notices .= sprintf(__('Thumbnail %d created.', $this->PID), $cur_num) . ' ';
				    } else {
    					$errors .= sprintf(__('Thumbnail %d error', $this->PID), $cur_num) . ': ' . $handle->error . ' ';
	    			}

		    		// Create AVATAR
			    	if ( $handle->createAvatar() ) {
				    	$notices .= sprintf(__('Avatar %d created.', $this->PID), $cur_num) . ' ';
    				} else {
		    			$errors .= sprintf(__('Avatar %d error', $this->PID), $cur_num) . ': ' . $handle->error . ' ';
			    	}

    				// Save the image name to user meta.
	    			++$gallery['lastID'];
		    		if ( 0 == count($gallery['images']) ) {		// Loading first file
			    		$gallery['main'] = $gallery['lastID'];
				    	$gallery['avatar'] = $gallery['lastID'];
    				}

	    			$approved = ( current_user_can('aoc_unmoderated') || current_user_can('aoc_manage_galleries') ) ? 1 : 0;
		    		$gallery['images'][$gallery['lastID']] = array('approved' => $approved, 'caption' => '', 'name' => $generated_name);
			    	update_usermeta($user->ID, $this->ID, $gallery);

    				if ( $approved ) {
	    				// Set the last update time. Since 0.5.3
		    			if ( 1 == $this->countApprovedImages($gallery) ) {	// Just uploaded first picture
			    			do_action('aoc_gallery_first_upload', $user->ID);
				    	}
					    update_usermeta($user->ID, $this->ID . '_update', gmdate('Y-m-d H:i:s'));

					    // Record activity log
					    $activity = array(
		    	        	'owner_id'      => $user->ID,
        					'object_type'   => 'gallery',
					        'object_action' => 'upload',
        					'object_id'	    => $user->ID,
					        'event_hook'	=> 'aoc_wall_gallery'
		    	        );
		    	        do_action('aoc_generic_event', $activity);

    				} else {
	    				$hostname = preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
		    			$mail_headers = 'From: ' . get_bloginfo('name')
			    			. ' <' . 'wordpress@' . $hostname . ">\n";
				    	$mail_subject = get_bloginfo('name') . ' ' . __('Please moderate user gallery', $this->PID);

					    $mail_body = sprintf(__('A new image has been uploaded to the gallery for %s', $this->PID), $user->user_login) ."\n\n";
    					$mail_body .= __('Please, moderate it at', $this->PID) . ' ' . get_bloginfo('wpurl') ."/wp-admin/users.php?user_id={$user->ID}&page={$this->slug}-my-gallery";

	    				wp_mail(get_bloginfo('admin_email'), $mail_subject, $mail_body, $mail_headers);
		    		}
			    } else {
    				$errors .= sprintf(__('Image %d error', $this->PID), $cur_num) . ': ' . $handle->error . ' ';
	    		}
		    }
		}

		if ( ! empty($notices) ) {
			ak_admin_notify($notices);
		}
		if ( ! empty($errors) ) {
			ak_admin_error($errors);
		}
	}

	/**
	 * Counts aproved images on a user gallery.
	 *
	 * @param array $gallery	The user gallery. Is the user_metadata 'akucom_gallery'
	 * @return int				Number of approved images.
	 */
	private function countApprovedImages ( $gallery )
	{
		$counter = 0;
		if ( ! empty($gallery) && 0 < count($gallery['images']) ) {
			foreach ( $gallery['images'] as $img ) {
				if ( 1 == $img['approved'] ) ++$counter;
			}
		}

		return $counter;
	}

	/**
	 * Shows a link to gallery admin in edit user page.
	 * Only if user has rights.
	 *
	 * @hook action 'edit_user_profile'
	 * @return void
	 */
	function _profileAdminLink ()
	{
		$uid = intval($_GET['user_id']);

		if ( current_user_can('edit_user', $uid) && current_user_can('aoc_manage_galleries') ) {
			echo '<table class="form-table">';
			echo '<tr><th scope="row">&nbsp;';
			echo '</strong></th><td>';
			echo '<strong><a href="users.php?user_id=' . $uid . '&amp;page='. $this->slug . '-my-gallery">';
			_e('Edit Photo Gallery', $this->PID);
			echo '</a></strong>';
			echo '</td></tr></table>';
		}
	}

	/**
	 * Deletes all user image files before deleteing the user.
	 *
	 * @hook action 'delete_user'
	 * @param int $user_id		User id to be deleted
	 * @return void
	 */
	function _deleteUserImages ( $user_id )
	{
		$gallery = $this->getUserGallery($user_id);

		$upload  = wp_upload_dir();
		$dir = trailingslashit($upload['basedir']) . 'alkivia/users/';

		if ( ! empty($gallery['images']) ) {
			foreach ( $gallery['images'] as $image ) {
				$filename = $dir . $image;

				@ unlink($filename . '.jpg');
				@ unlink($filename . '_thumb.jpg');
				@ unlink($filename . '_avatar.jpg');
			}
		}
	}

	/**
	 * Sends the user image and gallery link to the user gallery page.
	 *
	 * @uses apply_filters() Calls the 'aoc_profile_image' hook on the user picture and link.
	 * @param object $user The user object to get the pictures from.
	 * @param boolean $thumbnail Set to true if we want to get the thumbnail instead the large image.
	 * @return string New header with the user image and gallery link.
	 */
	public function getProfilePicture ( $user, $thumbnail = false )
	{
		$out = $this->getUserImage($user->ID, 'aligncenter', $thumbnail);

		$num = $this->countApprovedImages($user->{$this->ID});
		if ( 1 < $num ) {
			$out .= '<p style="text-align:center;"><strong><a href="'. aoc_create_link('gallery') . urlencode($user->user_login) .'">'
			 . sprintf(__('More user pictures [%d]', $this->PID), $num) .'</a></strong></p>';
		}

		$out = apply_filters('aoc_profile_image', $out);
		return $out;
	}

	/**
	 * Filter to replace user images with a warning.
	 * This warning is set when a user needs to load a picture to see other users images.
	 * Users who can manage others galleries, can always see the other users images.
	 *
	 * @hook filter 'aoc_profile_image' and filter 'aoc_gallery_page'
	 * @param $content	The img tag or the gallery page content.
	 * @return string	Replaced content with a Warning if user has not loaded any image.
	 */
	function _needsLoadImage ( $content )
	{
		if ( ! $this->getOption('needs_load') || current_user_can('aoc_manage_galleries') ) {
			return $content;
		}

		$warning = apply_filters('aoc_needs_load_warning', '<p class="alkivia-warning" style="text-align:center;"><strong>'
			. sprintf(__('To see pictures from other users, you have to load at least one picture to <a href="%s">your gallery</a>.', $this->PID) , get_bloginfo('wpurl') . '/wp-admin/users.php?page='. $this->slug .'-my-gallery')
			. '</strong></p>');

		if ( ! is_user_logged_in() ) {
			return $warning;
		}

        $approved = $this->hasApprovedImages();
		return ( $approved ) ? $content : $warning;
	}

	/**
	 * Checks if current user has approved images.
	 *
	 * @return boolean Returns true if user has approved images, false if not.
	 */
	private function hasApprovedImages ()
	{
		global $user_ID;

		$gallery = $this->getUserGallery($user_ID);
		$approved = false;

		if ( ! empty($gallery) && 0 < count($gallery['images']) ) {
			foreach ( $gallery['images'] as $key => $img ) {
				if ( 1 == $img['approved'] ) {
					$approved = true;
					break;
				}
			}
		}

		return $approved;
	}

	/**
	 * Creates the needed rewrite rules for user gallery.
	 *
	 * @hook filter 'generate_rewrite_rules'
	 * @param object $wp_rewrite	Current rewrite rules. Received by ref.
	 * @return void
	 */
	function _rewriteRules ( &$wp_rewrite )
	{
		$pid = ak_get_object($this->PID)->getOption('page_id');
		$slug = basename(get_page_uri($pid));

		$rules = array ( $slug . '/gallery/(.+)/?$' => 'index.php?page_id='. $pid .'&gallery='. $wp_rewrite->preg_index(1),
						'(.+)/' . $slug . '/gallery/(.+)/?$' => 'index.php?page_id='. $pid .'&gallery=' . $wp_rewrite->preg_index(2) );
		$wp_rewrite->rules = $rules + $wp_rewrite->rules;
	}

	/**
	 * Creates the query var to get a user gallery page.
	 *
	 * @hook filter 'query_vars'
	 * @param array $vars	Current WordPress query vars.
	 * @return array		New Query vars.
	 */
	function _rewriteVars ( $vars )
	{
		$vars[] = 'gallery';
		return $vars;
	}

	/**
	 * Loads and returns the user gallery.
	 * If it is an old gallery format, it updates it to the current array format.
	 * Starting at 0.6, each image is an array with 'name', 'caption' and 'approved'
	 *
	 * @since 0.6
	 * @param int $user_id User id to get the gallery from.
	 * @return array New gallery format array.
	 */
	private function getUserGallery ( $user_id )
	{
		$gallery = get_usermeta($user_id, $this->ID);
		$update = false;

		if ( is_array($gallery) && is_array($gallery['images']) ) {
			foreach ( $gallery['images'] as $key => $image ) {
				if ( ! is_array($gallery['images'][$key]) ) {
					$update = true;
					$gallery['images'][$key] = array('name' => $image, 'caption' => '', 'approved' => 1);
				}
			}
		} elseif ( ! is_array($gallery) ) {
			$update = true;
			$gallery = array(
				'lastID'	=> 0,
				'main'		=> 0,
				'avatar'	=> 0,
				'images'	=> array()
			);
		}

		if ( $update ) {
			update_usermeta($user_id, $this->ID, $gallery);
		}
		return $gallery;
	}

	/**
	 * Checks if we are editing the WordPress User profile or gallery.
	 * This is the only page were we need to load ThickBox.
	 *
	 * @return boolean	We are on options-discussion.php page.
	 */
	private function isUserAdmin ()
	{
		if (! is_admin() ) {
			return false;
		}

		$file = basename($_SERVER['SCRIPT_FILENAME']);

		if (isset($_SERVER['SCRIPT_FILENAME']) && ( 'users.php' == $file || 'profile.php' == $file ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Retrieves the local avatar url.
	 *
	 * @param int $user_ïd		User Nice Name.
	 * @return string|false		Full avatar url. If no local avatar, returns false.
	 */
	private function avatarURL ( $email )
	{
		if ( empty($email) ) {
			return false;
		} else {
			$user = get_user_by_email($email);
			if ( $user ) {
				$user_id = $user->ID;
			} else {
				return false;
			}
		}

		$avatar_url = false;
		$gallery = $this->getUserGallery($user_id);
		if ( is_array($gallery) ) {
			$key = $gallery['avatar'];

			if ( isset($gallery['images'][$key]) && 1 == $gallery['images'][$key]['approved'] ) {
				$upload	= wp_upload_dir();
				$dir	= trailingslashit($upload['basedir']);
				$uri	= trailingslashit($upload['baseurl']);
				$file	= "alkivia/users/{$gallery['images'][$key]['name']}_avatar.jpg";

				if ( file_exists($dir . $file) ) {
					$avatar_url = $uri . $file;
				}
			}
		}
		return $avatar_url;
	}

	/**
	 * Gets the user avatar from Gravatar. Only used in case there is no local avatar.
	 *
	 * @param string $email		User email.
	 * @param int $size			Avatar Size.
	 * @return string			<img> tag to the image.
	 */
	private function checkGravatar ( $email = '', $size = 80, $default )
	{
		$host = ( is_ssl() ) ? 'https://secure.gravatar.com' : 'http://www.gravatar.com';

		if ( ! empty($email) ) {
			$out = "$host/avatar/";
			$out .= md5( strtolower( $email ) );
			$out .= '?s='.$size;
			$out .= '&amp;d=' . urlencode( $default );

			$rating = get_option('avatar_rating');
			if ( ! empty( $rating ) ) {
				$out .= "&amp;r={$rating}";
			}

			$avatar = "<img src='{$out}' class='avatar avatar-{$size} photo' alt='avatar' height='{$size}' width='{$size}' />";
		} else {
			$avatar = "<img src='{$default}' class='avatar avatar-{$size} photo avatar-default' alt='avatar' height='{$size}' width='{$size}' />";
		}

		return $avatar;
	}

	/**
	 * Checks if local avatars will be used.
	 * At the same time, sets the filters for avatar settings on discussion page.
	 *
	 * @return boolean	If will use local avatars or not.
	 */
	public function localAvatars ()
	{
		$replace = false;

		if ( ! function_exists('get_avatar') && $this->getOption('local_avatar') ) {
			$replace = true;
			if ( is_admin() && isset($_SERVER['SCRIPT_FILENAME']) && 'options-discussion.php' == basename($_SERVER['SCRIPT_FILENAME'])) {
				$function = 'return array(\'mystery\' => "' .
							__('Local Anonymous <em>(Defined in Alkivia Gallery settings)</em>', $this->PID) .'");';
				add_filter('avatar_defaults', create_function('', $function));
			}
		}

		return $replace;
	}

	/**
	 * Returns the max upload size in bytes.
	 * Compares system to settings.
	 *
	 * @return int	Maximum upload size in bytes.
	 */
	public function maxUpload ()
	{
		$max = 1024 * (int) $this->getOption('max_size');
		return $max;
	}

	/**
	 * Retrieve the user avatar from the user gallery. Provided user ID or email address.
	 * This is a hack from the get_avatar() function from WordPress 2.7
	 *
	 * @uses apply_filters() Calls the 'get_avatar' hook on the avatar img tag.
	 * @param int|string|object $id_or_email A user ID,  email address, or comment object
	 * @param int $size Size of the avatar image. Default is 80.
	 * @param string $default URL to default image to use if no avatar is found.
	 * @return string <img> tag for the user's avatar
	 */
	public function getAvatar ( $id_or_email, $size = 80, $default = '' )
	{
		if ( ! get_option('show_avatars') ) {
			return false;
		}

		if ( ! is_numeric($size) ) {
			$size = 80;
		}

		$email = '';
		if ( is_numeric($id_or_email) ) {
			$id = (int) $id_or_email;
			$user = get_userdata($id);
			if ( $user )
				$email = $user->user_email;
		} elseif ( is_object($id_or_email) ) {
			if ( isset($id_or_email->comment_type) && '' != $id_or_email->comment_type && 'comment' != $id_or_email->comment_type )
				return false; // No avatar for pingbacks or trackbacks

			if ( ! empty($id_or_email->user_id) ) {
				$id = (int) $id_or_email->user_id;
				$user = get_userdata($id);
				if ( $user)
					$email = $user->user_email;
			} elseif ( ! empty($id_or_email->comment_author_email) ) {
				$email = $id_or_email->comment_author_email;
			}
		} else {
			$email = $id_or_email;
		}

		if ( empty($default) ) {
			$default = $this->getOption('anonymous_url');
		}

		$out = $this->avatarURL($email);
		if ( false !== $out ) {
			$avatar = "<img src='{$out}' class='avatar avatar-{$size} photo' alt='avatar' height='{$size}' width='{$size}' />";
		} elseif ( $this->getOption('check_gravatar') ) {
			$avatar = $this->checkGravatar( $email, $size, $default );
		} else {	// Anonymous Avatar
			$avatar = "<img src='{$default}' class='avatar avatar-{$size} photo' alt='avatar' height='{$size}' width='{$size}' />";
		}

		return apply_filters('get_avatar', $avatar, $id_or_email, $size, '', '');
	}

	/**
	 * Returns image sizes for an image type.
	 * Type can be 'large', 'thumb' or 'avatar'
	 *
	 * @param $type Type of image (large, thumb or avatar)
	 * @param $file Image file. If set to a valid image, sizes will be proportional for this image.
	 * @return array|int Image sizes.
	 * 						- An int for avatars (as they are square).
	 * 						- Array for others containing: width, height and, for thumbnails, crop.
	 */
    public function getImageSize ( $type = 'large', $file = '' )
    {
        $size = array();

        switch ( $type ) {
            case 'large':
                if ( 2 == $this->getOption('large') ) {	// Custom Settings
			        $size['width']   = (int) $this->getOption('large_w');
			        $size['height']  = (int) $this->getOption('large_h');
		        } else {							// WordPress Settings
			        $size['width']    = (int) get_option('large_size_w');
			        $size['height']  = (int) get_option('large_size_h');
		        }
		        $size['crop'] = false; //We never crop large images.
		        break;
            case 'thumb' :
                if ( 2 == $this->getOption('thumb') ) {	// Custom Settings
			        $size['width']   = (int) $this->getOption('thumb_w');
			        $size['height']  = (int) $this->getOption('thumb_h');
			        $size['crop']    = ( $this->getOption('thumb_crop') ) ? true : false;
		        } else {							// WordPress Settings
			        $size['width']	  = (int) get_option('thumbnail_size_w');
			        $size['height']  = (int) get_option('thumbnail_size_h');
			        $size['crop']    = ( 1 == get_option('thumbnail_crop') ) ? true : false;
		        }
		        break;
            case 'avatar' :
                $size = (int) $this->getOption('avatar_size');
                break;
        }

        if ( ! empty($file) && is_array($size) &&  file_exists($file) && ! $size['crop'] ) {
			$info = getimagesize($file);       // Now we need to set proportional width or height.
			if ( $info[1] > $info[0] ) {       // Portrait image.
			    $size['width']  = intval($size['height'] * $info[0] / $info[1]);
			} else {
			    $size['height'] = intval($size['width'] * $info[1] / $info[0]);
			}
        }

        return $size;
    }

	/**
	 * Retrieves img tag with the link to the default user image.
	 *
	 * @param int $user_id User ID
	 * @param string $class CSS class name.
	 * @param boolean $thumbnail	If set to true will return the user thumbnail, else returns the profile image.
	 * @return string			<img> tag for the image.
	 */
	public function getUserImage ( $user_id, $class = '', $thumbnail = false )
	{
		$gallery = $this->getUserGallery($user_id);
		if ( empty($gallery) ) {
			return ( $thumbnail ) ? $this->anonymousThumbnail($class) : '';
		}

		$image = '';
		$key = $gallery['main'];
		$a_class = ( empty($class) ) ? '' : ' class="'. $class .'"';

		if ( isset($gallery['images'][$key]) && 1 == $gallery['images'][$key]['approved'] ) {
			$upload	= wp_upload_dir();
			$dir	= trailingslashit($upload['basedir']);
			$uri	= trailingslashit($upload['baseurl']);
			$title = $gallery['images'][$key]['caption'];

			$file = "alkivia/users/{$gallery['images'][$key]['name']}";
			$file .= ( $thumbnail ) ? '_thumb.jpg' : '.jpg';
			if ( file_exists($dir . $file) ) {
			    $type = ( $thumbnail ) ? 'thumb' : 'large';
				$size = $this->getImageSize($type, $dir.$file);

				$image = sprintf('<img src="%s" width="%d" height="%d" title="%s" alt="%s" %s />',
                         $uri.$file, $size['width'], $size['height'], $title, $title, $a_class);
			}
		}

		if ( empty($image) && $thumbnail ) {
		    $image = $this->anonymousThumbnail($class);
		}
		return $image;
	}

	/**
	 * Gets the img tag for the anonymous thumbnail.
	 *
	 * @param string $class CSS class name.
	 * @return string			<img> tag for the image.
	 */
	private function anonymousThumbnail ( $class = '' )
	{
	    $a_class = ( empty($class) ) ? '' : ' class="'. $class .'"';
	    $file = AOC_PATH . '/images/anonymous.thumb.png';
		$size = $this->getImageSize('thumb', $file);

	    $url = ak_get_object($this->PID)->getURL() . '/images/anonymous.thumb.png';
		$image = sprintf('<img src="%s" width="%d" height="%d" title="%s" alt="%s" %s />',
                 $url, $size['width'], $size['height'], 'Anonymous', 'Anonymous', $a_class);

        return $image;
	}

	/**
	 * Returns an array with all gallery links.
	 * This provides two data for every image in the gallery: large image url and thumb <img> tag.
	 *
	 * @param int $user_id	The user id we want the gallery
	 * @param string $class	Class for the <img> tag.
	 * @return array		Array cointaining links to large image and <img> tags for thumbnails.
	 */
	public function userGalleryLinks ( $user_id, $class = 'user-thumbnail' )
	{
        $s = $this->getImageSize('thumb');
        $size = 'width="' . '" height="' . '"';


		$upload	= wp_upload_dir();
		$dir	= trailingslashit($upload['basedir']) . 'alkivia/users/';
		$uri	= trailingslashit($upload['baseurl']) . 'alkivia/users/';

		$gallery = $this->getUserGallery($user_id);
		if ( empty($gallery) || 0 == count($gallery['images']) ) {
			return array();
		}

		$images = array();
		foreach ( $gallery['images'] as $key => $img ) {
			if ( 1 == $img['approved'] ) {
				$images[$key]['link']	= $uri . $img['name'] . '.jpg';
				$size = $this->getImageSize('thumb', $dir . $img['name'] . '_thumb.jpg');
				$images[$key]['img']	= '<img src="'. $this->getOption('baseurl') . '/' . $img['name']
				                        . '_thumb.jpg" width="' . $size['width'] . '" height="' . $size['height']
				                        . '" alt="'. $img['caption'] .'" class="' . $class . '" />';
				$images[$key]['caption'] = $img['caption'];
			}
		}

		if ( $this->getOption('descending') ) {
		    krsort($images);
		}

		return $images;
	}
}
