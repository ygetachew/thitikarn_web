<?php
defined( '_JEXEC' ) or die( 'Restricted index access' );

global $Itemid;

// menu code
$document	= &JFactory::getDocument();
$renderer	= $document->loadRenderer( 'module' );
$options	 = array( 'style' => "raw" );
$module	 = JModuleHelper::getModule( 'mod_mainmenu' );
$mainnav = false; $subnav = false;
if($mtype == "splitmenu") : 
	$module->params	= "menutype=$menu_name\nstartLevel=0\nendLevel=1";
	$mainnav = $renderer->render( $module, $options );
	$module	 = JModuleHelper::getModule( 'mod_mainmenu' );
	$module->params	= "menutype=$menu_name\nstartLevel=1\nendLevel=9";
	$options	 = array( 'style' => "rounded");
	$subnav = $renderer->render( $module, $options );
elseif($mtype == "suckerfish") : 								      	
	$module->params	= "menutype=$menu_name\nshowAllChildren=1";
	$mainnav = $renderer->render( $module, $options );
endif;

// make sure subnav is empty
if (strlen($subnav) < 10) $subnav = false;

//Are we in edit mode
$editmode = false;
if (JRequest::getCmd('task') == 'edit' ) :
	$editmode = true;
endif;

$topmod_count = ($this->countModules('user1')>0) + ($this->countModules('user2')>0);
$topmod_width = $topmod_count > 0 ? ' w' . floor(99 / $topmod_count) : '';
$bottommod_count = ($this->countModules('user3')>0) + ($this->countModules('user4')>0);
$bottommod_width = $bottommod_count > 0 ? ' w' . floor(99 / $bottommod_count) : '';
$footermod_count = ($this->countModules('user5')>0) + ($this->countModules('user6')>0) + ($this->countModules('user7')>0);
$footermod_width = $footermod_count > 0 ? ' w' . floor(99 / $footermod_count) : '';

$side_column = ($this->countModules('left')>0 or $subnav) ? $side_column : "0";

if ($template_width=="fluid") { 
	$template_width = "width: 95%;margin: 0 auto";
} else {
	$template_width = 'margin: 0 auto; width: ' . $template_width . 'px;';
}

function isIe6() {
	$agent=$_SERVER['HTTP_USER_AGENT'];
	if (stristr($agent, 'msie 6')) return true;
	return false;
}

?>