<?php
/**
 * AOC Gallery Component.
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

require_once ( AK_VENDOR . '/upload/class.upload.php' );

/**
 * Class for uploading User Gallery Images.
 * Creates Image, Avatar and Thumbnail.
 *
 * @author	Jordi Canals
 * @link	http://alkivia.org
 * @package AOC
 * @subpackage Gallery
 */
class aocGalleryUpload extends akUpload
{
	/**
	 * Gallery settings readed from database.
	 * @var array
	 */
	private $settings;

	/**
	 * Destination folder for images.
	 * @var string
	 */
	private $upload_folder = '';

	/**
	 * Large image width. (pixels)
	 * @var int
	 */
	private $img_width;
	/**
	 * Large Image height. (pixels)
	 * @var int
	 */
	private $img_height;

	/**
	 * Thumbnail width. (pixels)
	 * @var int
	 */
	private $thumb_width;
	/**
	 * Thumbnail height. (pixels)
	 * @var int
	 */
	private $thumb_height;
	/**
	 * If thumbnail has to be cropt to the exact size.
	 * @var boolean
	 */
	private $thumb_crop;

	/**
	 * Avatar Size. Avatars are always square. (pixels)
	 * @var int
	 */
	private $avatar_size;

	/**
	 * Destination filename base.
	 * The image identifier, the class will append '.jpg', '_thumb.jpg' or '_avatar.jpg' as needed.
	 * @var string
	 */
	private $basename;

	/**
	 * Class constructor.
	 * Prepares basic configuarction.
	 *
	 * @param string $file	Name of the recently uploaded file.
	 * @param string $textDomain Translation textDomain.
	 * @return void
	 */
	function __construct ( $file, $textDomain )
	{
    	ini_set('max_execution_time', 0);	// Sometimes we need more processing time.

    	$this->loadSizes();
    	$this->setFolder();

		$this->akUpload($file, $textDomain);	// Call parent constructor.
	}

	/**
	 * Load images dimensions from WordPress and Plugin options.
	 *
	 * @return void
	 */
	private function loadSizes ()
	{
		$settings = ak_get_object('akucom_gallery')->getOption();
		$this->settings = $settings;

		if ( 2 == $settings['large'] ) {	// Custom Settings
			$this->img_width	= (int) $settings['large_w'];
			$this->img_height	= (int) $settings['large_h'];
		} else {							// WordPress Settings
			$this->img_width	= (int) get_option('large_size_w');
			$this->img_height	= (int) get_option('large_size_h');
		}

		if ( 2 == $settings['thumb'] ) {	// Custom Settings
			$this->thumb_width	= (int) $settings['thumb_w'];
			$this->thumb_height = (int) $settings['thumb_h'];
			$this->thumb_crop	= ( $settings['thumb_crop'] ) ? true : false;
		} else {							// WordPress Settings
			$this->thumb_width	= (int) get_option('thumbnail_size_w');
			$this->thumb_height = (int) get_option('thumbnail_size_h');
			$this->thumb_crop	= ( 1 == get_option('thumbnail_crop') ) ? true : false;
		}
	}

	/**
	 * Load images dimensions from WordPress and Plugin options.
	 *
	 * @return void
	 */
	private function prepareSettings ()
	{
    	$this->dir_chmod = 0705;	// Default permissions

    	$this->avatar_size		= (int) $this->settings['avatar_size'];
		$this->image_max_pixels	= intval($this->settings['megapixels'] * 1000000); // Convert Megapixels to pixels.
		$this->image_min_pixels = intval($this->settings['minpixels'] * 1000000);  // Convert Megapizels to pizels.
		$this->file_max_size	= aoc_gallery_max_upload();

		$this->image_resize		= true;
		$this->file_overwrite	= true;
		$this->file_auto_rename	= false;

		$this->allowed			= array('image/*');
		$this->image_convert	= 'jpg';

		$this->image_watermark	= null;
	}

	/**
	 * Sets the same rights to the uploaded file.
	 * Same as parent folder, but striping the executable bits.
	 *
	 * @param string $file	Filename (only the basename with no path)
	 * @return void
	 */
	private function setRights ( $file = '' )
	{
		$stat	= @ stat($this->upload_folder);
		$perms	= $stat['mode'] & 0007777;
		$perms	= $perms & 0000666;

		$filename = $this->upload_folder . '/' . $file .'.jpg';
		@ chmod($filename, $perms);
	}

	/**
	 * Sets the folder where to move the files.
	 * If no folder name given, will use defaults.
	 *
	 * @param string $folder	Folder name relative to uploads folder.
	 * @return void
	 */
	public function setFolder ( $folder = '')
	{
		if ( empty($this->upload_folder) || ! empty($folder) )
		{
			$uploads	= wp_upload_dir();
			$dir 		= trailingslashit($uploads['basedir']);

			$this->upload_folder = ( empty($folder) ) ? $dir . 'alkivia/users' : $dir . $folder;
		}
	}

	/**
	 * Sets the image base name.
	 *
	 * @param string $name	image name
	 * @return void
	 */
	public function setBaseName ( $name )
	{
		$this->basename = trim($name);
	}

	/**
	 * Processes de original file at large size.
	 *
	 * @param string $name	Name for the created file. (No extension). jpg extension will be added.
	 * @return boolean		If the file was processed successfully.
	 */
	public function uploadImage ( $name = '' )
	{
		if ( !empty ($name) ) {
			$this->setBaseName($name);
		}
		$this->prepareSettings();

		$this->image_ratio		= true;
		$this->image_x			= $this->img_width;
		$this->image_y			= $this->img_height;
		$this->jpeg_quality		= 85;

		if ( $this->settings['watermark'] ) {
			$dirs = wp_upload_dir();

			$this->image_watermark  = $dirs['basedir'] . '/alkivia/watermark.png'; // $this->watermark_file;
			$this->image_watermark_x = -10;
			$this->image_watermark_y = -10;
		}

		$this->file_new_name_body	= $this->basename;
		$this->process($this->upload_folder);
		$this->setRights($this->basename);

		return $this->processed;
	}

	/**
	 * Create the thumbnail for the uploaded file.
	 *
	 * @param string $name	Name for the file. (No extension). _thumb.jpg will be added.
	 * @return boolean		If the file was processed successfully.
	 */
	public function createThumb ( $name = '' )
	{
		if ( !empty ($name) ) {
			$this->setBaseName($name);
		}
		$this->prepareSettings();

		$this->image_ratio		= true;
		$this->image_ratio_crop	= $this->thumb_crop;
		$this->image_x			= $this->thumb_width;
		$this->image_y			= $this->thumb_height;
		$this->jpeg_quality		= 75;

		$this->file_new_name_body	= $this->basename . '_thumb';
		$this->process($this->upload_folder);
		$this->setRights($this->basename . '_thumb');

		return $this->processed;
	}

	/**
	 * Creates the user Avatar.
	 *
	 * @param string $name. Name for the file. (No extension). _avatar.jpg will be added.
	 * @return boolean		If the file was processed successfully.
	 */
	public function createAvatar ( $name = '' )
	{
		if ( !empty ($name) ) {
			$this->setBaseName($name);
		}
		$this->prepareSettings();

		$this->image_ratio		= false;
		$this->image_ratio_crop	= true;
		$this->image_x			= $this->avatar_size;
		$this->image_y			= $this->avatar_size;
		$this->jpeg_quality		= 75;

		$this->file_new_name_body = $this->basename . '_avatar';
		$this->process($this->upload_folder);
		$this->setRights($this->basename . '_avatar');

		return $this->processed;
	}
}
