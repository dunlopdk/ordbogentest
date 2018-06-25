<?php
class SessionController {

	public static function startSession( ) {
		if( !session_id( ) ) {
			session_start( );
		}
	}

	public static function getSessionData( ) {
		self::startSession( );
		$mydb = new myDB( );
		$sessiondata = $mydb->getRow( "SELECT * FROM SessionData WHERE sessionid = '" . session_id( ) . "'");
		$mydb = null;
		return $sessiondata;
	}

	public static function insertSessionData( ) {
		self::startSession( );
		$mydb = new myDB( );
		$result = $mydb->insert( "SessionData", array(
				"`sessionid`" => session_id( ),
				"`ip`" => $_SERVER['REMOTE_ADDR'],
				"`csrf`" => self::createToken( )
		), '', false );

		$mydb = null;
		return $result;
	}

	public static function updateSessionData( ) {
		$sessiondata = self::getSessionData( );
		if( !is_array( $sessiondata ) ) {
			return self::insertSessionData( );
		}
		$mydb = new myDB( );
		$newtoken = self::createToken( );
		$result = $mydb->update( "SessionData", array(
				"`csrf`" => $newtoken
		), array(
				array( "field" => "sessionid", "operator" => "=", "value" => session_id( ) ),
				array( "field" => "ip", "operator" => "=", "value" => $_SERVER['REMOTE_ADDR'] )
		) );
		$_SESSION['csrf'] = $newtoken;

		$mydb = null;
		return $result;
	}

	public static function isMe( $csrf ) {
		$sessiondata = self::getSessionData( );
		if( !is_array( $sessiondata ) ) {
			return false;
		}

		$return = true;
		foreach( $sessiondata as $key => $value ) {
			switch( $key ) {
				case 'ip' :
					if( $value != $_SERVER['REMOTE_ADDR'] ) {
						$return = false;
					}
					break;
				case 'csrf' :
					if( $value != $csrf ) {
						$return = false;
					}
					break;
				case 'sessionid' :
					if( $value != session_id( ) ) {
						$return = false;
					}
					break;
			}
		}
		return $return;
	}

	/* This function should really be in a different class or a generic functions file */ 
	public static function createToken( ) {
		if( version_compare( phpversion( ), '7.0.0', '<' ) ) {
			if( function_exists( 'mcrypt_create_iv' ) ) {
				return bin2hex( mcrypt_create_iv( 32, MCRYPT_DEV_URANDOM ) );
			} else {
				return bin2hex( openssl_random_pseudo_bytes( 32 ) );
			}
		} else {
			return bin2hex( random_bytes( 32 ) );
		}
	}
}