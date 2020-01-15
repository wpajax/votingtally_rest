jQuery(function($) {
	$( '.tally-button' ).on( 'click', function( e ) {
		e.preventDefault();
		var html = '<img src="' + votingtally.loading + '" alt="Loading Animation" />';
		$( '.voting-tally' ).html( html );
		$.post(
			votingtally.ajaxurl,
			{
				action: 'votingtally_record_vote',
				nonce: $( this ).data( 'nonce' ),
				post_id: $( this ).data( 'id' ),
				vote: $( this ).data('action')
			},
			function() {

			} )
			.done(function() {
				$( '.voting-tally' ).html( '<h5>' + votingtally.vote_recorded + '</h5>' );
			})
			.fail(function() {
				$( '.voting-tally' ).html( '<h5>' + votingtally.vote_error + '</h5>' );
			})
			.always(function() {
				
			})
	} );
});