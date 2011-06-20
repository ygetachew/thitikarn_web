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
defined('_JEXEC') or die('Restricted access');

if (version_compare(JVERSION, '1.6') < 0) {
	if (version_compare(PHP_VERSION, '5.2.0') < 0) {  // PHP version check for Joomla 1.5
		die('showplus requires PHP version 5.2 or later.');
	}

	if (!(version_compare(JVERSION, '1.5.19') >= 0 && JPluginHelper::isEnabled('system', 'mtupgrade')) && !file_exists(dirname(__FILE__).DS.'js'.DS.'mootools.override.js')) {
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('SHOWPLUS_EXCEPTION').': '.JText::sprintf('SHOWPLUS_EXCEPTION_MOOTOOLS', 'mootools.override.js', str_replace(array(JPATH_ROOT, DS), array('', '/'), dirname(__FILE__).DS.'js')), 'error');
		return;
	}
}

require_once dirname(__FILE__).DS.'helper.php';

// get parameters from the module's configuration
$settings = new ShowPlusParameters();
$settings->setParameters($params);
$helper = new ShowPlusSlideshow($settings);

// include the template for display
require JModuleHelper::getLayoutPath('mod_showplus');