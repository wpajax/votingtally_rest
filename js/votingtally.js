jQuery(function($) {
	$( '.tally-button' ).on( 'click', function( e ) {
		e.preventDefault();
		var html = '<img src="' + votingtally.loading + '" alt="Loading Animation" />';
		$( '.voting-tally' ).html( html );
		$.ajax( {
			url: votingtally.rest_url + '/record_vote/',
			type: 'post',
			data: {
				post_id: $( this ).data( 'id' ),
				vote: $( this ).data('action'),
			},
			headers: {
				'X-WP-Nonce': $( this ).data('nonce'),
			},
			success: function( data ) {

			},
			error: function( data ) {

			}
		} )
		.done(function( response ) {
			$( '.voting-tally' ).html( '<h5>' + response.message + '</h5>' );
			
		})
		.fail(function( response ) {
			$( '.voting-tally' ).html( '<h5>' + response.responseJSON.message + '</h5>' );
		})
		.always(function( data ) {
			
		});

			
	} );
});