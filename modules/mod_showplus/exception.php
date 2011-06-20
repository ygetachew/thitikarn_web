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

class ShowPlusException extends Exception {
	/** The language key for the exception. */
	protected function getErrorKey() {
		return false;
	}

	/** The standard error message text for the exception. */
	protected function getErrorText() {
		return false;
	}

	protected function getErrorMessage($errortext) {
		return $errortext;
	}

	/** The text of a critical error message. */
	public function __construct() {
		$errorheader = JText::_('SHOWPLUS_EXCEPTION');
		if ($errorheader == 'SHOWPLUS_EXCEPTION') {  // error message not mapped to language string
			$errorheader = '[showplus] Critical error';
		}

		$errorkey = $this->getErrorKey();
		if ($errorkey !== false) {
			$errormessage = JText::_($errorkey);  // use language-specific error message text if available
			if ($errormessage == $errorkey) {  // error message not available in language
				$errormessage = $this->getErrorText();  // use standard (English) error message
			}
			$errormessage = $this->getErrorMessage($errormessage);
		} else {
			$errormessage = parent::getMessage();
		}
		$this->message = '<p><strong>'.$errorheader.':</strong> '.$errormessage.'</p>';
	}
}

/** Thrown when a text file is not encoded with UTF-8. */
class ShowPlusEncodingException extends ShowPlusException {
	private $textfile;

	public function __construct($textfile) {
		$this->textfile = $textfile;
		parent::__construct();
	}

	protected function getErrorMessage($errortext) {
		return sprintf($errortext, '<kbd>'.str_replace(JPATH_ROOT, '<em>root</em>', $this->textfile).'</kbd>');
	}

	protected function getErrorKey() {
		return 'SHOWPLUS_EXCEPTION_ENCODING';
	}

	protected function getErrorText() {
		return 'Text files are assumed to have UTF-8 character encoding but %s uses a different encoding.';
	}
}

/** Thrown when a URL contains invalid characters. */
class ShowPlusURLEncodingException extends ShowPlusException {
	private $url;

	public function __construct($url) {
		$this->url = $url;
		parent::__construct();
	}

	protected function getErrorMessage($errortext) {
		return sprintf($errortext, '<kbd>'.$this->url.'</kbd>');
	}

	protected function getErrorKey() {
		return 'SHOWPLUS_EXCEPTION_URLENCODING';
	}

	protected function getErrorText() {
		return 'URLs are assumed to have been URL-encoded but the URL %s appears to have an invalid character.';
	}
}

class ShowPlusFolderException extends ShowPlusException {
	protected $folder;

	public function __construct($folder) {
		$this->folder = $folder;
		parent::__construct();
	}

	protected function getErrorMessage($errortext) {
		return sprintf($errortext, '<kbd>'.str_replace(JPATH_ROOT, '<em>root</em>', $this->folder).'</kbd>');
	}
}

/** Thrown when the image folder does not exist or is inaccessible. */
class ShowPlusImageFolderException extends ShowPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SHOWPLUS_EXCEPTION_FOLDER';
	}

	protected function getErrorText() {
		return 'Image folder %s specified in the administration back-end does not exist or is inaccessible.';
	}
}

/** Thrown when the thumbnail folder is not valid. */
class ShowPlusThumbFolderException extends ShowPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SHOWPLUS_EXCEPTION_FOLDER_THUMB';
	}

	protected function getErrorText() {
		return 'Thumb folder %s specified in administration back-end is expected to be a relative path w.r.t. the image folder.';
	}
}

/** Thrown when the extension lacks permissions to create the folder for thumbnail images. */
class ShowPlusFolderPermissionException extends ShowPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SHOWPLUS_EXCEPTION_PERMISSION';
	}

	protected function getErrorText() {
		return 'Insufficient file system permissions to create the folder %s.';
	}
}

/** Thrown when a required library dependency is not available. */
class ShowPlusLibraryUnavailableException extends ShowPlusException {
	protected function getErrorKey() {
		return 'SHOWPLUS_EXCEPTION_LIBRARY';
	}

	protected function getErrorText() {
		return 'The Graphics Draw (gd) or ImageMagick (imagick) image processing library has to be enabled in the PHP configuration to generate thumbnails.';
	}
}

/** Thrown when the extension attempts to allocate memory for a resource with prohibitively large memory footprint. */
class ShowPlusOutOfMemoryException extends ShowPlusException {
	private $required;
	private $available;
	private $resourcefile;

	public function __construct($required, $available, $resourcefile) {
		$this->required = $required;
		$this->available = $available;
		$this->resourcefile = $resourcefile;
		parent::__construct();
	}

	protected function getErrorKey() {
		return 'SHOWPLUS_EXCEPTION_MEMORY';
	}

	protected function getErrorText() {
		return 'Insufficient memory to carry out the requested operation on %3$s, %1$d bytes required, %2$d bytes available.';
	}

	protected function getErrorMessage($errortext) {
		return sprintf($errortext, $this->required, $this->available, '<kbd>'.str_replace(JPATH_ROOT, '<em>root</em>', $this->resourcefile).'</kbd>');
	}
}