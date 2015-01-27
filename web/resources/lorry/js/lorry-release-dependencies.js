var lorryTokenizer = function (a) {
	return [a.title, a.abbreviation, a.short];
};

function prefetchFilter(parsedResponse) {
	var addons = [];
	$(parsedResponse).each(function (key, addon) {
		addons.push(addon);
	});
	console.log(addons);
	return addons;
}

var publicAddons = new Bloodhound({
	datumTokenizer: lorryTokenizer,
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	local: [{title: 'Hazard', abbreviation: 'hzck', short: 'hazard'}, {title: 'Codename: Modern Combat', abbreviation: 'cmc', short: 'moderncombat'}], // example
	prefetch: {url: base + '/api/v0/addons/' + game + '.json?headless', filter: prefetchFilter},
	//		remote: {url: '{{base}}/search.json?for=%QUERY', filter: prefetchFilter},
	thumbprint: '3' // hash of all addon versions
});

publicAddons.initialize();

$('#add-dependency').typeahead(null, {
	name: 'public-addons',
	displayKey: 'short',
	source: publicAddons.ttAdapter(),
	templates: {
		suggestion: function (a) {
			return '<strong>' + a.short + '</strong> - ' + a.title + '';
		}
	}
}).on('typeahead:selected', selectDependencyAddon).on('keypress', (function(e) {
        if (e.keyCode === 13) {
            selectDependencyAddon();
            return false;
        }
}));

$('#add-dependency-reset').click(resetDependencyVersions);
$('#add-dependency-select').click(selectDependencyAddon);

resetDependencyVersions();

function resetDependencyVersions() {
	$('#add-dependency').removeAttr('disabled');
	$('#add-dependency-select').removeAttr('disabled');
	$('#add-dependency-version').empty();
	$('#add-dependency-version').append($('<option disabled>' + $('#message-text-dependencies-select-first').text() + '</option>'));
	$('#add-dependency-version').attr('disabled', true);
}

function selectDependencyAddon() {
	$('#add-dependency').blur();
	$('#add-dependency').attr('disabled', true);
	$('#add-dependency-select').attr('disabled', true);
	$('#add-dependency-version').empty();
	$('#add-dependency-version').append($('<option disabled>' + $('#message-text-dependencies-loading-releases').text() + '</option>'));
	$.ajax(base + '/api/v0/addons/' + game + '/' + $('#add-dependency').val().toLowerCase() + '.json', {
		success: function (data) {
			$('#add-dependency-version').removeAttr('disabled');
			$('#add-dependency-version').empty();
			$('#add-dependency-version').append($('<option disabled>{% trans %}Select a version&hellip;{% endtrans %}</option>'));
		},
		error: function (data) {
			var message = data.responseJSON && data.responseJSON.message ? data.responseJSON.message : $('#message-text-dependencies-loading-releases-failed').text();
			$('#add-dependency').removeAttr('disabled');
			$('#add-dependency-select').removeAttr('disabled');
			$('#add-dependency-version').empty();
			$('#add-dependency-version').append($('<option disabled>' + message + '</option>'));
			$('#add-dependency').focus();
		}
	});
}


$(document).ready(function () {
	$.ajax(releaseFileBaseUrl + '/dependencies',
			{
				success: function (data) {
					alert('gottcha');
				},
				error: function () {
					updateDependencies();
					$('#dependencies-none-text').text($('#message-text-dependencies-loading-failed').text());
				},
				complete: function () {
					$('#dependencies-loading').hide();
				}
			});
});

function updateDependencies() {
	var dependencyCount = 0;
	$('#dependencies-count').text(dependencyCount);
	if (dependencyCount > 0) {
		$('#dependencies-none').hide();
		$('#dependencies-count').show();
	}
	else {
		$('#dependencies-none').show();
		$('#dependencies-count').hide();
	}
}