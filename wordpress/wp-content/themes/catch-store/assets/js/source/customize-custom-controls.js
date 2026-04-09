jQuery( function($) {
	 $( ".custom-sortable" ).sortable({
	        stop : function(event, ui){
	          $(this).next().val( $(this).sortable( "toArray" ) ).trigger( 'change' );
	        }
	    });
	  $(".custom-sortable").disableSelection();
});
