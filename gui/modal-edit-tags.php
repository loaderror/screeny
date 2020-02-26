<div class="modal fade" id="editTagsModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header p-2">
				<h5 class="modal-title">Screenshot Tags bearbeiten</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body p-2">
				<textarea class="form-control" id="editTagsField" rows="5" placeholder="tag1, tag2, tag3, ..." autofucus></textarea>
			</div>
			<div class="modal-footer p-2">
				<button id="saveTags" type="button" class="btn btn-sm btn-primary">speichern</button>
			</div>
		</div>
	</div>
</div>

<script>
$( function()
{
	let entryID;
	$( "#editTagsModal" ).on( "show.bs.modal", function( event )
	{
		const button = $( event.relatedTarget );
		entryID = button.data( 'entryid' );
		
		$( "#editTagsField" ).val( '' );
		$.post( "ajax.php", { fnc:"getEntry", entryID:entryID }, function( data )
		{
			$( "#editTagsField" ).val( data['tags'] ).focus();
		} );
	} );
	
	$( "#saveTags" ).click( function()
	{
		const tags = $( "#editTagsField" ).val();
		$( "#editTagsModal" ).modal( "hide" );
		
		$.post( "ajax.php", { fnc:"setTags", entryID:entryID, tags:tags }, function( data )
		{
			if( data !== "failed" )
			{
				$( ".entry" + entryID + " .tags" ).html( data );
			}
		} );
	} );
} );
</script>
