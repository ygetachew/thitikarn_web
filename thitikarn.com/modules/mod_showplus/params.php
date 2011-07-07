<?php
/**
* @file
* @brief    showplus slideshow module for Joomla
* @author   Levente Hunyadi
* @version  1.0.0
* @remarks  Copyright (C) 2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/showplus
*/

/*
* showplus slideshow module for Joomla
* Copyright 2009-2010 Levente Hunyadi
*
* showplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* showplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with showplus.  If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once dirname(__FILE__).DS.'librarian.php';

// sort order for file system functions
define('SHOWPLUS_SORT_ASCENDING', 0);
define('SHOWPLUS_SORT_DESCENDING', 1);

// sort criterion override modes
define('SHOWPLUS_SORT_LABELS_OR_FILENAME', 0);  // sort based on labels file with fallback to file name
define('SHOWPLUS_SORT_LABELS_OR_MTIME', 1);     // sort based on labels file with fallback to last modified time
define('SHOWPLUS_SORT_FILENAME', 2);            // sort based on file name ignoring order in labels file
define('SHOWPLUS_SORT_MTIME', 3);               // sort based on last modified time ignoring order in labels file
define('SHOWPLUS_SORT_RANDOM', 4);              // random order
define('SHOWPLUS_SORT_RANDOMLABELS', 5);        // random order restricting images to those listed in labels file

/**
* Parameter values for images galleries.
* Global values are defined in the administration back-end, which are overridden in-place with local parameter values.
*/
class ShowPlusParameters {
	/** Folder w.r.t. Joomla root the slideshow draws images from. */
	public $folder = 'images';
	/** Unique identifier to use for the slideshow. */
	public $id = false;
	/** Width of slideshow [px]. */
	public $width = 600;
	/** Height of slideshow [px]. */
	public $height = 400;
	/** Width of thumbnail images [px]. */
	public $thumb_width = 60;
	/** Height of thumbnail images [px]. */
	public $thumb_height = 40;

	/** Alignment of image slideshow on page. */
	public $alignment = 'before';
	/** Orientation of image slideshow thumbnails used for fast navigation, or false to disable thumbnail navigation bar. */
	public $orientation = 'disabled';
	/** Show navigation control buttons overlaying slideshow. */
	public $buttons = true;
	/** Show captions overlaying slideshow. */
	public $captions = true;
	/** Default text to assign to images that have no explicit caption set. */
	public $defcaption = false;
	/** Default hyperlink to assign to images as target. */
	public $deflink = false;

	/** Time each image is shown before a transition effect morphs one image into another [ms]. */
	public $delay = 2000;
	/** Time taken for a transition effect to morph one image into another [ms]. */
	public $duration = 800;
	/** Transition effect. */
	public $transition = 'fade';
	/** Transition easing function. */
	public $transition_easing = 'linear';
	/** Pan factor. */
	public $transition_pan = 100;
	/** Zoom factor. */
	public $transition_zoom = 50;

	/** Margin [px], or false for default (inherit from slideshow.css). */
	public $margin = false;
	/** Border width [px], or false for default (inherit from slideshow.css). */
	public $border_width = false;
	/** Border style, or false for default (inherit from slideshow.css). */
	public $border_style = false;
	/** Border color as a hexadecimal value in between 000000 or ffffff inclusive, or false for default. */
	public $border_color = false;
	/** Padding [px], or false for default (inherit from slideshow.css). */
	public $padding = false;

	/** Whether to use Joomla cache for storing thumbnails. */
	public $thumb_cache = true;
	/** Folder to store image thumbnails. */
	public $thumb_folder = 'showplus';
	/** Color around thumbnail when being shown in slideshow. */
	public $thumb_color_active = false;
	/** Color around thumbnail when mouse pointer is over the image. */
	public $thumb_color_hover = false;
	/** JPEG quality. */
	public $thumb_quality = 85;

	/** Labels file name. */
	public $labels = 'labels';
	/** Whether to use multilingual labeling. */
	public $labels_multilingual = false;
	/** Whether a labels file is updated when new images are added to the image folder. */
	public $labels_update = true;

	/** Sort criterion. */
	public $sort_criterion = SHOWPLUS_SORT_LABELS_OR_FILENAME;
	/** Sort order, ascending or descending. */
	public $sort_order = SHOWPLUS_SORT_ASCENDING;

	/** Image processing library to use. */
	public $library = 'default';
	/** Whether to use minified CSS and javascript files. */
	public $debug = false;

	/** Casts a value to a nonnegative integer. */
	private static function as_nonnegative_integer($value, $default = 0) {
		if (is_null($value) || $value === '') {
			return false;
		} elseif ($value !== false) {
			$value = (int) $value;
			if ($value <= 0) {
				$value = $default;
			}
		}
		return $value;
	}

	private static function as_positive_integer($value, $default) {
		if (is_null($value) || $value === false || $value === '') {
			return $default;
		} else {
			$value = (int) $value;
			if ($value < 0) {
				$value = $default;
			}
			return $value;
		}
	}
	
	private static function as_percentage($value) {
		$value = (int) $value;
		if ($value < 0) {
			$value = 0;
		}
		if ($value > 100) {
			$value = 100;
		}
		return $value;
	}
	
	private static function as_color($value) {
		if (is_null($value) || $value === '' || $value !== false && !preg_match('/^[0-9A-Za-z]{6}$/', $value)) {
			return false;
		} else {
			return $value;
		}
	}

	private function validate() {
		$this->folder = str_replace("\\", '/', trim($this->folder, " /\\\n\r\t"));

		// dimensions
		$this->width = self::as_positive_integer($this->width, 600);
		$this->height = self::as_positive_integer($this->height, 400);
		$this->thumb_width = self::as_positive_integer($this->thumb_width, 60);
		$this->thumb_height = self::as_positive_integer($this->thumb_height, 40);

		// slideshow alignment and thumbnail bar orientation
		$language = JFactory::getLanguage();
		switch ($this->alignment) {
			case 'left': case 'left-clear': case 'left-float':
			case 'right': case 'right-clear': case 'right-float':
				str_replace(array('left','right'), $language->isRTL() ? array('after','before') : array('before','after'), $this->alignment); break;
			case 'before': case 'center': case 'right':
			case 'before-clear': case 'after-clear':
			case 'before-float': case 'after-float':
				break;
			default:
				$this->alignment = 'center';
		}
		switch ($this->orientation) {
			case 'horizontal': case 'vertical': break;
			case 'disabled': default: $this->orientation = false;
		}

		// overlay buttons and captions
		$this->buttons = (bool) $this->buttons;
		$this->captions = (bool) $this->captions;

		// delay times [ms]
		$this->delay = self::as_nonnegative_integer($this->delay);
		$this->duration = self::as_nonnegative_integer($this->duration);
		switch ($this->transition) {
			case 'fade': case 'flash': case 'fold': case 'kenburns': case 'push': break;
			default: $this->transition = 'fade';
		}
		switch ($this->transition_easing) {
			case 'linear': case 'quad': case 'cubic': case 'quart': case 'quint':
			case 'expo': case 'circ': case 'sine': case 'back': case 'bounce': case 'elastic': break;
			default: $this->transition_easing = 'linear';
		}
		$this->transition_pan = self::as_percentage($this->transition_pan);
		$this->transition_zoom = self::as_percentage($this->transition_zoom);

		// style
		$this->margin = self::as_nonnegative_integer($this->margin);
		$this->border_width = self::as_nonnegative_integer($this->border_width);
		switch ($this->border_style) {
			case 'none': case 'dotted': case 'dashed': case 'solid': case 'double': case 'groove': case 'ridge': case 'inset': case 'outset': break;
			default: $this->border_style = false;
		}
		$this->border_color = self::as_color($this->border_color);
		$this->padding = self::as_nonnegative_integer($this->padding);

		// thumbnail image generation
		$this->thumb_cache = (bool) $this->thumb_cache;
		$this->thumb_color_active = self::as_color($this->thumb_color_active);
		$this->thumb_color_hover = self::as_color($this->thumb_color_hover);
		$this->thumb_quality = self::as_percentage($this->thumb_quality);

		// image labels
		$this->labels = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace('.', '_', $this->labels));
		$this->labels_multilingual = (bool) $this->labels_multilingual;
		$this->labels_update = (bool) $this->labels_update;

		// sort criterion and sort order
		if (is_numeric($this->sort_criterion)) {
			$this->sort_criterion = (int) $this->sort_criterion;
		} else {
			switch ($this->sort_criterion) {
				case 'labels':
				case 'labels-filename':
				case 'labels-fname':
					$this->sort_criterion = SHOWPLUS_SORT_LABELS_OR_FILENAME; break;
				case 'labels-mtime':
					$this->sort_criterion = SHOWPLUS_SORT_LABELS_OR_MTIME; break;
				case 'filename':
				case 'fname':
					$this->sort_criterion = SHOWPLUS_SORT_FILENAME; break;
				case 'mtime':
					$this->sort_criterion = SHOWPLUS_SORT_MTIME; break;
				case 'random':
					$this->sort_criterion = SHOWPLUS_SORT_RANDOM; break;
				case 'randomlabels':
					$this->sort_criterion = SHOWPLUS_SORT_RANDOMLABELS; break;
				default:
					$this->sort_criterion = SHOWPLUS_SORT_LABELS_OR_FILENAME;
			}
		}
		if (is_numeric($this->sort_order)) {
			$this->sort_order = (int) $this->sort_order;
		} else {
			switch ($this->sort_order) {
				case 'asc':  case 'ascending':  $this->sort_order = SHOWPLUS_SORT_ASCENDING;  break;
				case 'desc': case 'descending': $this->sort_order = SHOWPLUS_SORT_DESCENDING; break;
				default:           $this->sort_order = SHOWPLUS_SORT_ASCENDING;
			}
		}

		// image library
		switch ($this->library) {
			case 'gd':
				if (!ShowPlusLibrarian::is_gd_supported()) {
					$this->library = 'default';
				}
				break;
			case 'imagick':
				if (!ShowPlusLibrarian::is_imagick_supported()) {
					$this->library = 'default';
				}
				break;
			default:
				$this->library = 'default';
		}
		switch ($this->library) {
			case 'default':
				if (ShowPlusLibrarian::is_imagick_supported()) {
					$this->library = 'imagick';
				} elseif (ShowPlusLibrarian::is_gd_supported()) {
					$this->library = 'gd';
				} else {
					$this->library = 'none';
				}
				break;
			default:
		}

		$this->debug = (bool) $this->debug;
	}

	/**
	* Hash value for the parameter object.
	*/
	public function hash() {
		return md5(serialize($this));
	}

	/**
	* Set parameters based on Joomla parameter object.
	*/
	public function setParameters(JRegistry $params) {
		foreach (get_class_vars(__CLASS__) as $name => $value) {
			$this->$name = $params->get($name, $value);
		}
		$this->validate();
	}
}
