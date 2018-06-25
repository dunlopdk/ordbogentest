<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/includes/classescontrollers.php' );

$username = isset( $_POST['username'] ) ? $_POST['username'] : '';
$password = isset( $_POST['password'] ) ? $_POST['password'] : '';
$csrf = isset( $_POST['csrf'] ) ? $_POST['csrf'] : '';

/*
 * Naturally a lookup into a users db, would be placed here.
 * However this is a short test and username is test and password is yes
 */
$response = array( 'text' => "", 'result' => false );

$sessiondata = SessionController::getSessionData( );
$logindata = LoginController::getLoginData( $username );
$loggedin = false;
if( $username== 'test' && SessionController::isMe( $csrf ) ) {
	if( !LoginController::okToLogin( $username) && $logindata['logincount'] >= MAXLOGINCOUNT ) {
		$response['text'] = 'The max login attempts of ' . MAXLOGINCOUNT . ' has been reached, please wait ' . date( 'i' , WAITINGTIME ) . " minutes before trying again"; 
	} else {
		if( $password == '1234' ) {
			$loggedin = true;
		}
	}
}

$_SESSION['loggedin'] = $loggedin;
$response['result'] = $loggedin;

SessionController::updateSessionData( );

echo json_encode( $response );