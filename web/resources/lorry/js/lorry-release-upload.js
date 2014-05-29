var releaseFileBaseUrl = base + '/publish/' + addon + '/' + release;
var releaseResumable = new Resumable({target: releaseFileBaseUrl + '/upload?type=data', permanentErrors: [403, 404, 415, 500, 501], query: {state: state}});
var releaseFilesExisting = [];

$('#release-files-error-loading').hide();

$(document).ready(function() {
	$.ajax(releaseFileBaseUrl + '/query',
			{
				success: function(data) {
					releaseFilesExisting = data.files;
					$(releaseFilesExisting).each(function(id, file) {
						releaseFilesAdd(file);
						releaseFilesSuccess(file);
					});
				},
				error: function() {
					$('#release-files-error-loading').show();
				},
				complete: function() {
					updateReleaseResumableState();
					$('#resumable-files-loading').hide();
				}
			});
});

if (!releaseResumable.support) {
	$('#resumable').hide();
	$('#release-files-no-resumable').show();
} else {
	$('#release-files-no-resumable').hide();
	releaseResumable.assignBrowse($('#resumable-select'));
	releaseResumable.assignDrop($('#files'));
	$('#resumable-pause').hide();
	$('#resumable-upload').click(function() {
		releaseResumable.upload();
		updateReleaseResumableState();
	});
	$('#resumable-pause').click(function() {
		releaseResumable.pause();
		updateReleaseResumableState();
	});
	$('#resumable').show();
}

releaseResumable.on('fileAdded', function(file) {
	var cancel = false;
	$.each(releaseFilesExisting, function(key, existingFile) {
		if (file.fileName === existingFile.fileName) {
			cancel = true;
			return false;
		}
	});
	if (!cancel) {
		releaseFilesAdd(file);
		releaseResumable.upload();
		updateReleaseResumableState();
	}
	else {
		alert($('#message-text-file-exists').text());
		file.cancel();
	}
});

releaseResumable.on('fileProgress', function(file) {
	var percentage = Math.min(Math.round(file.progress() * 100), 99);
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').text(percentage + '%');
});

releaseResumable.on('fileSuccess', function(file) {
	releaseFilesSuccess(file);
	updateReleaseResumableState();
});

releaseResumable.on('fileError', function(file, raw) {
	result = $.parseJSON(raw);
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').text(ucfirst(result.message));
	$('li[data-unique="' + file.uniqueIdentifier + '"]').removeClass('list-group-item-success');
	$('li[data-unique="' + file.uniqueIdentifier + '"]').addClass('list-group-item-danger');
	updateReleaseResumableState();
});

function releaseFilesAdd(file) {
	$('#resumable-files').append($('<li class="list-group-item resumable-item" data-unique="' + file.uniqueIdentifier + '"><span class="resumable-filename">' + file.fileName + '</span><button type="button" class="close" title="' + $('#message-text-remove').text() + '" aria-hidden="true">&times;</button><span class="resumable-progress"></span></li>'));
	$('li[data-unique="' + file.uniqueIdentifier + '"] .close').click(function() {
		if (!confirm($('#message-text-confirm-remove').text())) {
			return;
		}
		$.ajax(releaseFileBaseUrl + '/remove', {
			method: 'post',
			data: {state: state, fileName: file.fileName},
			success: function() {
				$('li[data-unique="' + file.uniqueIdentifier + '"]').remove();
				$.each(releaseResumable.files, function(key, clickedFile) {
					if (clickedFile.uniqueIdentifier === file.uniqueIdentifier) {
						file.cancel();
						return false;
					}
				});
				$.each(releaseFilesExisting, function(key, clickedFile) {
					if (clickedFile.uniqueIdentifier === file.uniqueIdentifier) {
						releaseFilesExisting.splice(key, 1);
						return false;
					}
				});

				updateReleaseResumableState();
			},
			error: function() {
				alert($('#message-text-remove-failed').text());
			}
		});
	});
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').text(translation.preparingUpload);
}

function releaseFilesSuccess(file) {
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').text($('#message-text-uploaded').text());
	$('li[data-unique="' + file.uniqueIdentifier + '"]').removeClass('list-group-item-danger');
	$('li[data-unique="' + file.uniqueIdentifier + '"]').addClass('list-group-item-success');
}

function updateReleaseResumableState() {
	if ((releaseResumable.files.length + releaseFilesExisting.length) > 0) {
		$('#resumable-files').show();
		$('#resumable-files-none').hide();
	}
	else {
		$('#resumable-files').hide();
		$('#resumable-files-none').show();
	}
	if (releaseResumable.files.length > 0 && releaseResumable.progress() < 1) {
		$('#resumable-upload').removeAttr('disabled');
	}
	else {
		$('#resumable-upload').attr('disabled', true);
	}

	if (releaseResumable.isUploading()) {
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