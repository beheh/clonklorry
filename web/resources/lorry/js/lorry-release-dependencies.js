var dependenciesEndpoint = base + '/api/internal/addons/' + addon + '/' + release + '/dependencies';

var lorryTokenizer = function (a) {
	return [a.title, a.abbreviation, a.short];
};

var dependencies = [];

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
	prefetch: {url: base + '/api/v0/addons/' + game + '', filter: prefetchFilter},
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
}).on('typeahead:selected', findDependency).on('keypress', (function (e) {
	if (e.keyCode === 13) {
		findDependency();
		return false;
	}
}));

$('#add-dependency-find').click(findDependency);

$(document).ready(function () {
	$.ajax(dependenciesEndpoint,
			{
				success: function (data) {
				},
				error: function () {
					$('#dependencies-none-text').text($('#message-text-dependencies-loading-failed').text());
				},
				complete: function () {
					updateDependencies();
					$('#dependencies-loading').hide();
				}
			});
});

function findDependency() {
	var focus = $(document.activeElement).is($('#add-dependency'));
	addDependency($('#add-dependency').val().toLowerCase());

}

function findDependency(string) {
	$('#add-dependency').attr('disabled', true);
	$('#add-dependency-find').attr('disabled', true);
	$.ajax(base + '/api/v0/addons/' + game + '/' + string, {
		success: function (data) {
			var short = data.responseJSON ? data.responseJSON.short : false;
			if (short) {
				addDependency(short);
			}
			$('#add-dependency').removeAttr('disabled');
		},
		error: function (data) {
			var message = data.responseJSON && data.responseJSON.message ? data.responseJSON.message : $('#message-text-dependencies-loading-releases-failed').text();
			$('#add-dependency').removeAttr('disabled');
			if (focus) {
				$('#add-dependency').focus();
			}
		},
		complete: function () {
			$('#add-dependency-find').removeAttr('disabled');
		}
	});
}

function updateDependencies() {
	var dependencyCount = dependencies.length;
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