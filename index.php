<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/includes/classescontrollers.php' );

if( count( SessionController::getSessionData( ) ) == 0 ) {
	SessionController::insertSessionData( );
}
$sessiondata = SessionController::getSessionData( );
$template = file_get_contents( $_SERVER['DOCUMENT_ROOT'] . "/index.htm" );
$template = str_replace( '[sitetitle]', SITETITLE, $template );

$textarea = '';
if( isset( $_SESSION['loggedin'] ) && $_SESSION['loggedin'] == true ) {
	$textarea= file_get_contents( $_SERVER['DOCUMENT_ROOT'] . "/restricted.htm" );
} else {
	$textarea= file_get_contents( $_SERVER['DOCUMENT_ROOT'] . "/login.htm" );
	$textarea= str_replace( '[csrf]', $sessiondata['csrf'], $textarea);
}

$template = str_replace( '[textarea]', $textarea, $template );

echo $template;