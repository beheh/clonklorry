// automatic enabling of bootstrap tabs
// based on https://stackoverflow.com/questions/7862233/twitter-bootstrap-tabs-go-to-specific-tab-on-page-reload
var url = document.location.toString();
if (url.match('#')) {
	$('.nav-tabs li:not(.disabled) a[href=#' + url.split('#')[1] + ']').tab('show');
}


$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
	if (e.relatedTarget) {
		var tab = $(e.relatedTarget).attr('href');
		if (!$(tab).children('form').hasClass('dirty')) {
			return;
		}
		var event = $.Event('beforeunload');
		if (!confirm(translation.unsavedChanges)) {
			e.preventDefault();
		} else {
			$(tab).children('form').trigger('reset'); //.trigger('reinitialize.areYouSure');
		}
	}
});

// keep tabs in history and enable forward/backward navigation
// based on http://redotheweb.com/2012/05/17/enable-back-button-handling-with-twitter-bootstrap-tabs-plugin.html
$('a[data-toggle="tab"]').on('click', function (e) {
	var href = $(e.target).attr('href');
	if (href === location.hash || (location.hash === "" && href === $('.nav-tabs li:not(.disabled) a:first').attr('href'))) {
		return;
	}
	history.pushState(null, null, $(e.target).attr('href'));
});

window.addEventListener("popstate", function (e) {
	if (location.hash) {
		activeTab = $('.nav-tabs li:not(.disabled) [href=' + location.hash + ']').tab('show');
	}
	else {
		activeTab = $('.nav-tabs li:not(.disabled) a:first').tab('show');
	}
});

$('.lorry-js-warn').each(function () {
	$(this).click(function (e) {
		var message = $(this).data('warning');
		message = message.replace(/\\n/g, '\n');
		if (!confirm(message)) {
			e.preventDefault();
			$(this).blur();
		}
	});
});

// session flags
function setFlag(flag, persistent) {
	var name = 'lorry_flag_' + flag;
	var date = new Date();
	if (persistent) {
		date.setTime(date.getTime() + (1000 * 60 * 60 * 24 * 365));
	}
	document.cookie = name + '=1; expires=' + date.toUTCString() + '; path=/';
}

$('#greeter-hide').click(function (e) {
	$('#greeter').slideUp();
	setFlag('knows_clonk', true);
	e.preventDefault();
});

// capitalize first character in string
// based on https://stackoverflow.com/questions/1026069/capitalize-the-first-letter-of-string-in-javascript
function ucfirst(string) {
	return string.charAt(0).toUpperCase() + string.slice(1);
}

function setupNullField(checkbox, input, message) {
	input.data('original-value', input.val());
	checkbox.change(function () {
		if ($(this).is(':checked')) {
			input.attr('type', input.data('original-type'));
			var original = input.data('original-value');
			input.val(original ? original : input.attr('placeholder'));
			input.removeAttr('disabled');
		}
		else {
			input.data('original-type', input.attr('type'));
			input.attr('type', 'text');
			input.data('original-value', input.val());
			input.val(message);
			input.attr('disabled', true);
		}
	});
	checkbox.trigger('change');
}