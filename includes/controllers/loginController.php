<?php

class LoginController {
	
	public static function getLoginData( $username) {
		$mydb = new myDB( );
		$logindata = $mydb->getRow( "SELECT * FROM LoginAttempts WHERE username = '" . $username. "'" );
		$mydb = null;
		return $logindata;
	}
	
	public static function insertLoginData( $username) {
		$mydb = new myDB( );
		$result = $mydb->insert( "LoginAttempts", array(
				"`username`" => $username,
				"`time`" => time( ),
				"`logincount`" => 1
		), '', false );

		$mydb = null;
		return $result;
	}
	
	public static function updateLoginData( $username ) {
		$logindata = self::getLoginData( $username );
		if( count( $logindata) == 0 ) {
			return self::insertLoginData( $username);
		}

		$logincount = 1;

		if( $logindata['time'] > time( ) - WAITINGTIME ) {
			$logincount= $logindata['logincount'] + 1;
		}

		$mydb = new myDB( );
		$result = $mydb->update( "LoginAttempts", array(
				"`time`" => time( ),
				"`logincount`" => $logincount
		), array(
				array( "field" => "username", "operator" => "=", "value" => $username )
		) );
		
		$mydb = null;
		return $result;
	}

	public static function okToLogin( $username ) {
		$logindata = self::getLoginData( $username );

		if( count( $logindata ) == 0 ) {
			return self::insertLoginData( $username );
		}

		if( $logindata['logincount'] > MAXLOGINCOUNT && $logindata['time'] > time( ) - WAITINGTIME ) {
			self::updateLoginData( $username );
			return false;
		}

		self::updateLoginData( $username );

		return true;
	}
}