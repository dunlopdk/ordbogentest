function login( ) {
	jQuery.post( '/login.php', {
		username : jQuery( '#name' ).val( ),
		password : jQuery( '#password' ).val( ),
		csrf : jQuery( '#csrf' ).val( ),
	}, function( response ) {
		var json = jQuery.parseJSON( response );
		console.log( json.result );

		if( json.result == false ) {
			jQuery('#errortext').html( json.text ).css( { 'display' : 'block' } );
		} else {
			location.reload( true );
		}
	} );
}