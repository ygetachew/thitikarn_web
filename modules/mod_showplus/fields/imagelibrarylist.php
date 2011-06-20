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

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

// This file is fully compatible with PHP 4.

if (!function_exists('is_gd_supported')) {
	/**
	* True if the server has GD library enabled with JPEG, PNG and GIF read support.
	*/
	function is_gd_supported() {
		$supported = extension_loaded('gd');
		if (!$supported) {
			return false;
		}

		$supported = function_exists('gd_info');  // might fail in rare cases even if GD is available
		if (!$supported) {
			return false;
		}
		$gd = gd_info();
		$supported = isset($gd['GIF Read Support']) && $gd['GIF Read Support']
				&& isset($gd['GIF Create Support']) && $gd['GIF Create Support']
				&& (isset($gd['JPG Support']) && $gd['JPG Support'] || isset($gd['JPEG Support']) && $gd['JPEG Support'])
				&& isset($gd['PNG Support']) && $gd['PNG Support'];
		return $supported;
	}
}

if (!function_exists('is_imagick_supported')) {
	function is_imagick_supported() {
		$supported = extension_loaded('imagick');
		if (!$supported) {
			return false;
		}

		$supported = class_exists('Imagick');
		return $supported;
	}
}

/**
* Renders a control that lists all supported image processing libraries.
* This class represents a user-defined control in the administration backend.
*/
class JElementImageLibraryList extends JElement {
	/**
	* Element type.
	*/
	var $_name = 'ImageLibraryList';

	/**
	* Generates an HTML @c select list with options.
	* @param name The value of the HTML name attribute.
	* @param attribs Additional HTML attributes for the <select> tag.
	* @param selected The key that is selected.
	* @return HTML for the select list.
	*/
	/*private*/ function renderHtmlSelect($options, $name, $attribs = null, $selected = null, $idtag = false) {
		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		$id = $name;
		if ($idtag) {
			$id = $idtag;
		}
		$id = str_replace('[','',$id);
		$id	= str_replace(']','',$id);

		$html = '<select name="'. $name .'" id="'. $id .'" '. $attribs .'>';
		foreach ($options as $value => $textkey) {
			$html .= '<option '.( $selected == $value ? 'selected="selected" ' : '' ).'value="'.$value.'">'.JText::_($textkey).'</option>';
		}
		$html .= '</select>';
		return $html;
	}

	/*private*/ function renderNone($text, $name, $attribs = null, $idtag = false) {
		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		$id = $name;
		if ($idtag) {
			$id = $idtag;
		}
		$id = str_replace('[','',$id);
		$id	= str_replace(']','',$id);

		return '<span style="color:red" '.$attribs.'><input type="hidden" name="'.$name.'" id="'.$id.'" value="none" />'.JText::_($text).'</span>';
	}

	/*public*/ function fetchElement($name, $value, &$node, $control_name) {
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="inputbox"' );

		// user-friendly names for image processing libraries
		$items = array();
		foreach ($node->children() as $o) {
			$val = $o->attributes('value');
			$textkey = $o->data();
			$items[$val] = $textkey;
		}

		// test which image processing libraries are supported
		$supported = array();
		if (is_gd_supported()) {
			$supported['gd'] = 'GD';
		}
		if (is_imagick_supported()) {
			$supported['imagick'] = 'ImageMagick';
		}

		if (empty($supported)) {  // no library is supported
			if (isset($items['none'])) {
				$textkey = $items['none'];
			} else {
				$textkey = 'none';
			}
			return $this->renderNone($textkey, $control_name.'['.$name.']', $class, $control_name.$name);
		} else {  // at least a single library is supported
			$supported['default'] = 'default';
			foreach ($items as $key => $textkey) {
				if (isset($supported[$key])) {
					$supported[$key] = $textkey;
				}
			}
			return $this->renderHtmlSelect($supported, $control_name.'['.$name.']', $class, $value, $control_name.$name);
		}
	}
}