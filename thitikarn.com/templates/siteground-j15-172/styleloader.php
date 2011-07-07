<?php
defined( '_JEXEC' ) or die( 'Restricted index access' );
$fontstyle = "f-" . $default_font;
$mtype = $menu_type;
$cookie_prefix = "nbus-";
$session = &JFactory::getSession();

//load menu type
if ($session->get($cookie_prefix. 'mtype')) {
    $mtype = $session->get($cookie_prefix. 'mtype');
} elseif (isset($_COOKIE[$cookie_prefix. 'mtype'])) {
    $mtype = htmlentities(JRequest::getVar( $cookie_prefix.'mtype', '', 'COOKIE', 'STRING'));
}

$menu_type = $mtype;

$thisurl = $_SERVER['PHP_SELF'] . rebuildQueryString();

function rebuildQueryString() {
  $ignores = array("contraststyle","fontstyle","widthstyle");
  if (!empty($_SERVER['QUERY_STRING'])) {
      $parts = explode("&", $_SERVER['QUERY_STRING']);
      $newParts = array();
      foreach ($parts as $val) {
          $val_parts = explode("=", $val);
          if (!in_array($val_parts[0], $ignores)) {
            array_push($newParts, $val);
          }
      }
      if (count($newParts) != 0) {
          $qs = implode("&amp;", $newParts);
      } else {
          return "?";
      }
      return "?" . $qs . "&amp;"; // this is your new created query string
  } else {
      return "?";
  } 
}
?>
