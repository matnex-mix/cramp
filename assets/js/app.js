function openDeleteOption( url ){
	window.currentIDL = url;
	$('#delete-container').modal('show');
}

function openRenameOption( url, name ){
	window.currentIRL = url;
	$('#new_name').val( name );
	$('#rename-container').modal('show');
}