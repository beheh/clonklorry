var imageResumable = new Resumable({target: base + '/api/internal', permanentErrors: [403, 404, 415, 500, 501], query: {state: state}});

var resumableBtn;
var resumableTarget;

function setupImageUpload(btn, file) {
	if(imageResumable.support) {
		imageResumable.assignBrowse(btn);
		imageResumable.assignDrop(btn);
	}
	resumableBtn = btn;
	resumableTarget = file;
}

imageResumable.on('fileAdded', function (file) {
	resumableBtn.attr('disabled', true);
	resumableBtn.data('previous-message', resumableBtn.html());
	resumableBtn.html($('#message-text-uploading').html());
	imageResumable.upload();
});

function imageUploadCancel(btn) {
	btn.removeAttr('disabled');
	resumableBtn.html(btn.data('previous-message'));
}

imageResumable.on('fileError', function (file, raw) {
	imageUploadCancel(resumableBtn);
});