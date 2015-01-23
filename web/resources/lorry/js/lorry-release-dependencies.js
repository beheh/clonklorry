$(document).ready(function() {
	$.ajax(releaseFileBaseUrl + '/dependencies',
			{
				success: function(data) {
					alert('gottcha');
				},
				error: function() {
					updateDependencies();
					$('#dependencies-none-text').text($('#message-text-dependencies-loading-failed').text());
				},
				complete: function() {
					$('#dependencies-loading').hide();
				}
			});
});

function updateDependencies() {
	var dependencyCount = 0;
	$('#dependencies-count').text(dependencyCount);
	if(dependencyCount > 0) {
		$('#dependencies-none').hide();
		$('#dependencies-count').show();
	}
	else {
		$('#dependencies-none').show();
		$('#dependencies-count').hide();
	}
}