var r = new Resumable({target: base + '/publish/addons/'+addon+'/'+release+'/upload', permanentErrors: [403, 404, 415, 500, 501], query: {state: state}});

$('#resumable-error').hide();
if (!r.support) {
	$('#resumable').hide();
	$('#no-resumable').show();
} else {
	$('#no-resumable').hide();
	r.assignBrowse($('#resumable-select'));
	$('#resumable-pause').hide();
	$('#resumable-upload').click(function() {
		r.upload();
		updateState();
	});
	$('#resumable-pause').click(function() {
		r.pause();
		updateState();
	});
	$('#resumable').show();
	updateState();
}

function updateState() {
	if (r.files.length > 0) {
		$('#resumable-files').show();
		$('#resumable-files-none').hide();
	}
	else {
		$('#resumable-files').hide();
		$('#resumable-files-none').show();
	}
	if (r.files.length > 0 && r.progress() < 1) {
		$('#resumable-upload').removeAttr('disabled');
	}
	else {
		$('#resumable-upload').attr('disabled', true);
	}

	if (r.isUploading()) {
		$('#resumable-select').attr('disabled', true);
		$('#resumable-pause').show();
		$('#resumable-upload').hide();
	}
	else {
		$('#resumable-select').removeAttr('disabled');
		$('#resumable-pause').hide();
		$('#resumable-upload').show();
	}
}

r.on('fileAdded', function(file) {
	$('#resumable-files').append($('<li class="list-group-item resumable-item" data-unique="' + file.uniqueIdentifier + '"><span class="resumable-filename">' + file.fileName + '</span><button type="button" class="close" title="' + $('#text-remove').text() + '" aria-hidden="true">&times;</button><span class="resumable-progress"></span></li>'));
	$('li[data-unique="' + file.uniqueIdentifier + '"] .close').click(function() {
		$.each(r.files, function(key, clickedFile) {
			if (clickedFile.uniqueIdentifier === file.uniqueIdentifier) {
				$.ajax(base + '/api/upload/remove', {method: 'post'});
				$('li[data-unique="' + file.uniqueIdentifier + '"]').remove();
				file.cancel();
				updateState();
			}
		});
	});
	updateState();
});

r.on('fileProgress', function(file) {
	var percentage = Math.min(Math.round(file.progress() * 100), 99);
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').text(percentage + '%');
});

r.on('fileSuccess', function(file) {
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').addClass('resumable-complete');
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').text($('#text-uploaded').text());
	$('li[data-unique="' + file.uniqueIdentifier + '"]').removeClass('list-group-item-error');
	$('li[data-unique="' + file.uniqueIdentifier + '"]').addClass('list-group-item-success');
	updateState();
});

r.on('fileError', function(message, file) {
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').addClass('resumable-error');
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').text(message);
	$('li[data-unique="' + file.uniqueIdentifier + '"]').removeClass('list-group-item-success');
	$('li[data-unique="' + file.uniqueIdentifier + '"]').addClass('list-group-item-error');
	updateState();
});

r.on('error', function(message, file) {
	$('#resumable-error').text(message);
	$('#resumable-error').show();
	updateState();
});