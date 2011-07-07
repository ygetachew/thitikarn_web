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

// sort criterion for file system functions
define('SHOWPLUS_FILENAME', 0);  // sort based on file name
define('SHOWPLUS_MTIME', 1);     // sort based on last modified time

// sort order for file system functions
define('SHOWPLUS_ASCENDING', 0);
define('SHOWPLUS_DESCENDING', 1);

class ShowPlusUtility {
	/**
	* Compute an estimate of available memory from memory limit and memory usage.
	* The memory limit value is extracted from php.ini configuration directive memory_limit, and converted to bytes.
	*/
	public static function memory_get_available() {
		static $limit = null;
		if (!isset($limit)) {
			$inilimit = trim(ini_get('memory_limit'));
			if (empty($inilimit)) {  // no limit set
				$limit = false;
			} elseif (ctype_digit($inilimit)) {
				$limit = (int) $inilimit;
			} else {
				$limit = (int) substr($inilimit, 0, -1);
				switch (strtolower(substr($inilimit, -1))) {
					case 'g':
						$limit *= 1024;
					case 'm':
						$limit *= 1024;
					case 'k':
						$limit *= 1024;
				}
			}
		}

		if ($limit !== false) {
			return $limit - memory_get_usage(true);
		} else {
			return false;
		}
	}

	/**
	* Filters those files that have an extension indicating a recognized image format.
	*/
	public static function is_image_file($file) {
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		switch ($extension) {
			case 'jpg': case 'jpeg': case 'JPG': case 'JPEG':
			case 'gif': case 'GIF':
			case 'png': case 'PNG':
				return true;
			default:
				return false;
		}
	}

	/**
	* Filters regular files, skipping those that are hidden.
	* The filename of a hidden file starts with a dot.
	*/
	public static function is_regular_file($filename) {
		return $filename[0] != '.';
	}

	public static function is_remote_path($path) {
		return preg_match('#^https?://#', $path);
	}

	/**
	* Ensures that all components of a URL are URL-encoded.
	*/
	public static function safeurlencode($url) {
		$urlparts = parse_url($url);
		$pattern = '#^([0-9A-Za-z!"$&\'()*+,.:;=@_-]|%[0-9A-Za-z]{2})+$#';
		$segments = explode('/', $urlparts['path']);
		foreach ($segments as &$segment) {
			if (!preg_match($pattern, $segment)) {  // path segment contains a character that has not been URL-encoded
				$segment = rawurlencode($segment);
			}
		}
		$urlparts['path'] = implode('/', $segments);
		if (!empty($urlparts['query'])) {
			if (!preg_match($pattern, $urlparts['query'])) {  // query contains a character that has not been URL-encoded
				$urlparts['query'] = rawurlencode($urlparts['query']);
			}
		}
		return
			$urlparts['scheme'].'://'.
			( empty($urlparts['user']) ? '' : $urlparts['user'].( empty($urlparts['pass']) ? '' : ':'.$urlparts['pass'] ).'@' ).
			$urlparts['host'].$urlparts['path'].
			( empty($urlparts['query']) ? '' : '?'.$urlparts['query'] ).
			( empty($urlparts['fragment']) ? '' : '#'.$urlparts['fragment'] );
	}

	/**
	* List files and directories inside the specified path with modification time.
	* @return An associative array with filenames as keys and timestamps as values.
	*/
	private static function scandirmtime($dir) {
		$dh = @opendir($dir);
		if ($dh === false) {  // cannot open directory
			return false;
		}
		$files = array();
		while (false !== ($filename = readdir($dh))) {
			if (!is_regular_file($filename)) {
				continue;
			}
			$files[$filename] = filemtime($dir.DS.$filename);
		}
		closedir($dh);
		return $files;
	}

	/**
	* List files and directories inside the specified path with custom sorting option.
	* @param folder The directory whose files and subdirectories to list.
	* @param criterion The sort criterion, e.g. filename or last modification time.
	* @param order The sort order, ascending or descending.
	*/
	public static function scandirsorted($folder, $criterion = SHOWPLUS_FILENAME, $order = SHOWPLUS_ASCENDING) {
		switch ($criterion) {
			case SHOWPLUS_FILENAME:
				$files = @scandir($folder, $order);
				if ($files === false) {
					return false;
				}
				return array_filter($files, array(__CLASS__, 'is_regular_file'));  // list files and directories inside the specified path but omit hidden files
			case SHOWPLUS_MTIME:
				$files = self::scandirmtime($folder);
				if ($files === false) {
					return false;
				}
				switch ($order) {
					case SHOWPLUS_ASCENDING:
						asort($files); break;
					case SHOWPLUS_DESCENDING:
						arsort($files); break;
				}
				return array_keys($files);
			default:
				return false;
		}
	}

	/**
	* Checks whether a file or directory exists accepting both lowercase and uppercase extension.
	* @return The file name with extension as found in the file system.
	*/
	public static function file_exists_lenient($path) {
		$realpath = realpath($path);
		if ($realpath !== false) {
			return pathinfo($realpath, PATHINFO_BASENAME);  // file name possibly with extension
		}
		$filename = pathinfo($path, PATHINFO_BASENAME);  // file name possibly with extension
		if (file_exists($path)) {  // file exists as-is, no inspection of extension is necessary
			return $filename;
		}
		$extension = pathinfo($path, PATHINFO_EXTENSION);  // file extension if present
		if ($extension) {  // if file has extension
			$p = strrpos($path, '.');              // starting position of extension (incl. dot)
			$base = substr($path, 0, $p);          // everything up to extension
			$extension = substr($path, $p);        // extension (incl. dot)
			$p = strrpos($filename, '.');
			$filename = substr($filename, 0, $p);  // drop extension from filename
			$extension = strtolower($extension);
			if (file_exists($base.$extension)) {   // file with lowercase extension
				return $filename.$extension;
			}
			$extension = strtoupper($extension);
			if (file_exists($base.$extension)) {   // file with uppercase extension
				return $filename.$extension;
			}
		}
		return false;  // file not found
	}
	
	/**
	* Create directory with index file in it.
	*/
	public static function make_directory($directory) {
		if (!is_dir($directory)) {  // directory does not exist
			@mkdir($directory, 0755, true);  // try to create it
			if (!is_dir($directory)) {
				throw new ShowPlusFolderPermissionException($directory);
			}
			// create an index.html to prevent getting a web directory listing
			@file_put_contents($directory.DS.'index.html', '<html><body bgcolor="#FFFFFF"></body></html>');
		}
	}	
}