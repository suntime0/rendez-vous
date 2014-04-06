( function( $ ) {
	/**
	 * My first idea wa to use Backbone to build the interface
	 * I think the Members profile page as they don't accept
	 * action_variables in url, it's a good place to do so.
	 * As it's quite long to build, this first version of the 
	 * plugin will use query vars like ?key=id
	 */

	$( '#rendez-vous-attendees-prefs :checkbox' ).on( 'click', function(){
		if ( $( this ).hasClass( 'none-resets-cb' ) && $( this ).prop( 'checked' ) ) {

			$( this ).parents( '.edited' ).first().find( ':checkbox' ).each( function(){
				$( this ).prop( 'checked', false );
			} );

			$( this ).prop( 'checked', true );
		} else {
			$( '.none-resets-cb' ).prop( 'checked', false );
		}
	} );

	$( '#rendez-vous-edit-privacy' ).on( 'click', function(){
		if ( $( this ).prop( 'checked' ) ) {
			$( '#rendez-vous-edit-activity' ).prop( 'checked', false ).prop( 'disabled', true );
		} else {
			$( '#rendez-vous-edit-activity' ).prop( 'disabled', false );
		}
	} );

	$( '#rendez-vous-list li.private a').on( 'click', function(e){
		if ( $( this ).prop( 'href' ).indexOf( '#noaccess' ) != -1 ) {
			e.preventDefault();

			alert( rendez_vous_vars.noaccess );
			return;
		}
	} );

	$( '.delete-rendez-vous' ).on( 'click', function(e) {
		if ( false == confirm( rendez_vous_vars.confirm ) ) {
			e.preventDefault();
			return;
		}
	});

} )( jQuery );
