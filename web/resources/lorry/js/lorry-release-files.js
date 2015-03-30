var filesEndpoint = base + '/api/internal/addons/' + addon + '/' + release;

var releaseResumable = new Resumable({target: filesEndpoint + '/upload?type=data', permanentErrors: [403, 404, 415, 500, 501], query: {state: state}});
var releaseFilesExisting = [];

$(document).ready(function () {
	$.ajax(filesEndpoint + '/query',
			{
				success: function (data) {
					if (typeof data.files != 'object') {
						$('#resumable-files-none-text').text($('#message-text-files-loading-failed').text());
						return;
					}
					loaded = true;
					releaseFilesExisting = data.files;
					$(releaseFilesExisting).each(function (id, file) {
						releaseFilesAdd(file);
						if (file.complete) {
							releaseFilesSuccess(file);
						}
						else {
							releaseFilesContinue(file);
						}
					});
				},
				error: function () {
					$('#resumable-files-none-text').text($('#message-text-files-loading-failed').text());
				},
				complete: function () {
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
	$('#resumable-upload').click(function () {
		releaseResumable.upload();
		updateReleaseResumableState();
	});
	$('#resumable-pause').click(function () {
		releaseResumable.pause();
		updateReleaseResumableState();
	});
	$('#resumable-select').removeAttr('disabled');
	$('#resumable-upload').removeAttr('disabled');
	$('#resumable-pause').removeAttr('disabled');
	$('#resumable').show();
}

releaseResumable.on('fileAdded', function (file) {
	var cancel = false;
	$.each(releaseFilesExisting, function (key, existingFile) {
		if (file.fileName === existingFile.fileName && existingFile.progress !== -1) {
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

releaseResumable.on('fileProgress', function (file) {
	if (file.isComplete())
		return;
	var percentage = Math.min(Math.round(file.progress() * 100), 99);
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').text(percentage + '%');
});

releaseResumable.on('fileSuccess', function (file) {
	releaseFilesSuccess(file);
	updateReleaseResumableState();
});

var errors = {};

releaseResumable.on('fileError', function (file, raw) {
	result = $.parseJSON(raw);
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').text(ucfirst(result.message));
	$('li[data-unique="' + file.uniqueIdentifier + '"]').removeClass('list-group-item-success');
	$('li[data-unique="' + file.uniqueIdentifier + '"]').addClass('list-group-item-danger');
	errors[file.uniqueIdentifier] = true;
	updateReleaseResumableState();
});

function releaseFilesAdd(file) {
	$('li[data-filename="' + file.fileName + '"]').remove();
	$('#resumable-files').append($('<li class="list-group-item resumable-item" data-unique="' + file.uniqueIdentifier + '" data-filename="' + file.fileName + '"><span class="resumable-filename">' + file.fileName + '</span><button type="button" class="close" title="' + $('#message-text-remove').text() + '" aria-hidden="true">&times;</button><span class="resumable-progress"></span></li>'));
	$('li[data-unique="' + file.uniqueIdentifier + '"] .close').click(function () {
		var errorFile = errors[file.uniqueIdentifier];
		if (((file.isComplete && file.isComplete()) || file.complete) && !errorFile) {
			if (!confirm($('#message-text-confirm-remove').text())) {
				return;
			}
		}
		$.each(releaseResumable.files, function (key, clickedFile) {
			if (clickedFile.uniqueIdentifier === file.uniqueIdentifier) {
				file.cancel();
				return;
			}
		});
		$.ajax(filesEndpoint + '/remove', {
			method: 'post',
			dataType: 'json',
			data: {state: state, fileName: file.fileName, uniqueIdentifier: file.uniqueIdentifier}
		}).always(function (result) {
			if (result.file === 'removed' || result.status === 404 || errorFile) {
				$('li[data-unique="' + file.uniqueIdentifier + '"]').remove();
				$.each(releaseFilesExisting, function (key, clickedFile) {
					if (clickedFile.uniqueIdentifier === file.uniqueIdentifier) {
						releaseFilesExisting.splice(key, 1);
						return false;
					}
				});
				errors[file.uniqueIdentifier] = false;
				updateReleaseResumableState();
			}
			else {
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

function releaseFilesContinue(file) {
	$('li[data-unique="' + file.uniqueIdentifier + '"] .resumable-progress').text($('#message-text-continue').text());
	$('li[data-unique="' + file.uniqueIdentifier + '"]').removeClass('list-group-item-danger');
}

function updateReleaseResumableState() {
	var fileCount = releaseResumable.files.length + releaseFilesExisting.length;
	$('#resumable-files-count').text(fileCount);
	if (fileCount > 0) {
		$('#resumable-files').show();
		$('#resumable-files-none').hide();
		$('#resumable-files-count').show();
        $('.files-some').show();
        $('.files-none').hide();
	}
	else {
		$('#resumable-files').hide();
		$('#resumable-files-none').show();
		$('#resumable-files-count').hide();
        $('.files-none').show();
        $('.files-some').hide();
	}

	if (releaseResumable.isUploading()) {
		$('#resumable-pause').show();
		$('#resumable-upload').hide();
	}
	else {
		$('#resumable-pause').hide();
		$('#resumable-upload').show();
	}
}

$(window).on('beforeunload', function () {
	if (releaseResumable.isUploading()) {
		return translation.stillUploading;
	}
});